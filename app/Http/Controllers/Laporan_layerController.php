<?php

namespace App\Http\Controllers;

use App\Models\LaporanLayerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Laporan_layerController extends Controller
{
    public function index(Request $r)
    {
        if (empty($r->tgl)) {
            $tgl = date("Y-m-d", strtotime("-1 day"));
        } else {
            $tgl = $r->tgl;
        }
        $tgl_sebelumnya = date("Y-m-d", strtotime($tgl . " -6 days"));
        $tgl_kemarin = date("Y-m-d", strtotime($tgl . " -1 days"));

        $tgl_minggu_kemaren = date("Y-m-d", strtotime($tgl_sebelumnya . " -1 days"));
        $tgl_minggu_sebelumnya = date("Y-m-d", strtotime($tgl_minggu_kemaren . " -6 days"));

        $tgl1 = date('Y-m-01', strtotime($tgl));

        $tgl_awal_harga = date("Y-m-d", strtotime($tgl . "-30 days"));

        $harga = DB::selectOne("SELECT b.nm_produk, a.tgl, sum(a.pcs / 1000) as pcs , sum(a.total_rp) as ttl_rupiah, a.admin
        FROM stok_produk_perencanaan as a 
        left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
        where a.h_opname = 'T' and a.tgl BETWEEN '$tgl_awal_harga' and '$tgl' and b.kategori = 'pakan' and a.pcs != 0;");

        $harga_pakan = DB::table('harga_pakan as h1')
            ->select('id_pakan', 'ttl_gr', 'ttl_rp', 'rp_lain')
            ->whereRaw('id_harga_pakan = (select max(id_harga_pakan) from harga_pakan as h2 where h2.id_pakan = h1.id_pakan)')
            ->get()
            ->keyBy('id_pakan');

        $data = [
            'title' => 'Laporan Layer',
            'tgl' => $tgl,
            'tgl_sebelum' => $tgl_sebelumnya,
            'tgl_kemarin' => $tgl_kemarin,
            'harga' => $harga,
            'kandang' => LaporanLayerModel::getLaporanLayer($tgl, $tgl_sebelumnya, $tgl_kemarin, $tgl_minggu_sebelumnya, $tgl_minggu_kemaren),
            'harga_pakan' => $harga_pakan
        ];
        return view('laporan.layer2', $data);
    }

    public function rumus_layer(Request $r)
    {
        if ($r->rumus == 'butir_today') {
            echo "<b>Butir Today - Yesterday =</b> <em >telur sekarang perbutir - telur kemarin perbutir</em>";
        }
        if ($r->rumus == 'hh') {
            echo "<b>Hen House =</b> <em >(Jumlah telur hari ini/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'hhkum') {
            echo "<b>Hen House Komulatif =</b> <em >(Jumlah telur dari awal sampai hari ini/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'kg_today') {
            echo "<b>Kg Today - Yesterday =</b> <em >telur sekarang kg - telur kemarin kg</em> <br><br>";
            echo "<b>Note =</b> <em >Jika minus maka akan merah</em>";
        }
        if ($r->rumus == 'hh_kg') {
            echo "<b>Hen House Kg =</b> <em >(Jumlah telur hari ini (kg)/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'hh_kgkum') {
            echo "<b>Hen House Komulatif Kg =</b> <em >(Jumlah telur dari awal sampai hari ini(kg)/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'gr_butir') {
            echo "<b>Gram Perbutir =</b> <em >(Jumlah telur hari ini (gr) - (jumlah pcs hari ini / 180)) / jumlah pcs hari ini)</em>";
        }
        if ($r->rumus == 'hd_day') {
            echo "<b>HD perday =</b> <em >(Jumlah telur hari ini/Jumlah ayam akhir) x 100%</em><br><br>";
            echo "<b>HH perday =</b> <em >(Jumlah telur hari ini/Jumlah ayam awal) x 100%</em><br><br>";
        }
        if ($r->rumus == 'hd_past') {
            echo "<b>HD past =</b> <em >(Jumlah telur kemarin/Jumlah ayam akhir kemarin) x 100%</em>";
        }
        if ($r->rumus == 'hd_week') {
            echo "<b>HD Week =</b> <em >(PCS Telur minggu ini/Jumlah ayam akhir minggu ini) x 100</em> <br><br>";
            echo "<b>HD Past Week =</b> <em >(PCS Telur minggu lalu/Jumlah ayam akhir minggu lalu) x 100</em>";
        }
        if ($r->rumus == 'fcr_week') {
            echo "<b>FCR week =</b> <em >Jumlah pakan minggu ini (kg)/(Jumlah telur minggu ini (kg) - (pcs telur minggu ini / 180))</em> <br><br>";
            echo "<b>FCR week + =</b> <em >(Jumlah pakan minggu ini (kg) + (Rupiah vitamin minggu ini /7000))/(Jumlah telur minggu ini (kg) - (pcs telur minggu ini / 180))</em> <br><br>";
            echo "<b>Note :</b> Jika Fcr diatas 2.2 maka kolom berwarna merah";
        }
        if ($r->rumus == 'fcrplus_week') {
            echo "<b>FCR+ week =</b> <em >(Jumlah pakan yang diberikan selama 1 minggu (kg) + (total rupiah vaksin & vitamin / 7000))/(Jumlah telur selama 1 minggu (kg) - (pcs telur selama 1 minggu / 180))</em>";
        }
        if ($r->rumus == 'd_c') {
            echo "<b>Note :</b> Jika mati lebih dari 3 maka kolom berwarna merah";
        }
        if ($r->rumus == 'mgg') {
            echo "<b>Note :</b> Jika Minggu mencapai 80 minggu atau lebih  maka kolom berwarna merah";
        }
        if ($r->rumus == 'butir') {
            echo "<b>Butir =</b> <em >telur sekarang pcs - telur kemarin pcs</em><br><br>";
            echo "<b>Note =</b> <em >Jika minus maka akan merah</em>";
        }
    }

    function get_history_produk(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-d');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }

        $history = DB::table('tb_produk_perencanaan')->where('id_produk', $r->id_produk)->first();
        $kandang = DB::table('kandang')->where('id_kandang', $r->id_kandang)->first();

        $data = [
            'history' => $history,
            'id_kandang' => $r->id_kandang,
            'id_produk' => $r->id_produk,
            'kandang' => $kandang,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
        ];

        return view('laporan.history_produk', $data);
    }

    public function hdTigaMinggu(Request $request)
    {
        $request->validate([
            'id_kandang' => ['required'],
            'tgl'        => ['required', 'date'],
        ]);

        /*
     * Jika sebelumnya $k berasal dari query yang lebih lengkap,
     * ganti query ini dengan query lama yang menghasilkan $k.
     */
        $k = DB::table('kandang')
            ->where('id_kandang', $request->id_kandang)
            ->first();

        abort_if(!$k, 404, 'Data kandang tidak ditemukan.');

        $tgl = Carbon::parse($request->tgl)->startOfDay();
        $chickIn = Carbon::parse($k->chick_in)->startOfDay();

        $selisihHari = $chickIn->diffInDays($tgl, false);

        abort_if(
            $selisihHari < 0,
            422,
            'Tanggal laporan tidak boleh sebelum tanggal chick-in.'
        );

        /*
     * Mengikuti perhitungan lama:
     * hari 1-7   = minggu 1
     * hari 8-14  = minggu 2
     */
        $mingguAktif = max(1, (int) ceil($selisihHari / 7));

        $awalMingguAktif = $chickIn
            ->copy()
            ->addDays((($mingguAktif - 1) * 7) + 1);

        $dataMingguan = [];

        // Urutannya: minggu 25, 26, 27
        for ($mundur = 2; $mundur >= 0; $mundur--) {
            $nomorMinggu = $mingguAktif - $mundur;

            if ($nomorMinggu < 1) {
                continue;
            }

            $awalMinggu = $awalMingguAktif
                ->copy()
                ->subWeeks($mundur);

            $dataMingguan[] = $this->ambilDataPerMinggu(
                $k,
                $nomorMinggu,
                $awalMinggu,
                $tgl
            );
        }

        $semuaTanggal = collect($dataMingguan)
            ->flatMap(function ($minggu) {
                return $minggu['tanggal_harian'];
            })
            ->values();

        $awalPeriode = $semuaTanggal->first();
        $akhirPeriode = $semuaTanggal->last();

        /*
 * Daftar produk pakan yang digunakan selama tiga minggu.
 */
        $produkPakan = DB::table('stok_produk_perencanaan as a')
            ->join(
                'tb_produk_perencanaan as b',
                'b.id_produk',
                '=',
                'a.id_pakan'
            )
            ->whereBetween('a.tgl', [$awalPeriode, $akhirPeriode])
            ->where('a.id_kandang', $k->id_kandang)
            ->where('b.kategori', 'pakan')
            ->select(
                'a.id_pakan',
                'b.nm_produk'
            )
            ->distinct()
            ->orderBy('b.nm_produk')
            ->get();

        /*
 * Ambil pemakaian semua produk untuk seluruh tanggal sekaligus.
 */
        $pemakaianPakan = DB::table('stok_produk_perencanaan as a')
            ->join(
                'tb_produk_perencanaan as b',
                'b.id_produk',
                '=',
                'a.id_pakan'
            )
            ->whereBetween('a.tgl', [$awalPeriode, $akhirPeriode])
            ->where('a.id_kandang', $k->id_kandang)
            ->where('b.kategori', 'pakan')
            ->selectRaw(
                '
            a.tgl,
            a.id_pakan,
            SUM(COALESCE(a.pcs_kredit, 0)) AS pcs_kredit
        '
            )
            ->groupBy(
                'a.tgl',
                'a.id_pakan'
            )
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->id_pakan . '|' . $item->tgl;

                return [
                    $key => (float) $item->pcs_kredit,
                ];
            })
            ->all();

        $idPakan = $produkPakan
            ->pluck('id_pakan')
            ->all();

        $riwayatHarga = empty($idPakan)
            ? collect()
            : DB::table('harga_pakan')
            ->whereIn('id_pakan', $idPakan)
            ->whereDate('tgl', '<=', $akhirPeriode)
            ->select(
                'id_harga_pakan',
                'id_pakan',
                'tgl',
                'ttl_gr',
                'ttl_rp',
                'rp_lain'
            )
            ->orderBy('id_harga_pakan')
            ->get()
            ->groupBy('id_pakan');

        $hargaPakan = [];

        foreach ($produkPakan as $produk) {
            $riwayatProduk = $riwayatHarga->get(
                $produk->id_pakan,
                collect()
            );

            foreach ($semuaTanggal as $tanggal) {
                /*
         * Mengikuti query lama:
         * mengambil id_harga_pakan terbesar sampai tanggal terkait.
         */
                $hargaTerakhir = $riwayatProduk
                    ->filter(function ($harga) use ($tanggal) {
                        return $harga->tgl <= $tanggal;
                    })
                    ->sortByDesc('id_harga_pakan')
                    ->first();

                $ttlGr = (float) ($hargaTerakhir->ttl_gr ?? 0);
                $ttlRp = (float) ($hargaTerakhir->ttl_rp ?? 0);
                $rpLain = (float) ($hargaTerakhir->rp_lain ?? 0);

                $key = $produk->id_pakan . '|' . $tanggal;

                $hargaPakan[$key] = $ttlGr > 0
                    ? ($ttlRp + $rpLain) / $ttlGr
                    : 0;
            }
        }

        return view(
            'laporan.partials.hd-tiga-minggu',
            compact(
                'k',
                'tgl',
                'dataMingguan',
                'produkPakan',
                'pemakaianPakan',
                'hargaPakan'
            )
        );
    }

    private function ambilDataPerMinggu(
        object $k,
        int $nomorMinggu,
        Carbon $awalMinggu,
        Carbon $tanggalLaporan
    ): array {
        $tanggalHarian = [];

        $popKurangPerHari = [];
        $hKuml = [];
        $popD = [];
        $popC = [];
        $butir2 = [];
        $kgKotor2 = [];
        $kgPakanD = [];
        $viFcrD = [];
        $vaFcrD = [];
        $popAkhirWeek = [];
        $hdl = [];

        for ($hari = 0; $hari < 7; $hari++) {
            $tanggal = $awalMinggu
                ->copy()
                ->addDays($hari)
                ->format('Y-m-d');

            $tanggalHarian[] = $tanggal;

            // Nilai default
            $popKurangPerHari[$tanggal] = 0;
            $hKuml[$tanggal] = 0;
            $popD[$tanggal] = 0;
            $popC[$tanggal] = 0;
            $butir2[$tanggal] = 0;
            $kgKotor2[$tanggal] = 0;
            $kgPakanD[$tanggal] = 0;
            $viFcrD[$tanggal] = 0;
            $vaFcrD[$tanggal] = 0;
            $popAkhirWeek[$tanggal] = 0;
            $hdl[$tanggal] = 0;

            // Jangan mengambil data tanggal yang belum terjadi
            if (Carbon::parse($tanggal)->gt($tanggalLaporan)) {
                continue;
            }

            $populasiKumulatif = DB::selectOne(
                "SELECT
                    SUM(
                        COALESCE(mati, 0) +
                        COALESCE(jual, 0) +
                        COALESCE(afkir, 0)
                    ) AS total
                FROM populasi
                WHERE id_kandang = ?
                    AND tgl BETWEEN ? AND ?
            ",
                [
                    $k->id_kandang,
                    $k->chick_in,
                    $tanggal,
                ]
            );

            $populasiHarian = DB::selectOne(
                "SELECT
                    SUM(COALESCE(mati, 0)) AS d,
                    SUM(COALESCE(jual, 0)) AS j,
                    SUM(COALESCE(afkir, 0)) AS c
                FROM populasi
                WHERE id_kandang = ?
                    AND tgl = ?
            ",
                [
                    $k->id_kandang,
                    $tanggal,
                ]
            );

            $telurHarian = DB::selectOne(
                "SELECT
                    SUM(COALESCE(pcs, 0)) AS pcs,
                    SUM(COALESCE(kg, 0)) AS kg
                FROM stok_telur
                WHERE tgl = ?
                    AND id_kandang = ?
            ",
                [
                    $tanggal,
                    $k->id_kandang,
                ]
            );

            $telurKumulatif = DB::selectOne(
                "SELECT
                    SUM(COALESCE(pcs, 0)) AS kuml_pcs,
                    SUM(COALESCE(kg, 0)) AS kuml_kg
                FROM stok_telur
                WHERE tgl BETWEEN ? AND ?
                    AND pcs != 0
                    AND id_kandang = ?
            ",
                [
                    $k->chick_in,
                    $tanggal,
                    $k->id_kandang,
                ]
            );

            $pakanHarian = DB::selectOne(
                "SELECT
                    SUM(COALESCE(d.pcs_kredit, 0)) AS kg_pakan
                FROM stok_produk_perencanaan AS d
                LEFT JOIN tb_produk_perencanaan AS e
                    ON e.id_produk = d.id_pakan
                WHERE d.tgl = ?
                    AND e.kategori = 'pakan'
                    AND d.id_kandang = ?
            ",
                [
                    $tanggal,
                    $k->id_kandang,
                ]
            );

            $vitaminHarian = DB::selectOne(
                "
                SELECT
                    SUM(COALESCE(debit, 0)) AS rp_vitamin
                FROM jurnal_accurate
                WHERE tgl = ?
                    AND nm_departemen = ?
                    AND kode = '5101-03'
            ",
                [
                    $tanggal,
                    $k->nm_kandang,
                ]
            );

            $vaksinHarian = DB::selectOne(
                "
                SELECT
                    SUM(COALESCE(ttl_rp, 0)) AS rp_vaksin
                FROM tb_vaksin_perencanaan
                WHERE tgl = ?
                    AND id_kandang = ?
            ",
                [
                    $tanggal,
                    $k->id_kandang,
                ]
            );

            $stokAwal = (float) ($k->stok_awal ?? 0);
            $totalPengurangan = (float) ($populasiKumulatif->total ?? 0);
            $populasiAkhir = $stokAwal - $totalPengurangan;

            $pcsTelur = (float) ($telurHarian->pcs ?? 0);
            $kgTelur = (float) ($telurHarian->kg ?? 0);
            $kumulatifPcs = (float) ($telurKumulatif->kuml_pcs ?? 0);

            $popD[$tanggal] = (float) ($populasiHarian->d ?? 0);
            $popC[$tanggal] = (float) ($populasiHarian->c ?? 0);
            $butir2[$tanggal] = $pcsTelur;
            $kgKotor2[$tanggal] = $kgTelur;
            $kgPakanD[$tanggal] = (float) ($pakanHarian->kg_pakan ?? 0);
            $viFcrD[$tanggal] = ((float) ($vitaminHarian->rp_vitamin ?? 0)) / 7000;
            $vaFcrD[$tanggal] = ((float) ($vaksinHarian->rp_vaksin ?? 0)) / 7000;
            $popAkhirWeek[$tanggal] = $totalPengurangan;

            $popKurangPerHari[$tanggal] = $populasiAkhir > 0
                ? ($pcsTelur / $populasiAkhir) * 100
                : 0;

            $hKuml[$tanggal] = $stokAwal > 0
                ? ($kumulatifPcs / $stokAwal) * 100
                : 0;

            /*
         * HDL: data pada hari yang sama di minggu sebelumnya.
         */
            $tanggalMingguLalu = Carbon::parse($tanggal)
                ->subWeek()
                ->format('Y-m-d');

            $populasiMingguLalu = DB::selectOne(
                "
                SELECT
                    SUM(
                        COALESCE(mati, 0) +
                        COALESCE(jual, 0) +
                        COALESCE(afkir, 0)
                    ) AS total
                FROM populasi
                WHERE id_kandang = ?
                    AND tgl BETWEEN ? AND ?
            ",
                [
                    $k->id_kandang,
                    $k->chick_in,
                    $tanggalMingguLalu,
                ]
            );

            $telurMingguLalu = DB::selectOne(
                "
                SELECT SUM(COALESCE(pcs, 0)) AS pcs
                FROM stok_telur
                WHERE tgl = ?
                    AND id_kandang = ?
            ",
                [
                    $tanggalMingguLalu,
                    $k->id_kandang,
                ]
            );

            $populasiAkhirMingguLalu =
                $stokAwal -
                (float) ($populasiMingguLalu->total ?? 0);

            $hdl[$tanggal] = $populasiAkhirMingguLalu > 0
                ? (
                    (float) ($telurMingguLalu->pcs ?? 0) /
                    $populasiAkhirMingguLalu
                ) * 100
                : 0;
        }

        $akhirMinggu = $awalMinggu
            ->copy()
            ->addDays(6)
            ->format('Y-m-d');

        $dtKdng = DB::select(
            "SELECT
                a.id_pakan,
                b.nm_produk,
                a.id_kandang
            FROM stok_produk_perencanaan AS a
            LEFT JOIN tb_produk_perencanaan AS b
                ON b.id_produk = a.id_pakan
            WHERE a.tgl BETWEEN ? AND ?
                AND a.id_kandang = ?
                AND b.kategori = 'pakan'
            GROUP BY
                a.id_pakan,
                b.nm_produk,
                a.id_kandang
        ",
            [
                $awalMinggu->format('Y-m-d'),
                $akhirMinggu,
                $k->id_kandang,
            ]
        );

        $peformance = DB::table('peformance')
            ->where('umur', $nomorMinggu)
            ->where('id_strain', $k->id_strain)
            ->first();



        return [
            'mgg'                  => $nomorMinggu,
            'awal_minggu'         => $awalMinggu->format('Y-m-d'),
            'akhir_minggu'        => $akhirMinggu,
            'tanggal_harian'      => $tanggalHarian,
            'pop_kurang_per_hari' => $popKurangPerHari,
            'h_kuml'              => $hKuml,
            'popD'                => $popD,
            'popC'                => $popC,
            'butir2'              => $butir2,
            'kg_kotor2'           => $kgKotor2,
            'kg_pakan_d'          => $kgPakanD,
            'vi_fcr_d'            => $viFcrD,
            'va_fcr_d'            => $vaFcrD,
            'pop_akihir_week'     => $popAkhirWeek,
            'hdl'                 => $hdl,
            'dt_kdng'             => $dtKdng,
            'peformance'           => $peformance,
        ];
    }
}
