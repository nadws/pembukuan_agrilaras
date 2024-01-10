<?php

namespace App\Exports;

use App\Models\Jurnal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


class MedionExport  implements FromView, WithEvents
{
    protected $id_kandang;
    protected $totalrow;

    public function __construct($id_kandang, $totalrow)
    {
        $this->id_kandang = $id_kandang;
        $this->totalrow = $totalrow;
    }

    public function view(): View
    {
        $medion = DB::select("SELECT
       CEIL(DATEDIFF(a.tgl, b.chick_in) / 7) AS umur_minggu, 
       DATEDIFF(a.tgl, b.chick_in) AS umur_hari,
       a.tgl, c.mati, c.jual, 
       (b.stok_awal - (populasi.mati + populasi.jual)) as hidup,
       round((((c.mati + c.jual)/(b.stok_awal - (e.mati + e.jual))) * 100),2) as deplesi,
       sum(a.gr/1000) as kg_pakan,
       (a.gr/ (b.stok_awal - (populasi.mati + populasi.jual))) as gr_perekor,
       normal.normalPcs, normal.normalKg, abnormal.abnormalPcs , abnormal.abnormalKg,
       f.feed,f.berat,f.berat_telur, f.telur as hd, g.nama_obat

               FROM tb_pakan_perencanaan as a
               LEFT JOIN kandang as b ON a.id_kandang = b.id_kandang
               LEFT JOIN populasi as c ON c.id_kandang = a.id_kandang AND c.tgl = a.tgl
               LEFT JOIN (
                   SELECT d.tgl, d.id_kandang, sum(d.pcs) as pcs, sum(d.kg) as kg
                   FROM stok_telur as d
                   group by d.tgl, d.id_kandang
               ) as d ON d.id_kandang = a.id_kandang AND d.tgl = a.tgl
               LEFT JOIN (
                   SELECT a.tgl,a.id_kandang, sum(a.pcs) as normalPcs, sum(a.kg) as normalKg FROM stok_telur as a
                   WHERE a.id_telur != 2 AND a.id_kandang = '$this->id_kandang'
                   GROUP BY a.tgl
               ) as normal ON normal.id_kandang = a.id_kandang AND normal.tgl = a.tgl
               LEFT JOIN (
                   SELECT a.tgl,a.id_kandang, sum(a.pcs) as abnormalPcs, sum(a.kg) as abnormalKg FROM stok_telur as a
                   WHERE a.id_telur = 2 AND a.id_kandang = '$this->id_kandang'
                   GROUP BY a.tgl
               ) as abnormal ON abnormal.id_kandang = a.id_kandang AND abnormal.tgl = a.tgl
               
               LEFT JOIN (
                SELECT 
                    a.tgl, 
                    a.id_kandang, 
                    SUM(a.mati) OVER (ORDER BY a.tgl) AS mati, 
                    SUM(a.jual) OVER (ORDER BY a.tgl) AS jual
                    FROM populasi as a
                    WHERE 
                    a.id_kandang = '$this->id_kandang' AND 
                    a.tgl BETWEEN '2020-01-01' AND NOW()
                    ORDER BY a.tgl
               ) as populasi ON populasi.id_kandang = a.id_kandang and populasi.tgl = a.tgl
               
               LEFT JOIN (
                   SELECT tgl, id_kandang,sum(mati) as mati, sum(jual) as jual FROM `populasi` WHERE id_kandang = '$this->id_kandang' GROUP BY tgl
               ) as e ON e.id_kandang = a.id_kandang and e.tgl = (a.tgl - INTERVAL 1 DAY)

               left join peformance as f on f.umur = CEIL(DATEDIFF(a.tgl, b.chick_in) / 7) and f.id_strain = b.id_strain

               left join (
                SELECT a.tgl, GROUP_CONCAT(b.nm_produk ORDER BY b.nm_produk SEPARATOR ', ') as nama_obat
                FROM stok_produk_perencanaan as a 
                LEFT JOIN tb_produk_perencanaan as b ON b.id_produk = a.id_pakan
                WHERE b.kategori IN ('obat_pakan', 'obat_air') AND a.id_kandang = '$this->id_kandang'
                GROUP BY a.tgl
               ) as g on g.tgl = a.tgl
               
        
               WHERE a.id_kandang = '$this->id_kandang'
               GROUP BY a.tgl
               ORDER BY a.tgl ASC;");


        return view('medion.export_laporan', [
            'medion' => $medion,
            'kandang' => DB::table('kandang')->join('strain', 'kandang.id_strain', 'strain.id_strain')->where('id_kandang', $this->id_kandang)->first(),
            'total_row' => $this->totalrow
        ]);
    }



    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $totalrow = $this->totalrow + 8;
                $cellRange = 'A1:Z1';
                // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                $event->sheet->getStyle('A1:Z1')->applyFromArray([
                    'font' => [
                        'name'  =>  'Arial',
                        'size'  =>  18,
                        'bold' => true
                    ]
                ]);
                $event->sheet->getStyle('A3:Z4')->applyFromArray([
                    'font' => [
                        'name'  =>  'Arial',
                        'size'  =>  10,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Pusatkan teks secara horizontal
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Pusatkan teks secara vertikal
                    ],
                ]);
                $event->sheet->getStyle('A6:Z8')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'font' => [
                        'name' => 'Arial',
                        'size' => 10,
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFF'], // Warna teks putih
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Pusatkan teks secara horizontal
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Pusatkan teks secara vertikal
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => '595959'], // Warna background biru
                    ],
                    'wrapText' => false,
                ]);

                $event->sheet->getStyle('A9:Z' . $totalrow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'font' => [
                        'name'  =>  'Arial',
                        'size'  =>  10,
                        'bold' => false
                    ]
                ]);
            },
        ];
    }
}
