<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\proyek;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SettingHal;

class PeralatanController extends Controller
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

    public function index()
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;
        $id_proyek = $this->id_proyek;
        $id_user = auth()->user()->id;
        $data = [
            'title' => 'Data Peralatan',
            'peralatan' => DB::select("SELECT a.*, b.*, c.beban FROM peralatan as a 
            left join kelompok_peralatan as b on b.id_kelompok = a.id_kelompok
            left join(
            SELECT sum(c.b_penyusutan) as beban , c.id_aktiva
                FROM depresiasi_peralatan as c
                group by c.id_aktiva
            ) as c on c.id_aktiva = a.id_aktiva
            order by a.id_aktiva DESC"),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'id_proyek' => $id_proyek,

            'user' => User::where('posisi_id', 1)->get(),
            'halaman' => 9,
            'create' => SettingHal::btnHal(37, $id_user),
            'edit' => SettingHal::btnHal(38, $id_user),
            'delete' => SettingHal::btnHal(39, $id_user),
            'detail' => SettingHal::btnHal(40, $id_user),
        ];
        return view('persediaan_barang.peralatan.index', $data);
    }

    public function add()
    {
        $data = [
            'title' => 'Tambah Peralatan',
            'kelompok' => DB::table('kelompok_peralatan')->get()
        ];
        return view('persediaan_barang.peralatan.add', $data);
    }

    public function save_kelompok(Request $r)
    {
        DB::table('kelompok_peralatan')->insert([
            'nm_kelompok' => $r->nm_kelompok,
            'umur' => $r->umur,
            'periode' => $r->periode,
            'tarif' => $r->tarif,
            'barang_kelompok' => $r->barang_kelompok,
        ]);

        return redirect()->route('peralatan.add')->with('sukses', 'Data Kelompok Berhasil ditambahkan');
    }

    public function load_aktiva()
    {
        $data = [
            'title' => 'Aktiva',
            'kelompok' => DB::table('kelompok_peralatan')->get()
        ];
        return view('persediaan_barang.peralatan.load_aktiva', $data);
    }

    public function get_data_kelompok(Request $r)
    {
        $id_kelompok = $r->id_kelompok;
        $kelompok =  DB::table('kelompok_peralatan')->where('id_kelompok', $id_kelompok)->first();

        $data = [
            'nilai_persen' => $kelompok->tarif,
            'tahun' => $kelompok->umur,
            'periode' => ucwords($kelompok->periode),
        ];
        echo json_encode($data);
    }

    public function save_aktiva(Request $r)
    {
        $id_kelompok = $r->id_kelompok;
        $nm_aktiva = $r->nm_aktiva;
        $tgl = $r->tgl;
        $h_perolehan = $r->h_perolehan;

        for ($x = 0; $x < count($id_kelompok); $x++) {
            $kelompok =  DB::table('kelompok_peralatan')->where('id_kelompok', $id_kelompok[$x])->first();

            $biaya_depresiasi = $kelompok->periode === 'bulan' ? $h_perolehan[$x] / $kelompok->umur : $h_perolehan[$x] / ($kelompok->umur * 12);

            $data = [
                'id_kelompok' => $id_kelompok[$x],
                'nm_aktiva' => $nm_aktiva[$x],
                'tgl' => $tgl[$x],
                'h_perolehan' => $h_perolehan[$x],
                'biaya_depresiasi' => $biaya_depresiasi,
                'admin' => auth()->user()->name,
            ];
            DB::table('peralatan')->insert($data);
        }

        return redirect()->route('peralatan.index')->with('sukses', 'Data berhasil ditambahkan');
    }

    public function delete_peralatan(Request $r)
    {
        $cek = DB::table('depresiasi_peralatan')->where('id_aktiva', $r->id_peralatan)->first();
        if (!$cek) {
            DB::table('peralatan')->where('id_aktiva', $r->id_aktiva)->delete();
            $status = 'sukses';
            $pesan = 'Data berhasil di hapus';
        }
        return redirect()->route('peralatan.index')->with($status ?? 'error', $pesan ?? 'Gagal dihapus ! peralatan tersedia di depresiasi');
    }

    public function load_edit(Request $r)
    {
        $data = [
            'title' => 'asd',
            'd' => DB::table('kelompok_peralatan')->where('id_kelompok', $r->id_kelompok)->first()
        ];
        return view('persediaan_barang.peralatan.load_edit', $data);
    }

    public function edit_kelompok(Request $r)
    {
        DB::table('kelompok_edit')->where('id_kelompok', $r->id_kelompok)->update([
            'nm_kelompok' => $r->nm_kelompok,
            'umur' => $r->umur,
            'periode' => $r->periode,
            'barang_kelompok' => $r->barang_kelompok,
        ]);
        return redirect()->route('peralatan.add')->with('sukses', 'Data berhasil dihapus');
    }

    public function delete_kelompok(Request $r)
    {
        DB::table('kelompok_peralatan')->where('id_kelompok', $r->id_kelompok)->delete();
        return redirect()->route('peralatan.add')->with('sukses', 'Data berhasil dihapus');
    }


    // jurnal peralatan bapa
    public function load_menu_add_aktiva(Request $r)
    {
        $data =  [
            'title' => 'Jurnal Umum',
            'akun' => Akun::all(),
            'proyek' => proyek::all(),
            'kategori' => $r->kategori,
            'satuan' => DB::table('tb_satuan')->get()
        ];
        return view('persediaan_barang.peralatan.load_menu_aktiva', $data);
    }

    public function tambah_baris_aktiva(Request $r)
    {
        $data =  [
            'title' => 'Jurnal Umum',
            'akun' => Akun::all(),
            'count' => $r->count

        ];
        return view('persediaan_barang.peralatan.tambah_baris_aktiva', $data);
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
                'admin' => auth()->user()->name,
                // 'no_dokumen' => $r->no_dokumen,
                'tgl_dokumen' => $r->tgl_dokumen,
                'id_proyek' => $id_proyek,
                'id_suplier' => $id_suplier,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
                'id_post_center' => $id_post[$i] ?? 0
            ];
            Jurnal::create($data);
        }

        $tgl1 = date('Y-m-01', strtotime($r->tgl));
        $tgl2 = date('Y-m-t', strtotime($r->tgl));
        return redirect()->route('Cek_aktiva', ['no_nota' => 'JU-' . $nota_t, 'kategori' => $r->kategori ?? 'aktiva', 'pembelian' => 'Y'])->with('sukses', 'Data berhasil ditambahkan');
    }

    public function nota_jurnal($no_nota, $kategori = null, $print = null)
    {
        $dataKategori = [
            'aktiva' => 'kelompok_aktiva',
            'peralatan' => 'kelompok_peralatan',
        ];
        $kelompok = $dataKategori[$kategori];
        $data =  [
            'title' => 'Jurnal Peralatan',
            'jurnal' => Jurnal::where('no_nota', $no_nota)->get(),
            'no_nota' => $no_nota,
            'kelompok' => DB::table($kelompok)->get(),
            'head_jurnal' => DB::selectOne("SELECT a.tgl, b.nm_proyek, a.id_proyek, a.no_dokumen,a.tgl_dokumen, a.no_nota, sum(a.debit) as debit , sum(a.kredit) as kredit FROM jurnal as a 
            left join proyek as b on b.id_proyek = a.id_proyek
            
            where a.no_nota = '$no_nota'")

        ];
        $view = empty($print) ? 'nota_jurnal' : 'print';
        return view('persediaan_barang.peralatan.' . $view, $data);
    }
}
