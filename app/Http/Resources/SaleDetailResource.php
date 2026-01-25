<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer_name' => $this->customer_name,
            'total_price' => $this->total_price,
            'payment_method' => $this->payment_method,
            'created_at' => $this->created_at,
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'qty' => $product->pivot->qty,
                        'price' => $product->pivot->price,
                        'subtotal' => $product->pivot->subtotal,
                    ];
                });
            }),
        ];
    }
}
