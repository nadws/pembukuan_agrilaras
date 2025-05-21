<?php

namespace App\Http\Controllers;

use App\Models\LabarugiKandang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanLabarugiKandangController extends Controller
{
    public function index(Request $r)
    {
        $bulan = $r->bulan ?? date('m');
        $tahun = $r->tahun ?? date('Y');

        $tgl = date('Y-m-t', strtotime($tahun . '-' . $bulan . '-01'));
        $tgl_sebelum = date('Y-m-t', strtotime($tahun . '-' . $bulan . '-01 -1 month'));
        $tanggalBatas = Carbon::createFromDate($tahun, $bulan, 1);
        $harga = DB::table('harga_telur')
            ->whereMonth('tgl', $bulan)
            ->whereYear('tgl', $tahun)
            ->orderByDesc('tgl')
            ->first();

        // Kalau tidak ada data, ambil data terbaru secara global
        if (!$harga) {
            // Ambil harga sebelum tanggal 1 bulan tsb
            $harga = DB::table('harga_telur')
                ->where('tgl', '<', $tanggalBatas)
                ->orderByDesc('tgl')
                ->first();
        }


        $data = [
            'kandang' => LabarugiKandang::kandang($bulan, $tahun, $tgl_sebelum),
            'biaya_pokok' => LabarugiKandang::biaya_pokok($bulan, $tahun),
            'biaya_pokok2' => LabarugiKandang::biaya_pokok2($bulan, $tahun),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'harga_telur' => $harga,
            'operasional' => LabarugiKandang::operasional($bulan, $tahun),
            'tgl' => $tgl,
            'bulan_array' => DB::table('bulan')->get(),
            'tahun_array' => DB::table('jurnal_accurate')
                ->selectRaw('YEAR(tgl) as tahun')
                ->distinct()
                ->orderBy('tahun', 'desc')
                ->get(),
            'tgl_sebelum' => $tgl_sebelum,


        ];
        return view('laporan-laba-rugi-kandang.index', $data);
    }

    public function saveLabaRugiKandang(Request $r)
    {
        $bulan = $r->bulan ?? date('m');
        $tahun = $r->tahun ?? date('Y');
        $tgl = date('Y-m-t', strtotime($tahun . '-' . $bulan . '-01'));
        $tgl_sebelum = date('Y-m-t', strtotime($tahun . '-' . $bulan . '-01 -1 month'));
        $tanggalBatas = Carbon::createFromDate($tahun, $bulan, 1);
        $harga = DB::table('harga_telur')
            ->whereMonth('tgl', $bulan)
            ->whereYear('tgl', $tahun)
            ->orderByDesc('tgl')
            ->first();
        if (!$harga) {
            $harga = DB::table('harga_telur')
                ->where('tgl', '<', $tanggalBatas)
                ->orderByDesc('tgl')
                ->first();
        }
        $kandang = LabarugiKandang::kandang($bulan, $tahun, $tgl_sebelum);

        $ttl_ayam_jual = sumBk($kandang, 'jual');

        DB::table('laba_rugi_kandang')->where('tgl', $tgl)->delete();

        foreach ($kandang as $k) {
            $penjualan_telur = [
                'tgl' => $tgl,
                'kandang_id' => $k->id_kandang,
                'kode' => '400001',
                'ttl_rp' => empty($harga->harga) ? 0 : ($k->kg - $k->pcs / 180) * ($harga->harga ?? 0),
                'buku' => '1',
            ];
            DB::table('laba_rugi_kandang')->insert($penjualan_telur);

            $ayam = LabarugiKandang::ayam1($bulan, $tahun, $k->nm_kandang);
            $ayam2 = LabarugiKandang::ayam2($bulan, $tahun);

            $ttl_ayam1 = $ayam->kredit ?? 0;
            $ttl_ayam2 = empty($ayam2->kredit) ? 0 : ($ayam2->kredit / $ttl_ayam_jual) * $k->jual;
            $penjualan_ayam = [
                'tgl' => $tgl,
                'kandang_id' => $k->id_kandang,
                'kode' => '400002',
                'ttl_rp' => $ttl_ayam1 + $ttl_ayam2,
                'buku' => '1',
            ];

            DB::table('laba_rugi_kandang')->insert($penjualan_ayam);
        }
        $biaya_pokok = LabarugiKandang::biaya_pokok($bulan, $tahun);
        $biaya_pokok2 = LabarugiKandang::biaya_pokok2($bulan, $tahun);

        foreach ($biaya_pokok as $b) {
            foreach ($kandang as $k) {
                $pokok = DB::table('jurnal_accurate')
                    ->where('kode', $b->kode)
                    ->where('nm_departemen', $k->nm_kandang)
                    ->whereMonth('tgl', $bulan)
                    ->whereYear('tgl', $tahun)
                    ->first();
                $biaya_pokok = [
                    'tgl' => $tgl,
                    'kandang_id' => $k->id_kandang,
                    'kode' => $b->kode,
                    'ttl_rp' => $pokok->debit ?? 0,
                    'buku' => '1',
                ];
                DB::table('laba_rugi_kandang')->insert($biaya_pokok);
            }
        }
        $ttl_ayam = sumBk($kandang, 'ttl_ayam');
        foreach ($biaya_pokok2 as $o) {
            foreach ($kandang as $k) {
                $biaya_pokok2 = [
                    'tgl' => $tgl,
                    'kandang_id' => $k->id_kandang,
                    'kode' => $o->kode,
                    'ttl_rp' => ($o->debit / $ttl_ayam) * $k->ttl_ayam,
                    'buku' => '1',
                ];
                DB::table('laba_rugi_kandang')->insert($biaya_pokok2);
            }
        }

        $operasional = LabarugiKandang::operasional($bulan, $tahun);
        foreach ($operasional as $o) {
            foreach ($kandang as $k) {
                $operasional = [
                    'tgl' => $tgl,
                    'kandang_id' => $k->id_kandang,
                    'kode' => $o->kode,
                    'ttl_rp' => ($o->debit / $ttl_ayam) * $k->ttl_ayam,
                    'buku' => '2',
                ];
                DB::table('laba_rugi_kandang')->insert($operasional);
            }
        }

        return redirect()->route('laporanlabakandang', [
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    public function detailLabaRugiKandang(Request $r)
    {

        $data = [
            'pendapatan' => LabarugiKandang::akumulasiKandang($r->id_kandang, 'REVE'),
            'biaya_pokok' => LabarugiKandang::akumulasiKandang($r->id_kandang, 'COGS'),
            'biaya_operasional' => LabarugiKandang::akumulasiKandang($r->id_kandang, 'EXPS'),
            'kandang' => DB::select("SELECT a.kandang_id , b.nm_kandang
            FROM laba_rugi_kandang as a
            left join kandang as b on a.kandang_id = b.id_kandang 
            group by a.kandang_id
            order by b.nm_kandang asc"),
            'kandang_id' => $r->id_kandang,
        ];

        return view('laporan-laba-rugi-kandang.detail', $data);
    }
}
