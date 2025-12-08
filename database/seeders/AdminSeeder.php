<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            "username" => "admin",
            "profile" => "profile",
            "password" => Hash::make("admin"),
            "fullname" => "Admin",
            "phone" => "0123456789",
        ]);
    }
}
