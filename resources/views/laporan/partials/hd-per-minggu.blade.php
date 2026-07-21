<style>
    /*
     * Header baris pertama:
     * th ke-3 = Minggu pertama
     * th ke-4 = Minggu kedua
     */
    .hd-tiga-minggu thead tr:first-child th:nth-child(3),
    .hd-tiga-minggu thead tr:first-child th:nth-child(4) {
        border-right: 7px solid #e7e7e7 !important;
    }

    /*
     * Header tanggal tidak memiliki kolom Ket dan :
     * karena kedua kolom tersebut menggunakan rowspan.
     */
    .hd-tiga-minggu thead tr:nth-child(2) th:nth-child(7),
    .hd-tiga-minggu thead tr:nth-child(2) th:nth-child(14) {
        border-right: 7px solid #e7e7e7 !important;
    }

    /*
     * Isi tabel:
     * kolom ke-9  = hari terakhir minggu pertama
     * kolom ke-16 = hari terakhir minggu kedua
     */
    .hd-tiga-minggu tbody tr>td:nth-child(9),
    .hd-tiga-minggu tbody tr>td:nth-child(16) {
        border-right: 7px solid #e7e7e7 !important;
    }

    .hd-tiga-minggu .week-age-header {
        padding: 7px 10px;
        background: #eef2ff !important;
        color: #3652ad;
        font-weight: 800;
        letter-spacing: 0.2px;
        white-space: nowrap;
    }
</style>
<style>
    /*
     * Wrapper tabel Ket tidak boleh membuat scroll sendiri.
     * Scroll mengikuti .table-container pada halaman utama.
     */
    .table-container .hd-tiga-minggu-wrapper {
        position: static;
        width: max-content;
        min-width: 100%;
        max-width: none;
        overflow: visible !important;
    }

    .table-container .hd-tiga-minggu {
        width: max-content;
        min-width: 100%;
        white-space: nowrap;
        border-collapse: separate !important;
        border-spacing: 0;
    }

    /*
     * Ket, D, C, Butir, dan seterusnya berhenti
     * tepat setelah kolom Kandang.
     */
    .table-container .hd-tiga-minggu .sticky-column {
        position: -webkit-sticky;
        position: sticky !important;
        left: var(--lebar-kandang, 0px) !important;
        z-index: 30;
        width: 170px;
        min-width: 170px;
        background: #fff !important;
        box-shadow: 3px 0 5px rgba(0, 0, 0, 0.18);
    }

    .table-container .hd-tiga-minggu thead .sticky-column {
        z-index: 40;
        background: #f8f9fa !important;
    }
</style>

<div class="table-responsive hd-tiga-minggu-wrapper">
    <table class="table table-sm table-bordered hd-tiga-minggu">
        <thead>
            {{-- Header nomor minggu --}}
            <tr>
                <th rowspan="2" class="align-middle sticky-column">
                    Ket
                </th>

                <th rowspan="2" class="align-middle text-center">
                    :
                </th>

                @foreach ($dataMingguan as $minggu)
                    <th colspan="{{ count($minggu['tanggal_harian']) }}" class="week-age-header text-center">
                        Minggu ke-{{ $minggu['mgg'] }}
                    </th>
                @endforeach
            </tr>

            {{-- Header hari 1-7 --}}
            <tr>
                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $index => $tgl_hari)
                        <th class="text-center">
                            {{ $index + 1 }}/7

                            <small class="d-block fw-normal">
                                {{ date('d/m', strtotime($tgl_hari)) }}
                            </small>
                        </th>
                    @endforeach
                @endforeach
            </tr>
        </thead>

        <tbody>
            {{-- D --}}
            <tr>
                <td class="sticky-column">D</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        <td class="text-center">
                            {{ number_format($minggu['popD'][$tgl_hari] ?? 0, 0) }}
                        </td>
                    @endforeach
                @endforeach
            </tr>

            {{-- C --}}
            <tr>
                <td class="sticky-column">C</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        <td class="text-center">
                            {{ number_format($minggu['popC'][$tgl_hari] ?? 0, 0) }}
                        </td>
                    @endforeach
                @endforeach
            </tr>

            {{-- Butir --}}
            <tr>
                <td class="sticky-column">Butir</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        <td class="text-center">
                            {{ number_format($minggu['butir2'][$tgl_hari] ?? 0, 0) }}
                        </td>
                    @endforeach
                @endforeach
            </tr>

            {{-- Kg bersih --}}
            <tr>
                <td class="sticky-column">Kg bersih</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        @php
                            $butir = $minggu['butir2'][$tgl_hari] ?? 0;
                            $kgKotor = $minggu['kg_kotor2'][$tgl_hari] ?? 0;
                            $kgBersih = $kgKotor - $butir / 180;
                        @endphp

                        <td class="text-center">
                            {{ $butir == 0 ? '0' : number_format($kgBersih, 1) }}
                        </td>
                    @endforeach
                @endforeach
            </tr>

            {{-- Gram per butir --}}
            <tr>
                <td class="sticky-column">Gr (butir)</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        @php
                            $butir = $minggu['butir2'][$tgl_hari] ?? 0;
                            $kgKotor = $minggu['kg_kotor2'][$tgl_hari] ?? 0;
                            $kgBersih = $kgKotor - $butir / 180;

                            $gramPerButir = $butir > 0 ? ($kgBersih * 1000) / $butir : 0;
                        @endphp

                        <td class="text-center">
                            {{ $butir == 0 ? '0' : number_format($gramPerButir, 1) }}
                        </td>
                    @endforeach
                @endforeach
            </tr>

            {{-- Target performa berat telur --}}
            <tr>
                <td class="sticky-column">P</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @php
                        $targetBeratTelur = data_get($minggu, 'peformance.berat_telur', 0);
                    @endphp

                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        <td class="text-center text-primary fw-bold">
                            {{ $targetBeratTelur > 0 ? number_format($targetBeratTelur, 1) : 'NA' }}
                        </td>
                    @endforeach
                @endforeach
            </tr>

            {{-- HD --}}
            <tr>
                <td class="sticky-column">HD</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        @php
                            $nilaiHd = $minggu['pop_kurang_per_hari'][$tgl_hari] ?? 0;

                            $selisihHd = ($k->p_hd ?? 0) - $nilaiHd;

                            $classHd = $nilaiHd > 0 && $selisihHd > 3 ? 'text-danger fw-bold' : '';
                        @endphp

                        <td class="text-center {{ $classHd }}">
                            {{ $nilaiHd == 0 ? '-' : number_format($nilaiHd, 0) }}
                        </td>
                    @endforeach
                @endforeach
            </tr>

            {{-- HDL --}}
            <tr>
                <td class="sticky-column">HDL</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        @php
                            $nilaiHdl = $minggu['hdl'][$tgl_hari] ?? 0;

                            $selisihHdl = ($k->p_hd ?? 0) - $nilaiHdl;

                            $classHdl = $nilaiHdl > 0 && $selisihHdl > 3 ? 'text-danger fw-bold' : '';
                        @endphp

                        <td class="text-center {{ $classHdl }}">
                            {{ $nilaiHdl == 0 ? '-' : number_format($nilaiHdl, 0) }}
                        </td>
                    @endforeach
                @endforeach
            </tr>

            <tr>
                <td class="sticky-column">P</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @php
                        $targetHd = data_get($minggu, 'peformance.telur', 0);
                    @endphp

                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        <td class="text-center text-primary fw-bold">
                            {{ $targetHd > 0 ? number_format($targetHd, 1) : 'NA' }}
                        </td>
                    @endforeach
                @endforeach
            </tr>

            {{-- Pakan --}}

            @foreach ($produkPakan as $produk)
                {{-- Pemakaian pakan --}}
                <tr>
                    <td class="sticky-column">
                        {{ $produk->nm_produk }}
                    </td>

                    <td class="text-center">:</td>

                    @foreach ($dataMingguan as $minggu)
                        @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                            @php
                                $key = $produk->id_pakan . '|' . $tgl_hari;

                                $pemakaian = (float) ($pemakaianPakan[$key] ?? 0);
                            @endphp

                            <td class="text-center">
                                {{ $pemakaian > 0 ? number_format($pemakaian / 1000, 1) : '0' }}
                            </td>
                        @endforeach
                    @endforeach
                </tr>

                {{-- Harga pakan --}}
                <tr>
                    <td class="sticky-column">
                        Harga
                    </td>

                    <td class="text-center">:</td>

                    @foreach ($dataMingguan as $minggu)
                        @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                            @php
                                $key = $produk->id_pakan . '|' . $tgl_hari;

                                $pemakaian = (float) ($pemakaianPakan[$key] ?? 0);

                                $harga = (float) ($hargaPakan[$key] ?? 0);
                            @endphp

                            <td class="text-center">
                                {{ $pemakaian > 0 && $harga > 0 ? number_format($harga, 0) : '0' }}
                            </td>
                        @endforeach
                    @endforeach
                </tr>
            @endforeach
            {{-- Pakan --}}

            {{-- FCR harian --}}
            <tr>
                <td class="sticky-column">FCR D</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        @php
                            $butir = $minggu['butir2'][$tgl_hari] ?? 0;

                            $kgKotor = $minggu['kg_kotor2'][$tgl_hari] ?? 0;

                            $kgPakan = $minggu['kg_pakan_d'][$tgl_hari] ?? 0;

                            $kgBersih = $kgKotor - $butir / 180;

                            $fcrD = $kgBersih > 0 ? $kgPakan / 1000 / $kgBersih : 0;
                        @endphp

                        <td class="text-center {{ $fcrD >= 2.5 ? 'text-danger fw-bold' : '' }}">
                            {{ number_format($fcrD, 2) }}
                        </td>
                    @endforeach
                @endforeach
            </tr>
            <tr>
                <td class="sticky-column">FCR D+</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        @php
                            $butir = (float) ($minggu['butir2'][$tgl_hari] ?? 0);

                            $kgKotor = (float) ($minggu['kg_kotor2'][$tgl_hari] ?? 0);

                            $kgPakan = (float) ($minggu['kg_pakan_d'][$tgl_hari] ?? 0);

                            $vitamin = (float) ($minggu['vi_fcr_d'][$tgl_hari] ?? 0);

                            $vaksin = (float) ($minggu['va_fcr_d'][$tgl_hari] ?? 0);

                            $kgBersih = $kgKotor - $butir / 180;

                            $fcrDPlus = $kgBersih > 0 ? ($kgPakan / 1000 + $vitamin + $vaksin) / $kgBersih : 0;
                        @endphp

                        <td class="text-center {{ $fcrDPlus >= 2.2 ? 'text-danger fw-bold' : '' }}">
                            {{ $fcrDPlus > 0 ? number_format($fcrDPlus, 2) : '0' }}
                        </td>
                    @endforeach
                @endforeach
            </tr>
            <tr>
                <th class="sticky-column">HD & FCR WEEK</th>
                <th colspan="29"></th>
            </tr>
            <tr>
                <td class="sticky-column">HD W</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @php
                        // Reset per minggu
                        $telurPcsHdWeek = 0;
                        $jumlahHari = 0;
                    @endphp

                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        @php
                            $sudahTerjadi = \Carbon\Carbon::parse($tgl_hari)->lte($tgl);

                            $butirHarian = (float) ($minggu['butir2'][$tgl_hari] ?? 0);

                            /*
                             * pop_akihir_week berisi total pengurangan populasi
                             * dari chick-in sampai tanggal tersebut.
                             */
                            $totalPengurangan = (float) ($minggu['pop_akihir_week'][$tgl_hari] ?? 0);

                            $populasiAktif = (float) ($k->stok_awal ?? 0) - $totalPengurangan;

                            if ($sudahTerjadi) {
                                $telurPcsHdWeek += $butirHarian;
                                $jumlahHari++;
                            }

                            $hdWeek =
                                $sudahTerjadi && $jumlahHari > 0 && $populasiAktif > 0
                                    ? ($telurPcsHdWeek / $jumlahHari / $populasiAktif) * 100
                                    : 0;
                        @endphp

                        <td class="text-center">
                            {{ $hdWeek > 0 ? number_format($hdWeek, 0) : '0' }}
                        </td>
                    @endforeach
                @endforeach
            </tr>
            <tr>
                <td class="sticky-column">FCR W</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @php
                        // Reset pada setiap minggu
                        $kgKotorFcrWeek = 0;
                        $butirFcrWeek = 0;
                        $kgPakanFcrWeek = 0;
                    @endphp

                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        @php
                            $sudahTerjadi = \Carbon\Carbon::parse($tgl_hari)->lte($tgl);

                            $kgKotorHarian = (float) ($minggu['kg_kotor2'][$tgl_hari] ?? 0);

                            $butirHarian = (float) ($minggu['butir2'][$tgl_hari] ?? 0);

                            $kgPakanHarian = (float) ($minggu['kg_pakan_d'][$tgl_hari] ?? 0);

                            if ($sudahTerjadi) {
                                $kgKotorFcrWeek += $kgKotorHarian;
                                $butirFcrWeek += $butirHarian;
                                $kgPakanFcrWeek += $kgPakanHarian;
                            }

                            /*
                             * Berat bersih telur kumulatif:
                             * kg kotor - berat tray.
                             */
                            $kgBersihFcrWeek = $kgKotorFcrWeek - $butirFcrWeek / 180;

                            $fcrWeek =
                                $sudahTerjadi && $butirHarian > 0 && $kgBersihFcrWeek > 0
                                    ? $kgPakanFcrWeek / 1000 / $kgBersihFcrWeek
                                    : 0;
                        @endphp

                        <td class="text-center {{ $fcrWeek >= 2.5 ? 'text-danger fw-bold' : '' }}">
                            {{ $fcrWeek > 0 ? number_format($fcrWeek, 2) : '0' }}
                        </td>
                    @endforeach
                @endforeach
            </tr>
            <tr>
                <td class="sticky-column">FCR W+</td>
                <td class="text-center">:</td>

                @foreach ($dataMingguan as $minggu)
                    @php
                        // Reset pada awal setiap minggu
                        $kgKotorFcrWeek = 0;
                        $butirFcrWeek = 0;
                        $kgPakanFcrWeek = 0;
                        $vitaminFcrWeek = 0;
                        $vaksinFcrWeek = 0;
                    @endphp

                    @foreach ($minggu['tanggal_harian'] as $tgl_hari)
                        @php
                            $sudahTerjadi = \Carbon\Carbon::parse($tgl_hari)->lte($tgl);

                            $kgKotorHarian = (float) ($minggu['kg_kotor2'][$tgl_hari] ?? 0);

                            $butirHarian = (float) ($minggu['butir2'][$tgl_hari] ?? 0);

                            $kgPakanHarian = (float) ($minggu['kg_pakan_d'][$tgl_hari] ?? 0);

                            $vitaminHarian = (float) ($minggu['vi_fcr_d'][$tgl_hari] ?? 0);

                            $vaksinHarian = (float) ($minggu['va_fcr_d'][$tgl_hari] ?? 0);

                            if ($sudahTerjadi) {
                                $kgKotorFcrWeek += $kgKotorHarian;
                                $butirFcrWeek += $butirHarian;
                                $kgPakanFcrWeek += $kgPakanHarian;
                                $vitaminFcrWeek += $vitaminHarian;
                                $vaksinFcrWeek += $vaksinHarian;
                            }

                            $kgBersihFcrWeek = $kgKotorFcrWeek - $butirFcrWeek / 180;

                            $fcrWeekPlus =
                                $sudahTerjadi && $butirHarian > 0 && $kgBersihFcrWeek > 0
                                    ? ($kgPakanFcrWeek / 1000 + $vitaminFcrWeek + $vaksinFcrWeek) /
                                        $kgBersihFcrWeek
                                    : 0;
                        @endphp

                        <td class="text-center {{ $fcrWeekPlus >= 2.5 ? 'text-danger fw-bold' : '' }}">
                            {{ $fcrWeekPlus > 0 ? number_format($fcrWeekPlus, 2) : '0' }}
                        </td>
                    @endforeach
                @endforeach
            </tr>
        </tbody>
    </table>
</div>
