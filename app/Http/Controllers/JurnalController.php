<?php

namespace App\Http\Controllers;

use App\Exports\JurnalExport;
use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\proyek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JurnalImport;
use App\Models\User;
use Filter;
use SettingHal;

class JurnalController extends Controller
{
    protected $tgl1, $tgl2, $id_proyek, $period, $id_buku;
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
            $tglawal = "$tahun" . "-" . "$bulan" . "-" . "01";
            $tglakhir = "$tahun" . "-" . "$bulan" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tglawal));
            $this->tgl2 = date('Y-m-t', strtotime($tglakhir));
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

        $this->id_proyek = $r->id_proyek ?? 0;
        $this->id_buku = $r->id_buku ?? 2;
    }



    public function index(Request $r)
    {
        // dd($r->id_buku);
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;
        $id_proyek = $this->id_proyek;
        $id_user = auth()->user()->id;
        if (empty($r->id_buku)) {
            $id_buku = '2';
        } else {
            $id_buku = $r->id_buku;
        }



        $jurnal =  DB::select("SELECT a.penutup, a.no_dokumen, a.id_jurnal,a.no_urut,a.admin, a.id_akun, a.tgl, a.debit, a.kredit, a.ket,a.no_nota, b.nm_akun, c.nm_post, d.nm_proyek FROM jurnal as a 
            left join akun as b on b.id_akun = a.id_akun
            left join tb_post_center as c on c.id_post_center = a.id_post_center
            left join proyek as d on d.id_proyek = a.id_proyek
            where a.id_buku ='$id_buku' and a.tgl between '$tgl1' and '$tgl2' order by  a.id_jurnal DESC");

        $buku = DB::select("SELECT a.id_akun, a.kode_akun , a.nm_akun, b.debit , b.kredit, c.debit as debit_saldo, c.kredit as kredit_saldo
        FROM akun as a

        left JOIN(
            SELECT b.id_akun , sum(b.debit) as debit, sum(b.kredit) as kredit
            FROM jurnal as b
            where b.penutup = 'T' and b.tgl BETWEEN '2022-01-01' and '$tgl2'
            group by b.id_akun
        ) as b on b.id_akun = a.id_akun

        left JOIN (
            SELECT c.id_akun , sum(c.debit) as debit, sum(c.kredit) as kredit
            FROM jurnal_saldo as c 
            where  c.tgl BETWEEN '2022-01-01' and '$tgl2'
            group by c.id_akun
        ) as c on c.id_akun = a.id_akun
        where a.id_klasifikasi  = '9'
        group by a.id_akun
        ORDER by a.kode_akun ASC;
        ");


        $data =  [
            'title' => 'Jurnal Umum',
            'jurnal' => $jurnal,
            'proyek' => proyek::where('status', 'berjalan')->get(),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'id_proyek' => $id_proyek,
            'id_buku' => $id_buku,
            // button
            'user' => User::where('posisi_id', 1)->get(),
            'halaman' => 1,
            'tambah' => SettingHal::btnHal(1, $id_user),
            'import' => SettingHal::btnHal(2, $id_user),
            'export' => SettingHal::btnHal(3, $id_user),
            'detail' => SettingHal::btnHal(6, $id_user),
            'edit' => SettingHal::btnHal(4, $id_user),
            'hapus' => SettingHal::btnHal(5, $id_user),
            'buku' => $buku
        ];
        if ($id_buku == 14) {
            return view('jurnal.hutang', $data);
        } else {
            return view('jurnal.index', $data);
        }
    }

    public function add(Request $r)
    {
        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '2')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }

        $kategori = [
            2 => 'biaya',
            12 => 'pengeluaran aktiva gantung',
            13 => 'pembalikan aktiva gantung',
            6 => 'penjualan',
            7 => 'kas & bank',
            10 => 'pembelian asset',
            14 => 'Hutang',
        ];



        $data =  [
            'title' => "Tambah Jurnal " . ucwords($kategori[$r->id_buku]),
            'max' => $nota_t,
            'proyek' => proyek::where('status', 'berjalan')->get(),
            'suplier' => DB::table('tb_suplier')->get(),
            'id_buku' => $r->id_buku,
            'id_akun' => $r->id_akun,
            'kategori' => $r->kategori ?? 'aktiva',
            'akun' => DB::select("SELECT * FROM akun as a where a.id_akun in('43','9') and a.nonaktif = 'T'")

        ];
        switch ($r->id_buku) {
            case '10':
                return view('persediaan_barang.peralatan.add_peralatan', $data);
                break;

            default:
                return view('jurnal.add', $data);

                break;
        }
    }



    public function get_proyek()
    {
        $proyek =  DB::table('proyek')->get();

        echo '<option value="">Pilih</option>';
        foreach ($proyek as $p) {
            echo "<option value='$p->id_proyek'>$p->nm_proyek</option>";
        }
        echo '<option value="tambah_proyek">+Proyek</option>';
    }

    public function load_menu(Request $r)
    {
        if ($r->id_buku == '7') {
            $akun = Akun::where('nonaktif', 'T')->whereIn('id_klasifikasi', ['1', '2'])->get();
        } elseif ($r->id_buku == '14') {
            $akun = Akun::where('nonaktif', 'T')->whereIn('id_klasifikasi', ['1', '2', '9'])->get();
        } elseif ($r->id_buku == '12') {
            $akun = Akun::where('nonaktif', 'T')->whereIn('id_klasifikasi', ['1', '2', '12'])->get();
        } elseif ($r->id_buku == '2') {
            $akun = Akun::where('nonaktif', 'T')->whereIn('id_klasifikasi', ['3', '2', '1'])->get();
        } else {
            $akun = Akun::where('nonaktif', 'T')->get();
        }

        $data =  [
            'title' => 'Jurnal Umum',
            'akun' => $akun,
            'proyek' => proyek::all(),
            'satuan' => DB::table('tb_satuan')->get(),
            'id_akun' => $r->id_akun,
            'id_buku' => $r->id_buku
        ];
        return view('jurnal.load_menu', $data);
    }
    public function tambah_baris_jurnal(Request $r)
    {
        if ($r->id_buku == '7') {
            $akun = Akun::where('nonaktif', 'T')->whereIn('id_klasifikasi', ['1', '2'])->get();
        } elseif ($r->id_buku == '14') {
            $akun = Akun::where('nonaktif', 'T')->whereIn('id_klasifikasi', ['1', '2', '9'])->get();
        } elseif ($r->id_buku == '12') {
            $akun = Akun::where('nonaktif', 'T')->whereIn('id_klasifikasi', ['1', '2', '12'])->get();
        } elseif ($r->id_buku == '2') {
            $akun = Akun::where('nonaktif', 'T')->whereIn('id_klasifikasi', ['3', '2', '1'])->get();
        } else {
            $akun = Akun::where('nonaktif', 'T')->get();
        }
        $data =  [
            'title' => 'Jurnal Umum',
            'akun' => $akun,
            'count' => $r->count

        ];
        return view('jurnal.tbh_baris', $data);
    }

    public function save_jurnal(Request $r)
    {


        $tgl = $r->tgl;
        // $no_nota = $r->no_nota;
        $id_akun = $r->id_akun;
        $keterangan = $r->keterangan;
        $debit = $r->debit;
        $kredit = $r->kredit;
        $id_proyek = $r->id_proyek;
        $id_suplier = $r->id_suplier;
        // $tipe_jurnal = $r->tipe_jurnal;
        $no_urut = $r->no_urut;
        $id_post = $r->id_post;
        $id_buku = $r->id_buku;


        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '2')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '2']);


        for ($i = 0; $i < count($id_akun); $i++) {

            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $id_akun[$i])->first();
            $akun = DB::table('akun')->where('id_akun', $id_akun[$i])->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
            $data = [
                'tgl' => $tgl,
                'no_nota' => 'JU-' . $nota_t,
                'id_akun' => $id_akun[$i],
                'no_dokumen' => $no_urut[$i],
                'id_buku' => $id_buku,
                'ket' => $keterangan[$i],
                'debit' => $debit[$i],
                'kredit' => $kredit[$i],
                'admin' => Auth::user()->name,
                // 'no_dokumen' => $r->no_dokumen,
                // 'tipe_jurnal' => $tipe_jurnal,
                'tgl_dokumen' => $r->tgl_dokumen,
                'id_proyek' => $id_proyek,
                'id_suplier' => $id_suplier,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
                'id_post_center' => $id_post[$i]
            ];
            Jurnal::create($data);
        }

        $tgl1 = date('Y-m-01', strtotime($r->tgl));
        $tgl2 = date('Y-m-t', strtotime($r->tgl));
        return redirect()->route('jurnal', ['period' => 'costume', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'id_proyek' => 0, 'id_buku' => $id_buku])->with('sukses', 'Data berhasil ditambahkan');
    }

    public function delete(Request $r)
    {
        $nomer = substr($r->no_nota, 3);
        DB::table('notas')->where('nomor_nota', $nomer)->delete();
        Jurnal::where('no_nota', $r->no_nota)->delete();

        $tgl1 = $r->tgl1;
        $tgl2 = $r->tgl2;
        $id_proyek = $r->id_proyek;
        return redirect()->route('jurnal', ['period' => 'costume', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'id_proyek' => $id_proyek, 'id_buku' => $r->id_buku])->with('sukses', 'Data berhasil dihapus');
    }

    public function export(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;
        $id_proyek = $r->id_proyek;
        $id_buku = $r->id_buku;

        $idp = $id_proyek == 0 ? '' : "and a.id_proyek = '$id_proyek'";

        $total = DB::selectOne("SELECT count(a.id_jurnal) as jumlah FROM jurnal as a where a.id_buku not in('6','4') and a.tgl between '$tgl1' and '$tgl2' and a.debit != '0'");

        $totalrow = $total->jumlah;

        return Excel::download(new JurnalExport($tgl1, $tgl2, $id_proyek, $id_buku, $totalrow), 'jurnal.xlsx');
    }

    public function edit(Request $r)
    {
        $data =  [
            'title' => 'Edit Jurnal Umum',
            'proyek' => proyek::all(),
            'jurnal' => Jurnal::where('no_nota', $r->no_nota)->get(),
            'akun' => Akun::where('nonaktif', 'T')->get(),
            'no_nota' => $r->no_nota,
            'head_jurnal' => DB::selectOne("SELECT a.id_buku, a.tgl, a.id_proyek, a.no_dokumen,a.tgl_dokumen, sum(a.debit) as debit , sum(a.kredit) as kredit FROM jurnal as a where a.no_nota = '$r->no_nota'")

        ];
        return view('jurnal.edit', $data);
    }

    public function edit_save(Request $r)
    {
        $tgl = $r->tgl;
        // $no_nota = $r->no_nota;
        $id_akun = $r->id_akun;
        $id_akun2 = $r->id_akun2;
        $keterangan = $r->keterangan;
        $debit = $r->debit;
        $kredit = $r->kredit;
        $id_proyek = $r->id_proyek;
        $no_urut = $r->no_urut;
        $nota_t = $r->no_nota;
        $id_post = $r->id_post;
        $id_jurnal = $r->id_jurnal;
        $no_dokumen = $r->no_dokumen;

        Jurnal::where('no_nota', $nota_t)->delete();

        for ($i = 0; $i < count($id_akun); $i++) {
            if ($id_akun[$i] == $id_akun2[$i] || !empty($id_akun2[$i])) {
                $no_urutan = $no_urut[$i];
            } else {
                $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $id_akun[$i])->first();
                $akun = DB::table('akun')->where('id_akun', $id_akun[$i])->first();
                if ($max_akun->urutan == 0) {
                    $urutan = '1001';
                } else {
                    $urutan = $max_akun->urutan + 1;
                }
                $no_urutan = $akun->inisial . '-' . $urutan;
            }

            $data = [
                'tgl' => $tgl,
                'no_nota' => $nota_t,
                'id_akun' => $id_akun[$i],
                'id_buku' => $r->id_buku,
                'ket' => $keterangan[$i],
                'debit' => $debit[$i],
                'kredit' => $kredit[$i],
                'admin' => Auth::user()->name,
                'no_dokumen' => empty($no_dokumen[$i]) ? ' ' : $no_dokumen[$i],
                'tgl_dokumen' => $r->tgl_dokumen,
                'id_proyek' => $id_proyek,
                'no_urut' => $no_urutan,
                'id_post_center' => $id_post[$i]
            ];
            Jurnal::insert($data);
        }

        $tgl1 = date('Y-m-01', strtotime($r->tgl));
        $tgl2 = date('Y-m-t', strtotime($r->tgl));
        return redirect()->route('jurnal', ['period' => 'costume', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'id_proyek' => 0, 'id_buku' => $r->id_buku])->with('sukses', 'Data berhasil ditambahkan');
    }

    public function detail_jurnal(Request $r)
    {

        $data =  [
            'title' => 'Jurnal Umum',
            'jurnal' => Jurnal::where('no_nota', $r->no_nota)->get(),
            'no_nota' => $r->no_nota,
            'head_jurnal' => DB::selectOne("SELECT c.nm_suplier, a.tgl, b.nm_proyek, a.id_proyek, a.no_dokumen,a.tgl_dokumen, a.no_nota, sum(a.debit) as debit , sum(a.kredit) as kredit FROM jurnal as a 
            left join proyek as b on b.id_proyek = a.id_proyek
            left join tb_suplier as c on c.id_suplier = a.id_suplier
            where a.no_nota = '$r->no_nota'")

        ];
        return view('jurnal.detail', $data);
    }

    public function import_jurnal()
    {
        Excel::import(new JurnalImport, request()->file('file'));

        return back();
    }

    public function saldo_akun(Request $r)
    {
        $id_akun = $r->id_akun;

        $jurnal =  DB::selectOne("SELECT a.id_akun, a.nm_akun, b.debit, b.kredit,  c.debit as debit_saldo, c.kredit as kredit_saldo
        FROM akun as a
        LEFT JOIN (
            SELECT b.id_akun, SUM(b.debit) as debit, SUM(b.kredit) as kredit
            FROM jurnal as b
            WHERE b.id_akun = $id_akun AND b.penutup = 'T'
            GROUP BY b.id_akun
        ) as b ON b.id_akun = a.id_akun
        LEFT JOIN (
         SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
         FROM jurnal_saldo as c
          WHERE c.id_akun = $id_akun
          GROUP BY c.id_akun
           ) as c ON c.id_akun = a.id_akun
        WHERE a.id_akun = $id_akun;
        ");

        $saldo = $jurnal->debit + $jurnal->debit_saldo - $jurnal->kredit - $jurnal->kredit_saldo;

        if (empty($saldo)) {
            $saldo_input = 0;
        } else {
            $saldo_input = $saldo;
        }

        $akun = DB::table('akun')->where('id_akun', $id_akun)->first();

        if (empty($akun->id_klasifikasi)) {
            $id_klasifikasi = 0;
        } else {
            $id_klasifikasi = $akun->id_klasifikasi;
        }

        if ($akun->cash_uang_ditarik == 'T') {
            $nilai = 1;
        } else {
            $nilai = 0;
        }


        $data = [
            'id_klasifikasi' => $id_klasifikasi,
            'nilai' => $nilai,
            'saldo' => $saldo_input
        ];
        echo json_encode($data);
    }

    public function get_post(Request $r)
    {
        $id_akun = $r->id_akun;
        $post = DB::table('tb_post_center')->where('id_akun', $id_akun)->get();

        echo "<option value=''>Pilih sub akun</option>";
        foreach ($post as $k) {
            echo "<option value='" . $k->id_post_center  . "'>" . $k->nm_post . "</option>";
        }
    }

    public function get_total_post(Request $r)
    {
        $total =  DB::selectOne("SELECT sum(a.debit) as debit FROM jurnal as a where a.id_post_center = $r->id_post");
        $formattedTotal = number_format($total->debit, 0, ',', '.');

        $data = [
            'format' => "Rp. $formattedTotal",
            'biasa' => $total->debit
        ];
        return response()->json($data);
    }

    public function get_post2(Request $r)
    {
        $id_akun = $r->id_akun;
        // $post = DB::table('tb_post_center')->where('id_akun', $id_akun)->get();
        $post = DB::select("SELECT * FROM tb_post_center as a where a.id_akun = $id_akun and a.nm_post not in(SELECT b.nm_aktiva FROM aktiva as b)");

        echo "<option value=''>Pilih sub akun</option>";
        foreach ($post as $k) {
            echo "<option value='" . $k->id_post_center  . "'>" . $k->nm_post . "</option>";
        }
    }
}
