<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\VariantSize;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /*
     * Add product to cart
     * @param product_id
     * @param qty
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'user_id'    => 'nullable|integer',
            'product_id' => 'required|integer',
            'qty'        => 'required|integer',
        ]);

        try {
            // Ambil atau buat cart aktif untuk user
            $cart = Cart::firstOrCreate(
                ['user_id' => $request->user_id],
                ['session' => $request->cookie('guest_token')],
                ['created_at' => now()]
            );

            // Cek variant
            $variant = VariantSize::where('id', $request->product_id)->firstOrFail();

            // Tambahkan item ke cart_items
            CartItem::create([
                'cart_id'        => $cart->id,
                'variantsize_id' => $variant->id,
                'qty'            => $request->qty,
                'discount'       => $variant->discount,
                'original_price' => $variant->price,
                'price'          => $variant->discount != 0 ? $variant->price_after_discount : $variant->price,
            ]);

            return response()->json([
                'cart_id' => $cart->id,
                'session' => $cart->session,
                'message' => 'Product added to cart successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /*
     * Get cart
     */
    // public function getCart(Request $request)
    // {
    //     try {
    //         $user = Auth::user();

    //         // Ambil cart
    //         if ($user) {
    //             $cart = CartItem::whereHas('cart', function ($q) use ($user) {
    //                 $q->where('user_id', $user->id)
    //                     ->where('status', 'active');
    //             })->get();

    //             $cartId = optional($cart->first()->cart)->id;
    //             $data = CartResource::collection($cart);
    //         } else {
    //             $guestToken = $request->cookie('guest_token');
    //             $cart = Cart::where('session', $guestToken)->where('status', 'active')->first();
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'cart_id'    => $cartId,
    //             'data'    => $data,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to get cart.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /*
     * Increment cart
     */
    // public function increment(Request $request)
    // {
    //     try {
    //         $user = Auth::user();

    //         // Ambil cart
    //         if ($user) {
    //             $cart = CartItem::findorFail($request->id);
    //         } else {
    //             $guestToken = $request->cookie('guest_token');
    //             $cart = CartItem::whereHas('cart', function ($q) use ($guestToken) {
    //                 $q->where('session', $guestToken)->where('status', 'active');
    //             });
    //         }

    //         if ($cart) {
    //             $cart->update([
    //                 'qty' => $cart->qty + 1,
    //                 'price' => $cart->original_price * ($cart->qty + 1)
    //             ]);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message'    => 'Cart updated successfully.',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to get cart.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /*
     * Decrement cart
     */
    // public function decrement(Request $request)
    // {
    //     try {
    //         $user = Auth::user();

    //         // Ambil cart
    //         if ($user) {
    //             $cart = CartItem::findorFail($request->id);
    //         } else {
    //             $guestToken = $request->cookie('guest_token');
    //             $cart = CartItem::whereHas('cart', function ($q) use ($guestToken) {
    //                 $q->where('session', $guestToken)->where('status', 'active');
    //             });
    //         }

    //         if ($cart) {
    //             if ($cart->qty > 1) {
    //                 $cart->update([
    //                     'qty' => $cart->qty - 1,
    //                     'price' => $cart->original_price * ($cart->qty - 1)
    //                 ]);
    //             } else {
    //                 $cart->delete();
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message'    => 'Cart updated successfully.',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to get cart.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /*
     * Remove cart
     */
    // public function remove(Request $request, $id)
    // {
    //     try {
    //         $user = Auth::user();

    //         // Ambil cart
    //         if ($user) {
    //             $cart = CartItem::findorFail($id);
    //         } else {
    //             $guestToken = $request->cookie('guest_token');
    //             $cart = CartItem::whereHas('cart', function ($q) use ($guestToken) {
    //                 $q->where('session', $guestToken)->where('status', 'active');
    //             });
    //         }

    //         if ($cart) {
    //             $cart->delete();
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message'    => 'Cart deleted successfully.',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to get cart.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
