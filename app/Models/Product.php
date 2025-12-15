<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
