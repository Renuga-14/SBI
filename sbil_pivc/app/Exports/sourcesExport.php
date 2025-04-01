<?php

namespace App\Exports;

use App\Models\sources;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class SourcesExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return sources::all(['name', 'desc', 'status', 'created_on', 'updated_on']); // Select the required columns
    }

    public function headings(): array
    {
        return [
            'name',
            'desc',
            'Price',
            'created_on',
            'updated_on'
        ];
}

public function styles(Worksheet $sheet)
{
    return [
        1 => ['font' => ['bold' => true]], // Makes the first row (headings) bold
    ];
}
}
