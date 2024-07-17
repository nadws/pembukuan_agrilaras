<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RakController extends Controller
{
    public $id_akun = 47;
    public function history(Request $r)
    {
        $stok = DB::select("SELECT * FROM tb_rak_telur as a WHERE a.h_opname = 'Y' AND a.cek = 'T'");
        $max_tgl = DB::selectOne("SELECT min(a.tgl) as tgl
            FROM tb_rak_telur as a
            where a.`cek` ='T' and a.id_gudang = 1
            ");
        $data = [
            'title' => 'Biaya Rak Telur',
            'stok' => $stok,
            'max_tgl' => $max_tgl->tgl
        ];

        return view('stok_pakan.rak.history', $data);
    }
    public function pembukuan_biaya(Request $r)
    {
        $data = [
            'title' => 'Pembiayaan Rak Telur',
            'nota' => $r->no_nota,
            'akun' => DB::table('akun')->get(),
            'id_akun' => $this->id_akun
        ];
        return view('stok_pakan.rak.setor', $data);
    }

    public function bukukan(Request $r)
    {
        try {
            DB::beginTransaction();
            $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '4')->first();

            if (empty($max)) {
                $nota_t = '1000';
            } else {
                $nota_t = $max->nomor_nota + 1;
            }
            $id_akun_penualan_ayam = 46;
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
                    'ket' => 'Biaya Pengeluaran Rak Telur ' . $r->nota_rak[$x],
                    'debit' => '0',
                    'kredit' => $r->pembayaran[$x],
                    'admin' => auth()->user()->name,
                    'no_urut' => $akun->inisial . '-' . $urutan,
                    'urutan' => $urutan,
                ];
                DB::table('jurnal')->insert($data);

                DB::table('tb_rak_telur')->where('id_rak', $r->id_stok_telur[$x])->update(['cek' => 'Y', 'cek_admin' => auth()->user()->name]);
            }

            for ($x = 0; $x < count($r->debit); $x++) {
                $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $this->id_akun)->first();
                $akun = DB::table('akun')->where('id_akun', $this->id_akun)->first();

                $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
                $data = [
                    'tgl' => $r->tgl[$x],
                    'no_nota' => 'JUP-' . $nota_t,
                    'id_akun' => $this->id_akun,
                    'id_buku' => '4',
                    'ket' => 'Biaya Pengeluaran Rak Telur',
                    'debit' => $r->debit[$x],
                    'kredit' => $r->kredit[$x],
                    'admin' => auth()->user()->name,
                    'no_urut' => $akun->inisial . '-' . $urutan,
                    'urutan' => $urutan,
                ];
                DB::table('jurnal')->insert($data);
            }
            DB::commit();
            return redirect()->route('penyesuaian.index')->with('sukses', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            // Rollback semua perubahan jika terjadi kesalahan
            DB::rollback();

            // Tampilkan pesan kesalahan
            return redirect()->route('rak.history')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
