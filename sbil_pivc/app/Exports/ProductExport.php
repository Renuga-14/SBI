<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Product::all(['uin_no', 'product_name', 'source_id', 'product_slug','status','created_on','updated_on']); // Select the required columns
    }

    public function headings(): array
    {
        return [
            'uin_no',
            'product_name',
            'source_id',
            'product_slug',
            'status',
            'created_on',
            'updated_on',

        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Makes the first row (headings) bold
        ];
    }
}
