<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPerencanaanExport implements WithMultipleSheets
{
    public function __construct(private array $data)
    {
    }

    public function sheets(): array
    {
        return array_map(fn ($sheet) => new PerencanaanSheet($sheet['title'], $sheet['columns'], $sheet['rows'], $sheet['scope'], $sheet['note']), $this->data);
    }
}
