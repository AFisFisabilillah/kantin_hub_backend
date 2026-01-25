<?php

namespace App\Imports;

use App\Models\Sale;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SaleImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Sale([
            'invoice_number' => 'INV-'. now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            "customer_name" => $row["customer_name"],
            'total_price'    => (int) $row['total_price'],
            'payment_method' => $row['payment_method']
        ]);
    }
}
