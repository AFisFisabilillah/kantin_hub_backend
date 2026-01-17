<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow,WithCalculatedFormulas
{
    public function model(array $row): Product
    {
        return new Product([
            'name' => $row['name'],
            'brand' => $row['brand'],
            'price' => $row['price'],
            'stok' => $row['stok'],
            'description' => $row['description'],
        ]);
    }

}
