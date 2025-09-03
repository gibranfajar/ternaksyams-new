<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('id', 'desc')->get();
        return view('orders.index', compact('orders'));
    }

    /**
     * Display a listing of the resource.
     */
    public function pickup()
    {
        $orders = Order::orderBy('id', 'desc')->where('status', 'processing')->get();
        return view('orders.pickup', compact('orders'));
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
