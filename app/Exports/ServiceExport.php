<?php

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServiceExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Service::select(
            'service_code',
            'customer_name',
            'laptop_brand',
            'total_cost',
            'status',
            'created_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Kode Servis',
            'Customer',
            'Laptop',
            'Total',
            'Status',
            'Tanggal'
        ];
    }
}
