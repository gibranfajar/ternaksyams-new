<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Mail\OrderInvoiceMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\ShippingInformation;
use App\Models\ShippingOption;
use App\Models\VariantSize;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Midtrans\Notification;

class TransactionController extends Controller
{
    /**
     * Create transaction.
     */
    public function createTransaction(Request $request)
    {
        DB::beginTransaction();

        try {
            // === 1️⃣ Ambil cart aktif ===
            $cart = Cart::with('items.variantsize.variant.product', 'items.variantsize.size')
                ->where('id', $request->cart_id)
                ->where('status', 'active')
                ->first();

            if (!$cart) {
                return response()->json(['message' => 'Cart not found'], 404);
            }

            // === 2️⃣ Generate invoice ===
            $invoice = 'INV-TS/' . now()->format('ymd') . '/' . rand(1000, 9999);

            // === 3️⃣ Simpan shipping option ===
            $shippingOption = ShippingOption::create([
                'expedition' => $request->courier,
                'service'    => $request->service,
                'cost'       => $request->cost,
                'etd'        => $request->etd
            ]);

            // === 4️⃣ Simpan shipping information ===
            $shippingInfo = ShippingInformation::create([
                'name'        => $request->name,
                'phone'       => $request->phone,
                'email'       => $request->email,
                'address'     => $request->address,
                'province'    => $request->province,
                'city'        => $request->city,
                'district'    => $request->district,
                'postal_code' => $request->postal_code,
                'destination_id' => $request->destination_id
            ]);

            // === 5️⃣ Buat order ===
            $order = Order::create([
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'session' => $cart->session,
                'invoice' => $invoice,
                'total'   => $request->total,
                'note'    => $request->note
            ]);

            // === 6️⃣ Simpan item order ===
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'variantsize_id' => $item->variantsize->id,
                    'name'           => $item->variantsize->variant->product->name,
                    'variant'        => $item->variantsize->variant->flavour->name,
                    'size'           => $item->variantsize->size->label . ' ' . $item->variantsize->size->unit,
                    'original_price' => $item->original_price,
                    'discount_type'  => $item->discount_type,
                    'discount'       => $item->discount,
                    'price'          => $item->price,
                    'qty'            => $item->qty,
                    'total'          => $item->price * $item->qty,
                    'is_sale'        => $item->is_sale
                ]);
            }

            // === 7️⃣ Midtrans config ===
            Config::$serverKey    = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized  = true;
            Config::$is3ds        = true;

            // === 8️⃣ Kumpulin item detail untuk Midtrans ===
            $itemDetails = [];
            foreach ($order->items as $orderItem) {
                $itemDetails[] = [
                    'id'       => $orderItem->id,
                    'price'    => (int) $orderItem->price,
                    'quantity' => (int) $orderItem->qty,
                    'name'     => $orderItem->name . ' - ' . $orderItem->variant . ' (' . $orderItem->size . ')',
                ];
            }

            // Tambahkan ongkir
            $itemDetails[] = [
                'id'       => 'shipping',
                'price'    => (int) $shippingOption->cost,
                'quantity' => 1,
                'name'     => 'Shipping - ' . $shippingOption->expedition . ' ' . $shippingOption->service,
            ];

            // === 9️⃣ Voucher (jika ada) ===
            $discountAmount = 0;

            if ($request->filled('voucher_code')) {
                $voucher = Voucher::where('code', $request->voucher_code)->first();

                if (!$voucher) {
                    return response()->json(['message' => 'Voucher not found'], 404);
                }

                // hitung diskon
                if ($voucher->type === 'percentage') {
                    $discountAmount = ($voucher->value / 100) * $order->total;
                } else {
                    $discountAmount = $voucher->value;
                }

                $discountAmount = min($discountAmount, $order->total);

                // kurangi total order
                $order->update([
                    'total' => $order->total - $discountAmount
                ]);

                // Tambahkan ke Midtrans
                $itemDetails[] = [
                    'id'       => 'voucher',
                    'price'    => -$discountAmount,
                    'quantity' => 1,
                    'name'     => 'Voucher Discount (' . $voucher->code . ')',
                ];

                // Simpan penggunaan voucher
                VoucherUsage::create([
                    'order_id'   => $order->id,
                    'voucher_id' => $voucher->id,
                    'user_id'    => $cart->user_id,
                    'session'    => $cart->session,
                    'used_at'    => now(),
                ]);
            }

            // === 🔟 Buat parameter Midtrans ===
            $params = [
                'transaction_details' => [
                    'order_id'     => $order->invoice,
                    'gross_amount' => (int) $order->total,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $request->name,
                    'phone'      => $request->phone,
                    'email'      => $request->email ?? 'guest@example.com',
                    'shipping_address' => [
                        'first_name' => $request->name,
                        'phone'      => $request->phone,
                        'address'    => $request->address,
                        'city'       => $request->city,
                        'postal_code' => $request->postal_code,
                    ]
                ]
            ];

            // === 11️⃣ Dapetin Snap Token ===
            $snapToken = Snap::getSnapToken($params);

            // === 12️⃣ Simpan payment ===
            Payment::create([
                'order_id' => $order->id,
                'method'   => 'midtrans',
                'token'    => $snapToken
            ]);

            // === 13️⃣ Simpan shipping ===
            Shipping::create([
                'order_id'                => $order->id,
                'shipping_options_id'     => $shippingOption->id,
                'shipping_information_id' => $shippingInfo->id,
                'weight'                  => $request->total_weight
            ]);

            // === 14️⃣ Update cart & stok ===
            $cart->update(['status' => 'ordered']);

            foreach ($order->items as $orderItem) {
                VariantSize::where('id', $orderItem->variantsize_id)
                    ->decrement('stock', $orderItem->qty);
            }

            DB::commit();

            // === 15️⃣ Kirim email invoice ===
            $paymentUrl = 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken;
            Mail::to($request->email)->send(new OrderInvoiceMail($order, $paymentUrl));

            // === ✅ Return response ===
            return response()->json([
                'message'    => 'success',
                'invoice'    => $invoice,
                'snap_token' => $snapToken,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * List Transaction By User.
     */
    public function getTransactionUser()
    {
        $orders = Order::where('user_id', Auth::user()->id)->get();

        $data = TransactionResource::collection($orders);

        return response()->json([
            'data' => $data
        ], 200);
    }

    /**
     * Callback Midtrans
     */
    public function callback(Request $request)
    {
        // set midtrans config
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        $notif = new Notification();

        $transaction = $notif->transaction_status;
        $type        = $notif->payment_type;
        $orderId     = $notif->order_id;
        $fraud       = $notif->fraud_status;

        // cari order berdasarkan invoice
        $order = Order::where('invoice', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // update payment log
        Payment::where('order_id', $order->id)->update([
            'status'  => $transaction,
        ]);

        // handle status
        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $order->update(['status' => 'challenge']);
                } else {
                    $order->update(['status' => 'paid']);
                }
            }
        } elseif ($transaction == 'settlement') {
            $order->update(['status' => 'processing']);

            $itemDetailsKomship = [];
            foreach ($order->items as $orderItem) {
                $itemDetailsKomship[] = [
                    "product_name" => $orderItem->name,
                    "product_variant_name" => $orderItem->variant . ' - ' . $orderItem->size,
                    "product_price" => $orderItem->price,
                    "product_weight" => intval($orderItem->variantSize->size->label),
                    "product_width" => 0,
                    "product_height" => 0,
                    "product_length" => 0,
                    "qty" => $orderItem->qty,
                    "subtotal" => $orderItem->total
                ];
            }

            // Create order to komship komerce
            $response = Http::withHeaders([
                'x-api-key' => env('RAJAONGKIR_DELIVERY_API_KEY'),
                'Accept' => 'application/json',
            ])->post('https://api-sandbox.collaborator.komerce.id/order/api/v1/orders/store', [
                "order_date" => now(),
                "brand_name" => "TernakSyams",
                "shipper_name" => "TernakSyams",
                "shipper_phone" => "-",
                "shipper_destination_id" => 2163,
                "shipper_address" => "Komplek kramayudha. Blok D5 No. 10. RT 003/018, Mekarsari, Kec. Cimanggis, Kota Depok, Jawa Barat 16452",
                "shipper_email" => "ternaksyams.id@gmail.com",
                "receiver_name" => $orderItem->shipping->shippingInfo->name,
                "receiver_phone" => $orderItem->shipping->shippingInfo->phone,
                "receiver_destination_id" => $orderItem->shipping->shippingInfo->destination_id,
                "receiver_address" => $orderItem->shipping->shippingInfo->address,
                "receiver_email" => $orderItem->shipping->shippingInfo->email,
                "shipping" => strtoupper($orderItem->shipping->shippingOption->expedition),
                "shipping_type" => strtoupper($orderItem->shipping->shippingOption->service),
                "payment_method" => "BANK TRANSFER",
                "shipping_cost" => $orderItem->shipping->shippingOption->cost,
                "shipping_cashback" => 0,
                "service_fee" => 0,
                "additional_cost" => 0,
                "grand_total" => $order->total,
                "cod_value" => 0,
                "insurance_value" => 0,
                "order_details" => $itemDetailsKomship
            ]);

            // Ambil response JSON
            $result = data_get($response->json(), 'data');

            // Update shipping
            $order->shipping()->update([
                'order_number' => $result['order_no']
            ]);
        } elseif ($transaction == 'pending') {
            $order->update(['status' => 'pending']);
        } elseif ($transaction == 'deny') {
            $order->update(['status' => 'denied']);
        } elseif ($transaction == 'expire') {
            $order->update(['status' => 'expired']);
        } elseif ($transaction == 'cancel') {
            $order->update(['status' => 'cancelled']);
        }

        return response()->json(['message' => 'Callback processed']);
    }
}
