<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class ExportRecordingController extends Controller
{
    public function commercial_layer(Request $r)
    {
        $id_kandang = $r->id_kandang;
        $kandang = DB::table('kandang')->where('id_kandang', $id_kandang)->first();

        $report = DB::SELECT("SELECT max(o.tgl) as tgl_week, o.id_kandang,  sum(o.kg_p_week) as kg_p_week, count(o.id_kandang) as jlh_hari, FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7) AS mgg_hd, b.pop_mati, c.pcs_week, c.kg_telur_week, d.telur
        FROM 
        ( SELECT o.id_kandang, o.tgl,sum(o.pcs_kredit) as kg_p_week
        FROM stok_produk_perencanaan as o
        left join tb_produk_perencanaan as q on q.id_produk = o.id_pakan
        where q.kategori = 'pakan' and o.id_kandang ='1'
        group by o.tgl , o.id_kandang
        ) as o
        left join kandang as p on p.id_kandang = o.id_kandang

        left join (
                SELECT b.id_kandang ,FLOOR(DATEDIFF(b.tgl, c.chick_in) / 7) AS mgg_pop, sum(b.mati+b.jual) as pop_mati
                FROM populasi as b
                left JOIN kandang as c on c.id_kandang = b.id_kandang
            	where  b.id_kandang = '1'
                group by b.id_kandang,FLOOR(DATEDIFF(b.tgl, c.chick_in) / 7)
        ) as b on  b.mgg_pop = FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7)

        left join (
                SELECT b.id_kandang ,FLOOR(DATEDIFF(b.tgl, c.chick_in) / 7) AS mgg_telur, sum(b.pcs) as pcs_week,sum(b.kg) as kg_telur_week
                FROM stok_telur as b
                left JOIN kandang as c on c.id_kandang = b.id_kandang
            	where b.id_kandang = '1'
                group by b.id_kandang,FLOOR(DATEDIFF(b.tgl, c.chick_in) / 7)
        ) as c on  c.mgg_telur = FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7)

        left join peformance as d on d.id_strain = p.id_strain and d.umur = FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7)

        group by FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7), p.id_kandang
        order by FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7) ASC");



        $spreadsheet = new Spreadsheet;

        $style = array(
            'font' => array(
                'size' => 9,
                'color' => array('argb' => '0000FF')
            ),
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => 'FFFF00') // Warna kuning (yellow)

            ),
        );
        $style_atas = array(
            'font' => array(
                'size' => 9,
                'setBold' => true,
                'color' => array('argb' => '0000FF') // Warna biru (blue)
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => 'FFFF00') // Warna kuning (yellow)
            ),
        );
        $style2 = array(
            'font' => array(
                'size' => 9,
            ),
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            )
        );
        $style3 = array(
            'font' => array(
                'size' => 9,
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT // Rata kanan
            )
        );
        $style4 = array(
            'font' => array(
                'size' => 9,
            ),
        );
        $style_biru_muda = array(
            'font' => ['color' => ['rgb' => '0000FF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ADD8E6']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'font' => ['size' => 9],
        );


        // daily production
        $pullet = 'tes';
        $spreadsheet->setActiveSheetIndex(0);
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('COMMERCIAL LAYER');

        $sheet1->setCellValue('A1', 'COMMERCIAL LAYER PRODUCTION');
        $sheet1->setCellValue('A2', 'RECORD PRODUCTION FARM AFTER TRANSFER 14 WEEKS');
        $sheet1->setCellValue('A3', 'HEN HOUSE / POPULATION   :');
        $sheet1->setCellValue('A4', 'HOUSE    :');
        $sheet1->setCellValue('F3', $kandang->stok_awal);
        $sheet1->setCellValue('F4', $kandang->nm_kandang);

        $sheet1->mergeCells("A1:Z1");
        $sheet1->mergeCells("A3:E3");
        $sheet1->mergeCells("A4:E4");
        $sheet1->getStyle('A1:Z1')->applyFromArray($style_atas);
        $sheet1->getStyle('A3:F4')->applyFromArray($style3);
        $sheet1->getStyle('A2')->applyFromArray($style4);
        $sheet1->getStyle('A6:Z8')->applyFromArray($style);

        $sheet1->setCellValue('A6', 'DATE');
        $sheet1->setCellValue('B6', 'WEEK OF');
        $sheet1->setCellValue('D6', 'CHICK');
        $sheet1->setCellValue('E6', 'DEPLETION');
        $sheet1->setCellValue('I6', 'FEED CONSUMTION (kg)');
        $sheet1->setCellValue('L6', 'EGG PRODUCTION');
        $sheet1->setCellValue('R6', 'EGG WEIGHT');
        $sheet1->setCellValue('T6', 'WEIGHT EGG PRODUCTION');
        $sheet1->setCellValue('X6', 'FCR');
        $sheet1->setCellValue('Z6', 'kg EGG/');

        $sheet1->setCellValue('A7', 'END OF');
        $sheet1->setCellValue('B7', 'PROD');
        $sheet1->setCellValue('D7', 'AMOUNT');
        $sheet1->setCellValue('E7', 'PER WEEK');
        $sheet1->setCellValue('G7', 'CUM');
        $sheet1->setCellValue('I7', 'PER');
        $sheet1->setCellValue('J7', '');
        $sheet1->setCellValue('K7', 'FEED/DAY');
        $sheet1->setCellValue('L7', 'PER');
        $sheet1->setCellValue('M7', 'CUM');
        $sheet1->setCellValue('N7', '%HD');
        $sheet1->setCellValue('P7', 'CUM HH');
        $sheet1->setCellValue('R7', '(GRAM/BUTIR)');
        $sheet1->setCellValue('T7', 'PER WEEK');
        $sheet1->setCellValue('V7', 'CUM HH (KG)');
        $sheet1->setCellValue('X7', 'PER');
        $sheet1->setCellValue('Y7', 'CUM');
        $sheet1->setCellValue('Z7', '100 BIRDS/');

        $sheet1->setCellValue('A8', 'WEEK');
        $sheet1->setCellValue('B8', 'AGE');
        $sheet1->setCellValue('C8', 'AOL');
        $sheet1->setCellValue('D8', '');
        $sheet1->setCellValue('E8', 'BIRD');
        $sheet1->setCellValue('F8', '%');
        $sheet1->setCellValue('G8', 'BIRD');
        $sheet1->setCellValue('H8', '%');
        $sheet1->setCellValue('I8', 'WEEK');
        $sheet1->setCellValue('J8', 'CUM');
        $sheet1->setCellValue('K8', '100/BIRDS');
        $sheet1->setCellValue('L8', 'WEEK');
        $sheet1->setCellValue('M8', '');
        $sheet1->setCellValue('N8', 'STD');
        $sheet1->setCellValue('O8', 'ACT');
        $sheet1->setCellValue('P8', 'STD');
        $sheet1->setCellValue('Q8', 'ACT');
        $sheet1->setCellValue('R8', 'STD');
        $sheet1->setCellValue('S8', 'ACT');
        $sheet1->setCellValue('T8', 'KG');
        $sheet1->setCellValue('U8', 'CUM');
        $sheet1->setCellValue('V8', 'STD');
        $sheet1->setCellValue('W8', 'ACT');
        $sheet1->setCellValue('X8', 'WEEK');
        $sheet1->setCellValue('Y8', '');
        $sheet1->setCellValue('Z8', 'DAY');

        $kolom = 9;
        $pop_mati = 0;
        $kg_pakan_week = 0;
        $pcs_telur_week = 0;
        $kg_telur_week = 0;
        foreach ($report as $r) {
            $pop_mati += $r->pop_mati;
            $kg_pakan_week += $r->kg_p_week / 1000;
            $pcs_telur_week += $r->pcs_week;
            $kg_telur_week += empty($r->pcs_week) ? '0' : $r->kg_telur_week - ($r->pcs_week / 180);



            $sheet1->setCellValue('A' . $kolom, $r->tgl_week);
            $sheet1->setCellValue('B' . $kolom, $r->mgg_hd);
            $sheet1->setCellValue('C' . $kolom, '(AOL)');
            $sheet1->setCellValue('D' . $kolom, $kandang->stok_awal - $pop_mati);
            $sheet1->getStyle('E' . $kolom)->applyFromArray($style_biru_muda);
            $sheet1->setCellValue('E' . $kolom, $r->pop_mati);
            $sheet1->setCellValue('F' . $kolom, '(%)');
            $sheet1->setCellValue('G' . $kolom, $pop_mati);
            $sheet1->setCellValue('H' . $kolom, round(($pop_mati / $kandang->stok_awal) * 100, 2));
            $sheet1->getStyle('I' . $kolom)->applyFromArray($style_biru_muda);

            $sheet1->setCellValue('I' . $kolom, round($r->kg_p_week / 1000, 2));
            $sheet1->setCellValue('J' . $kolom, round($kg_pakan_week, 2));
            $sheet1->setCellValue('K' . $kolom, round(((($r->kg_p_week / 1000) / 7) / ($kandang->stok_awal - $pop_mati)) * 100, 2));
            $sheet1->getStyle('L' . $kolom)->applyFromArray($style_biru_muda);
            $sheet1->getStyle('N' . $kolom)->applyFromArray([
                'font' => [
                    'color' => array('argb' => 'FFFFFF'),
                    'size' => 9
                ], // Set warna teks menjadi putih (#FFFFFF)
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D00A5']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],

            ]);
            $sheet1->getStyle('T' . $kolom)->applyFromArray($style_biru_muda);
            $sheet1->setCellValue('L' . $kolom, empty($r->pcs_week) ? '0' : $r->pcs_week);
            $sheet1->setCellValue('M' . $kolom, $pcs_telur_week);
            $sheet1->setCellValue('N' . $kolom, $r->telur);
            $sheet1->setCellValue('O' . $kolom, round(((($r->pcs_week / 7) / ($kandang->stok_awal - $pop_mati)) * 100), 2));
            $sheet1->setCellValue('P' . $kolom, 'STD');
            $sheet1->setCellValue('Q' . $kolom, round($pcs_telur_week / $kandang->stok_awal, 2));
            $sheet1->setCellValue('R' . $kolom, 'STD');
            $sheet1->setCellValue('S' . $kolom, empty($r->pcs_week) ? '0' : round((($r->kg_telur_week - ($r->pcs_week / 180)) / $r->pcs_week) * 1000, 2));
            $sheet1->setCellValue('T' . $kolom, empty($r->pcs_week) ? '0' : round($r->kg_telur_week - ($r->pcs_week / 180), 2));
            $kg_telurnull = empty($r->pcs_week) ? '0' : $r->kg_telur_week - ($r->pcs_week / 180);
            $sheet1->setCellValue('U' . $kolom, round($kg_telur_week, 2));
            $sheet1->setCellValue('V' . $kolom, 'STD');
            $sheet1->setCellValue('W' . $kolom, round($kg_telur_week / $kandang->stok_awal, 2));
            $sheet1->setCellValue('X' . $kolom, $kg_telurnull == '0' ? '0' :  round(($r->kg_p_week / 1000) / $kg_telurnull, 2));
            $sheet1->setCellValue('Y' . $kolom, $kg_telur_week == '0' ? '0' : round($kg_pakan_week / $kg_telur_week, 2));
            $p = ($kandang->stok_awal - $pop_mati);
            $sheet1->setCellValue('Z' . $kolom, round((($kg_telurnull / 7) / $p) * 100, 2));
            $kolom++;
        }



        $sheet1->mergeCells("B6:C6");
        $sheet1->mergeCells("E6:H6");
        $sheet1->mergeCells("I6:K6");
        $sheet1->mergeCells("L6:Q6");
        $sheet1->mergeCells("R6:S6");
        $sheet1->mergeCells("T6:W6");
        $sheet1->mergeCells("X6:Y6");

        $sheet1->mergeCells("B7:C7");
        $sheet1->mergeCells("E7:F7");
        $sheet1->mergeCells("G7:H7");
        $sheet1->mergeCells("N7:O7");
        $sheet1->mergeCells("P7:Q7");
        $sheet1->mergeCells("R7:S7");
        $sheet1->mergeCells("T7:U7");
        $sheet1->mergeCells("V7:W7");



        // end vaksin ---------------------------------------------
        $batas = $kolom - 1;
        $spreadsheet->getActiveSheet()->getStyle('A9:Z' . $batas)->applyFromArray($style2);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Commercial Layer Production.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }
}
