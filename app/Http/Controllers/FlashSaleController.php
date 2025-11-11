<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\FlashsaleItem;
use App\Models\Product;
use App\Models\Variant;
use App\Models\VariantSize;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FlashSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $flashSales = FlashSale::with('variants.sizes.size', 'variants.images', 'variants.product')->orderBy('id', 'desc')->get();

        // dd($flashSales);

        return view('flashsales.index', compact('flashSales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::with('variants')->get();
        return view('flashsales.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());
        try {
            $selectedProducts = $request->selected_products;

            // buatkan untuk status jika memang start date tanggal sekarang maka active
            $status = $request->start_date <= now() ? 'ongoing' : 'draft';

            // create flash sale
            $flashSale = FlashSale::create([
                'title'       => $request->title,
                'slug'        => Str::slug($request->title),
                'description' => $request->description,
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date,
                'status'      => $status
            ]);

            foreach ($selectedProducts as $data) {
                // 1. Update flag flash sale di variant
                Variant::where('id', $data['id'])
                    ->update([
                        'is_flashsale' => true,
                        'flashsale_id' => $flashSale->id,
                    ]);

                // 2. Ambil semua ukuran dari variant ini
                $variantSizes = VariantSize::where('variant_id', $data['id'])->get();

                foreach ($variantSizes as $variantSize) {
                    $originalPrice = $variantSize->price ?? 0; // harga asli tiap size
                    $discount      = (int) $data['discount'];
                    $qty           = (int) $data['qty'];

                    // Hitung harga setelah diskon
                    $priceAfterDiscount = $originalPrice - ($originalPrice * $discount / 100);

                    // Update tiap record size
                    $variantSize->update([
                        'stock_flashsale'               => $qty,
                        'discount_flashsale'            => $discount,
                        'price_after_discount_flashsale' => $priceAfterDiscount,
                    ]);
                }
            }

            return redirect()->route('flash-sales.index')->with('success', 'Flash Sale berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
        $products = Product::with('variants')->get();

        // semua variant dari flashsale + relasi
        $variants = $flashSale->variants()
            ->with('sizes.size', 'images', 'product') // tambahin product disini
            ->get();

        // ini ambil variant yang masuk flash sale (berdasarkan flashsale_id)
        $productVariants = $flashSale->variants()->with('sizes')->get();

        return view('flashsales.edit', compact(
            'flashSale',
            'products',
            'variants',
            'productVariants'
        ));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FlashSale $flashSale)
    {
        try {
            $selectedProducts = $request->selected_products;

            // buatkan untuk status jika memang start date tanggal sekarang maka active
            $status = $request->start_date <= now() ? 'ongoing' : 'draft';

            // create flash sale
            $flashSale->update([
                'title'       => $request->title,
                'slug'        => Str::slug($request->title),
                'description' => $request->description,
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date,
                'status'      => $status
            ]);

            foreach ($selectedProducts as $data) {
                // 1. Update flag flash sale di variant
                Variant::where('id', $data['id'])
                    ->update([
                        'is_flashsale' => true,
                        'flashsale_id' => $flashSale->id,
                    ]);

                // 2. Ambil semua ukuran dari variant ini
                $variantSizes = VariantSize::where('variant_id', $data['id'])->get();

                foreach ($variantSizes as $variantSize) {
                    $originalPrice = $variantSize->price ?? 0; // harga asli tiap size
                    $discount      = (int) $data['discount'];
                    $qty           = (int) $data['qty'];

                    // Hitung harga setelah diskon
                    $priceAfterDiscount = $originalPrice - ($originalPrice * $discount / 100);

                    // Update tiap record size
                    $variantSize->update([
                        'stock_flashsale'               => $qty,
                        'discount_flashsale'            => $discount,
                        'price_after_discount_flashsale' => $priceAfterDiscount,
                    ]);
                }
            }

            return redirect()->route('flash-sales.index')->with('success', 'Flash Sale berhasil diupdate!');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
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
