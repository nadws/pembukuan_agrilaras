<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Layer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,400;6..12,500;6..12,600;6..12,700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }

        .table_layer {
            border: 0.5px solid white;
            font-size: 10px;
            padding: 5px;
            white-space: nowrap
        }

        .td_layer {
            border: 0.5px solid #d6ddf5;
            font-size: 10px;
            padding: 8px;

        }

        .table-two th,
        .table-two td {
            border-color: transparent;
            font-size: 10px;
            padding: 2px;
        }

        .table-two_pakan th,
        .table-two_pakan td {
            border-color: transparent;
            font-size: 10px;
            padding: 2px;
        }

        .w_pakan {
            background-color: #F2C293;
            color: black;
            text-align: right
        }

        .dhead {
            background-color: #435EBE !important;
            color: white;
        }

        @media screen and (max-width: 768px) {
            .elemen-hilang {
                display: none;
            }
        }
    </style>
    <style>
        /*
     * Satu-satunya area scroll untuk seluruh tabel.
     */
        .table-container {
            --lebar-kandang: 0px;
            position: relative;
            width: 100%;
            max-height: 600px;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
            isolation: isolate;
        }

        /*
     * Header biru Kandang:
     * freeze horizontal dan vertikal.
     */
        .freeze-cell1_th {
            position: sticky !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 120 !important;
            background-color: #435ebe !important;
            color: #fff !important;
        }

        /*
     * Isi kolom Kandang:
     * hanya freeze horizontal.
     */
        .freeze-cell1_td {
            position: sticky !important;
            left: 0 !important;
            z-index: 70 !important;
            background-color: #f2f7ff !important;
            box-shadow: 3px 0 5px rgba(0, 0, 0, 0.15);
        }

        /*
     * Semua header biru selain Kandang.
     * Harus lebih tinggi daripada Ket.
     */
        .th_atas {
            position: sticky !important;
            top: 0 !important;
            z-index: 100 !important;
            background-color: #435ebe !important;
            color: #fff !important;
        }

        /*
     * Jika masih menggunakan header baris kedua.
     */
        .th_atas2 {
            position: sticky !important;
            top: 66.5px !important;
            z-index: 100 !important;
            background-color: #435ebe !important;
            color: #fff !important;
        }

        /*
     * Class lama jika masih digunakan.
     */
        .freeze-cell_th1 {
            position: sticky !important;
            top: 0 !important;
            left: 62.5px !important;
            z-index: 110 !important;
            background-color: #435ebe !important;
            color: #fff !important;
        }

        .freeze-cell_th2 {
            position: sticky !important;
            top: 66.5px !important;
            left: 62.5px !important;
            z-index: 110 !important;
            background-color: #435ebe !important;
            color: #fff !important;
        }

        .freeze-cell_td {
            position: sticky !important;
            left: 62.5px !important;
            z-index: 60 !important;
            background-color: #f2f7ff !important;
        }

        /*
     * Wrapper Ket tidak boleh memiliki scroll sendiri.
     */
        .table-container .hd-tiga-minggu-wrapper {
            position: static !important;
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
     * Ket berhenti setelah lebar Kandang.
     */
        .table-container .hd-tiga-minggu .sticky-column {
            position: sticky !important;
            left: var(--lebar-kandang, 0px) !important;
            z-index: 30 !important;
            width: 170px;
            min-width: 170px;
            background-color: #fff !important;
            box-shadow: 3px 0 5px rgba(0, 0, 0, 0.15);
        }

        /*
     * Header Ket masih di bawah header biru.
     */
        .table-container .hd-tiga-minggu thead .sticky-column {
            z-index: 40 !important;
            background-color: #f8f9fa !important;
        }

        @media screen and (max-width: 768px) {

            /*
     * Hilangkan scroll besar milik halaman.
     */
            html,
            body {
                width: 100%;
                height: 100%;
                overflow: hidden !important;
            }

            /*
     * Halaman dibuat setinggi layar HP.
     */
            .laporan-layer-page {
                display: flex;
                flex-direction: column;
                width: 100%;
                height: calc(100dvh - 0.5rem);
                min-height: 0;
                overflow: hidden !important;
                padding-bottom: env(safe-area-inset-bottom);
            }

            /*
     * Filter tanggal tidak ikut scroll.
     */
            .laporan-layer-page>form {
                flex: 0 0 auto;
            }

            /*
     * Hanya tabel ini yang dapat di-scroll.
     */
            .laporan-layer-page>.table-container {
                flex: 1 1 auto;
                width: 100%;
                min-height: 0;
                max-height: none !important;
                overflow: auto !important;
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
            }

            /*
     * Tabel Ket tidak membuat scroll tambahan.
     */
            .table-container .hd-tiga-minggu-wrapper {
                overflow: visible !important;
                max-height: none !important;
            }
        }

        @media screen and (max-width: 991.98px) {
            .laporan-layer-page {
                margin-top: 0 !important;
                padding-top: 0 !important;
                height: 100dvh;
            }

            .table-container {
                flex: 1 1 100%;
                max-height: none !important;
                height: 100%;
            }
        }
    </style>

</head>

<body>
    <nav class="navbar elemen-hilang" style="background: #FFFFFF; border: #435EBE">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="/assets/login/img/agri_laras2.png" alt="Bootstrap" width="40" height="40">
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-2 laporan-layer-page">

        <form action="" class="d-none d-md-block">
            <div class="row ">
                <div class="col-6 col-lg-9 elemen-hilang">
                    <h6 class="mb-2">Laporan Layer {{ tanggal($tgl) }}</h6>
                </div>

                <div class="col-12 col-lg-3 float-end d-flex align-items-center">

                    <input type="date" class="form-control" name="tgl" value="{{ $tgl }}">
                    <button type="submit" class="btn btn-primary btn-sm ms-2">Filter</button>
                </div>
                <div class="col-lg-4 mb-2">
                    <ul class="nav nav-pills nav-fill">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('laporan_layer') ? 'active' : '' }}"
                                href="{{ route('laporan_layer') }}">Laporan layer</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('labaRugiKandang2') ? 'active' : '' }}"
                                href="{{ route('labaRugiKandang2') }}">Laba rugi kandang</a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dokumentasi_laporan_layer') ? 'active' : '' }}"
                                href="{{ route('dokumentasi_laporan_layer', request()->only('tgl')) }}">Dokumentasi rumus</a>
                        </li> --}}

                    </ul>
                </div>

            </div>
        </form>

        <div class="table-responsive table-container">

            <table style="text-align: center; " class="table_layer" width="100%">
                <thead style="border: 1px solid white">
                    <tr>
                        <th class="dhead freeze-cell1_th table_layer ">
                            Kandang
                        </th>
                        <th class="dhead table_layer th_atas "> Data telur
                        </th>

                        {{-- <th class="dhead table_layer th_atas">Pakan</th> --}}

                        <th class="dhead table_layer th_atas">kg / (gr/ekor) / p /(day)</th>
                        <th class="dhead table_layer th_atas">Obat/vit</th>
                    </tr>
                    {{-- <tr> --}}



                    {{-- Data Telur --}}

                    {{-- pakan --}}
                    {{-- <th class="dhead table_layer th_atas2"></th> --}}
                    {{-- KUML --}}
                    {{-- <th class="dhead table_layer th_atas2">pakan(kg) <br> telur(kg) <br> <span
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Summary dari = (rata rata penjualan telur perhari * produksi telur perhari) / ttl kg bersih">R2Rp</span>
                        </th> --}}
                    {{-- <th class="dhead table_layer th_atas2"></th> --}}
                    {{-- <th class="dhead table_layer th_atas2">Pakan</th> --}}





                    {{-- KUML --}}
                    {{-- </tr> --}}
                </thead>
                <tbody>
                    @php
                        $ayam_awal = 0;
                        $ayam_akhir = 0;

                        $kg_total = 0;
                        $kg_kotor = 0;
                        $gr_butir = 0;
                        $pakan = 0;
                        $butir = 0;
                        $kg_today = 0;
                        $pcs = 0;

                        // kuml
                        $pakan_kuml = 0;
                        $telur_kuml = 0;
                        $obat_kuml = 0;
                        $vaksin_kuml = 0;

                        $mati = 0;
                        $jual = 0;

                        $butir_minggu = 0;
                        $kg_minggu = 0;

                        $dc_week = 0;
                        $rp_ayam = 0;

                        $gjl_ttl = 0;

                        $rp_vitamin = 0;
                        $rp_vaksin = 0;

                        $ttl_tpl = 0;
                    @endphp
                    @foreach ($kandang as $k)
                        @php
                            $dc_week += $k->mati_week + $k->jual_week;
                            $kg_total += empty($k->pcs) ? '0' : $k->kg - $k->pcs / 180;
                            $kg_kotor += empty($k->pcs) ? '0' : $k->kg;
                            $gr_butir += empty($k->pcs)
                                ? '0'
                                : number_format((($k->kg - $k->pcs / 180) * 1000) / $k->pcs, 0);
                            $pakan += empty($k->kg_pakan) ? '0' : $k->kg_pakan / 1000;
                            $butir += $k->pcs - $k->pcs_past;
                            $kg_today += $k->kg - $k->pcs / 180 - ($k->kg_past - $k->pcs_past / 180);

                            // kuml
                            $pakan_kuml += $k->kg_pakan_kuml / 1000;
                            $telur_kuml += $k->kuml_kg - $k->kuml_pcs / 180;
                            $obat_kuml += $k->kuml_rp_vitamin;
                            $vaksin_kuml += $k->kum_ttl_rp_vaksin;

                            $ayam_awal += $k->stok_awal;
                            $ayam_akhir += $k->stok_awal - $k->pop_kurang;

                            $mati += empty($k->mati) ? '0' : $k->mati;
                            $jual += empty($k->jual) ? '0' : $k->jual;

                            $butir_minggu += $k->pcs_satu_minggu - $k->pcs_minggu_sebelumnya;
                            $kg_minggu +=
                                $k->kg_satu_minggu -
                                $k->pcs_satu_minggu / 180 -
                                ($k->kg_minggu_sebelumnya - $k->pcs_minggu_sebelumnya / 180);

                            $pcs += $k->pcs;
                            $rp_ayam += $k->rupiah;
                            $gjl_ttl += 185 * $k->stok_awal * $k->ttl_gjl;

                            $rp_vitamin += empty($k->rp_vitamin) ? '0' : $k->rp_vitamin / 7000;
                            $rp_vaksin += empty($k->ttl_rp_vaksin) ? '0' : $k->ttl_rp_vaksin / 7000;
                        @endphp
                        <tr>
                            <td align="center" class="kandang baris-kandang2 freeze-cell1_td td_layer"
                                data-id="{{ $k->id_kandang }}" tgl="{{ $tgl }}">
                                <table width="100%">
                                    <tr>
                                        <td>kndg</td>
                                        <td>: <a href="#" data-bs-toggle="modal" data-bs-target="#laba-rugi"
                                                class="laba-rugi" id_kandang="{{ $k->id_kandang }}">
                                                {{ $k->nm_kandang }}</a></td>
                                    </tr>
                                    <tr>
                                        <td>chk in</td>
                                        <td>: <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="tanggal chick in">
                                                {{ date('d/m/y', strtotime($k->chick_in)) }}
                                            </span></td>
                                    </tr>
                                    @php
                                        $chick_in_next = date('Y-m-d', strtotime($k->chick_out . ' +1 month'));
                                        $merah = date('Y-m-d', strtotime($chick_in_next . ' -15 weeks'));
                                        $tgl_hari_ini = date('Y-m-d');
                                        $afkir = date('Y-m-d', strtotime($k->chick_in . ' +99 weeks'));
                                        $afkir2 = date('Y-m-d', strtotime($afkir . ' -4 weeks'));

                                        $ckin2 = date('Y-m-d', strtotime($k->chick_in . ' +80 weeks'));
                                        $ckin21 = date('Y-m-d', strtotime($ckin2 . ' -4 weeks'));
                                    @endphp
                                    <tr>
                                        <td>afkir</td>
                                        <td>: <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="tanggal afkir hitung 99 minggu dari chick in"
                                                class="{{ $tgl_hari_ini >= $afkir2 ? 'text-danger fw-bold' : '' }}">
                                                {{ date('d/m/y', strtotime($afkir)) }} </span></td>
                                    </tr>
                                    <tr>
                                        <td>chk in2</td>
                                        <td>: <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="tanggal chick in 2 hitung 80 minggu dari chick in"
                                                class="{{ $tgl_hari_ini >= $ckin21 ? 'text-danger fw-bold' : '' }}">
                                                {{ date('d/m/y', strtotime($ckin2)) }}
                                            </span></td>
                                    </tr>
                                    <tr>
                                        <td>mgg/gjl</td>
                                        <td>:
                                            {{ $k->mgg }} / <span data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="gjl : total hari ayam makan / 7">{{ number_format($k->ttl_gjl / 7) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>afkir</td>
                                        <td>:
                                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="perkiran afkir 99 minggu sama semua kandang">
                                                {{ $k->mgg_afkir }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>afkir(%)</td>
                                        <td>:
                                            ({{ number_format(($k->mgg / $k->mgg_afkir) * 100, 0) }}%)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>pop awal</td>
                                        <td>:
                                            {{ $k->stok_awal }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>pop akhir</td>
                                        <td>:
                                            {{ $k->stok_awal - $k->pop_kurang }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>pop(%)</td>
                                        <td>:
                                            {{ number_format((($k->stok_awal - $k->pop_kurang) / $k->stok_awal) * 100, 1) }}%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>r2 pakan</td>
                                        <td>: <span class="txt-rata-pakan"></span></td>
                                    </tr>
                                    <tr>
                                        <td>fcrk</td>
                                        <td>: <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="fcrk = ttl kg pakan / ttl telur" class="txt-fcrk"></span></td>

                                    </tr>
                                    <tr>
                                        <td>fcrk+</td>
                                        <td>: <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="fcrk+ = (ttl kg pakan + ((vitamin + vaksin + pullet + rak + operasional) / rata2 pakan)) / ttl telur"
                                                class=" txt-fcrkplus"></span></td>

                                    </tr>
                                    <tr>
                                        <td align="left">ttl Profit</td>
                                        <td align="left">:<span class="txt-telur-kg"></span></td>
                                    </tr>
                                    <tr>
                                        <td align="left">B Pakan</td>
                                        <td align="left">:<span class="txt-b_pakan"></span></td>
                                    </tr>
                                    <tr>
                                        <td align="left">B Vitamin</td>
                                        <td align="left">:<span class="txt-b_vitamin"></span></td>
                                    </tr>
                                    <tr>
                                        <td align="left">B Vaksin</td>
                                        <td align="left">:<span class="txt-b_vaksin"></span></td>
                                    </tr>
                                    <tr>
                                        <td align="left">B Pullet</td>
                                        <td align="left">:<span class="txt-b_pullet"></span></td>
                                    </tr>
                                    <tr>
                                        <td align="left">B Rak</td>
                                        <td align="left">:<span class="txt-b_rak"></span></td>
                                    </tr>
                                    <tr>
                                        <td align="left">B Operasional</td>
                                        <td align="left">:<span class="txt-b_oper"></span></td>
                                    </tr>
                                    <tr>
                                        <td align="left">Profit - B</td>
                                        <td align="left">:<span class="txt-laba"></span></td>
                                    </tr>
                                    <tr>
                                        <td align="left">PNL / ttl kg</td>
                                        <td align="left">:<span class="txt-rata"></span></td>
                                    </tr>
                                </table>
                            </td>

                            <!-- umur -->

                            <!-- populasi -->
                            @php
                                $tot_ayam_mati = empty($k->mati) ? '0' : $k->mati;
                                $tot_ayam_jual = empty($k->jual) ? '0' : $k->jual;
                                $tot_ayam_semua_hilang = $tot_ayam_mati;
                            @endphp
                            @php
                                $gr_butir = empty($k->pcs)
                                    ? '0'
                                    : number_format((($k->kg - $k->pcs / 180) * 1000) / $k->pcs, 1);
                            @endphp

                            <td align="center" class="hd perday td_layer">
                                <div class="js-hd-tiga-minggu" data-url="{{ route('laporan_layer.hd_tiga_minggu') }}"
                                    data-id-kandang="{{ $k->id_kandang }}" data-tgl="{{ $tgl }}">
                                    <span class="text-muted">Memuat data...</span>
                                </div>
                            </td>

                            <!-- (12777) / (3) / (2296) -->



                            <!-- data telur -->
                            <!-- kuml -->
                            <td align="center" class="pakan(kg) td_layer">
                                &nbsp; <br>
                                {{ number_format(empty($k->kg_pakan_kuml) ? '0' : $k->kg_pakan_kuml / 1000, 1) }}
                                <br>
                                {{ number_format($k->kuml_kg - $k->kuml_pcs / 180, 1) }}
                                <br>
                                {{ number_format($k->rata, 0) }}
                            </td>
                            {{-- vitamin --}}
                            <td class="td_layer">
                                @php
                                    $vitamin = DB::select("SELECT a.id_pakan, b.nm_produk, c.nm_satuan, a.id_kandang, a.pcs_kredit, b.kategori
                                        FROM stok_produk_perencanaan as a
                                        left JOIN tb_produk_perencanaan as b on b.id_produk = a.id_pakan
                                        left join tb_satuan as c on c.id_satuan = b.dosis_satuan
                                        WHERE a.tgl = '$tgl' and a.id_kandang = '$k->id_kandang' and b.kategori in('obat_pakan', 'obat_air');");
                                @endphp

                                @foreach ($vitamin as $v)
                                    <a href="#" onclick="return false;" data-bs-toggle="modal"
                                        data-bs-target="#history" class="history" id_produk="{{ $v->id_pakan }}"
                                        id_kandang="{{ $k->id_kandang }}">
                                        {{ $v->nm_produk }} :
                                        {{ number_format($v->pcs_kredit, 1) }}
                                        {{ $v->nm_satuan }} </a> <br>
                                @endforeach
                            </td>
                            {{-- <td class="td_layer">
                                @php
                                    $vitamin = DB::select("SELECT a.id_pakan, b.nm_produk, c.nm_satuan, a.id_kandang, a.pcs_kredit, b.kategori,  a.total_rp
                                    FROM stok_produk_perencanaan as a
                                    left JOIN tb_produk_perencanaan as b on b.id_produk = a.id_pakan
                                    left join tb_satuan as c on c.id_satuan = b.dosis_satuan
                                    WHERE a.tgl = '$tgl' and a.id_kandang = '$k->id_kandang' and b.kategori in('pakan');");

                                    $ttl_rp_pakan = 0;
                                    $gr_pakan_ttl = 0;
                                @endphp
                                @foreach ($vitamin as $v)
                                    @php
                                        $ttl_rp_pakan += $v->total_rp;
                                        $gr_pakan_ttl += $v->pcs_kredit / 1000;
                                    @endphp
                                    <a href="#" onclick="return false;" data-bs-toggle="modal"
                                        data-bs-target="#history" class="history" id_produk="{{ $v->id_pakan }}"
                                        id_kandang="{{ $k->id_kandang }}">{{ $v->nm_produk }} :
                                        {{ number_format($v->pcs_kredit / 1000, 1) }}
                                        Kg</a> :
                                    {{ number_format(($harga_pakan[$v->id_pakan]->ttl_rp + $harga_pakan[$v->id_pakan]->rp_lain) / $harga_pakan[$v->id_pakan]->ttl_gr, 0) }}
                                    <br>
                                @endforeach
                            </td> --}}







                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="dhead freeze-cell1_th table_layer">Total</th>
                        <th class="dhead  table_layer">
                            <span>butir : {{ number_format($pcs, 0) }}</span> <br>
                            <span>kg bersih : {{ number_format($kg_total, 0) }}</span> <br>
                            <span>kg kotor : {{ number_format($kg_kotor, 0) }}</span>
                        </th>
                        {{-- <th class="dhead table_layer"></th> --}}
                        {{-- <th class="dhead table_layer">{{ number_format($pakan, 2) }} </th> --}}

                        <th class="dhead table_layer">{{ number_format($pakan_kuml, 2) }} <br>
                            {{ number_format($telur_kuml, 2) }} </th>
                        <th class="dhead table_layer"></th>
                        {{-- <th class="dhead table_layer">{{ number_format($pakan, 1) }}</th> --}}



                    </tr>
                </tfoot>


            </table>
        </div>

    </div>
    <div class="modal fade" id="rumus" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Rumus</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="rumus_layer"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="history" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Kegunaan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="history_pemakaian"></div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .modal-lg-max {
            max-width: 90%;

        }
    </style>
    <div class="modal fade" id="laba-rugi" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg-max">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Laba dan Rugi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-12">
                            <div id="laba-rugi_kandang"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(function() {
        /*
         * =========================================================
         * FREEZE KANDANG DAN KET
         * =========================================================
         */
        function sesuaikanFreezeKet() {
            $('.table-container').each(function() {
                var tableContainer = this;

                var $kolomKandang = $(tableContainer)
                    .find('.freeze-cell1_th')
                    .first();

                if (!$kolomKandang.length) {
                    return;
                }

                var lebarKandang = Math.ceil(
                    $kolomKandang.outerWidth()
                );

                tableContainer.style.setProperty(
                    '--lebar-kandang',
                    lebarKandang + 'px'
                );
            });
        }

        /*
         * =========================================================
         * TOOLTIP BOOTSTRAP
         * =========================================================
         */
        function aktifkanTooltip(parent) {
            var root = parent || document;

            var tooltipElements = [].slice.call(
                root.querySelectorAll('[data-bs-toggle="tooltip"]')
            );

            tooltipElements.forEach(function(element) {
                if (
                    typeof bootstrap !== 'undefined' &&
                    bootstrap.Tooltip
                ) {
                    bootstrap.Tooltip.getOrCreateInstance(element);
                }
            });
        }

        /*
         * =========================================================
         * LOAD TABEL HD TIGA MINGGU
         * =========================================================
         */
        function loadHdTigaMinggu() {
            $('.js-hd-tiga-minggu').each(function() {
                var $container = $(this);

                /*
                 * Mencegah AJAX dipanggil dua kali.
                 */
                if (
                    $container.data('loading') ||
                    $container.data('loaded')
                ) {
                    return;
                }

                $container.data('loading', true);

                $.ajax({
                    type: 'GET',
                    url: $container.data('url'),
                    data: {
                        id_kandang: $container.data('id-kandang'),
                        tgl: $container.data('tgl')
                    },
                    beforeSend: function() {
                        $container.html(
                            '<div class="text-muted p-2">' +
                            '<span class="spinner-border spinner-border-sm me-1"></span>' +
                            'Memuat data tiga minggu...' +
                            '</div>'
                        );
                    },
                    success: function(html) {
                        $container.html(html);
                        $container.data('loaded', true);

                        /*
                         * Tunggu HTML selesai dirender, kemudian ukur
                         * lebar Kandang untuk menentukan posisi Ket.
                         */
                        requestAnimationFrame(function() {
                            sesuaikanFreezeKet();
                            aktifkanTooltip($container[0]);
                        });
                    },
                    error: function(xhr) {
                        var message = 'Gagal memuat data tiga minggu.';

                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.message
                        ) {
                            message = xhr.responseJSON.message;
                        }

                        var $alert = $('<div>', {
                            class: 'alert alert-danger mb-0 p-2',
                            text: message
                        });

                        $container
                            .empty()
                            .append($alert);
                    },
                    complete: function() {
                        $container.data('loading', false);
                    }
                });
            });
        }

        /*
         * =========================================================
         * LOAD DATA RINGKASAN SETIAP KANDANG
         * =========================================================
         */
        function loadRingkasanKandang() {
            $('.baris-kandang2').each(function() {
                var $row = $(this);

                /*
                 * Mencegah pemanggilan berulang.
                 */
                if ($row.data('ringkasan-loaded')) {
                    return;
                }

                $row.data('ringkasan-loaded', true);

                $.ajax({
                    type: 'GET',
                    url: "{{ route('labaRugiKandang_view') }}",
                    data: {
                        id_kandang: $row.data('id'),
                        tgl: $row.attr('tgl')
                    },
                    success: function(response) {
                        $row.find('.txt-rata-pakan')
                            .text(response.rata_pakan ?? 0);

                        $row.find('.txt-fcrk')
                            .text(response.fcrk ?? 0);

                        $row.find('.txt-fcrkplus')
                            .text(response.fcrkplus ?? 0);

                        $row.find('.txt-telur-kg')
                            .text(response.penjualan_telur ?? 0);

                        $row.find('.txt-t_biaya')
                            .text(response.total_biaya ?? 0);

                        $row.find('.txt-b_pakan')
                            .text(response.biaya_pakan ?? 0);

                        $row.find('.txt-b_vitamin')
                            .text(response.biaya_vitamin ?? 0);

                        $row.find('.txt-b_vaksin')
                            .text(response.biaya_vaksin ?? 0);

                        $row.find('.txt-b_pullet')
                            .text(response.biaya_pullet ?? 0);

                        $row.find('.txt-b_rak')
                            .text(response.biaya_rak ?? 0);

                        $row.find('.txt-b_oper')
                            .text(response.biaya_oper ?? 0);

                        $row.find('.txt-laba')
                            .text(response.laba ?? 0);

                        $row.find('.txt-rata')
                            .text(response.rata ?? 0);
                    },
                    error: function() {
                        /*
                         * Supaya bisa dicoba lagi jika diperlukan.
                         */
                        $row.data('ringkasan-loaded', false);
                    }
                });
            });
        }

        /*
         * =========================================================
         * MODAL RUMUS
         * =========================================================
         */
        $(document).on('click', '.rumus', function(event) {
            event.preventDefault();

            var rumus = $(this).attr('rumus');

            $.ajax({
                type: 'GET',
                url: "{{ route('rumus_layer') }}",
                data: {
                    rumus: rumus
                },
                success: function(response) {
                    $('#rumus_layer').html(response);
                    $('#rumus').modal('show');
                },
                error: function() {
                    $('#rumus_layer').html(
                        '<div class="alert alert-danger">' +
                        'Gagal memuat rumus.' +
                        '</div>'
                    );
                }
            });
        });

        /*
         * =========================================================
         * HISTORY PRODUK
         * =========================================================
         */
        function tampilkanHistoryProduk(data) {
            $.ajax({
                type: 'GET',
                url: "{{ route('get_history_produk') }}",
                data: data,
                success: function(response) {
                    $('#history_pemakaian').html(response);

                    var $tableHistory = $('#tableahisory');

                    if (
                        $.fn.DataTable &&
                        $tableHistory.length
                    ) {
                        $tableHistory.DataTable({
                            searching: false,
                            scrollY: '400px',
                            scrollX: false,
                            scrollCollapse: false,
                            stateSave: true,
                            autoWidth: true,
                            paging: false
                        });
                    }
                },
                error: function() {
                    $('#history_pemakaian').html(
                        '<div class="alert alert-danger">' +
                        'Gagal memuat history produk.' +
                        '</div>'
                    );
                }
            });
        }

        $(document).on('click', '.history', function(event) {
            event.preventDefault();

            tampilkanHistoryProduk({
                id_kandang: $(this).attr('id_kandang'),
                id_produk: $(this).attr('id_produk')
            });
        });

        $(document).on(
            'submit',
            '#history_produk',
            function(event) {
                event.preventDefault();

                tampilkanHistoryProduk({
                    id_kandang: $('#id_kandang').val(),
                    id_produk: $('#id_produk').val(),
                    tgl1: $('#tgl1').val(),
                    tgl2: $('#tgl2').val()
                });
            }
        );

        /*
         * =========================================================
         * LABA RUGI
         * =========================================================
         */
        $(document).on('click', '.laba-rugi', function(event) {
            event.preventDefault();

            $.ajax({
                type: 'GET',
                url: "{{ route('labaRugiKandang') }}",
                data: {
                    id_kandang: $(this).attr('id_kandang')
                },
                success: function(response) {
                    $('#laba-rugi_kandang').html(response);
                },
                error: function() {
                    $('#laba-rugi_kandang').html(
                        '<div class="alert alert-danger">' +
                        'Gagal memuat data laba rugi.' +
                        '</div>'
                    );
                }
            });
        });

        /*
         * =========================================================
         * TAB BOOTSTRAP
         * =========================================================
         */
        $(document).on('click', '#myTab a', function(event) {
            event.preventDefault();

            if (
                typeof bootstrap !== 'undefined' &&
                bootstrap.Tab
            ) {
                bootstrap.Tab
                    .getOrCreateInstance(this)
                    .show();
            } else {
                $(this).tab('show');
            }
        });

        /*
         * =========================================================
         * RESIZE DAN ROTASI HP
         * =========================================================
         */
        var resizeTimer = null;

        $(window).on('resize orientationchange', function() {
            clearTimeout(resizeTimer);

            resizeTimer = setTimeout(function() {
                sesuaikanFreezeKet();
            }, 100);
        });

        /*
         * =========================================================
         * JALANKAN SAAT HALAMAN SIAP
         * =========================================================
         */
        sesuaikanFreezeKet();
        aktifkanTooltip(document);
        loadRingkasanKandang();
        loadHdTigaMinggu();
    });
</script>


</html>
