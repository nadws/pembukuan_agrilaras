<?php

namespace App\Http\Controllers;

use App\Exports\BukuBesarBaruDetailExport;
use App\Exports\BukuBesarBaruExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PembukuanBaruBukuBesarController extends Controller
{
    private function periode(Request $r): array
    {
        return [
            $r->tgl1 ?: (DB::table('jurnal_perkiraan')->min('tanggal') ?: date('Y-m-01')),
            $r->tgl2 ?: (DB::table('jurnal_perkiraan')->max('tanggal') ?: date('Y-m-d')),
        ];
    }

    private function queryBukuBesar(string $tgl1, string $tgl2, string $cari = '')
    {
        return DB::table('akun_perkiraan as a')
            ->leftJoin('jurnal_perkiraan as j', function ($q) use ($tgl1, $tgl2) {
                $q->on('j.id_akun_perkiraan', '=', 'a.id_akun_perkiraan')
                    ->whereBetween('j.tanggal', [$tgl1, $tgl2]);
            })
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($w) use ($cari) {
                    $w->where('a.kode_perkiraan', 'like', "%{$cari}%")
                        ->orWhere('a.nama', 'like', "%{$cari}%");
                });
            })
            ->select('a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama', 'a.tipe_akun')
            ->selectRaw('COALESCE(SUM(j.debit), 0) as debit, COALESCE(SUM(j.kredit), 0) as kredit, COALESCE(SUM(j.debit - j.kredit), 0) as saldo')
            ->groupBy('a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama', 'a.tipe_akun')
            ->orderBy('a.kode_perkiraan');
    }

    public function index(Request $r)
    {
        [$tgl1, $tgl2] = $this->periode($r);
        $cari = trim((string) $r->cari);

        $buku = $this->queryBukuBesar($tgl1, $tgl2, $cari)
            ->paginate(500)
            ->withQueryString();

        return view('pembukuan_baru.buku_besar.index', compact('buku', 'tgl1', 'tgl2') + ['title' => 'Buku Besar']);
    }

    public function export(Request $r): BinaryFileResponse
    {
        [$tgl1, $tgl2] = $this->periode($r);
        $cari = trim((string) $r->cari);

        $data = $this->queryBukuBesar($tgl1, $tgl2, $cari)->get();
        $filename = 'buku-besar-' . date('Ymd', strtotime($tgl1)) . '-' . date('Ymd', strtotime($tgl2)) . '.xlsx';

        return Excel::download(new BukuBesarBaruExport($data, $tgl1, $tgl2), $filename);
    }

    public function detail(Request $r, int $id)
    {
        [$tgl1, $tgl2] = $this->periode($r);
        $akun = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $id)->first();
        abort_if(! $akun, 404);

        $detail = DB::table('jurnal_perkiraan')
            ->where('id_akun_perkiraan', $id)
            ->whereBetween('tanggal', [$tgl1, $tgl2])
            ->when($r->cari, function ($q) use ($r) {
                $q->where(function ($w) use ($r) {
                    $w->where('nomor_transaksi', 'like', '%' . $r->cari . '%')
                        ->orWhere('deskripsi', 'like', '%' . $r->cari . '%')
                        ->orWhere('tipe_transaksi', 'like', '%' . $r->cari . '%');
                });
            })
            ->orderBy('tanggal')
            ->orderBy('id_jurnal_perkiraan')
            ->orderBy('nomor_transaksi')
            ->paginate(20)
            ->withQueryString();

        $saldo = 0;
        foreach ($detail as $d) {
            $saldo += (float) $d->debit - (float) $d->kredit;
            $d->saldo = $saldo;
        }

        return view('pembukuan_baru.buku_besar.detail', compact('akun', 'detail', 'tgl1', 'tgl2') + ['title' => 'Detail Buku Besar']);
    }

    public function exportDetail(Request $r, int $id): BinaryFileResponse
    {
        [$tgl1, $tgl2] = $this->periode($r);
        $akun = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $id)->first();
        abort_if(! $akun, 404);

        $rows = DB::table('jurnal_perkiraan')
            ->where('id_akun_perkiraan', $id)
            ->whereBetween('tanggal', [$tgl1, $tgl2])
            ->when($r->cari, function ($q) use ($r) {
                $q->where(function ($w) use ($r) {
                    $w->where('nomor_transaksi', 'like', '%' . $r->cari . '%')
                        ->orWhere('deskripsi', 'like', '%' . $r->cari . '%')
                        ->orWhere('tipe_transaksi', 'like', '%' . $r->cari . '%');
                });
            })
            ->orderBy('tanggal')
            ->orderBy('id_jurnal_perkiraan')
            ->orderBy('nomor_transaksi')
            ->get();

        $saldo = 0;
        foreach ($rows as $d) {
            $saldo += (float) $d->debit - (float) $d->kredit;
            $d->saldo = $saldo;
        }

        $filename = 'buku-besar-' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $akun->kode_perkiraan . '_' . $akun->nama) . '-' . date('Ymd', strtotime($tgl1)) . '-' . date('Ymd', strtotime($tgl2)) . '.xlsx';

        return Excel::download(new BukuBesarBaruDetailExport($akun, $rows, $tgl1, $tgl2), $filename);
    }
}
