<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $smallestSize = $this->sizes->sortBy('label')->first();

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'category'       => $this->category->name ?? null,
            'image'          => $this->images()->first()->image_path ?? null,
            // 'size'           => $smallestSize?->size->label,
            // 'unit'           => $smallestSize?->size->unit,
            'price'          => $smallestSize?->price,
            'discount'       => $smallestSize?->discount,
            'price_discount' => $smallestSize?->price_after_discount,
        ];
    }
}
