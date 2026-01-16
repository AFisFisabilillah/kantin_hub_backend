<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $images = [];
        if ($this->images && count($this->images) > 0) {
            foreach ($this->images as $img) {
                $images[] = asset("storage/{$img}");
            }
        } else {
            $images[] = asset("no-item.png");
        }
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'brand' => $this->brand,
            'price' => $this->price,
            'stok' => $this->stok,
            'description' => $this->description,
            'images' => $images,
            'qty' => $this->pivot->qty,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
