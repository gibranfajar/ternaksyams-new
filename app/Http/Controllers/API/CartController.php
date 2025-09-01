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
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        try {
            $user = Auth::user();

            // Ambil variant size
            $variant = VariantSize::find($request->product_id);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variant not found.'
                ], 404);
            }

            // Hitung harga setelah diskon
            $priceAfterDiscount = $variant->type_disc === 'percent'
                ? $variant->price - ($variant->price * $variant->discount / 100)
                : $variant->price - $variant->discount;

            // ===== Buat / Ambil Cart =====
            if ($user) {
                // Cek cart aktif user
                $cart = Cart::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->first();

                if (!$cart) {
                    $cart = Cart::create([
                        'user_id' => $user->id,
                        'status'  => 'active',
                    ]);
                }
            } else {
                // Guest pakai cookie guest_token
                $guestToken = $request->cookie('guest_token');
                if (!$guestToken) {
                    $guestToken = bin2hex(random_bytes(16));
                }

                $cart = Cart::where('session', $guestToken)
                    ->where('status', 'active')
                    ->first();

                if (!$cart) {
                    $cart = Cart::create([
                        'session' => $guestToken,
                        'status'  => 'active',
                    ]);
                }
            }

            // ===== Tambahkan / Update Item =====
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('variantsize_id', $variant->id)
                ->first();

            if ($existingItem) {
                // Update qty + price total
                $newQty = $existingItem->qty + 1;
                $existingItem->update([
                    'qty'   => $newQty,
                    'price' => $priceAfterDiscount * $newQty,
                ]);
                $cartItem = $existingItem;
            } else {
                // Insert baru
                $cartItem = CartItem::create([
                    'cart_id'        => $cart->id,
                    'variantsize_id' => $variant->id,
                    'qty'            => 1,
                    'original_price' => $variant->price,
                    'price'          => $priceAfterDiscount,
                ]);
            }

            // ===== Response =====
            $response = response()->json([
                'message' => 'Product added to cart successfully.'
            ], 200);

            // Tambahkan cookie kalau guest
            if (!$user) {
                $response->cookie('guest_token', $guestToken, 60 * 24 * 30); // 30 hari
            }

            return $response;
        } catch (\Exception $e) {
            return response()->json([
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
            $user = Auth::user();

            // Ambil cart
            if ($user) {
                $cart = CartItem::whereHas('cart', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->where('status', 'active');
                })->get();

                $cartId = optional($cart->first()->cart)->id;
                $data = CartResource::collection($cart);
            } else {
                $guestToken = $request->cookie('guest_token');
                $cart = Cart::where('session', $guestToken)->where('status', 'active')->first();
            }

            return response()->json([
                'success' => true,
                'cart_id'    => $cartId,
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
    public function increment(Request $request)
    {
        try {
            $user = Auth::user();

            // Ambil cart
            if ($user) {
                $cart = CartItem::findorFail($request->id);
            } else {
                $guestToken = $request->cookie('guest_token');
                $cart = CartItem::whereHas('cart', function ($q) use ($guestToken) {
                    $q->where('session', $guestToken)->where('status', 'active');
                });
            }

            if ($cart) {
                $cart->update([
                    'qty' => $cart->qty + 1,
                    'price' => $cart->original_price * ($cart->qty + 1)
                ]);
            }

            return response()->json([
                'success' => true,
                'message'    => 'Cart updated successfully.',
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
     * Decrement cart
     */
    public function decrement(Request $request)
    {
        try {
            $user = Auth::user();

            // Ambil cart
            if ($user) {
                $cart = CartItem::findorFail($request->id);
            } else {
                $guestToken = $request->cookie('guest_token');
                $cart = CartItem::whereHas('cart', function ($q) use ($guestToken) {
                    $q->where('session', $guestToken)->where('status', 'active');
                });
            }

            if ($cart) {
                if ($cart->qty > 1) {
                    $cart->update([
                        'qty' => $cart->qty - 1,
                        'price' => $cart->original_price * ($cart->qty - 1)
                    ]);
                } else {
                    $cart->delete();
                }
            }

            return response()->json([
                'success' => true,
                'message'    => 'Cart updated successfully.',
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
     * Remove cart
     */
    public function remove(Request $request, $id)
    {
        try {
            $user = Auth::user();

            // Ambil cart
            if ($user) {
                $cart = CartItem::findorFail($id);
            } else {
                $guestToken = $request->cookie('guest_token');
                $cart = CartItem::whereHas('cart', function ($q) use ($guestToken) {
                    $q->where('session', $guestToken)->where('status', 'active');
                });
            }

            if ($cart) {
                $cart->delete();
            }

            return response()->json([
                'success' => true,
                'message'    => 'Cart deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
