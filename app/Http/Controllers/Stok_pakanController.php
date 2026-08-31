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
            ->get(['fpd.pakan_id', 'fpd.satuan', 'fpd.harga_satuan', 'fp.no_faktur', 'fp.tanggal_faktur']);

        $map = [];
        foreach ($latestFaktur as $f) {
            $satuan = strtolower(trim($f->satuan ?? ''));
            $hargaSatuan = (float) $f->harga_satuan;

            if ($satuan === 'zak') {
                $hppPerGr = $hargaSatuan / 50000;
            } elseif ($satuan === 'kg') {
                $hppPerGr = $hargaSatuan / 1000;
            } else {
                $hppPerGr = $hargaSatuan;
            }

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
        $kategori = in_array($r->kategori, ['pakan', 'vitamin'], true)
            ? $r->kategori
            : 'pakan';

        $hppMap = $this->getHppPerGramMap();

        if ($kategori == 'pakan') {
            $stok = DB::select("SELECT a.tgl, a.id_stok_telur, a.id_pakan, b.nm_produk, c.nm_kandang, a.pcs_kredit, a.total_rp, d.nm_satuan, a.admin
            FROM stok_produk_perencanaan as a
            left JOIN tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            left join kandang as c on c.id_kandang = a.id_kandang
            left join tb_satuan as d on d.id_satuan = b.dosis_satuan
            where a.`check` ='T' and b.kategori = 'pakan' and a.h_opname = 'T' and a.id_kandang != '0'
            order by a.tgl, a.id_kandang ASC
            ");

            $max_tgl = DB::selectOne("SELECT min(a.tgl) as tgl
            FROM stok_produk_perencanaan as a
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            where a.`check` ='T' and b.kategori = 'pakan' and a.id_kandang != '0'
            ");
        } else {
            $stok = DB::select("SELECT a.tgl, a.id_stok_telur, a.id_pakan, b.nm_produk, c.nm_kandang, a.pcs_kredit, a.total_rp, d.nm_satuan, a.admin
            FROM stok_produk_perencanaan as a
            left JOIN tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            left join kandang as c on c.id_kandang = a.id_kandang
            left join tb_satuan as d on d.id_satuan = b.dosis_satuan
            where a.`check` ='T' and b.kategori in ('obat_pakan','obat_air','obat_ayam') and a.h_opname = 'T' and a.id_kandang != '0'
            order by a.tgl, a.id_kandang ASC");

            $max_tgl = DB::selectOne("SELECT min(a.tgl) as tgl
            FROM stok_produk_perencanaan as a
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            where a.`check` ='T' and b.kategori in ('obat_pakan','obat_air','obat_ayam') and a.id_kandang != '0'
            ");
        }

        foreach ($stok as $s) {
            $pcs = (float) $s->pcs_kredit;
            if (isset($hppMap[$s->id_pakan])) {
                $s->hpp_per_gr = $hppMap[$s->id_pakan]['hpp_per_gr'];
                $s->total_rp = round($pcs * $s->hpp_per_gr, 0);
            } else {
                $s->hpp_per_gr = $pcs > 0 ? ((float) $s->total_rp / $pcs) : 0;
                $s->total_rp = round((float) $s->total_rp, 0);
            }
        }

        $data = [
            'title' => 'History Perencanaan',
            'stok' => $stok,
            'kategori' => $kategori,
            'max_tgl' => $max_tgl->tgl ?? null
        ];
        return view('stok_pakan.history_pakan', $data);
    }

    public function pembukuan_biaya_pv(Request $r)
    {
        $kategori = $r->kategori === 'vitamin' ? 'vitamin' : 'pakan';
        $id_akun = $kategori === 'pakan' ? 125 : 124;

        $notaIds = collect($r->no_nota ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $hppMap = $this->getHppPerGramMap();

        $stokItems = collect();
        $totalSemua = 0;

        if ($notaIds->isNotEmpty()) {
            $rawStok = DB::table('stok_produk_perencanaan as a')
                ->leftJoin('tb_produk_perencanaan as b', 'b.id_produk', '=', 'a.id_pakan')
                ->leftJoin('kandang as c', 'c.id_kandang', '=', 'a.id_kandang')
                ->leftJoin('tb_satuan as d', 'd.id_satuan', '=', 'b.dosis_satuan')
                ->whereIn('a.id_stok_telur', $notaIds)
                ->get([
                    'a.tgl',
                    'a.id_stok_telur',
                    'a.id_pakan',
                    'b.nm_produk',
                    'c.nm_kandang',
                    'a.pcs_kredit',
                    'a.total_rp',
                    'd.nm_satuan',
                    'a.admin'
                ]);

            foreach ($rawStok as $s) {
                $pcs = (float) $s->pcs_kredit;
                if (isset($hppMap[$s->id_pakan])) {
                    $s->hpp_per_gr = $hppMap[$s->id_pakan]['hpp_per_gr'];
                    $s->total_rp = round($pcs * $s->hpp_per_gr, 0);
                } else {
                    $s->hpp_per_gr = $pcs > 0 ? ((float) $s->total_rp / $pcs) : 0;
                    $s->total_rp = round((float) $s->total_rp, 0);
                }
                $totalSemua += $s->total_rp;
                $stokItems->push($s);
            }
        }

        $data = [
            'title' => 'Penerimaan Uang Penjualan Ayam',
            'nota' => $r->no_nota,
            'stokItems' => $stokItems,
            'totalSemua' => $totalSemua,
            'akun' => DB::table('akun_perkiraan')
                ->where('aktif', true)
                ->orderBy('kode_perkiraan')
                ->get(),
            'kategori' => $kategori,
            'id_akun' => $id_akun
        ];
        return view('stok_pakan.setor', $data);
    }

    public function bukukan_pv(Request $r)
    {
        $kategori = $r->kategori === 'vitamin' ? 'vitamin' : 'pakan';

        if ($kategori === 'pakan') {
            $idAkunPersediaan = 25;
            $idAkunBiaya = 125;
        } else {
            $idAkunPersediaan = 26;
            $idAkunBiaya = 124;
        }

        $ids = collect($r->input('id_stok_telur', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        abort_if($ids->isEmpty(), 422, 'Tidak ada transaksi yang dipilih.');

        $stokQuery = DB::table('stok_produk_perencanaan as s')
            ->join('tb_produk_perencanaan as p', 'p.id_produk', '=', 's.id_pakan')
            ->leftJoin('kandang as k', 'k.id_kandang', '=', 's.id_kandang')
            ->whereIn('s.id_stok_telur', $ids)
            ->where('s.check', 'T')
            ->where('s.h_opname', 'T')
            ->where('s.id_kandang', '!=', 0);

        if ($kategori === 'pakan') {
            $stokQuery->where('p.kategori', 'pakan');
        } else {
            $stokQuery->whereIn('p.kategori', ['obat_pakan', 'obat_air', 'obat_ayam']);
        }

        $stokDipilih = $stokQuery
            ->select('s.id_stok_telur', 's.id_pakan', 's.pcs_kredit', 's.tgl', 's.total_rp', 'p.nm_produk', 'k.nm_kandang')
            ->orderBy('s.id_stok_telur')
            ->get();

        abort_if(
            $stokDipilih->count() !== $ids->count(),
            422,
            'Sebagian transaksi sudah dibukukan atau tidak sesuai kategori.'
        );

        $hppMap = $this->getHppPerGramMap();

        foreach ($stokDipilih as $stok) {
            $pcs = (float) $stok->pcs_kredit;
            if (isset($hppMap[$stok->id_pakan])) {
                $stok->total_rp = round($pcs * $hppMap[$stok->id_pakan]['hpp_per_gr'], 0);
            } else {
                $stok->total_rp = round((float) $stok->total_rp, 0);
            }
        }

        $batchId = DB::transaction(function () use (
            $stokDipilih,
            $idAkunPersediaan,
            $idAkunBiaya,
            $kategori
        ) {
            $max = DB::table('notas')
                ->where('id_buku', '4')
                ->orderByDesc('nomor_nota')
                ->lockForUpdate()
                ->first();

            $nota_t = empty($max) ? 1000 : $max->nomor_nota + 1;
            $noNota = 'JUP-' . $nota_t;

            DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '4']);

            abort_unless(
                DB::table('akun_perkiraan')
                    ->whereIn('id_akun_perkiraan', [$idAkunPersediaan, $idAkunBiaya])
                    ->where('aktif', true)
                    ->count() === 2,
                422,
                'Akun perkiraan untuk pembukuan tidak tersedia atau tidak aktif.'
            );

            $kelompokTanggal = $stokDipilih->groupBy('tgl');
            $totalOtomatis = $stokDipilih->sum(
                fn ($stok) => round((float) $stok->total_rp, 0)
            );
            $sekarang = now();

            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Pembukuan otomatis ' . $kategori . ' ' . $noNota,
                'hash_file' => hash(
                    'sha256',
                    'history-perencanaan|' . $kategori . '|' . $noNota . '|' .
                        $stokDipilih->pluck('id_stok_telur')->implode(',')
                ),
                'periode_awal' => $stokDipilih->min('tgl'),
                'periode_akhir' => $stokDipilih->max('tgl'),
                'jumlah_transaksi' => $kelompokTanggal->count(),
                'jumlah_detail' => $stokDipilih->count() + $kelompokTanggal->count(),
                'total_debit' => $totalOtomatis,
                'total_kredit' => $totalOtomatis,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $detailJurnal = [];

            foreach ($kelompokTanggal as $tanggal => $stokPerTanggal) {
                $urutanDetail = 1;
                $totalTanggal = 0;

                foreach ($stokPerTanggal as $stok) {
                    $nominal = round((float) $stok->total_rp, 0);
                    $totalTanggal += $nominal;

                    $detailJurnal[] = [
                        'id_impor_jurnal_perkiraan' => $batchId,
                        'id_akun_perkiraan' => $idAkunPersediaan,
                        'tanggal' => $tanggal,
                        'nomor_transaksi' => $noNota,
                        'tipe_transaksi' => 'Pemakaian ' . ucfirst($kategori),
                        'urutan_detail' => $urutanDetail++,
                        'deskripsi' => 'Biaya Pengeluaran ' . $stok->nm_produk .
                            ' (kandang ' . $stok->nm_kandang . ')',
                        'debit' => 0,
                        'kredit' => $nominal,
                        'created_at' => $sekarang,
                        'updated_at' => $sekarang,
                    ];

                    DB::table('jurnal_perkiraan_stok_perencanaan')->insert([
                        'id_impor_jurnal_perkiraan' => $batchId,
                        'id_stok_telur' => $stok->id_stok_telur,
                        'check_sebelum' => 'T',
                        'cek_admin_sebelum' => null,
                        'created_at' => $sekarang,
                        'updated_at' => $sekarang,
                    ]);

                    $diperbarui = DB::table('stok_produk_perencanaan')
                        ->where('id_stok_telur', $stok->id_stok_telur)
                        ->where('check', 'T')
                        ->update([
                            'check' => 'Y',
                            'cek_admin' => auth()->user()->name,
                            'total_rp' => $nominal,
                        ]);

                    abort_if($diperbarui !== 1, 422, 'Transaksi sudah dibukukan oleh pengguna lain.');
                }

                $detailJurnal[] = [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $idAkunBiaya,
                    'tanggal' => $tanggal,
                    'nomor_transaksi' => $noNota,
                    'tipe_transaksi' => 'Pemakaian ' . ucfirst($kategori),
                    'urutan_detail' => $urutanDetail,
                    'deskripsi' => 'Biaya Pengeluaran ' . $kategori,
                    'debit' => $totalTanggal,
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            }

            collect($detailJurnal)
                ->chunk(500)
                ->each(fn ($detail) => DB::table('jurnal_perkiraan')->insert($detail->all()));

            return $batchId;
        });

        return redirect()
            ->route('jurnal-perkiraan.detail-batch', $batchId)
            ->with('sukses', 'Data berhasil dibukukan ke Jurnal Perkiraan.');
    }
}
