<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;

class SaleExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Sale::select(
            'invoice_number',
            'customer_name',
            'total_price',
            'payment_method',
            'created_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Customer',
            'Total',
            'Payment Method',
            'Tanggal'
        ];
    }
}
