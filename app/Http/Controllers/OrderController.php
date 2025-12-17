<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('voucher')->orderBy('id', 'desc')->get();
        return view('orders.index', compact('orders'));
    }

    /**
     * Display a listing of the resource.
     */
    public function pickup()
    {
        $orders = Order::where('status', 'packaging')
            ->whereHas('shipping', function ($query) {
                $query->whereNotNull('order_number')
                    ->where('order_number', '!=', '');
            })
            ->orderByDesc('id')
            ->get();

        return view('orders.pickup', compact('orders'));
    }

    /**
     * Print shipping labels
     */
    public function printLabel()
    {
        $orders = Order::where('status', 'packaging')
            ->whereHas('shipping', function ($query) {
                $query->whereNotNull('order_number')
                    ->where('order_number', '!=', '');
            })
            ->orderByDesc('id')
            ->get();

        return view('orders.printLabel', compact('orders'));
    }


    public function labelStore(Request $request)
    {
        $request->validate([
            'selected_orders' => 'required|array|min:1',
        ]);

        $orderNos = implode(',', $request->selected_orders);

        $query = http_build_query([
            'page' => 'page_6',
            'order_no' => $orderNos,
        ]);

        try {
            $response = Http::withHeaders([
                'Accept'    => 'application/json',
                'x-api-key' => env('RAJAONGKIR_DELIVERY_API_KEY'),
            ])->post("https://api-sandbox.collaborator.komerce.id/order/api/v1/orders/print-label?$query");

            $data = $response->json();

            if ($response->failed() || ($data['meta']['status'] ?? '') === 'error') {
                return response()->json([
                    'success' => false,
                    'message' => $data['meta']['message'] ?? 'Gagal generate label',
                    'detail'  => $data['data'] ?? '',
                ], 422);
            }

            $pdfPath = $data['data']['path'] ?? null;
            if ($pdfPath) {
                $url = 'https://api-sandbox.collaborator.komerce.id/order' . $pdfPath;
                return response()->json(['success' => true, 'url' => $url]);
            }

            return response()->json(['success' => false, 'message' => 'File path tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }





    /**
     * Request order to komship
     */
    public function orderRequest(Order $order)
    {
        try {
            // ✅ Pastikan relasi lengkap
            if (!$order->shipping || !$order->shipping->shippingInfo || !$order->shipping->shippingOption) {
                return back()->with('error', 'Data pengiriman belum lengkap untuk order ini.');
            }

            // ✅ Bangun item details
            $itemDetailsKomship = $order->items->map(function ($item) {
                return [
                    "product_name" => $item->name,
                    "product_variant_name" => ($item->variant ?? '-') . ' - ' . ($item->size ?? '-'),
                    "product_price" => $item->price,
                    "product_weight" => intval(optional($item->variantSize->size)->label ?? 0),
                    "product_width" => 0,
                    "product_height" => 0,
                    "product_length" => 0,
                    "qty" => $item->qty,
                    "subtotal" => $item->total,
                ];
            })->toArray();

            // ✅ Persiapkan data pengiriman (pakai $order, bukan $orderItem)
            $shippingInfo = $order->shipping->shippingInfo;
            $shippingOption = $order->shipping->shippingOption;

            // ✅ Request ke Komship
            $response = Http::withHeaders([
                'x-api-key' => env('RAJAONGKIR_DELIVERY_API_KEY'),
                'Accept' => 'application/json',
            ])->post('https://api-sandbox.collaborator.komerce.id/order/api/v1/orders/store', [
                "order_date" => now()->toDateTimeString(),
                "brand_name" => "TernakSyams",
                "shipper_name" => "TernakSyams",
                "shipper_phone" => "-",
                "shipper_destination_id" => 2163,
                "shipper_address" => "Komplek Kramayudha. Blok D5 No.10, RT003/018, Mekarsari, Cimanggis, Depok, 16452",
                "shipper_email" => "ternaksyams.id@gmail.com",
                "receiver_name" => $shippingInfo->name,
                "receiver_phone" => $shippingInfo->phone,
                "receiver_destination_id" => $shippingInfo->destination_id,
                "receiver_address" => $shippingInfo->address,
                "receiver_email" => $shippingInfo->email,
                "shipping" => strtoupper($shippingOption->expedition),
                "shipping_type" => strtoupper($shippingOption->service),
                "payment_method" => "BANK TRANSFER",
                "shipping_cost" => intval($shippingOption->cost),
                "shipping_cashback" => 0,
                "service_fee" => 0,
                "additional_cost" => 0,
                "grand_total" => $order->items->sum('total') + $shippingOption->cost,
                "cod_value" => 0,
                "insurance_value" => 0,
                "order_details" => $itemDetailsKomship,
            ]);

            // ✅ Cek hasil response
            if ($response->failed()) {
                Log::error('Komship API Error', [
                    'order_id' => $order->id,
                    'response' => $response->json(),
                ]);
                return back()->with('error', 'Gagal mengirim data ke Komship. Silakan coba lagi.');
            }

            // ✅ Ambil data response
            $result = data_get($response->json(), 'data');
            if (!$result || !isset($result['order_no'])) {
                return back()->with('error', 'Response dari Komship tidak valid.');
            }

            // ✅ Update order status
            $order->update([
                'status' => 'packaging',
            ]);

            // ✅ Update shipping order number
            $order->shipping()->update([
                'order_number' => $result['order_no'],
            ]);

            return redirect()->route('orders.index')->with('success', 'Order berhasil dikirim ke Komship.');
        } catch (\Throwable $e) {
            Log::error('Komship Request Error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Terjadi kesalahan internal: ' . $e->getMessage());
        }
    }

    /**
     * Store pickup order
     */
    public function pickupStore(Request $request)
    {
        $request->validate([
            'pickup_date'    => 'required|date',
            'pickup_time'    => 'required',
            'pickup_vehicle' => 'required|in:motor,mobil,truck',
            'orders'         => 'required|array|min:1',
        ]);

        // mapping order_no sesuai format API
        $orders = array_map(fn($order) => ['order_no' => $order], $request->orders);

        $payload = [
            "pickup_date"    => $request->pickup_date,
            "pickup_time"    => $request->pickup_time,
            "pickup_vehicle" => $request->pickup_vehicle,
            "orders"         => $orders,
        ];

        // kirim request JSON ke API Komerce
        $response = Http::withHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'x-api-key'    => env('RAJAONGKIR_DELIVERY_API_KEY'),
        ])->post('https://api-sandbox.collaborator.komerce.id/order/api/v1/pickup/request', $payload);

        $data = $response->json();
        $message = $data['meta']['message'] ?? 'Unknown error';

        // handle error 400
        if ($response->status() == 400) {
            return back()->with('error', 'Failed to pickup order: ' . $message);
        }

        if ($response->successful()) {
            $shippingData = $data['data'] ?? [];

            foreach ($shippingData as $item) {
                $orderNo = $item['order_no'] ?? null;
                $awb     = $item['awb'] ?? null;

                if ($orderNo && $awb) {
                    // update shipping berdasarkan order_no
                    $shipping = Shipping::where('order_number', $orderNo)->first();
                    if ($shipping) {
                        $shipping->update([
                            'status'         => 'sent',
                            'shipped_at'     => now(),
                            'receipt_number' => $awb,
                        ]);

                        // update order terkait
                        $shipping->order()->update([
                            'status' => 'shipped',
                        ]);
                    }
                }
            }

            return redirect()->route('orders.index')->with('success', 'Order pickup successfully.');
        }


        return back()->with('error', 'Failed to pickup order: ' . ($message ?? $response->body()));
    }
}
