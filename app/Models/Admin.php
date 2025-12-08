<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens;

    protected $fillable = [
        "profile",
        "username",
        "fullname",
        "phone",
    ];

    protected $hidden = [
        "password",
    ];
}
