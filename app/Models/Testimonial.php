<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name_customer',
        'rating',
        'comment',
        'visible',
    ];

    protected function casts()
    {
        return [
            'visible' => 'boolean',
        ];
    }
}
