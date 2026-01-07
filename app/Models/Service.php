<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'service_code',
        'customer_name',
        'customer_phone',
        'laptop_brand',
        'laptop_model',
        'complaint',
        'service_cost',
        'total_cost',
        'status',
        'images',
    ];

    protected $casts = [
        "images" => "array",
    ];

    function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, "service_products")->withPivot(['qty', 'price', 'subtotal']);
    }
}
