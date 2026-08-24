<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PembukuanBaruJurnalPenyesuaianController extends Controller
{
    public function index(Request $request) {
        $tgl1 = $request->input('tgl1', date('Y-m-01')); $tgl2 = $request->input('tgl2', date('Y-m-t')); $cari = trim((string) $request->input('cari', ''));
        $jurnal = DB::table('jurnal_perkiraan as j')->leftJoin('akun_perkiraan as a','a.id_akun_perkiraan','=','j.id_akun_perkiraan')->whereIn('j.tipe_transaksi',['Stok Opname','Penyusutan Aktiva'])->whereBetween('j.tanggal',[$tgl1,$tgl2])->when($cari, fn($q)=>$q->where(function($w) use($cari){$w->where('j.nomor_transaksi','like',"%$cari%")->orWhere('j.deskripsi','like',"%$cari%")->orWhere('a.nama','like',"%$cari%");}))->orderByDesc('j.tanggal')->orderByDesc('j.id_impor_jurnal_perkiraan')->select(['j.*','a.kode_perkiraan','a.nama as nama_akun'])->paginate(15)->withQueryString();
        return view('pembukuan_baru.jurnal_penyesuaian.index', ['title' => 'Jurnal Penyesuaian','jurnal'=>$jurnal]);
    }

    public function stokOpname()
    {
        $items = DB::table('pembukuan_baru_stok')->select('id_produk','nama_produk','satuan')->selectRaw('SUM(qty) as qty_masuk, SUM(qty * harga_satuan) as nilai_masuk')->groupBy('id_produk','nama_produk','satuan')->orderBy('nama_produk')->get();
        if ($items->isEmpty()) {
            $items = DB::table('jurnal_perkiraan')->where('tipe_transaksi','Pembelian Umum')->where('debit','>',0)->get(['deskripsi','debit'])->map(function ($r) {
                preg_match('/^Pembelian\s+(.+?)\s+\(([0-9.,]+)\s+([^@]+)\s+@\s+Rp\s+([0-9.,]+)/i', $r->deskripsi, $m);
                return (object) ['nama_produk' => $m[1] ?? $r->deskripsi, 'satuan' => trim($m[3] ?? '-'), 'qty_masuk' => (float) str_replace(',', '', $m[2] ?? 0), 'nilai_masuk' => (float) $r->debit];
            })->groupBy(fn($r) => strtolower($r->nama_produk))->map(function($rows){$first=$rows->first();$first->qty_masuk=$rows->sum('qty_masuk');$first->nilai_masuk=$rows->sum('nilai_masuk');return $first;})->values();
        }
        return view('pembukuan_baru.jurnal_penyesuaian.stok_opname', ['title'=>'Stok Opname','items'=>$items]);
    }

    public function penyusutanAktiva()
    {
        $aktiva = DB::table('aktiva_pembukuan_baru as a')->leftJoin('kelompok_aktiva as k','k.id_kelompok','=','a.id_kelompok')->leftJoin('akun_perkiraan as ap','ap.id_akun_perkiraan','=','a.id_akun_aset')->orderBy('a.tgl')->get(['a.*','k.nm_kelompok','ap.kode_perkiraan','ap.nama as nama_akun_aset']);
        return view('pembukuan_baru.jurnal_penyesuaian.penyusutan_aktiva', ['title'=>'Penyusutan Aktiva','aktiva'=>$aktiva]);
    }

    public function simpanPenyusutan(Request $request)
    {
        $v=$request->validate(['tanggal'=>'required|date','id_aktiva'=>'required|array']); $aktiva=DB::table('aktiva_pembukuan_baru')->whereIn('id',$v['id_aktiva'])->get(); if($aktiva->isEmpty()) return back()->with('sukses','Tidak ada aktiva dipilih.');
        $now=now();$no='JPA-'.date('YmdHis');$total=0;$rows=[];$akunBiaya=[];$akunAkumulasi=DB::table('akun_perkiraan')->where('kode_perkiraan','1200060')->where('aktif',1)->first(); $aktiva=$aktiva->map(function($a){if(!$a->id_akun_aset)$a->id_akun_aset=79;return $a;}); foreach($aktiva as $a){$akumulasi=min((float)$a->h_perolehan,(float)($a->akumulasi_penyusutan??0)+(float)$a->biaya_depresiasi);DB::table('aktiva_pembukuan_baru')->where('id',$a->id)->update(['akumulasi_penyusutan'=>$akumulasi,'sisa_umur_bulan'=>!is_null($a->sisa_umur_bulan)?max(0,$a->sisa_umur_bulan-1):null,'updated_at'=>now()]);}
        foreach($aktiva as $a){$akun=DB::table('akun_perkiraan')->where('id_akun_perkiraan',$a->id_akun_aset)->first(); if(!$akun) continue; $biaya=DB::table('akun_perkiraan')->where('aktif',1)->where('nama','like','%Penyusutan%')->where('nama','like','%'.strtok($akun->nama,' ').'%')->first() ?: DB::table('akun_perkiraan')->where('aktif',1)->where('nama','like','%Penyusutan%')->first(); if(!$biaya) continue; $nilai=(float)$a->biaya_depresiasi;$total+=$nilai;$akunBiaya[$biaya->id_akun_perkiraan]=($akunBiaya[$biaya->id_akun_perkiraan]??0)+$nilai;}
        foreach($akunBiaya as $id=>$nilai){$rows[]=['id_akun_perkiraan'=>$id,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$no,'tipe_transaksi'=>'Penyusutan Aktiva','urutan_detail'=>count($rows)+1,'deskripsi'=>'Penyusutan aktiva bulan '.date('m-Y',strtotime($v['tanggal'])),'debit'=>$nilai,'kredit'=>0,'created_at'=>$now,'updated_at'=>$now];}
        $aset=DB::table('akun_perkiraan')->where('kode_perkiraan','110406')->first(); if(!$rows||!$aset)return back()->withErrors(['akun'=>'Akun biaya penyusutan atau Persediaan Umum belum tersedia.']);$rows[]=['id_akun_perkiraan'=>$aset->id_akun_perkiraan,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$no,'tipe_transaksi'=>'Penyusutan Aktiva','urutan_detail'=>count($rows)+1,'deskripsi'=>'Akumulasi penyusutan aktiva','debit'=>0,'kredit'=>$total,'created_at'=>$now,'updated_at'=>$now];$batch=DB::table('impor_jurnal_perkiraan')->insertGetId(['nama_file'=>'Penyusutan Aktiva '.$no,'hash_file'=>hash('sha256',$no.$now),'periode_awal'=>$v['tanggal'],'periode_akhir'=>$v['tanggal'],'jumlah_transaksi'=>1,'jumlah_detail'=>count($rows),'total_debit'=>$total,'total_kredit'=>$total,'status'=>'aktif','diimpor_oleh'=>auth()->id(),'created_at'=>$now,'updated_at'=>$now]);foreach($rows as &$r)$r['id_impor_jurnal_perkiraan']=$batch;DB::table('jurnal_perkiraan')->insert($rows);return redirect()->route('pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva')->with('sukses','Jurnal penyusutan berhasil dibuat.');
    }

    public function simpanStokOpname(Request $request)
    {
        $v = $request->validate(['tanggal'=>['required','date'],'nama_produk'=>['required','array'],'qty_sistem'=>['required','array'],'qty_fisik'=>['required','array'],'nilai_satuan'=>['required','array']]);
        $persediaan = DB::table('akun_perkiraan')->where('kode_perkiraan','110406')->where('aktif',1)->first();
        $biaya = DB::table('akun_perkiraan')->where('aktif',1)->where(function($q){$q->where('nama','like','%Penyesuaian Persediaan%')->orWhere('nama','like','%Stock Opname%');})->first();
        if (!$persediaan || !$biaya) return back()->withErrors(['akun'=>'Akun Persediaan Umum atau Biaya Penyesuaian Persediaan belum tersedia.'])->withInput();
        $rows=[]; $totalDebit=0; $totalKredit=0; $no='SO-'.date('YmdHis'); $now=now(); $deskripsi=[];
        foreach ($v['nama_produk'] as $i=>$nama) { $selisih=(float)$v['qty_fisik'][$i]-(float)$v['qty_sistem'][$i]; $nilai=round(abs($selisih)*(float)$v['nilai_satuan'][$i],2); if($nilai<=0) continue; $deskripsi[]=$nama; if($selisih<0)$totalDebit+=$nilai;else $totalKredit+=$nilai; }
        $total=max($totalDebit,$totalKredit); if($totalDebit>0)$rows[]=['id_akun_perkiraan'=>$biaya->id_akun_perkiraan,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$no,'tipe_transaksi'=>'Stok Opname','urutan_detail'=>1,'deskripsi'=>'Total selisih stok: '.implode(', ',$deskripsi),'debit'=>$totalDebit,'kredit'=>0,'created_at'=>$now,'updated_at'=>$now]; if($totalKredit>0)$rows[]=['id_akun_perkiraan'=>$persediaan->id_akun_perkiraan,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$no,'tipe_transaksi'=>'Stok Opname','urutan_detail'=>2,'deskripsi'=>'Total selisih stok: '.implode(', ',$deskripsi),'debit'=>0,'kredit'=>$totalKredit,'created_at'=>$now,'updated_at'=>$now];
        if (!$rows) return back()->with('sukses','Tidak ada selisih stok untuk dijurnal.');
        $batch=DB::table('impor_jurnal_perkiraan')->insertGetId(['nama_file'=>'Stok Opname '.$no,'hash_file'=>hash('sha256',$no.$now),'periode_awal'=>$v['tanggal'],'periode_akhir'=>$v['tanggal'],'jumlah_transaksi'=>1,'jumlah_detail'=>count($rows),'total_debit'=>$total,'total_kredit'=>$total,'status'=>'aktif','diimpor_oleh'=>auth()->id(),'created_at'=>$now,'updated_at'=>$now]);
        foreach($rows as &$r) $r['id_impor_jurnal_perkiraan']=$batch;
        DB::table('jurnal_perkiraan')->insert($rows);
        return redirect()->route('pembukuan-baru.jurnal-penyesuaian.stok-opname')->with('sukses','Jurnal stok opname berhasil dibuat.');
    }

    public function simpanPenyusutanGrouped(Request $request)
    {
        $v=$request->validate(['tanggal'=>'required|date','id_aktiva'=>'required|array']);$aktiva=DB::table('aktiva_pembukuan_baru')->whereIn('id',$v['id_aktiva'])->get();$debit=[];$credit=[];$now=now();$no='JPA-'.date('YmdHis');
        foreach($aktiva as $a){$asset=DB::table('akun_perkiraan')->where('id_akun_perkiraan',$a->id_akun_aset)->first();if(!$asset)continue;$expense=DB::table('akun_perkiraan')->where('aktif',1)->where('tipe_akun','EXPS')->where('nama','like','%Biaya Penyusutan%')->where('nama','like','%'.strtok($asset->nama,' ').'%')->first();if(!$expense)continue;$nilai=(float)$a->biaya_depresiasi;$debit[$expense->id_akun_perkiraan]=($debit[$expense->id_akun_perkiraan]??0)+$nilai;$credit[$asset->id_akun_perkiraan]=($credit[$asset->id_akun_perkiraan]??0)+$nilai;DB::table('aktiva_pembukuan_baru')->where('id',$a->id)->update(['akumulasi_penyusutan'=>min($a->h_perolehan,($a->akumulasi_penyusutan??0)+$nilai),'sisa_umur_bulan'=>is_null($a->sisa_umur_bulan)?null:max(0,$a->sisa_umur_bulan-1),'updated_at'=>$now]);}
        $rows=[];foreach($debit as $id=>$nilai)$rows[]=['id_akun_perkiraan'=>$id,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$no,'tipe_transaksi'=>'Penyusutan Aktiva','urutan_detail'=>count($rows)+1,'deskripsi'=>'Biaya penyusutan aktiva bulan '.date('m-Y',strtotime($v['tanggal'])),'debit'=>$nilai,'kredit'=>0,'created_at'=>$now,'updated_at'=>$now];foreach($credit as $id=>$nilai)$rows[]=['id_akun_perkiraan'=>$id,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$no,'tipe_transaksi'=>'Penyusutan Aktiva','urutan_detail'=>count($rows)+1,'deskripsi'=>'Kredit akun aset tetap tujuan','debit'=>0,'kredit'=>$nilai,'created_at'=>$now,'updated_at'=>$now];$total=array_sum($debit);if(!$rows)return back()->withErrors(['akun'=>'Akun biaya penyusutan belum tersedia.']);$batch=DB::table('impor_jurnal_perkiraan')->insertGetId(['nama_file'=>'Penyusutan Aktiva '.$no,'hash_file'=>hash('sha256',$no.$now),'periode_awal'=>$v['tanggal'],'periode_akhir'=>$v['tanggal'],'jumlah_transaksi'=>1,'jumlah_detail'=>count($rows),'total_debit'=>$total,'total_kredit'=>$total,'status'=>'aktif','diimpor_oleh'=>auth()->id(),'created_at'=>$now,'updated_at'=>$now]);foreach($rows as &$row)$row['id_impor_jurnal_perkiraan']=$batch;DB::table('jurnal_perkiraan')->insert($rows);return redirect()->route('pembukuan-baru.jurnal-penyesuaian.index')->with('sukses','Jurnal penyusutan berhasil dibuat.');
    }

    public function simpanPenyusutanCorrect(Request $request)
    {
        $v=$request->validate(['tanggal'=>'required|date','id_aktiva'=>'required|array']);
        $aktiva=DB::table('aktiva_pembukuan_baru')->whereIn('id',$v['id_aktiva'])->get(); $rows=[]; $now=now(); $no='JPA-'.date('YmdHis'); $groups=[];
        foreach($aktiva as $a){$asset=DB::table('akun_perkiraan')->where('id_akun_perkiraan',$a->id_akun_aset)->first(); if(!$asset) continue; $expense=DB::table('akun_perkiraan')->where('aktif',1)->where('tipe_akun','EXPS')->where('nama','like','%Biaya Penyusutan%')->where('nama','like','%'.strtok($asset->nama,' ').'%')->first(); if(!$expense) continue; $groups[$expense->id_akun_perkiraan]=($groups[$expense->id_akun_perkiraan]??0)+(float)$a->biaya_depresiasi; DB::table('aktiva_pembukuan_baru')->where('id',$a->id)->update(['akumulasi_penyusutan'=>min($a->h_perolehan,($a->akumulasi_penyusutan??0)+$a->biaya_depresiasi),'sisa_umur_bulan'=>is_null($a->sisa_umur_bulan)?null:max(0,$a->sisa_umur_bulan-1),'updated_at'=>$now]);}
        $total=array_sum($groups); if(!$total)return back()->withErrors(['akun'=>'Akun biaya penyusutan sesuai aset belum tersedia.']); foreach($groups as $id=>$nilai)$rows[]=['id_akun_perkiraan'=>$id,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$no,'tipe_transaksi'=>'Penyusutan Aktiva','urutan_detail'=>count($rows)+1,'deskripsi'=>'Biaya penyusutan aktiva bulan '.date('m-Y',strtotime($v['tanggal'])),'debit'=>$nilai,'kredit'=>0,'created_at'=>$now,'updated_at'=>$now]; $credit=$aktiva->first()->id_akun_aset; $rows[]=['id_akun_perkiraan'=>$credit,'tanggal'=>$v['tanggal'],'nomor_transaksi'=>$no,'tipe_transaksi'=>'Penyusutan Aktiva','urutan_detail'=>count($rows)+1,'deskripsi'=>'Kredit akun aset tetap tujuan','debit'=>0,'kredit'=>$total,'created_at'=>$now,'updated_at'=>$now]; $batch=DB::table('impor_jurnal_perkiraan')->insertGetId(['nama_file'=>'Penyusutan Aktiva '.$no,'hash_file'=>hash('sha256',$no.$now),'periode_awal'=>$v['tanggal'],'periode_akhir'=>$v['tanggal'],'jumlah_transaksi'=>1,'jumlah_detail'=>count($rows),'total_debit'=>$total,'total_kredit'=>$total,'status'=>'aktif','diimpor_oleh'=>auth()->id(),'created_at'=>$now,'updated_at'=>$now]); foreach($rows as &$r)$r['id_impor_jurnal_perkiraan']=$batch; DB::table('jurnal_perkiraan')->insert($rows); return redirect()->route('pembukuan-baru.jurnal-penyesuaian.index')->with('sukses','Jurnal penyusutan berhasil dibuat.');
    }
}
