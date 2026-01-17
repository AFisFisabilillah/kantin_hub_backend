<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes,HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'brand',
        'price',
        'stok',
        'description',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function services(){
        return $this->belongsToMany(Service::class, 'service_products');
    }

    protected static function booted(){
        static::creating(function (Product $product) {
            if (empty($product->sku)) {
                $product->sku = "SKU-".now()->format('YmdHis')."-".Str::upper(Str::random(4));
            }
        });
    }
}
