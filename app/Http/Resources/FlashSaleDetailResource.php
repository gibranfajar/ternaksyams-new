<?php

namespace App\Http\Resources;

use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $now = now();

        /** 🔥 semua flavour dalam 1 product */
        $flavours = Variant::with('flavour')
            ->where('product_id', $this->product_id)
            ->get()
            ->unique('flavour_id')
            ->map(fn($variant) => [
                'id'   => $variant->flavour->id,
                'name' => $variant->flavour->name,
            ])
            ->values();

        /** 🔥 semua variant + size + harga flash sale */
        $variants = Variant::with([
            'sizes.size',
            'flashSaleItems.flashSale',
        ])
            ->where('product_id', $this->product_id)
            ->get()
            ->map(function ($variant) use ($now) {

                return [
                    'variant_id'   => $variant->id,
                    'variant_name' => $variant->name,
                    'variant_slug' => $variant->slug,

                    'sizes' => $variant->sizes->map(function ($size) use ($variant, $now) {

                        $flashItem = $variant->flashSaleItems
                            ->first(
                                fn($item) =>
                                $item->variantsize_id === $size->id &&
                                    $item->flashSale &&
                                    $item->flashSale->status === 'ongoing' &&
                                    $item->flashSale->start_date <= $now &&
                                    $item->flashSale->end_date >= $now
                            );

                        return [
                            'variant_size_id' => $size->id,
                            'size'            => $size->size->label,

                            'price_original'  => $size->price,
                            'price_flash_sale' => $flashItem?->flashsale_price,
                            'discount'        => $flashItem?->discount,

                            'final_price'     => $flashItem?->flashsale_price ?? $size->price,
                            'stock_flash_sale' => $flashItem?->stock,
                            'is_flash_sale'   => (bool) $flashItem,
                        ];
                    }),
                ];
            });

        /** 🔥 related product */
        $related = Variant::where('category_id', $this->category_id)
            ->where('product_id', '!=', $this->product_id)
            ->take(4)
            ->get();

        return [
            'product_name' => $this->product->name,
            'brand'        => $this->product->brand->brand ?? null,

            'gizi_path'    => $this->product->gizi_path,
            'description' => $this->product->description,
            'benefits'    => $this->product->benefits,

            'flavours' => $flavours,
            'variants' => $variants,

            'related'  => ProductResource::collection($related),
        ];
    }
}
