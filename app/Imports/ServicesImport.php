<?php

namespace App\Imports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServicesImport implements ToModel,WithHeadingRow,WithCalculatedFormulas
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Service([
            'service_code'   => $row['service_code'],
            'customer_name'  => $row['customer_name'],
            'customer_phone' => $row['customer_phone'],
            'laptop_brand'   => $row['laptop_brand'],
            'laptop_model'   => $row['laptop_model'],
            'complaint'      => $row['complaint'],
            'service_cost'   => $row['service_cost'] ?? 0,
            'total_cost'     => $row['total_cost'] ?? $row['service_cost'] ?? 0,
            'status'         => $row['status'] ?? 'received',
        ]);
    }
}
