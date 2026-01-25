<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'total_price',
        'payment_method',
    ];

    protected $casts = [
        'total_price' => 'integer',
    ];


    public function products()
    {
        return $this->belongsToMany(Product::class, 'sale_items')
            ->withPivot('qty', 'price', 'subtotal');
    }

    /**
     * Relasi one-to-many ke SaleItem
     * (berguna untuk laporan detail)
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
