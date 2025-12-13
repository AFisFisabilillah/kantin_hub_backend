<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductsImport implements ToModel
{
    public function model(array $row): Product
    {
        return new Product([
            'sku' => $row['sku'],
            'name' => $row['nama'],
            'brand' => $row['brand'],
            'price' => $row['harga'],
            'stok' => $row['stok'],
            'description' => $row['deskripsi'],
        ]);
    }

}
