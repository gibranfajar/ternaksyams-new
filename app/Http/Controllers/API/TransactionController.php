<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\ShippingInformation;
use App\Models\ShippingOption;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
    /**
     * Create transaction.
     */
    public function createTransaction(Request $request)
    {
        DB::beginTransaction();
        try {

            //  get cart where cart id
            $cart = Cart::with('items')->where('id', $request->cart_id)->first();

            if (!$cart) {
                return response()->json([
                    'message' => 'cart not found'
                ], 404);
            }

            // generate invoice
            $invoice = 'INV-TS/' . now()->format('ymd') . '/' . rand(1000, 9999);

            // create table shipping options
            $shippingOption = ShippingOption::create([
                'expedition' => $request->courier,
                'service'    => $request->service,
                'cost'       => $request->cost,
                'etd'        => $request->etd
            ]);

            // create shipping information
            $shippingInfo = ShippingInformation::create([
                'name'        => $request->name,
                'phone'       => $request->phone,
                'address'     => $request->address,
                'province'    => $request->province,
                'city'        => $request->city,
                'district'    => $request->district,
                'postal_code' => $request->postal_code
            ]);

            // create table orders
            $order = Order::create([
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'session' => $cart->session,
                'invoice' => $invoice,
                'total'   => $request->total,
                'note'    => $request->note
            ]);

            // create shipping
            Shipping::create([
                'order_id' => $order->id,
                'shipping_options_id' => $shippingOption->id,
                'shipping_information_id' => $shippingInfo->id
            ]);

            // crate table order_items
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'name'           => $item->variantsize->variant->product->name,
                    'variant'        => $item->variantsize->variant->flavour->name,
                    'size'           => $item->variantsize->size->label . ' ' . $item->variantsize->size->unit,
                    'original_price' => $item->original_price,
                    'discount_type'  => $item->discount_type,
                    'discount'       => $item->discount,
                    'price'          => $item->price,
                    'qty'            => $item->qty,
                    'total'          => $item->price,
                    'is_sale'        => $item->is_sale
                ]);
            }

            // === Midtrans Integration ===
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // kumpulin item details
            $itemDetails = [];
            foreach ($order->items as $orderItem) {
                $itemDetails[] = [
                    'id'       => $orderItem->id,
                    'price'    => (int) $orderItem->price,
                    'quantity' => (int) $orderItem->qty,
                    'name'     => $orderItem->name . ' - ' . $orderItem->variant . ' (' . $orderItem->size . ')',
                ];
            }

            // tambahin ongkir juga
            $itemDetails[] = [
                'id'       => 'shipping',
                'price'    => (int) $shippingOption->cost,
                'quantity' => 1,
                'name'     => 'Shipping Cost - ' . $shippingOption->expedition . ' ' . $shippingOption->service,
            ];

            $params = [
                'transaction_details' => [
                    'order_id' => $order->invoice,
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

            $snapToken = Snap::getSnapToken($params);

            // create table payment
            Payment::create([
                'order_id' => $order->id,
                'method' => 'midtrans',
                'token' => $snapToken
            ]);

            // create table voucher usage jika ada voucher
            if ($request->voucher_code) {
                $voucher = Voucher::where('code', $request->voucher_code)->first();
                VoucherUsage::create([
                    'order_id' => $order->id,
                    'voucher_id' => $voucher->id,
                    'user_id' => $cart->user_id,
                    'session' => $cart->session,
                    'used_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'success',
                'snap_token' => $snapToken,
                'order' => $order
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th
            ]);
        }
    }
}
