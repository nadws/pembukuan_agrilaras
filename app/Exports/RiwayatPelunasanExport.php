<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RiwayatPelunasanExport implements FromView, WithEvents
{
    protected $rows;
    protected $jenis;
    protected $awal;
    protected $akhir;
    protected $totalrow;

    public function __construct($rows, $jenis, $awal, $akhir)
    {
        $this->rows = $rows;
        $this->jenis = $jenis;
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->totalrow = count($rows) + 3;
    }

    public function view(): View
    {
        return view('transaksi.piutang.export_riwayat', [
            'rows' => $this->rows,
            'jenis' => $this->jenis,
            'awal' => $this->awal,
            'akhir' => $this->akhir,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastCol = 'J';
                $sheet->getStyle("A1:{$lastCol}3")->getFont()->setBold(true);
                $sheet->getStyle("A1:{$lastCol}{$this->totalrow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 11,
                        'bold' => false,
                    ],
                ]);
                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
