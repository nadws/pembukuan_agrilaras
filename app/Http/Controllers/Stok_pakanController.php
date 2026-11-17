<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Stok_pakanController extends Controller
{
    public function load_stok_pakan(Request $r)
    {
        if (empty($r->tgl)) {
            $tgl = date('Y-m-d');
        } else {
            $tgl = $r->tgl;
        }
        $data = [
            'pakan' => DB::select("SELECT a.id_pakan, b.nm_produk, sum(a.pcs) as pcs_debit, sum(a.pcs_kredit) as pcs_kredit, c.nm_satuan
            FROM stok_produk_perencanaan as a 
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            left join tb_satuan as c on c.id_satuan = b.dosis_satuan
            where b.kategori ='pakan'
            group by a.id_pakan;"),

            'vitamin' => DB::select("SELECT a.id_pakan, b.nm_produk, sum(a.pcs) as pcs_debit, sum(a.pcs_kredit) as pcs_kredit, c.nm_satuan
            FROM stok_produk_perencanaan as a 
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            left join tb_satuan as c on c.id_satuan = b.dosis_satuan
            where b.kategori in('obat_pakan','obat_air')
            group by a.id_pakan;"),

            'stok_rak' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo FROM tb_rak_telur as a where a.id_gudang = '1'"),

            'total_rak' => DB::selectOne("SELECT COUNT(a.id_rak) as total
            FROM tb_rak_telur as a
            where a.`cek` = 'T' AND a.h_opname = 'Y' and a.id_gudang = '1';"),
            'total_pakan' => DB::selectOne("SELECT COUNT(a.id_stok_telur) as total
            FROM stok_produk_perencanaan as a
            left JOIN tb_produk_perencanaan  as b on b.id_produk = a.id_pakan
            left join kandang as c on c.id_kandang = a.id_kandang
            where a.`check` = 'T' and b.kategori = 'pakan' and a.h_opname = 'T' and a.id_kandang != '0';"),

            'total_vitamin' => DB::selectOne("SELECT COUNT(a.id_stok_telur) as total
            FROM stok_produk_perencanaan as a
            left JOIN tb_produk_perencanaan  as b on b.id_produk = a.id_pakan
            left join kandang as c on c.id_kandang = a.id_kandang
            where a.`check` = 'T' and b.kategori in('obat_pakan','obat_air') and a.h_opname = 'T' and a.id_kandang != '0';"),
            'hrga_pakan' => DB::select("SELECT a.id_harga_pakan, b.nm_produk, a.tgl, a.ttl_gr, a.ttl_rp, a.rp_lain
            FROM harga_pakan as a 
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan and b.kategori ='pakan'
            order by a.tgl DESC"),
            'pakan_table' => DB::table('tb_produk_perencanaan')->where('kategori', 'pakan')->get(),

            'pengeluaran_pakan' => DB::select("SELECT b.nm_produk, b.kategori, sum(a.pcs_kredit) as qty, c.nm_satuan, sum(a.total_rp) as ttl_rp
            FROM stok_produk_perencanaan as a 
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            left join tb_satuan as c on c.id_satuan = b.dosis_satuan
            WHERE a.tgl = '$tgl' and a.id_kandang != '0'
            group by a.id_pakan;"),
            'tgl' => $tgl,


        ];
        return view('stok_pakan.stok', $data);
    }

    public function tbh_stok_pakan(Request $r)
    {
        $data = [
            'count' => $r->count,
            'pakan_table' => DB::table('tb_produk_perencanaan')->where('kategori', 'pakan')->get()
        ];
        return view('stok_pakan.tbh_stok_pakan', $data);
    }
    public function get_edit_hrga_pakan(Request $r)
    {
        $data = [
            'pakan_table' => DB::table('tb_produk_perencanaan')->where('kategori', 'pakan')->get(),
            'pakan' => DB::table('harga_pakan')->where('id_harga_pakan', $r->id_harga_pakan)->first()
        ];
        return view('stok_pakan.edit_stok_pakan', $data);
    }

    public function save_stok_pakan(Request $r)
    {
        for ($i = 0; $i < count($r->id_pakan); $i++) {
            $data = [
                'id_pakan' => $r->id_pakan[$i],
                'ttl_gr' => $r->sak[$i],
                'ttl_rp' => $r->total_rp[$i],
                'rp_lain' => $r->rp_lain[$i],
                'admin' => Auth::user()->name,
                'tgl' => $r->tgl[$i]
            ];
            DB::table('harga_pakan')->insert($data);
        }
        return redirect()->route('produk_telur')->with('sukses', 'Data berhasil di simpan');
    }
    public function edit_stok_pakan(Request $r)
    {

        $data = [
            'id_pakan' => $r->id_pakan,
            'ttl_gr' => $r->sak,
            'ttl_rp' => $r->total_rp,
            'rp_lain' => $r->rp_lain,
            'admin' => Auth::user()->name,
            'tgl' => $r->tgl
        ];
        DB::table('harga_pakan')->where('id_harga_pakan', $r->id_harga_pakan)->update($data);

        return redirect()->route('produk_telur')->with('sukses', 'Data berhasil di edit');
    }
    public function hapus_stok_pakan(Request $r)
    {

        DB::table('harga_pakan')->where('id_harga_pakan', $r->id_harga_pakan)->delete();

        return redirect()->route('produk_telur')->with('sukses', 'Data berhasil di hapus');
    }

    public function history_stok(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-t');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }

        $data = [
            'stok' => DB::select("SELECT a.tgl, b.nm_produk, a.pcs, a.pcs_kredit, a.admin, a.h_opname
            FROM stok_produk_perencanaan as a 
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            where a.tgl BETWEEN '$tgl1' and '$tgl2' and a.opname ='T' and a.id_pakan = '$r->id_pakan'
            GROUP by a.id_stok_telur;"),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'id_pakan' => $r->id_pakan
        ];
        return view('stok_pakan.history_stok', $data);
    }

    public function opname_pakan(Request $r)
    {
        if (empty($r->tgl)) {
            $tgl = date('Y-m-d');
        } else {
            $tgl = $r->tgl;
        }


        $data = [
            'pakan' => DB::select("SELECT a.id_pakan, b.nm_produk, sum(a.pcs) as pcs_debit, sum(a.pcs_kredit) as pcs_kredit, c.nm_satuan
            FROM stok_produk_perencanaan as a 
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            left join tb_satuan as c on c.id_satuan = b.dosis_satuan
            where b.kategori = 'pakan' and a.opname = 'T' and a.tgl between '2023-01-01' and '$tgl'
            group by a.id_pakan;"),
            'tgl' => $tgl
        ];
        return view('opname.opname_pakan', $data);
    }
    public function opnme_vitamin(Request $r)
    {
        if (empty($r->tgl)) {
            $tgl = date('Y-m-d');
        } else {
            $tgl = $r->tgl;
        }
        $data = [
            'pakan' => DB::select("SELECT a.id_pakan, b.nm_produk, sum(a.pcs) as pcs_debit, sum(a.pcs_kredit) as pcs_kredit, c.nm_satuan
            FROM stok_produk_perencanaan as a 
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            left join tb_satuan as c on c.id_satuan = b.dosis_satuan
            where b.kategori in('obat_pakan','obat_air') and a.opname = 'T' and a.tgl between '2023-01-01' and '$tgl'
            group by a.id_pakan;"),
            'tgl' => $tgl
        ];
        return view('opname.opname_pakan', $data);
    }

    public function save_opname_pakan(Request $r)
    {
        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '4')->first();
        if (empty($max)) {
            $no_nota = '1000';
        } else {
            $no_nota = $max->nomor_nota + 1;
        }
        
        $now = now();
        $totalDebit = 0;
        $totalKredit = 0;
        $jurnalPerkiraanData = [];
        
        // $no_nota = strtoupper(str()->random(5));
        for ($x = 0; $x < count($r->id_pakan); $x++) {
            $id_pakan = $r->id_pakan[$x];
            
            // Ambil kategori produk untuk menentukan akun
            $produk = DB::table('tb_produk_perencanaan')->where('id_produk', $id_pakan)->first();
            $kategori = $produk->kategori ?? 'pakan';
            
            // Tentukan kode akun berdasarkan kategori
            // Pakan: BPP 5101-04, Persediaan 110403
            // Vitamin/Obat: BPP 5101-03, Persediaan 110404
            if ($kategori === 'pakan') {
                $kodeAkunBiaya = '5101-04';
                $kodeAkunPersediaan = '110403';
            } elseif (in_array($kategori, ['obat_pakan', 'obat_air', 'vitamin'])) {
                $kodeAkunBiaya = '5101-03';
                $kodeAkunPersediaan = '110404';
            } else {
                // Skip jurnal perkiraan untuk kategori lain
                continue;
            }
            
            // Ambil id_akun_perkiraan dari tabel akun_perkiraan
            $akunBiaya = DB::table('akun_perkiraan')->where('kode_perkiraan', $kodeAkunBiaya)->first();
            $akunPersediaan = DB::table('akun_perkiraan')->where('kode_perkiraan', $kodeAkunPersediaan)->first();
            
            $hrga = DB::selectOne("SELECT sum(a.total_rp/a.pcs) as rata_rata
            FROM stok_produk_perencanaan as a 
            where a.id_pakan = '$id_pakan' and a.pcs != '0' and a.h_opname ='T'
            group by a.id_pakan;");

            $selisih = $r->stk_program[$x] - $r->stk_aktual[$x];
            $nilaiSelisih = abs($selisih) * $hrga->rata_rata;

            if ($selisih < 0) {
                // Stok fisik lebih banyak dari sistem (ada penambahan)
                $qty_selisih = $selisih * -1;

                // Jurnal lama (tetap dipertahankan)
                $data = [
                    'id_akun' => '522',
                    'id_buku' => '4',
                    'ket' => 'Penyesuian stok ' . ($kategori === 'pakan' ? 'pakan' : 'vitamin'),
                    'debit' => $qty_selisih * $hrga->rata_rata,
                    'kredit' => '0',
                    'tgl' => $r->tgl,
                    'no_nota' => 'JPP-' . $no_nota,
                    'admin' => Auth::user()->name,
                ];
                DB::table('jurnal')->insert($data);
                $data = [
                    'id_akun' => '521',
                    'id_buku' => '4',
                    'ket' => 'Penyesuian stok ' . ($kategori === 'pakan' ? 'pakan' : 'vitamin'),
                    'debit' => 0,
                    'kredit' => $qty_selisih * $hrga->rata_rata,
                    'tgl' => $r->tgl,
                    'no_nota' => 'JPP-' . $no_nota,
                    'admin' => Auth::user()->name,
                ];
                DB::table('jurnal')->insert($data);
                
                // Jurnal Perkiraan baru
                if ($akunPersediaan && $akunBiaya) {
                    $jurnalPerkiraanData[] = [
                        'id_akun_perkiraan' => $akunBiaya->id_akun_perkiraan,
                        'tanggal' => $r->tgl,
                        'nomor_transaksi' => 'JPP-' . $no_nota,
                        'tipe_transaksi' => 'Stok Opname',
                        'urutan_detail' => (count($jurnalPerkiraanData) + 1),
                        'deskripsi' => 'Penyesuaian stok opname ' . $produk->nm_produk . ' (fisik > sistem)',
                        'debit' => $nilaiSelisih,
                        'kredit' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $jurnalPerkiraanData[] = [
                        'id_akun_perkiraan' => $akunPersediaan->id_akun_perkiraan,
                        'tanggal' => $r->tgl,
                        'nomor_transaksi' => 'JPP-' . $no_nota,
                        'tipe_transaksi' => 'Stok Opname',
                        'urutan_detail' => (count($jurnalPerkiraanData) + 1),
                        'deskripsi' => 'Penyesuaian stok opname ' . $produk->nm_produk . ' (fisik > sistem)',
                        'debit' => 0,
                        'kredit' => $nilaiSelisih,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $totalDebit += $nilaiSelisih;
                    $totalKredit += $nilaiSelisih;
                }
            } else {
                // Stok fisik lebih sedikit dari sistem (ada pengurangan/kehilangan)
                $qty_selisih = $selisih;
                
                // Jurnal lama (tetap dipertahankan)
                $data = [
                    'id_akun' => '521',
                    'id_buku' => '4',
                    'ket' => 'Penyesuian stok ' . ($kategori === 'pakan' ? 'pakan' : 'vitamin'),
                    'debit' => $qty_selisih * $hrga->rata_rata,
                    'kredit' => '0',
                    'tgl' => $r->tgl,
                    'no_nota' => 'JPP-' . $no_nota,
                    'admin' => Auth::user()->name,
                ];
                DB::table('jurnal')->insert($data);
                $data = [
                    'id_akun' => '522',
                    'id_buku' => '4',
                    'ket' => 'Penyesuian stok ' . ($kategori === 'pakan' ? 'pakan' : 'vitamin'),
                    'debit' => 0,
                    'kredit' => $qty_selisih * $hrga->rata_rata,
                    'tgl' => $r->tgl,
                    'no_nota' => 'JPP-' . $no_nota,
                    'admin' => Auth::user()->name,
                ];
                DB::table('jurnal')->insert($data);
                
                // Jurnal Perkiraan baru
                if ($akunPersediaan && $akunBiaya) {
                    $jurnalPerkiraanData[] = [
                        'id_akun_perkiraan' => $akunPersediaan->id_akun_perkiraan,
                        'tanggal' => $r->tgl,
                        'nomor_transaksi' => 'JPP-' . $no_nota,
                        'tipe_transaksi' => 'Stok Opname',
                        'urutan_detail' => (count($jurnalPerkiraanData) + 1),
                        'deskripsi' => 'Penyesuaian stok opname ' . $produk->nm_produk . ' (fisik < sistem)',
                        'debit' => $nilaiSelisih,
                        'kredit' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $jurnalPerkiraanData[] = [
                        'id_akun_perkiraan' => $akunBiaya->id_akun_perkiraan,
                        'tanggal' => $r->tgl,
                        'nomor_transaksi' => 'JPP-' . $no_nota,
                        'tipe_transaksi' => 'Stok Opname',
                        'urutan_detail' => (count($jurnalPerkiraanData) + 1),
                        'deskripsi' => 'Penyesuaian stok opname ' . $produk->nm_produk . ' (fisik < sistem)',
                        'debit' => 0,
                        'kredit' => $nilaiSelisih,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $totalDebit += $nilaiSelisih;
                    $totalKredit += $nilaiSelisih;
                }
            }

            DB::table('stok_produk_perencanaan')->where(['id_pakan' => $r->id_pakan[$x], 'opname' => 'T'])->update(['opname' => 'Y', 'no_nota' => $no_nota]);
            $data = [
                'pcs' => $r->stk_aktual[$x],
                'id_pakan' => $r->id_pakan[$x],
                'opname' => 'T',
                'tgl' => $r->tgl,
                'admin' => Auth::user()->name,
                'no_nota' => $no_nota,
                'h_opname' => 'Y',
                'total_rp' => $qty_selisih * $hrga->rata_rata
            ];
            DB::table('stok_produk_perencanaan')->insert($data);
        }
        
        // Simpan batch dan detail jurnal perkiraan
        if (!empty($jurnalPerkiraanData)) {
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Opname Pakan/Vitamin - JPP-' . $no_nota,
                'hash_file' => hash('sha256', 'opname-pakan-vitamin|' . $no_nota . '|' . $r->tgl),
                'periode_awal' => $r->tgl,
                'periode_akhir' => $r->tgl,
                'jumlah_transaksi' => 1,
                'jumlah_detail' => count($jurnalPerkiraanData),
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            // Set id_impor_jurnal_perkiraan untuk semua detail
            foreach ($jurnalPerkiraanData as &$detail) {
                $detail['id_impor_jurnal_perkiraan'] = $batchId;
            }
            
            DB::table('jurnal_perkiraan')->insert($jurnalPerkiraanData);
        }
        
        return redirect()->route('produk_telur')->with('sukses', 'Data berhasil di simpan');
    }

    public function tambah_pakan(Request $r)
    {
        $data = [
            'produk' => DB::table('tb_produk_perencanaan')->where('kategori', 'pakan')->get(),
            'kategori' => 'pakan'
        ];
        return view('stok_pakan.tbh_stok', $data);
    }
    public function tambah_vitamin(Request $r)
    {
        $data = [
            'produk' => DB::select("SELECT * FROM tb_produk_perencanaan as a where a.kategori in('obat_pakan','obat_air')"),
            'kategori' => 'vitamin'
        ];
        return view('stok_pakan.tbh_stok', $data);
    }

    public function save_tambah_pakan(Request $r)
    {
        for ($x = 0; $x < count($r->id_pakan); $x++) {
            $data = [
                'id_pakan' => $r->id_pakan[$x],
                'pcs' => $r->pcs[$x],
                'total_rp' => $r->ttl_rp[$x],
                'admin' => Auth::user()->name,
                'tgl' => $r->tgl
            ];
            DB::table('stok_produk_perencanaan')->insert($data);
        }

        return redirect()->route('produk_telur')->with('sukses', 'Data berhasil di simpan');
    }

    public function tambah_baris_stok(Request $r)
    {
        $data = [
            'produk' => DB::table('tb_produk_perencanaan')->where('kategori', 'pakan')->get(),
            'count' => $r->count,

        ];
        return view('stok_pakan.tbh_baris_stok', $data);
    }
    public function tambah_baris_stok_vitamin(Request $r)
    {
        $data = [
            'produk' => DB::select("SELECT * FROM tb_produk_perencanaan as a where a.kategori in('obat_pakan','obat_air')"),
            'count' => $r->count,

        ];
        return view('stok_pakan.tbh_baris_stok', $data);
    }

    public function getHppPerGramMap(): array
    {
        $latestFaktur = DB::table('faktur_pembelian_detail as fpd')
            ->join('faktur_pembelian as fp', 'fp.id', '=', 'fpd.faktur_pembelian_id')
            ->whereIn('fpd.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('faktur_pembelian_detail')
                    ->groupBy('pakan_id');
            })
            ->get(['fpd.pakan_id', 'fpd.satuan', 'fpd.qty', 'fpd.subtotal', 'fpd.harga_satuan', 'fp.no_faktur', 'fp.tanggal_faktur']);

        $map = [];
        foreach ($latestFaktur as $f) {
            $satuan = strtolower(trim($f->satuan ?? ''));
            $qty = (float) $f->qty;
            $subtotal = (float) $f->subtotal;
            // HPP per gram diturunkan dari subtotal/qty (bukan kolom harga_satuan)
            // agar tetap benar walau harga_satuan di faktur lama tidak konsisten.
            if ($qty <= 0) {
                $hppPerGr = 0;
            } elseif ($satuan === 'zak') {
                $hppPerGr = $subtotal / ($qty * 50000);
            } elseif ($satuan === 'kg') {
                $hppPerGr = $subtotal / ($qty * 1000);
            } else {
                $hppPerGr = $subtotal / $qty;
            }
            $hargaSatuan = (float) $f->harga_satuan;

            $map[$f->pakan_id] = [
                'hpp_per_gr' => $hppPerGr,
                'satuan_faktur' => $f->satuan,
                'harga_satuan_faktur' => $hargaSatuan,
                'no_faktur' => $f->no_faktur,
                'tanggal_faktur' => $f->tanggal_faktur,
            ];
        }

        return $map;
    }

    public function history_perencanaan_pakan(Request $r)
    {
        return app(LaporanPerencanaanController::class)->history($r);
    }

    public function pembukuan_biaya_pv(Request $r)
    {
        abort(410, 'Pembukuan manual perencanaan dinonaktifkan. Jurnal PPH sudah otomatis dari kandang; gunakan Koreksi.');
    }

    public function bukukan_pv(Request $r)
    {
        abort(410, 'Pembukuan manual perencanaan dinonaktifkan. Jurnal PPH sudah otomatis dari kandang; gunakan Koreksi.');
    }
}