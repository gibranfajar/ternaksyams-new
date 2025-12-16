<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\FlashsaleItem;
use App\Models\Product;
use App\Models\VariantSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FlashSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $flashSales = FlashSale::with([
            'items.variant.product',
            'items.variant.images',
            'items.variant.flavour',
            'items.variant.category',
            'items.variantSize.size',
        ])
            ->orderBy('id', 'desc')
            ->get();

        return view('flashsales.index', compact('flashSales'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::with('variants', 'variants.sizes', 'variants.images')->get();

        return view('flashsales.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'title' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'selected_products' => 'required|array',
            ]);

            DB::transaction(function () use ($request) {

                /** =======================
                 *  STATUS FLASH SALE
                 *  ======================= */
                $status = now()->between($request->start_date, $request->end_date)
                    ? 'ongoing'
                    : 'draft';

                /** =======================
                 *  CREATE FLASH SALE
                 *  ======================= */
                $flashSale = FlashSale::create([
                    'title'       => $request->title,
                    'slug'        => Str::slug($request->title),
                    'description' => $request->description,
                    'start_date'  => $request->start_date,
                    'end_date'    => $request->end_date,
                    'status'      => $status,
                ]);

                /** =======================
                 *  LOOP SELECTED PRODUCTS
                 *  ======================= */
                foreach ($request->selected_products as $product) {

                    $variantId = $product['variant_id'];

                    /** =======================
                     *  LOOP SIZES
                     *  ======================= */
                    foreach ($product['sizes'] as $size) {

                        $variantSize = VariantSize::lockForUpdate()
                            ->findOrFail($size['variant_size_id']);

                        $discount = (int) $size['discount'];
                        $qty      = (int) $size['qty'];

                        // VALIDASI STOK
                        if ($variantSize->stock < $qty) {
                            throw new \Exception(
                                "Stok tidak cukup untuk size {$variantSize->size->label}"
                            );
                        }

                        $price = $variantSize->price;
                        $flashsalePrice = $price - ($price * $discount / 100);

                        /** =======================
                         *  INSERT FLASHSALE ITEM
                         *  ======================= */
                        FlashSaleItem::create([
                            'flashsale_id'    => $flashSale->id,
                            'variant_id'      => $variantId,
                            'variantsize_id' => $variantSize->id,
                            'stock'           => $qty,
                            'discount'        => $discount,
                            'flashsale_price' => $flashsalePrice,
                        ]);

                        /** =======================
                         *  KURANGI STOK
                         *  ======================= */
                        $variantSize->decrement('stock', $qty);
                    }
                }
            });

            return redirect()
                ->route('flash-sales.index')
                ->with('success', 'Flash Sale berhasil dibuat');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(FlashSale $flashSale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FlashSale $flashSale)
    {
        // untuk nambah produk baru ke flash sale
        $products = Product::with('variants.images')->get();

        // item flash sale yang SUDAH dipilih
        $flashSaleItems = $flashSale->items()
            ->with([
                'variant.product',
                'variant.images',
                'variantSize.size',
            ])
            ->get();

        if ($flashSale->status === 'ongoing') {
            return redirect()
                ->route('flash-sales.index')
                ->with('error', 'Flash sale yang sedang berlangsung tidak bisa diedit');
        }

        return view('flashsales.edit', compact(
            'flashSale',
            'products',
            'flashSaleItems'
        ));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FlashSale $flashSale)
    {
        try {
            $request->validate([
                'title' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'selected_products' => 'required|array',
            ]);

            if ($flashSale->status === 'ongoing') {
                return redirect()
                    ->route('flash-sales.index')
                    ->with('error', 'Flash sale yang sedang berlangsung tidak bisa diedit');
            }

            DB::transaction(function () use ($request, $flashSale) {

                /** =======================
                 *  1. RESTORE STOCK LAMA
                 *  ======================= */
                $oldItems = $flashSale->items()->get();

                foreach ($oldItems as $item) {
                    VariantSize::where('id', $item->variantsize_id)
                        ->increment('stock', $item->stock);
                }

                /** =======================
                 *  2. UPDATE FLASH SALE
                 *  ======================= */
                $status = now()->between($request->start_date, $request->end_date)
                    ? 'ongoing'
                    : 'draft';

                $flashSale->update([
                    'title'       => $request->title,
                    'slug'        => Str::slug($request->title),
                    'description' => $request->description,
                    'start_date'  => $request->start_date,
                    'end_date'    => $request->end_date,
                    'status'      => $status,
                ]);

                /** =======================
                 *  3. DELETE ITEMS LAMA
                 *  ======================= */
                $flashSale->items()->delete();

                /** =======================
                 *  4. INSERT ITEMS BARU
                 *  ======================= */
                foreach ($request->selected_products as $product) {

                    $variantId = $product['variant_id'];

                    foreach ($product['sizes'] as $size) {

                        $variantSize = VariantSize::lockForUpdate()->findOrFail(
                            $size['variant_size_id']
                        );

                        $discount = (int) $size['discount'];
                        $qty      = (int) $size['qty'];

                        // VALIDASI STOK
                        if ($variantSize->stock < $qty) {
                            throw new \Exception(
                                "Stok tidak cukup untuk size {$variantSize->size->label}"
                            );
                        }

                        $price = $variantSize->price;
                        $flashsalePrice = $price - ($price * $discount / 100);

                        FlashSaleItem::create([
                            'flashsale_id'     => $flashSale->id,
                            'variant_id'       => $variantId,
                            'variantsize_id'  => $variantSize->id,
                            'stock'            => $qty,
                            'discount'         => $discount,
                            'flashsale_price'  => $flashsalePrice,
                        ]);

                        /** =======================
                         *  5. KURANGI STOK BARU
                         *  ======================= */
                        $variantSize->decrement('stock', $qty);
                    }
                }
            });

            return redirect()
                ->route('flash-sales.index')
                ->with('success', 'Flash Sale berhasil diupdate!');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FlashSale $flashSale)
    {
        //
    }

    public function getSizes($variantId)
    {
        $sizes = VariantSize::with('size')
            ->where('variant_id', $variantId)
            ->get()
            ->map(function ($vs) {
                return [
                    'id' => $vs->id,
                    'size_name' => $vs->size->label ?? '-',
                    'stock' => $vs->stock,
                    'price' => $vs->price,
                    'discount' => $vs->discount,
                    'type_disc' => $vs->type_disc,
                    'price_after_discount' => $vs->price_after_discount,
                ];
            });

        return response()->json($sizes);
    }
}
