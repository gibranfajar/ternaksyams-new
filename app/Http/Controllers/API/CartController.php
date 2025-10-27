<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Models\VariantSize;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            'session'    => 'nullable|string',
            'product_id' => 'required|integer',
            'qty'        => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Normalisasi session (hindari whitespace)
            $guestToken = $request->session ? trim($request->session) : null;

            // 1) Cari cart aktif yang sudah ada (prioritaskan user_id kalau ada)
            if ($request->user_id) {
                $cart = Cart::where('user_id', $request->user_id)
                    ->where('status', 'active')
                    ->first();
            } else {
                $cart = Cart::where('session', $guestToken)
                    ->where('status', 'active')
                    ->first();
            }

            // 2) Jika belum ada, buat cart baru (di dalam transaction)
            if (! $cart) {
                $cart = Cart::create([
                    'user_id' => $request->user_id ?? null,
                    'session' => $request->user_id ? null : $guestToken,
                    'status'  => 'active',
                    'created_at' => now(),
                ]);
            } else {
                // update touched
                $cart->touch();
            }

            // 3) Ambil variant (akan throw 404 kalau gak ketemu)
            $variant = VariantSize::findOrFail($request->product_id);

            // 4) Cari cart item yang sama (based on variant size)
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('variantsize_id', $variant->id)
                ->first();

            if ($cartItem) {
                // update qty dan harga sekaligus
                $cartItem->update([
                    'qty' => $cartItem->qty + $request->qty,
                    'discount' => $variant->discount,
                    'original_price' => $variant->price,
                    'price' => $variant->discount != 0 ? $variant->price_after_discount : $variant->price,
                ]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'variantsize_id' => $variant->id,
                    'qty' => $request->qty,
                    'discount' => $variant->discount,
                    'original_price' => $variant->price,
                    'price' => $variant->discount != 0 ? $variant->price_after_discount : $variant->price,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'cart_id' => $cart->id,
                    'session' => $cart->session,
                ],
                'message' => 'Product added to cart successfully.',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Jika duplicate key karena race condition, coba cari ulang cart yg sudah ada
            if ($e->errorInfo[1] === 1062) { // MySQL duplicate entry kode
                $retryCart = Cart::where('session', $guestToken)->where('status', 'active')->first();
                if ($retryCart) {
                    // lakukan insert cart_item minimal atau balas sukses dengan cart id
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'cart_id' => $retryCart->id,
                            'session' => $retryCart->session,
                        ],
                        'message' => 'Product added to cart (retried).',
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart.',
                'error'   => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();

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
    public function getCart(Request $request)
    {
        try {
            if ($request->user_id) {
                // Cari user
                $user = User::find($request->user_id);

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found.',
                    ], 404);
                }

                // Ambil cart untuk user login
                $cartItems = CartItem::whereHas('cart', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->where('status', 'active');
                })->get();

                $data = CartResource::collection($cartItems);
            } else {
                // Ambil cart untuk guest
                $guestToken = $request->session;
                $cart = Cart::where('session', $guestToken)
                    ->where('status', 'active')
                    ->first();

                if ($cart) {
                    $cartItems = $cart->items; // pastikan relasi `items()` ada di model Cart
                    $data = CartResource::collection($cartItems);
                } else {
                    $data = [];
                }
            }

            return response()->json([
                'success' => true,
                'cart_id' => $cart->id ?? null,
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


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
