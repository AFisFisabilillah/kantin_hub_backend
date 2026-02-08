<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    protected $fillable = [
        'hero_tittle',
        'hero_image',
        'about_me',
        'about_image',
    ];
}
