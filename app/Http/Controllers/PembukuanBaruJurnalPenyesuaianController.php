<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PembukuanBaruJurnalPenyesuaianController extends Controller
{
    public function index(Request $request) {
        $tgl1 = $request->input('tgl1', date('Y-m-01')); $tgl2 = $request->input('tgl2', date('Y-m-t')); $cari = trim((string) $request->input('cari', ''));
        $jurnal = DB::table('jurnal_perkiraan as j')->leftJoin('akun_perkiraan as a','a.id_akun_perkiraan','=','j.id_akun_perkiraan')->whereIn('j.tipe_transaksi',['Stok Opname','Penyusutan Aktiva'])->whereBetween('j.tanggal',[$tgl1,$tgl2])->when($cari, fn($q)=>$q->where(function($w) use($cari){$w->where('j.nomor_transaksi','like',"%$cari%")->orWhere('j.deskripsi','like',"%$cari%")->orWhere('a.nama','like',"%$cari%");}))->orderByDesc('j.tanggal')->orderByDesc('j.id_impor_jurnal_perkiraan')->select(['j.*','a.kode_perkiraan','a.nama as nama_akun'])->paginate(15)->withQueryString();
        return view('pembukuan_baru.jurnal_penyesuaian.index', ['title' => 'Jurnal Penyesuaian','jurnal'=>$jurnal]);
    }

    public function stokOpname()
    {
        $items = DB::table('pembukuan_baru_stok')
            ->select('id_produk','nama_produk','satuan')
            ->selectRaw('SUM(qty) as qty_masuk, SUM(qty * harga_satuan) as nilai_masuk')
            ->groupBy('id_produk','nama_produk','satuan')
            ->havingRaw('ABS(SUM(qty)) > 0.000001')
            ->orderBy('nama_produk')->get();
        return view('pembukuan_baru.jurnal_penyesuaian.stok_opname', ['title'=>'Stok Opname','items'=>$items]);
    }

    public function penyusutanAktiva(Request $request)
    {
        $tanggal = (string) $request->input('tanggal', date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || strtotime($tanggal) === false) {
            $tanggal = date('Y-m-d');
        }
        $periode = date('Y-m', strtotime($tanggal));

        $aktiva = DB::table('aktiva_pembukuan_baru as a')
            ->leftJoin('akun_perkiraan as ap','ap.id_akun_perkiraan','=','a.id_akun_aset')
            ->leftJoin('penyusutan_aktiva_pembukuan_baru as p', function ($join) use ($periode) {
                $join->on('p.id_aktiva', '=', 'a.id')->where('p.periode', '=', $periode);
            })
            ->whereColumn('a.akumulasi_penyusutan', '<', 'a.h_perolehan')
            ->whereNotNull('a.id_akun_aset')
            ->orderBy('a.tgl')
            ->get(['a.*','ap.kode_perkiraan','ap.nama as nama_akun_aset','p.id as id_penyusutan_periode'])
            ->map(function ($a) {
                $a->nilai_buku = max(0, (float) $a->h_perolehan - (float) $a->akumulasi_penyusutan);
                $a->penyusutan_bulan = !empty($a->umur_aktiva_bulan)
                    ? round((float) $a->h_perolehan / (int) $a->umur_aktiva_bulan, 2)
                    : (float) $a->biaya_depresiasi;
                $a->nominal_periode = min($a->nilai_buku, $a->penyusutan_bulan);
                $a->sisa_periode = $a->penyusutan_bulan > 0
                    ? (int) ceil($a->nilai_buku / $a->penyusutan_bulan)
                    : 0;
                $akunBiaya = DB::table('akun_perkiraan')->where('aktif', 1)
                    ->where('tipe_akun', 'EXPS')
                    ->where('nama', 'like', '%Biaya Penyusutan%')
                    ->where('nama', 'like', '%' . strtok((string) $a->nama_akun_aset, ' ') . '%')
                    ->first(['kode_perkiraan', 'nama']);
                $a->akun_biaya = $akunBiaya
                    ? $akunBiaya->kode_perkiraan . ' - ' . $akunBiaya->nama
                    : null;
                return $a;
            });

        return view('pembukuan_baru.jurnal_penyesuaian.penyusutan_aktiva', [
            'title' => 'Penyusutan Aktiva',
            'aktiva' => $aktiva,
            'tanggal' => $tanggal,
            'periode' => $periode,
        ]);
    }

    public function simpanStokOpname(Request $request)
    {
        $v = $request->validate([
            'tanggal'=>['required','date'],
            'id_produk'=>['required','array'], 'id_produk.*'=>['required','integer','distinct','exists:tb_produk,id_produk'],
            'nama_produk'=>['required','array'], 'qty_sistem'=>['required','array'],
            'qty_fisik'=>['required','array'], 'qty_fisik.*'=>['required','numeric','min:0'],
            'nilai_satuan'=>['required','array'], 'nilai_satuan.*'=>['required','numeric','min:0'],
        ]);
        $persediaan = DB::table('akun_perkiraan')->where('kode_perkiraan','110406')->where('aktif',1)->first();
        // Selisih opname barang umum dibebankan ke BPP Telur (Telur/Rak),
        // mengikuti akun yang digunakan pada jurnal pembelian umum.
        $biaya = DB::table('akun_perkiraan')->where('aktif', 1)->where('kode_perkiraan', '5101-01')->first()
            ?: DB::table('akun_perkiraan')->where('aktif', 1)->where(function($q){$q->where('nama','like','%Penyesuaian Persediaan%')->orWhere('nama','like','%Stock Opname%');})->first();
        if (!$persediaan || !$biaya) return back()->withErrors(['akun'=>'Akun Persediaan Umum (110406) atau Biaya Pokok Penjualan Telur (Telur/Rak) (5101-01) belum tersedia.'])->withInput();
        $no='SO-'.date('YmdHis').'-'.random_int(100,999); $now=now();

        $hasil = DB::transaction(function () use ($v, $persediaan, $biaya, $no, $now) {
            $kurang=0; $lebih=0; $deskripsi=[]; $penyesuaian=[];
            foreach ($v['nama_produk'] as $i=>$nama) {
                $sistem=(float)$v['qty_sistem'][$i]; $fisik=(float)$v['qty_fisik'][$i];
                $selisih=round($fisik-$sistem,6); $harga=(float)$v['nilai_satuan'][$i];
                if (abs($selisih) < 0.000001) continue;
                if ($harga <= 0) throw ValidationException::withMessages(['nilai_satuan'=>'Harga satuan '.$nama.' harus lebih dari 0 karena terdapat selisih stok.']);
                $nilai=round(abs($selisih)*$harga,2);
                $deskripsi[]=$nama; $selisih<0 ? $kurang+=$nilai : $lebih+=$nilai;
                $penyesuaian[]=['i'=>$i,'nama'=>$nama,'sistem'=>$sistem,'fisik'=>$fisik,'selisih'=>$selisih,'harga'=>$harga,'nilai'=>$nilai];
            }
            if (!$penyesuaian) return false;

            foreach ($penyesuaian as $p) {
                DB::table('pembukuan_baru_stok_opname')->insert([
                    'tanggal'=>$v['tanggal'],'id_produk'=>$v['id_produk'][$p['i']],'nama_produk'=>$p['nama'],
                    'qty_sistem'=>$p['sistem'],'qty_fisik'=>$p['fisik'],'nilai_selisih'=>$p['nilai'],
                    'nomor_transaksi'=>$no,'created_at'=>$now,'updated_at'=>$now,
                ]);
                $satuan = DB::table('tb_produk as pr')->leftJoin('tb_satuan as s','s.id_satuan','=','pr.satuan_id')
                    ->where('pr.id_produk',$v['id_produk'][$p['i']])->value('s.nm_satuan');
                DB::table('pembukuan_baru_stok')->insert([
                    'id_produk'=>$v['id_produk'][$p['i']],'nama_produk'=>$p['nama'],'satuan'=>$satuan,
                    'qty'=>$p['selisih'],'harga_satuan'=>$p['harga'],'tanggal'=>$v['tanggal'],
                    'nomor_transaksi'=>$no,'created_at'=>$now,'updated_at'=>$now,
                ]);
            }

            $net=round($kurang-$lebih,2); $rows=[]; $ringkas='Total selisih stok: '.implode(', ',$deskripsi);
            if ($net>0) {
                $rows[]=['id_akun_perkiraan'=>$biaya->id_akun_perkiraan,'debit'=>$net,'kredit'=>0];
                $rows[]=['id_akun_perkiraan'=>$persediaan->id_akun_perkiraan,'debit'=>0,'kredit'=>$net];
            } elseif ($net<0) {
                $net=abs($net);
                $rows[]=['id_akun_perkiraan'=>$persediaan->id_akun_perkiraan,'debit'=>$net,'kredit'=>0];
                $rows[]=['id_akun_perkiraan'=>$biaya->id_akun_perkiraan,'debit'=>0,'kredit'=>$net];
            }
            if ($rows) {
                $batch=DB::table('impor_jurnal_perkiraan')->insertGetId(['nama_file'=>'Stok Opname '.$no,'hash_file'=>hash('sha256',$no.$now),'periode_awal'=>$v['tanggal'],'periode_akhir'=>$v['tanggal'],'jumlah_transaksi'=>1,'jumlah_detail'=>count($rows),'total_debit'=>$net,'total_kredit'=>$net,'status'=>'aktif','diimpor_oleh'=>auth()->id(),'created_at'=>$now,'updated_at'=>$now]);
                foreach($rows as $urutan=>&$r) $r += ['tanggal'=>$v['tanggal'],'nomor_transaksi'=>$no,'tipe_transaksi'=>'Stok Opname','urutan_detail'=>$urutan+1,'deskripsi'=>$ringkas,'created_at'=>$now,'updated_at'=>$now,'id_impor_jurnal_perkiraan'=>$batch];
                DB::table('jurnal_perkiraan')->insert($rows);
            }
            return true;
        });
        if (!$hasil) return back()->with('sukses','Tidak ada selisih stok. Stok sistem sudah sama dengan stok fisik.');
        return redirect()->route('gudang-persediaan.barang-umum')->with('sukses','Stok opname berhasil disimpan, stok sistem sudah diperbarui, dan jurnal penyesuaian sudah dibuat.');
    }

    public function simpanPenyusutanGrouped(Request $request)
    {
        $v = $request->validate([
            'tanggal' => ['required', 'date'],
            'id_aktiva' => ['required', 'array', 'min:1'],
            'id_aktiva.*' => ['required', 'integer', 'distinct', 'exists:aktiva_pembukuan_baru,id'],
        ]);
        $periode = date('Y-m', strtotime($v['tanggal']));

        $hasil = DB::transaction(function () use ($v, $periode) {
            $aktiva = DB::table('aktiva_pembukuan_baru')
                ->whereIn('id', $v['id_aktiva'])->lockForUpdate()->get();
            $sudahDiproses = DB::table('penyusutan_aktiva_pembukuan_baru')
                ->where('periode', $periode)->whereIn('id_aktiva', $v['id_aktiva'])
                ->pluck('id_aktiva')->map(fn ($id) => (int) $id)->all();

            $debit = [];
            $kredit = [];
            $detailAktiva = [];
            $akunTidakLengkap = [];
            $dilewati = 0;

            foreach ($aktiva as $a) {
                if (in_array((int) $a->id, $sudahDiproses, true)) {
                    $dilewati++;
                    continue;
                }

                $akunAset = DB::table('akun_perkiraan')->where('aktif', 1)
                    ->where('id_akun_perkiraan', $a->id_akun_aset)->first();
                if (!$akunAset) {
                    $akunTidakLengkap[] = $a->nm_aktiva . ' (akun aset tidak ditemukan)';
                    continue;
                }
                $akunBiaya = DB::table('akun_perkiraan')->where('aktif', 1)
                    ->where('tipe_akun', 'EXPS')
                    ->where('nama', 'like', '%Biaya Penyusutan%')
                    ->where('nama', 'like', '%' . strtok($akunAset->nama, ' ') . '%')
                    ->first();
                if (!$akunBiaya) {
                    $akunTidakLengkap[] = $a->nm_aktiva . ' (biaya penyusutan ' . $akunAset->nama . ' tidak ditemukan)';
                    continue;
                }

                $nilaiBuku = max(0, (float) $a->h_perolehan - (float) $a->akumulasi_penyusutan);
                $penyusutanBulanan = !empty($a->umur_aktiva_bulan)
                    ? round((float) $a->h_perolehan / (int) $a->umur_aktiva_bulan, 2)
                    : (float) $a->biaya_depresiasi;
                $nilai = round(min($nilaiBuku, $penyusutanBulanan), 2);
                if ($nilai <= 0) {
                    $dilewati++;
                    continue;
                }

                $debit[$akunBiaya->id_akun_perkiraan] = ($debit[$akunBiaya->id_akun_perkiraan] ?? 0) + $nilai;
                $kredit[$akunAset->id_akun_perkiraan] = ($kredit[$akunAset->id_akun_perkiraan] ?? 0) + $nilai;
                $detailAktiva[] = compact('a', 'nilai', 'nilaiBuku', 'penyusutanBulanan');
            }

            if ($akunTidakLengkap) {
                throw ValidationException::withMessages([
                    'akun' => 'Akun belum lengkap: ' . implode(', ', $akunTidakLengkap),
                ]);
            }
            if (!$detailAktiva) return ['diproses' => 0, 'dilewati' => $dilewati];

            $now = now();
            $nomor = 'JPA-' . date('YmdHis') . '-' . random_int(100, 999);
            $rows = [];
            foreach ($debit as $idAkun => $nilai) {
                $rows[] = ['id_akun_perkiraan'=>$idAkun,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$nomor,'tipe_transaksi'=>'Penyusutan Aktiva','urutan_detail'=>count($rows)+1,'deskripsi'=>'Biaya penyusutan aktiva periode '.date('m-Y',strtotime($v['tanggal'])),'debit'=>$nilai,'kredit'=>0,'created_at'=>$now,'updated_at'=>$now];
            }
            foreach ($kredit as $idAkun => $nilai) {
                $rows[] = ['id_akun_perkiraan'=>$idAkun,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$nomor,'tipe_transaksi'=>'Penyusutan Aktiva','urutan_detail'=>count($rows)+1,'deskripsi'=>'Pengurangan nilai buku aset periode '.date('m-Y',strtotime($v['tanggal'])),'debit'=>0,'kredit'=>$nilai,'created_at'=>$now,'updated_at'=>$now];
            }
            $total = round(array_sum($debit), 2);
            $batch = DB::table('impor_jurnal_perkiraan')->insertGetId(['nama_file'=>'Penyusutan Aktiva '.$nomor,'hash_file'=>hash('sha256',$nomor.$now),'periode_awal'=>$v['tanggal'],'periode_akhir'=>$v['tanggal'],'jumlah_transaksi'=>1,'jumlah_detail'=>count($rows),'total_debit'=>$total,'total_kredit'=>$total,'status'=>'aktif','diimpor_oleh'=>auth()->id(),'created_at'=>$now,'updated_at'=>$now]);
            foreach ($rows as &$row) $row['id_impor_jurnal_perkiraan'] = $batch;
            DB::table('jurnal_perkiraan')->insert($rows);

            foreach ($detailAktiva as $detail) {
                $a = $detail['a'];
                $nilai = $detail['nilai'];
                $akumulasiBaru = min((float) $a->h_perolehan, (float) $a->akumulasi_penyusutan + $nilai);
                $nilaiBukuBaru = max(0, (float) $a->h_perolehan - $akumulasiBaru);
                $sisaPeriode = $detail['penyusutanBulanan'] > 0 ? (int) ceil($nilaiBukuBaru / $detail['penyusutanBulanan']) : 0;
                DB::table('aktiva_pembukuan_baru')->where('id', $a->id)->update([
                    'biaya_depresiasi' => $detail['penyusutanBulanan'],
                    'akumulasi_penyusutan' => $akumulasiBaru,
                    'sisa_umur_bulan' => $sisaPeriode,
                    'updated_at' => $now,
                ]);
                DB::table('penyusutan_aktiva_pembukuan_baru')->insert([
                    'id_aktiva' => $a->id,
                    'periode' => $periode,
                    'tanggal' => $v['tanggal'],
                    'nominal' => $nilai,
                    'nomor_transaksi' => $nomor,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return ['diproses' => count($detailAktiva), 'dilewati' => $dilewati];
        });

        if (!$hasil['diproses']) {
            return back()->with('sukses', 'Tidak ada penyusutan baru. Aktiva yang dipilih sudah diproses pada periode tersebut atau nilai bukunya sudah habis.');
        }
        $pesan = $hasil['diproses'] . ' aktiva berhasil disusutkan dan jurnal penyesuaian sudah dibuat.';
        if ($hasil['dilewati']) $pesan .= ' ' . $hasil['dilewati'] . ' aktiva dilewati.';
        return redirect()->route('pembukuan-baru.jurnal-penyesuaian.index')->with('sukses', $pesan);
    }

}
