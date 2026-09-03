<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Penjualan_umum_cekController extends Controller
{
    protected $tgl1, $tgl2, $period;
    public function __construct(Request $r)
    {
        if (empty($r->period)) {
            $this->tgl1 = date('Y-m-01');
            $this->tgl2 = date('Y-m-t');
        } elseif ($r->period == 'daily') {
            $this->tgl1 = date('Y-m-d');
            $this->tgl2 = date('Y-m-d');
        } elseif ($r->period == 'weekly') {
            $this->tgl1 = date('Y-m-d', strtotime("-6 days"));
            $this->tgl2 = date('Y-m-d');
        } elseif ($r->period == 'mounthly') {
            $bulan = $r->bulan;
            $tahun = $r->tahun;
            $tgl = "$tahun" . "-" . "$bulan" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tgl));
            $this->tgl2 = date('Y-m-t', strtotime($tgl));
        } elseif ($r->period == 'costume') {
            $this->tgl1 = $r->tgl1;
            $this->tgl2 = $r->tgl2;
        } elseif ($r->period == 'years') {
            $tahun = $r->tahunfilter;
            $tgl_awal = "$tahun" . "-" . "01" . "-" . "01";
            $tgl_akhir = "$tahun" . "-" . "12" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tgl_awal));
            $this->tgl2 = date('Y-m-t', strtotime($tgl_akhir));
        }
    }

    public function index(Request $r)
    {
        $tgl1 = $this->tgl1;
        $tgl2 = $this->tgl2;
        $pencarian = trim((string) $r->input('pencarian', ''));
        $perPage = in_array((int) $r->input('per_page', 20), [20, 50, 100], true)
            ? (int) $r->input('per_page', 20)
            : 20;

        $dasarPenjualan = DB::table('penjualan_agl as a')
            ->leftJoin('customer as b', 'b.id_customer', '=', 'a.id_customer')
            ->where('a.lokasi', 'mtd')
            ->whereBetween('a.tgl', [$tgl1, $tgl2])
            ->when($pencarian !== '', function ($query) use ($pencarian) {
                $query->where(function ($subQuery) use ($pencarian) {
                    $subQuery->where('a.urutan', 'like', "%{$pencarian}%")
                        ->orWhere('a.nota_manual', 'like', "%{$pencarian}%")
                        ->orWhere('a.id_customer', 'like', "%{$pencarian}%")
                        ->orWhere('b.nm_customer', 'like', "%{$pencarian}%");
                });
            });

        $total = (clone $dasarPenjualan)
            ->selectRaw('COALESCE(SUM(a.total_rp), 0) as total')
            ->selectRaw("COALESCE(SUM(CASE WHEN a.cek != 'Y' OR a.cek IS NULL THEN a.total_rp ELSE 0 END), 0) as belum_dicek")
            ->first();

        $penjualan = (clone $dasarPenjualan)
            ->select('a.urutan')
            ->selectRaw('MAX(a.tgl) as tgl')
            ->selectRaw("COALESCE(MAX(NULLIF(a.kode, '')), 'PUM') as kode")
            ->selectRaw("COALESCE(MAX(NULLIF(b.nm_customer, '')), MAX(NULLIF(a.id_customer, '')), '-') as nm_customer")
            ->selectRaw('SUM(a.total_rp) as total')
            ->selectRaw('COUNT(a.id_penjualan) as ttl_produk')
            ->selectRaw('SUM(a.qty) as total_qty')
            ->selectRaw('MAX(a.cek) as cek')
            ->selectRaw('MAX(a.admin_cek) as admin_cek')
            ->groupBy('a.urutan')
            ->orderByRaw("MAX(CASE WHEN a.cek = 'Y' THEN 1 ELSE 0 END) ASC")
            ->orderByRaw('MAX(a.tgl) DESC')
            ->orderByDesc('a.urutan')
            ->paginate($perPage)
            ->withQueryString();

        $data = [
            'title' => 'Penjualan Umum Martadah',
            'penjualan' => $penjualan,
            'ttlRp' => (float) $total->total,
            'ttlRpBelumDiCek' => (float) $total->belum_dicek,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'pencarian' => $pencarian,
            'perPage' => $perPage,
        ];
        return view('penjualan_umum_cek.index', $data);
    }

    public function detailMartadah(int $urutan)
    {
        $produk = DB::table('penjualan_agl as a')
            ->join('tb_produk as b', 'a.id_produk', '=', 'b.id_produk')
            ->where('a.urutan', $urutan)
            ->where('a.lokasi', 'mtd')
            ->orderBy('a.id_penjualan')
            ->get(['a.*', 'b.nm_produk']);

        abort_if($produk->isEmpty(), 404, 'Penjualan umum Martadah tidak ditemukan.');

        $head = $produk->first();
        $customer = DB::table('customer')->where('id_customer', $head->id_customer)->value('nm_customer')
            ?: ($head->id_customer ?: '-');

        return view('penjualan_umum_cek.detail', [
            'title' => 'Detail Penjualan Umum Martadah',
            'head_jurnal' => $head,
            'customer' => $customer,
            'produk' => $produk,
        ]);
    }

    public function terima_invoice_umum_cek(Request $r)
    {
        $no_nota = $r->no_nota[0];

        $penjualan = DB::table('penjualan_agl as a')
            ->leftJoin('tb_produk as b', 'a.id_produk', '=', 'b.id_produk')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'a.id_customer')
            ->where('a.urutan', $no_nota)
            ->where('a.lokasi', 'mtd')
            ->orderBy('a.id_penjualan')
            ->get();

        if ($penjualan->isEmpty()) {
            return redirect()->route('penjualan_umum_cek', ['lokasi' => 'mtd'])
                ->with('error', 'Nota Penjualan Umum tidak ditemukan.');
        }

        $head = $penjualan->first();
        $customer = DB::table('customer')->where('id_customer', $head->id_customer)->value('nm_customer')
            ?: ($head->id_customer ?: '-');

        $data = [
            'title' => 'Penerimaan Uang Penjualan Umum',
            'nota' => $r->no_nota,
            'penjualan' => $penjualan,
            'head' => $head,
            'customer' => $customer,
            'akun' => DB::table('akun_perkiraan')
                ->where('aktif', 1)
                ->orderBy('kode_perkiraan')
                ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama', 'tipe_akun']),
        ];
        return view('penjualan_umum_cek.penerimaan_uang', $data);
    }

    public function save_cek_umum_invoice(Request $r)
    {
        $produkNames = DB::table('tb_produk')->whereIn('id_produk', $r->id_produk)->pluck('nm_produk');
        $produkNames = implode(', ', $produkNames->toArray());

        $urutan = collect($r->urutan)->map(fn ($v) => (int) $v)->filter()->unique()->values()->all();
        if (empty($urutan)) {
            return back()->withErrors(['urutan' => 'Nomor nota tidak valid.'])->withInput();
        }

        $penjualan = DB::table('penjualan_agl')
            ->whereIn('urutan', $urutan)
            ->where('lokasi', 'mtd')
            ->get();

        if ($penjualan->isEmpty()) {
            return back()->withErrors(['urutan' => 'Nota Penjualan Umum tidak ditemukan.'])->withInput();
        }

        $tglPerNota = $penjualan->groupBy('urutan')->map->first()->map(fn ($row) => $row->tgl);
        $tanggalNota = (string) $tglPerNota->first();
        if ($tanggalNota === '' || $tanggalNota === '0000-00-00') {
            $tanggalNota = date('Y-m-d');
        }

        $akunPiutang = DB::table('akun_perkiraan')
            ->where('kode_perkiraan', '110201')
            ->where('aktif', 1)
            ->first();

        if (! $akunPiutang) {
            return back()->withErrors(['id_akun' => 'Akun Piutang Penjualan Umum tidak ditemukan.'])->withInput();
        }

        $akunIds = collect($r->id_akun)->filter()->unique()->values();
        $akunMap = DB::table('akun_perkiraan')
            ->whereIn('id_akun_perkiraan', $akunIds)
            ->where('aktif', 1)
            ->get()
            ->keyBy('id_akun_perkiraan');

        DB::transaction(function () use ($r, $produkNames, $akunPiutang, $akunMap, $urutan, $tglPerNota, $tanggalNota, $penjualan) {
            $now = now();

            $detailRows = [];

            foreach ($urutan as $noUrutan) {
                $totalNota = round((float) $penjualan->where('urutan', $noUrutan)->sum('total_rp'), 2);
                $tanggalNotaUtk = $tglPerNota[$noUrutan] ?? $tanggalNota;
                $noNota = 'PUM-' . $noUrutan;

                $customer = $penjualan->where('urutan', $noUrutan)->first();
                $deskripsi = $noNota . ':' . ($customer->id_customer ?? '') . ' ' . $produkNames;

                $detailRows[] = [
                    'id_akun_perkiraan' => $akunPiutang->id_akun_perkiraan,
                    'tanggal' => $tanggalNotaUtk,
                    'nomor_transaksi' => $noNota,
                    'tipe_transaksi' => 'Penjualan Umum',
                    'urutan_detail' => count($detailRows) + 1,
                    'deskripsi' => $deskripsi,
                    'debit' => 0,
                    'kredit' => $totalNota,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                DB::table('bayar_umum')->insert([
                    'tgl' => $tanggalNotaUtk,
                    'no_nota' => $noUrutan,
                    'debit' => 0,
                    'kredit' => $totalNota,
                ]);
            }

            for ($x = 0; $x < count($r->id_akun); $x++) {
                if (empty($r->id_akun[$x])) continue;

                $idAkun = (int) $r->id_akun[$x];
                $akun = $akunMap[$idAkun] ?? null;
                if (! $akun) continue;

                $debit = round((float) ($r->debit[$x] ?? 0), 2);
                $kredit = round((float) ($r->kredit[$x] ?? 0), 2);
                if ($debit <= 0 && $kredit <= 0) continue;

                $noNota = 'PUM-' . $urutan[0];
                $detailRows[] = [
                    'id_akun_perkiraan' => $idAkun,
                    'tanggal' => $tanggalNota,
                    'nomor_transaksi' => $noNota,
                    'tipe_transaksi' => 'Penjualan Umum',
                    'urutan_detail' => count($detailRows) + 1,
                    'deskripsi' => 'Penjualan lain-lain di Martadah : ' . $produkNames,
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (($akun->tipe_akun ?? '') !== 'AREC') {
                    DB::table('bayar_umum')->insert([
                        'tgl' => $tanggalNota,
                        'no_nota' => $urutan[0],
                        'debit' => $debit,
                        'kredit' => $kredit,
                        'no_nota_piutang' => 'PUM-' . $urutan[0],
                    ]);
                }
            }

            DB::table('penjualan_agl')
                ->whereIn('urutan', $urutan)
                ->where('lokasi', 'mtd')
                ->update(['cek' => 'Y', 'admin_cek' => Auth::user()->name]);

            $totalDebit = collect($detailRows)->sum('debit');
            $totalKredit = collect($detailRows)->sum('kredit');

            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Penjualan umum Martadah ' . implode(',', $urutan),
                'hash_file' => hash('sha256', 'penjualan-umum-martadah|' . implode(',', $urutan)),
                'periode_awal' => $tanggalNota,
                'periode_akhir' => $tanggalNota,
                'jumlah_transaksi' => count($urutan),
                'jumlah_detail' => count($detailRows),
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($detailRows as &$row) {
                $row['id_impor_jurnal_perkiraan'] = $batchId;
            }
            DB::table('jurnal_perkiraan')->insert($detailRows);
        });

        return redirect()->route('penjualan_umum_cek')->with('sukses', 'Data berhasil ditambahkan');
    }

    public function penyetoran(Request $r)
    {
        $tgl1 = $this->tgl1;
        $tgl2 = $this->tgl2;

        $invoice = DB::table('jurnal_perkiraan as j')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('j.tipe_transaksi', 'Penjualan Umum')
            ->where('j.tanggal', '>=', $tgl1)
            ->where('j.tanggal', '<=', $tgl2)
            ->where('a.kode_perkiraan', '!=', '400001')
            ->where('j.debit', '>', 0)
            ->whereNotIn('j.id_jurnal_perkiraan', function ($query) {
                $query->select('jurnal_perkiraan_id')->from('setoran_kas_detail');
            })
            ->groupBy('j.nomor_transaksi')
            ->orderBy('j.tanggal')
            ->get([
                'j.id_jurnal_perkiraan',
                'j.tanggal as tgl',
                'j.nomor_transaksi as no_nota',
                'a.nama as nm_akun',
                'j.deskripsi as ket',
                'j.debit',
                'a.id_akun_perkiraan as id_akun',
            ]);

        $data = [
            'title' => 'Penyetoran Umum',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'invoice' => $invoice,
        ];
        return view('penjualan_umum_cek.penyetoran', $data);
    }

    public function perencanaan_setor(Request $r)
    {
        $max = DB::table('setoran_umum')->latest('urutan')->first();

        if (empty($max->urutan)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }

        $data = [
            'title' => 'Perencanaan Setoran Umum',
            'id_jurnal' => $r->id_jurnal,
            'akun' => DB::table('akun_perkiraan')
                ->whereIn('tipe_akun', ['AREC', 'BANK', 'OCAS'])
                ->where('aktif', 1)
                ->orderBy('kode_perkiraan')
                ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama', 'tipe_akun']),
            'nota' => $nota_t
        ];
        return view('penjualan_umum_cek.perencanaan', $data);
    }

    public function save_perencanaan(Request $r)
    {
        $max = DB::table('setoran_umum')->latest('urutan')->first();

        if (empty($max->urutan)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }
        for ($x = 0; $x < count($r->id_jurnal); $x++) {
            $data = [
                'nota_setor' => 'PEUMUM-' . $nota_t,
                'tgl' => $r->tgl,
                'id_jurnal' => $r->id_jurnal[$x],
                'no_nota_jurnal' => $r->no_nota_jurnal[$x],
                'nominal' => $r->nominal[$x],
                'urutan' => $nota_t,
                'id_akun' => $r->id_akun_pem[$x]
            ];
            DB::table('setoran_umum')->insert($data);
        }

        return redirect()->route('penyetoran_penjualan_umum')->with('sukses', 'Data berhasil ditambahkan');
    }

    public function get_history_perencanaan(Request $r)
    {
        $tgl1 = $r->tgl1 ?? date('Y-m-01');
        $tgl2 = $r->tgl2 ?? date('Y-m-t');

        $data =  [
            'invoice' => DB::table('setoran_umum as a')
                ->leftJoin('akun_perkiraan as b', 'b.id_akun_perkiraan', '=', 'a.id_akun')
                ->where('a.tgl', '>=', $tgl1)
                ->where('a.tgl', '<=', $tgl2)
                ->groupBy('a.nota_setor')
                ->selectRaw('a.tgl, a.nota_setor, b.nama as nm_akun, SUM(a.nominal) as nominal, a.selesai')
                ->get(),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
        ];
        return view('penjualan_umum_cek.history_perencanaan', $data);
    }

    public function print_setoran(Request $r)
    {
        $invoice = DB::table('setoran_umum')->where('nota_setor', $r->no_nota)->first();
        $data = [
            'invoice' => DB::table('setoran_umum as a')
                ->leftJoin('akun_perkiraan as b', 'b.id_akun_perkiraan', '=', 'a.id_akun')
                ->leftJoin('jurnal_perkiraan as c', 'c.id_jurnal_perkiraan', '=', 'a.id_jurnal')
                ->where('a.nota_setor', $r->no_nota)
                ->groupBy('a.no_nota_jurnal')
                ->selectRaw('c.tanggal as tgl, a.no_nota_jurnal, b.nama as nm_akun, c.deskripsi as nm_customer, a.nominal')
                ->orderBy('c.tanggal', 'ASC')
                ->get(),
            'akun' => DB::table('akun_perkiraan')
                ->whereIn('tipe_akun', ['AREC', 'BANK', 'OCAS'])
                ->where('aktif', 1)
                ->where('id_akun_perkiraan', '!=', $invoice->id_akun)
                ->orderBy('kode_perkiraan')
                ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']),
            'no_nota' => $r->no_nota,
            'invo' => $invoice,
            'title' => 'Print Setoran Umum'
        ];
        return view('penjualan_umum_cek.print_perencanaan', $data);
    }

    public function delete_perencanaan(Request $r)
    {
        $invoice = DB::table('setoran_umum')->where('nota_setor', $r->no_nota)->get();

        DB::table('setoran_umum')->where('nota_setor', $r->no_nota)->delete();

        return redirect()->route('penyetoran_penjualan_umum')->with('sukses', 'Data berhasil dihapus');
    }

    public function get_list_perencanaan_umum(Request $r)
    {
        $data =  [
            'invoice' => DB::table('setoran_umum as a')
                ->leftJoin('akun_perkiraan as b', 'b.id_akun_perkiraan', '=', 'a.id_akun')
                ->where('a.selesai', 'T')
                ->groupBy('a.nota_setor')
                ->selectRaw('a.tgl, a.nota_setor, b.nama as nm_akun, SUM(a.nominal) as nominal')
                ->get()
        ];
        return view('penjualan_umum_cek.list_perencanaan', $data);
    }

    public function get_perencanaan_umum(Request $r)
    {
        $invoice = DB::table('setoran_umum')->where('nota_setor', $r->no_nota)->first();
        $data = [
            'invoice' => DB::table('setoran_umum as a')
                ->leftJoin('akun_perkiraan as b', 'b.id_akun_perkiraan', '=', 'a.id_akun')
                ->leftJoin('jurnal_perkiraan as c', 'c.id_jurnal_perkiraan', '=', 'a.id_jurnal')
                ->where('a.nota_setor', $r->no_nota)
                ->selectRaw('c.tanggal as tgl, a.no_nota_jurnal, b.nama as nm_akun, c.deskripsi as ket, a.nominal')
                ->get(),
            'akun' => DB::table('akun_perkiraan')
                ->whereIn('tipe_akun', ['AREC', 'BANK', 'OCAS'])
                ->where('aktif', 1)
                ->orderBy('kode_perkiraan')
                ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama', 'tipe_akun']),
            'no_nota' => $r->no_nota,
            'invo' => $invoice
        ];
        return view('penyetoran.get_perencanaan', $data);
    }

public function save_setoran_umum(Request $r)
    {
        DB::table('setoran_umum')->where('nota_setor', $r->no_nota)->update(['selesai' => 'Y']);

        if (empty($r->id_akun)) {
            return redirect()->route('penyetoran_penjualan_umum')->with('sukses', 'Data berhasil disetor');
        }

        $akunMap = DB::table('akun_perkiraan')
            ->whereIn('id_akun_perkiraan', [$r->id_akun_kredit, $r->id_akun])
            ->where('aktif', 1)
            ->get()
            ->keyBy('id_akun_perkiraan');

        $akunKredit = $akunMap[$r->id_akun_kredit] ?? null;
        $akunDebit = $akunMap[$r->id_akun] ?? null;

        if (! $akunKredit || ! $akunDebit) {
            return back()->withErrors(['id_akun' => 'Akun perkiraan tidak valid.'])->withInput();
        }

        DB::transaction(function () use ($r, $akunKredit, $akunDebit) {
            $now = now();

            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Setoran umum ' . $r->no_nota,
                'hash_file' => hash('sha256', 'setoran-umum|' . $r->no_nota),
                'periode_awal' => $r->tgl,
                'periode_akhir' => $r->tgl,
                'jumlah_transaksi' => 1,
                'jumlah_detail' => 2,
                'total_debit' => $r->total_setor,
                'total_kredit' => $r->total_setor,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('jurnal_perkiraan')->insert([
                [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunKredit->id_akun_perkiraan,
                    'tanggal' => $r->tgl,
                    'nomor_transaksi' => $r->no_nota,
                    'tipe_transaksi' => 'Setoran Umum',
                    'urutan_detail' => 1,
                    'deskripsi' => $r->ket,
                    'debit' => 0,
                    'kredit' => $r->total_setor,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunDebit->id_akun_perkiraan,
                    'tanggal' => $r->tgl,
                    'nomor_transaksi' => $r->no_nota,
                    'tipe_transaksi' => 'Setoran Umum',
                    'urutan_detail' => 2,
                    'deskripsi' => $r->ket,
                    'debit' => $r->total_setor,
                    'kredit' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        });

        return redirect()->route('penyetoran_penjualan_umum')->with('sukses', 'Data berhasil disetor');
    }
}
