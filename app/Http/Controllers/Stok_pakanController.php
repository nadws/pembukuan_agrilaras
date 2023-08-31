<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Stok_pakanController extends Controller
{
    public function load_stok_pakan(Request $r)
    {
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
        ];
        return view('stok_pakan.stok', $data);
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
        // $no_nota = strtoupper(str()->random(5));
        for ($x = 0; $x < count($r->id_pakan); $x++) {
            $id_pakan = $r->id_pakan[$x];
            $hrga = DB::selectOne("SELECT sum(a.total_rp/a.pcs) as rata_rata
            FROM stok_produk_perencanaan as a 
            where a.id_pakan = '$id_pakan' and a.pcs != '0' and a.h_opname ='T'
            group by a.id_pakan;");

            $selisih = $r->stk_program[$x] - $r->stk_aktual[$x];

            if ($selisih < 0) {
                $qty_selisih = $selisih * -1;

                $data = [
                    'id_akun' => '522',
                    'id_buku' => '4',
                    'ket' => 'Penyesuian stok pakan',
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
                    'ket' => 'Penyesuian stok pakan',
                    'debit' => 0,
                    'kredit' => $qty_selisih * $hrga->rata_rata,
                    'tgl' => $r->tgl,
                    'no_nota' => 'JPP-' . $no_nota,
                    'admin' => Auth::user()->name,
                ];
                DB::table('jurnal')->insert($data);
            } else {
                $qty_selisih = $selisih;
                $data = [
                    'id_akun' => '521',
                    'id_buku' => '4',
                    'ket' => 'Penyesuian stok pakan',
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
                    'ket' => 'Penyesuian stok pakan',
                    'debit' => 0,
                    'kredit' => $qty_selisih * $hrga->rata_rata,
                    'tgl' => $r->tgl,
                    'no_nota' => 'JPP-' . $no_nota,
                    'admin' => Auth::user()->name,
                ];
                DB::table('jurnal')->insert($data);
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

    public function history_perencanaan_pakan(Request $r)
    {
        $kategori = $r->kategori;
        if ($kategori == 'pakan') {
            $stok = DB::select("SELECT a.tgl, a.id_stok_telur, b.nm_produk, c.nm_kandang, a.pcs_kredit, a.total_rp, d.nm_satuan, a.admin
            FROM stok_produk_perencanaan as a
            left JOIN tb_produk_perencanaan  as b on b.id_produk = a.id_pakan
            left join kandang as c on c.id_kandang = a.id_kandang
            left join tb_satuan as d on d.id_satuan = b.dosis_satuan
            where a.`check` ='T' and b.kategori = 'pakan' and a.h_opname = 'T' and a.id_kandang != '0'
            order by a.tgl , a.id_kandang ASC
            ");


            $max_tgl = DB::selectOne("SELECT min(a.tgl) as tgl
            FROM stok_produk_perencanaan as a
            left join tb_produk_perencanaan  as b on b.id_produk = a.id_pakan
            where a.`check` ='T' and b.kategori = 'pakan' and a.id_kandang != '0'
            ");
        } else {
            $stok = DB::select("SELECT a.tgl, a.id_stok_telur, b.nm_produk, c.nm_kandang, a.pcs_kredit, a.total_rp, d.nm_satuan, a.admin
            FROM stok_produk_perencanaan as a
            left JOIN tb_produk_perencanaan  as b on b.id_produk = a.id_pakan
            left join kandang as c on c.id_kandang = a.id_kandang
            left join tb_satuan as d on d.id_satuan = b.dosis_satuan
            where a.`check` ='T' and b.kategori in ('obat_pakan','obat_air') and a.h_opname = 'T' and a.id_kandang != '0'
            order by a.tgl , a.id_kandang ASC");
            $max_tgl = DB::selectOne("SELECT min(a.tgl) as tgl
            FROM stok_produk_perencanaan as a
            left join tb_produk_perencanaan  as b on b.id_produk = a.id_pakan
            where a.`check` ='T' and b.kategori in ('obat_pakan','obat_air') and a.id_kandang != '0'
            ");
        }






        $data = [
            'title' => 'Biaya',
            'stok' => $stok,
            'kategori' => $kategori,
            'max_tgl' => $max_tgl->tgl
        ];
        return view('stok_pakan.history_pakan', $data);
    }


    public function pembukuan_biaya_pv(Request $r)
    {
        if ($r->kategori == 'pakan') {
            $id_akun = 92;
        } else {
            $id_akun = 93;
        }
        $data = [
            'title' => 'Penerimaan Uang Penjualan Ayam',
            'nota' => $r->no_nota,
            'akun' => DB::table('akun')->get(),
            'kategori' => $r->kategori,
            'id_akun' => $id_akun
        ];
        return view('stok_pakan.setor', $data);
    }

    public function bukukan_pv(Request $r)
    {
        if ($r->kategori == 'pakan') {
            $id_akun_penualan_ayam = 1;
        } else {
            $id_akun_penualan_ayam = 32;
        }


        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '6')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '4']);

        for ($x = 0; $x < count($r->id_stok_telur); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $id_akun_penualan_ayam)->first();
            $akun = DB::table('akun')->where('id_akun', $id_akun_penualan_ayam)->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'JUP-' . $nota_t,
                'id_akun' => $id_akun_penualan_ayam,
                'id_buku' => '4',
                'ket' => 'Biaya Pengeluaran  ' . $r->nm_produk[$x] . " (kandang " . $r->nm_kandang[$x] . ")",
                'debit' => '0',
                'kredit' => $r->pembayaran[$x],
                'admin' => auth()->user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            DB::table('stok_produk_perencanaan')->where('id_stok_telur', $r->id_stok_telur[$x])->update(['check' => 'Y', 'cek_admin' => auth()->user()->name]);
        }

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();

            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'JUP-' . $nota_t,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '4',
                'ket' => 'Biaya Pengeluaran ' . $r->kategori,
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => auth()->user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);
        }

        return redirect()->route('penyesuaian.index')->with('sukses', 'Data berhasil ditambahkan');
    }
}
