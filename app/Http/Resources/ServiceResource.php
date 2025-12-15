<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_code' => $this->service_code,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'laptop_brand' => $this->laptop_brand,
            'laptop_model' => $this->laptop_model,
            'complaint' => $this->complaint,
            'service_cost' => $this->service_cost,
            'total_cost' => $this->total_cost,
            'status' => $this->status,
            'images' => $this->images,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products_count' => $this->products_count,
            'products' => ProductResource::collection($this->products),
        ];
    }
}
