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
            padding: 10px;
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
        .table-container {
            overflow-x: auto;
            max-height: 600px;
        }

        .freeze-cell1_th {
            position: sticky;
            z-index: 30;
            background-color: #F2F7FF;
            top: 0;
            left: 0;
        }

        .freeze-cell1_td {
            position: sticky;
            z-index: 10;
            background-color: #F2F7FF;
            left: 0;
        }

        .freeze-cell_th1 {
            position: sticky;
            z-index: 30;
            background-color: #F2F7FF;
            top: 0;
            left: 62.5px;
            /* Sesuaikan dengan lebar elemen .freeze-cell1 */
        }

        .freeze-cell_th2 {
            position: sticky;
            z-index: 30;
            background-color: #F2F7FF;
            top: 51px;
            left: 62.5px;
            /* Sesuaikan dengan lebar elemen .freeze-cell1 */
        }

        .freeze-cell_td {
            position: sticky;
            z-index: 10;
            background-color: #F2F7FF;
            left: 62.5px;
            /* Sesuaikan dengan lebar elemen .freeze-cell1 */
        }

        .th_atas {
            position: sticky;
            z-index: 10;
            background-color: #F2F7FF;
            top: 0;
        }

        .th_atas2 {
            position: sticky;
            z-index: 10;
            background-color: #F2F7FF;
            top: 51px;
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
    <nav id="navbar-example2" class="navbar bg-body-tertiary px-3 mb-3">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('laporan_layer') ? 'active' : '' }}"
                    href="{{ route('laporan_layer') }}">
                    Laporan Layer
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('laporanlabakandang') ? 'active' : '' }}"
                    href="{{ route('saveLabaRugiKandang') }}">
                    Laporan Laba Rugi Kandang
                </a>
            </li>

        </ul>
    </nav>
    <div class="container-fluid mt-2">

        <form action="">
            <div class="row ">
                <div class="col-6 col-lg-9 elemen-hilang">
                    <h6 class="mb-2">Laporan Layer {{ tanggal($tgl) }}</h6>
                </div>

                <div class="col-12 col-lg-3 float-end d-flex align-items-center">

                    <input type="date" class="form-control" name="tgl" value="{{ $tgl }}">
                    <button type="submit" class="btn btn-primary btn-sm ms-2">Filter</button>
                </div>

            </div>
        </form>

        <div class="table-responsive table-container">

            <table style="text-align: center; " class="table_layer">
                <thead style="border: 1px solid white">
                    <tr>
                        <th class="dhead freeze-cell1_th table_layer " rowspan="2">Kdg <br> chick in <br>
                            Afkir <br>
                            chick in2
                            <br>
                            {{ date('d/m/y') }}
                        </th>
                        <th class="dhead freeze-cell_th1 table_layer ">Umur <br> 85 mgg</th>
                        <th class="dhead table_layer th_atas" width="5%" colspan="2">Populasi</th>
                        <th class="dhead table_layer th_atas" colspan="7">Data Telur</th>
                        <th class="dhead table_layer th_atas">Pakan</th>
                        {{-- <th class="dhead" colspan="2">Berat Badan</th> --}}
                        <th class="dhead table_layer th_atas" colspan="6">KUML</th>
                    </tr>
                    <tr>
                        {{-- Umur --}}
                        <th class="dhead freeze-cell_th2 table_layer">mgg <br>
                            <i class="fas text-white fa-question-circle rumus" rumus="mgg"
                                style="cursor: pointer"></i>
                        </th>
                        {{-- Umur --}}

                        {{-- Populasi --}}
                        <th class="dhead table_layer th_atas2">D <br>C <br>Week<br>
                            <i class="fas text-white fa-question-circle rumus" rumus="d_c"
                                style="cursor: pointer"></i>
                        </th>
                        <th class="dhead table_layer th_atas2">pop <br>awal <br> akhir</th>
                        {{-- Populasi --}}

                        {{-- Data Telur --}}
                        <th class="dhead table_layer th_atas2">kg bersih <br> butir <br> kg kotor

                        </th>
                        <th class="dhead table_layer th_atas2">gr / p <br> (butir) <br>
                            <i class="fas text-white fa-question-circle rumus" rumus="gr_butir"
                                style="cursor: pointer"></i>
                        </th>
                        <th class="dhead table_layer th_atas2">selisih <br> kg <br> butir<br>
                            <i class="fas text-white fa-question-circle rumus" rumus="butir"
                                style="cursor: pointer"></i>
                        </th>
                        {{-- <th class="dhead table_layer th_atas2">
                            ttl <br> selisih <br> (kg/butir)<br> 1 minggu
                        </th> --}}

                        <th class="dhead table_layer th_atas2">
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                title="hd : ttl pcs telur / populasi ayam sekarang">hd</span>
                            <br>
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                title="p : peforma hd note data dari dokter">
                                p
                            </span>
                            <br>
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                title="hh : total kg telur bersih / total ayam awal">
                                hh
                            </span>
                            <br>

                        </th>
                        <th class="dhead table_layer th_atas2">hd/ hd<br>present/past<br>week(%)
                            <i class="fas text-white fa-question-circle rumus" rumus="hd_week"
                                style="cursor: pointer"></i>
                        </th>
                        <th class="dhead table_layer th_atas2">FCR <br> <span data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="D (fcr perday): total pakan hari ini / total kg bersih telur hari ini | D+ (fcr perday+): total pakan hari ini + vaksin + vitamin / total kg bersih telur hari ini">D
                                / D+ </span>
                            <br>
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                title="W (fcr perday): total pakan minggu ini / total kg bersih telur minggu ini | W+ (fcr perday+): total pakan minggu ini + vaksin + vitamin / total kg bersih telur minggu ini">
                                W / W+
                            </span>

                            <br>
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                title="PW (fcr perday): total pakan minggu lalu / total kg bersih telur minggu lalu | PW+ (fcr perday+): total pakan minggu lalu + vaksin + vitamin / total kg bersih telur minggu lalu">
                                PW / PW+
                            </span>
                            <i class="fas text-white fa-question-circle rumus" rumus="fcr_week"
                                style="cursor: pointer"></i>
                        </th>
                        <th class="dhead table_layer th_atas2">Telur(%)</th>
                        {{-- Data Telur --}}

                        {{-- pakan --}}
                        <th class="dhead table_layer th_atas2">kg <br> (gr/ekor) / p <br>(day)</th>
                        {{-- KUML --}}
                        <th class="dhead table_layer th_atas2">pakan(kg) <br> telur(kg) <br> <span
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Summary dari = (rata rata penjualan telur perhari * produksi telur perhari) / ttl kg bersih">R2Rp</span>
                        </th>
                        <th class="dhead table_layer th_atas2">Obat/vit</th>
                        <th class="dhead table_layer th_atas2">Pakan</th>

                        {{-- <th class="dhead table_layer">telur(kg)</th> --}}
                        <th class="dhead table_layer th_atas2">fcr <br> k&k+ <br>
                            ({{ number_format($harga->ttl_rupiah / $harga->pcs, 0) }}) </th>
                        {{-- <th class="dhead table_layer th_atas2"> testing </th> --}}
                        <th class="dhead table_layer th_atas2"> ttl rp pakan <br>obat/vit <br> vaksin <br> Ayam <br>
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                title="gjl : 185 * pop awal * total hari ayam makan">
                                GjL
                            </span>


                        </th>
                        <th class="dhead table_layer th_atas2">
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                title="tpl : (rp telur - (fcr kuml * rp pakan)) * ttl kg telur">
                                tpl
                            </span>
                            <br>
                            <br>
                            <br>
                        </th>
                        {{-- KUML --}}
                    </tr>
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
                            <td align="center" class="kandang freeze-cell1_td td_layer">{{ $k->nm_kandang }}
                                <br>
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="tanggal chick in">
                                    {{ date('d/m/y', strtotime($k->chick_in)) }}
                                </span>
                                <br>

                                @php
                                    $chick_in_next = date('Y-m-d', strtotime($k->chick_out . ' +1 month'));
                                    $merah = date('Y-m-d', strtotime($chick_in_next . ' -15 weeks'));
                                    $tgl_hari_ini = date('Y-m-d');
                                    $afkir = date('Y-m-d', strtotime($k->chick_in . ' +99 weeks'));
                                    $afkir2 = date('Y-m-d', strtotime($afkir . ' -4 weeks'));

                                    $ckin2 = date('Y-m-d', strtotime($k->chick_in . ' +80 weeks'));
                                    $ckin21 = date('Y-m-d', strtotime($ckin2 . ' -4 weeks'));
                                @endphp

                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="tanggal afkir hitung 99 minggu dari chick in"
                                    class="{{ $tgl_hari_ini >= $afkir2 ? 'text-danger fw-bold' : '' }}">
                                    {{ date('d/m/y', strtotime($afkir)) }} </span> <br>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="tanggal chick in 2 hitung 80 minggu dari chick in"
                                    class="{{ $tgl_hari_ini >= $ckin21 ? 'text-danger fw-bold' : '' }}">
                                    {{ date('d/m/y', strtotime($ckin2)) }}
                                </span>
                                {{-- <br>
                                {{ date('d/m/y', strtotime($k->tgl_masuk_kandang)) }} --}}


                            </td>
                            <!-- Umur -->
                            <td align="center"
                                class="freeze-cell_td td_layer mgg {{ $k->mgg >= '85' ? 'text-danger fw-bold' : '' }}">
                                <br>
                                {{ $k->mgg }} / <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="gjl : total hari ayam makan / 7">{{ number_format($k->ttl_gjl / 7) }}</span>
                                <br>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="perkiran afkir 99 minggu sama semua kandang">
                                    99
                                </span>

                                <br>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="hitungan persen minggu menuju 99 minggu">

                                    ({{ number_format(($k->mgg / 99) * 100, 0) }}%) <br>
                                </span>

                            </td>
                            {{-- <td align="center" class="hari">{{$k->hari}}</td>
                            <td align="center" class="afkir 80 minggu">{{number_format(($k->mgg / 80) * 100,0)}}%</td>
                            --}}
                            <!-- umur -->

                            <!-- populasi -->
                            @php
                                $tot_ayam_mati = empty($k->mati) ? '0' : $k->mati;
                                $tot_ayam_jual = empty($k->jual) ? '0' : $k->jual;
                                $tot_ayam_semua_hilang = $tot_ayam_mati;
                            @endphp
                            <td align="center"
                                class="D/C td_layer {{ $tot_ayam_semua_hilang > 3 ? 'text-danger fw-bold' : '' }}">
                                &nbsp; <br>
                                {{ empty($k->mati) ? '0' : $k->mati }} <br> {{ empty($k->jual) ? '0' : $k->jual }}
                                <br>
                                {{ $k->mati_week + $k->jual_week }}
                            </td>
                            <td align="center" class="pop awal td_layer">
                                &nbsp; <br>{{ $k->stok_awal }} <br> {{ $k->stok_awal - $k->pop_kurang }} <br>
                                {{ number_format((($k->stok_awal - $k->pop_kurang) / $k->stok_awal) * 100, 1) }}%
                            </td>

                            <!-- populasi -->

                            <!-- data telur -->
                            <!-- mencari ikat  1 ikat = 1kg  -->
                            <td align="center" class="kg telur td_layer">
                                &nbsp; <br>
                                {{ number_format($k->kg - $k->pcs / 180, 1) }}<br>
                                {{ number_format($k->pcs, 0) }} <br>
                                {{ number_format($k->kg, 1) }}
                            </td>
                            @php
                                $gr_butir = empty($k->pcs)
                                    ? '0'
                                    : number_format((($k->kg - $k->pcs / 180) * 1000) / $k->pcs, 1);
                            @endphp
                            <td align="center" class="td_layer ">
                                <p style="margin: 0; padding: 0;">&nbsp;</p>
                                <p style="margin: 0; padding: 0;">&nbsp;</p>
                                <p style="margin: 0; padding: 0;"
                                    class="{{ $gr_butir < 58 ? 'text-danger fw-bold' : '' }}">
                                    {{ $gr_butir }}</p>
                                <p style="margin: 0; padding: 0;">{{ empty($k->t_peforma) ? 'NA' : $k->t_peforma }}
                                </p>
                            </td>
                            <td align="center" class="butir td_layer">
                                @php
                                    $kg = $k->kg - $k->pcs / 180 - ($k->kg_past - $k->pcs_past / 180);
                                @endphp
                                <p style="margin: 0; padding: 0;">&nbsp;</p>
                                <p style="margin: 0; padding: 0;" class="{{ $kg < 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ number_format($kg, 1) }}
                                </p>
                                <p style="margin: 0; padding: 0;"
                                    class="{{ $k->pcs - $k->pcs_past < 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ number_format($k->pcs - $k->pcs_past, 0) }}</p>
                                <p style="margin: 0; padding: 0;">&nbsp;</p>
                            </td>

                            {{-- <td align="center" class="butir td_layer">
                                &nbsp; <br>

                                {{ number_format(
                                    $k->kg_satu_minggu - $k->pcs_satu_minggu / 180 - ($k->kg_minggu_sebelumnya - $k->pcs_minggu_sebelumnya / 180),
                                    1,
                                ) }}
                                <br>
                                {{ number_format($k->pcs_satu_minggu - $k->pcs_minggu_sebelumnya, 0) }}
                                <br> &nbsp;
                            </td> --}}
                            <td align="center" class="hd perday (%) td_layer">
                                {{-- {{$k->pcs}} --}}
                                &nbsp; <br>
                                @php
                                    $hd =
                                        $k->stok_awal - $k->pop_kurang == 0
                                            ? 0
                                            : ($k->pcs / ($k->stok_awal - $k->pop_kurang)) * 100;
                                @endphp
                                <span
                                    class="{{ empty($k->p_hd) ? '' : ($k->p_hd - $hd > 3 ? 'text-danger fw-bold' : '') }}">
                                    {{ number_format($hd, 0) }}
                                </span>
                                <br>
                                {{ empty($k->p_hd) ? 'NA' : $k->p_hd }}
                                <br>
                                {{ number_format((($k->kuml_kg - $k->kuml_pcs / 180) / $k->stok_awal) * 100, 1) }}

                            </td>


                            <td align="center" class="hd week td_layer">
                                {{-- ({{$k->pcs_telur_week}} {{$k->jlh_hari}}) --}}
                                &nbsp; <br>
                                {{ empty($k->pcs_telur_week) ||
                                empty($k->jlh_hari) ||
                                empty($k->pop_kurang) ||
                                $k->stok_awal - $k->pop_kurang == 0
                                    ? '0'
                                    : number_format(($k->pcs_telur_week / $k->jlh_hari / ($k->stok_awal - $k->pop_kurang)) * 100, 0) }}
                                <br>
                                {{ empty($k->pcs_telur_week_past) ||
                                empty($k->jlh_hari_past) ||
                                empty($k->pop_kurang_past) ||
                                $k->stok_awal - $k->pop_kurang_past == 0
                                    ? '0'
                                    : number_format(($k->pcs_telur_week_past / $k->jlh_hari_past / ($k->stok_awal - $k->pop_kurang_past)) * 100, 0) }}
                                <br> &nbsp;

                            </td>

                            <!-- (12777) / (3) / (2296) -->
                            @php
                                $fcr =
                                    empty($k->kg_p_week) || empty($k->kg_telur_week) || empty($k->pcs_telur_week)
                                        ? '0'
                                        : $k->kg_p_week / 1000 / ($k->kg_telur_week - $k->pcs_telur_week / 180);

                                $vitamin = empty($k->rp_vitamin) ? '0' : $k->rp_vitamin / 7000;
                                $vaksin = empty($k->ttl_rp_vaksin) ? '0' : $k->ttl_rp_vaksin / 7000;

                                $vitamin_week = empty($k->rp_vitamin_week) ? '0' : $k->rp_vitamin_week / 7000;
                                $gjl_week = (185 * $k->stok_awal * $k->jlh_hari) / 7000;

                                $fcr_plus_week =
                                    empty($k->kg_p_week) || empty($k->kg_telur_week) || empty($k->pcs_telur_week)
                                        ? '0'
                                        : ($k->kg_p_week / 1000 + $vitamin_week + $gjl_week) /
                                            ($k->kg_telur_week - $k->pcs_telur_week / 180);

                                $fcr_plus =
                                    empty($k->kg_pakan) || empty($k->kg)
                                        ? '0'
                                        : number_format(
                                            ($k->kg_pakan / 1000 + $vitamin + $vaksin) / ($k->kg - $k->pcs / 180),
                                            2,
                                        );

                                $fcr_day =
                                    empty($k->kg_pakan) || empty($k->pcs)
                                        ? '0'
                                        : number_format($k->kg_pakan / 1000 / ($k->kg - $k->pcs / 180), 2);

                                $fcr_past_week = empty($k->kg_telur_past_week)
                                    ? '0'
                                    : $k->kg_p_past_week /
                                        1000 /
                                        ($k->kg_telur_past_week - $k->pcs_telur_past_week / 180);

                                $vaksin_past_week = $k->kum_ttl_rp_past_vaksin / 7000;
                                $vitamin_past_week = $k->rp_vitamin_past_week / 7000;
                                $gjl_pastweek = (185 * $k->stok_awal * 7) / 7000;

                                $fcr_past_week_plus = empty($k->kg_telur_past_week)
                                    ? '0'
                                    : ($k->kg_p_past_week / 1000 +
                                            $vaksin_past_week +
                                            $vitamin_past_week +
                                            $gjl_pastweek) /
                                        ($k->kg_telur_past_week - $k->pcs_telur_past_week / 180);
                            @endphp

                            @php
                                $pkn = DB::select("SELECT a.id_pakan, b.nm_produk, c.nm_satuan, a.id_kandang, (COALESCE(d.ttl_rp,0) + COALESCE(d.rp_lain,0) ) as ttl_rp, (d.ttl_gr) as ttl_gr
                                FROM stok_produk_perencanaan as a
                                left JOIN tb_produk_perencanaan as b on b.id_produk = a.id_pakan
                                left join tb_satuan as c on c.id_satuan = b.dosis_satuan
                                left JOIN (
                                    SELECT a.*
                                FROM harga_pakan as a 
                                where a.id_pakan = a.id_pakan and a.tgl = (SELECT max(b.tgl) as tgl FROM harga_pakan as b where b.id_pakan = a.id_pakan)
                                ) as d on d.id_pakan = a.id_pakan
                                WHERE a.tgl = '$tgl' and a.id_kandang = '$k->id_kandang' and b.kategori in('pakan')
                                 GROUP by a.id_pakan;
                                ");

                                $tl_rp_pakan = sumbK($pkn, 'ttl_rp');
                                $tl_gr_pkn = sumBk($pkn, 'ttl_gr');
                            @endphp
                            <td align="center" class="FCR(week)  td_layer">
                                <br>
                                @if ($k->mgg < 21)
                                    0 / 0 <br> 0 / 0
                                @else
                                    <span
                                        class="{{ $fcr_day >= 2.2 ? 'text-danger fw-bold' : '' }}">{{ $fcr_day }}</span>
                                    / <span class="{{ $fcr_plus >= 2.2 ? 'text-danger fw-bold' : '' }}">
                                        {{ $fcr_plus }}</span>
                                    <br>
                                    <span
                                        class="{{ $fcr >= 2.2 ? 'text-danger fw-bold' : '' }}">{{ number_format($fcr, 2) }}
                                    </span>
                                    /
                                    <span
                                        class="{{ $fcr_plus_week >= 2.2 ? 'text-danger fw-bold' : '' }}">{{ number_format($fcr_plus_week, 2) }}</span>
                                @endif
                                <br>
                                {{ $k->jlh_hari }} / 7 <br>
                                {{ number_format($fcr_past_week, 2) }} / {{ number_format($fcr_past_week_plus, 2) }}
                                <br>
                                @php
                                    $hrga_stn_pkn = empty($tl_rp_pakan) ? 0 : $tl_rp_pakan / $tl_gr_pkn;
                                @endphp
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="harga satuan pakan * fcr past week">
                                    {{ number_format(round($hrga_stn_pkn, 0) * round($fcr_past_week, 2), 0) }}
                                </span> /
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="harga satuan pakan * fcr past week plus">
                                    {{ number_format(round($hrga_stn_pkn, 0) * round($fcr_past_week_plus, 2), 0) }}
                                </span>
                            </td>
                            <td class="td_layer">
                                @php
                                    $telur = DB::select("SELECT a.id_telur, b.nm_telur, sum(a.pcs) as pcs, sum(a.kg) FROM stok_telur as a
                                    left join telur_produk as b on b.id_produk_telur = a.id_telur
                                    where a.tgl ='$tgl' and a.id_kandang = '$k->id_kandang'
                                    group by a.id_telur , a.id_kandang
                                    ");
                                @endphp
                                <table>
                                    <tr>
                                        <th>grd</th>
                                        <th>:</th>
                                        <th>d1</th>
                                        <th>&nbsp;</th>
                                        <th>d7</th>
                                        <th>&nbsp;</th>
                                        <th>d7+</th>
                                        <th>&nbsp;</th>
                                        <th>dkk</th>
                                        <th>&nbsp;</th>
                                        <th>rpp</th>
                                    </tr>
                                    @foreach ($telur as $t)
                                        @php
                                            $d7 = DB::selectOne("SELECT a.id_kandang, sum(a.pcs) as pcs7, sum(a.kg) as kg7, CEIL(DATEDIFF(a.tgl, b.chick_in) / 7) AS mgg_hd_week_past
                                            FROM stok_telur as a 
                                            left JOIN kandang as b on b.id_kandang = a.id_kandang
                                            where a.id_kandang = '$k->id_kandang' and a.id_telur = '$t->id_telur' and 
                                            CEIL(DATEDIFF(a.tgl, b.chick_in) / 7) = ($k->mgg - 1)
                                            group by CEIL(DATEDIFF(a.tgl, b.chick_in) / 7), a.id_kandang, a.id_telur;");

                                            $d7_plus = DB::selectOne("SELECT a.id_kandang, sum(a.pcs) as pcs7, sum(a.kg) as kg7, CEIL(DATEDIFF(a.tgl, b.chick_in) / 7) AS mgg_hd_week_past
                                            FROM stok_telur as a 
                                            left JOIN kandang as b on b.id_kandang = a.id_kandang
                                            where a.id_kandang = '$k->id_kandang' and a.id_telur = '$t->id_telur' and 
                                            CEIL(DATEDIFF(a.tgl, b.chick_in) / 7) = ($k->mgg - 2)
                                            group by CEIL(DATEDIFF(a.tgl, b.chick_in) / 7), a.id_kandang, a.id_telur;");

                                            $dkk = DB::selectOne("SELECT sum(a.pcs) as pcs_kk, sum(a.kg) as kg_kk
                                            FROM stok_telur as a
                                            where  a.id_kandang = '$k->id_kandang' and a.id_telur = '$t->id_telur'
                                            and a.tgl between '2020-01-01' and '$tgl'
                                            group by a.id_kandang, a.id_telur
                                            ");

                                            $harga_telur = DB::table('harga_telur')
                                                ->where('produk_telur_id', $t->id_telur)
                                                ->orderBy('invoice', 'desc')
                                                ->first();

                                        @endphp
                                        <tr>
                                            <td>{{ $t->nm_telur ?? '-' }}</td>
                                            <td>:</td>
                                            <td>
                                                {{ ($k->pcs ?? 0) != 0 ? number_format((($t->pcs ?? 0) / $k->pcs) * 100, 0) . '%' : '0%' }}
                                            </td>
                                            <th>&nbsp;</th>
                                            <td>
                                                {{ ($k->pcs_telur_week_past ?? 0) != 0 ? number_format((($d7->pcs7 ?? 0) / $k->pcs_telur_week_past) * 100, 0) . '%' : '0%' }}
                                            </td>
                                            <th>&nbsp;</th>
                                            <td>
                                                {{ ($k->pcs_telur_week_past_plus ?? 0) != 0 ? number_format((($d7_plus->pcs7 ?? 0) / $k->pcs_telur_week_past_plus) * 100, 0) . '%' : '0%' }}
                                            </td>
                                            <td>&nbsp;</td>
                                            <td>
                                                {{ ($k->kuml_pcs ?? 0) != 0 ? number_format((($dkk->pcs_kk ?? 0) / $k->kuml_pcs) * 100, 0) . '%' : '0%' }}
                                            </td>
                                            <td>&nbsp;</td>
                                            <td>
                                                {{ $harga_telur?->harga ? number_format($harga_telur->harga, 0) : '0' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>



                            <!-- data telur -->


                            <!-- pakan -->
                            <td style="text-align: center" class="kg w_pakan td_layer">
                                &nbsp; <br>
                                {{ number_format($k->kg_pakan / 1000, 1) }} <br>
                                {{ $k->stok_awal - $k->pop_kurang == 0 ? 0 : number_format($k->kg_pakan / ($k->stok_awal - $k->pop_kurang), 0) }}
                                <br> {{ empty($k->feed) ? 'NA' : $k->feed }}
                            </td>
                            <!-- pakan -->



                            <!-- kuml -->
                            <td align="center" class="pakan(kg) td_layer">
                                &nbsp; <br>
                                {{ number_format(empty($k->kg_pakan_kuml) ? '0' : $k->kg_pakan_kuml / 1000, 1) }}
                                <br>
                                {{ number_format($k->kuml_kg - $k->kuml_pcs / 180, 1) }}

                                <br>
                                {{-- {{ empty($k->kg_bagi_y) ? '0' : number_format($k->rp_satuan_y / $k->kg_bagi_y, 0) }} --}}
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
                            <td class="td_layer">
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
                                        Kg</a> <br>
                                @endforeach
                            </td>

                            {{-- vitamin --}}
                            <td align="center" class="fcr k / fcr k+ (7,458) td_layer">
                                @php
                                    $kg_pakan_kuml = $k->kg_pakan_kuml / 1000;
                                    $kg_pakan_rp_vit = $k->kuml_rp_vitamin / 7000;
                                    $kg_pakan_rp_vak = $k->kum_ttl_rp_vaksin / 7000;
                                    $ayam = $k->rupiah / 7000;
                                    // $gjl = ($k->ttl_gjl * 700000) / 7000;
                                    $gjl = (185 * $k->stok_awal * $k->ttl_gjl) / 7000;
                                @endphp

                                &nbsp; <br>
                                {{ empty($k->kg_pakan_kuml) || empty($k->kuml_pcs)
                                    ? '0'
                                    : number_format($kg_pakan_kuml / ($k->kuml_kg - $k->kuml_pcs / 180), 1) }}
                                <br>


                                {{ empty($k->kg_pakan_kuml) || empty($k->kuml_pcs)
                                    ? '0'
                                    : number_format(
                                        ($kg_pakan_kuml + $kg_pakan_rp_vit + $kg_pakan_rp_vak + $ayam + $gjl) / ($k->kuml_kg - $k->kuml_pcs / 180),
                                        1,
                                    ) }}
                                <br> &nbsp;
                                {{-- {{ number_format($kg_pakan_kuml, 0) }} /
                                {{ number_format($k->kuml_kg - $k->kuml_pcs / 180) }} --}}
                            </td>


                            <!--(144,502.2 , 60,920.9 , 864,183.0)-->
                            <td align="center" class="obat/vit td_layer">

                                &nbsp; <br>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="ttl rp pakan = ttl kg : {{ empty($k->kg_pakan_kuml) ? '0' : $k->kg_pakan_kuml / 1000 }} || rata2 pakan hari ini = {{ number_format($hrga_stn_pkn, 0) }} ">
                                    {{ number_format(empty($k->kg_pakan_kuml) ? '0' : ($k->kg_pakan_kuml / 1000) * $hrga_stn_pkn, 1) }}
                                </span>

                                <br>
                                {{-- {{ number_format($k->ttl_rupiah_hrga, 0) }} <br> --}}
                                {{ number_format($k->kuml_rp_vitamin, 0) }} <br>
                                {{ number_format($k->kum_ttl_rp_vaksin, 0) }} <br>
                                {{ number_format($k->rupiah, 0) }} <br>

                                {{-- {{ number_format($k->ttl_gjl * 700000) }} --}}
                                {{ number_format(185 * $k->stok_awal * $k->ttl_gjl, 0) }}

                                @php
                                    $gjl_new = 185 * $k->stok_awal * $k->ttl_gjl;
                                    $gjl_new_kemarin = 185 * $k->stok_awal * ($k->ttl_gjl - 1);
                                @endphp

                            </td>
                            <td class="obat/vit td_layer" align="center">

                                @php
                                    $fcr_kuml_plus =
                                        empty($k->kuml_kg) || $k->kuml_kg - $k->kuml_pcs / 180 == 0
                                            ? 0
                                            : round(
                                                ($kg_pakan_kuml + $kg_pakan_rp_vit + $kg_pakan_rp_vak + $ayam + $gjl) /
                                                    ($k->kuml_kg - $k->kuml_pcs / 180),
                                                1,
                                            );

                                    $ttl_kg_telur = empty($k->kuml_kg) ? 0 : $k->kuml_kg - $k->kuml_pcs / 180;

                                    $rp_telur = $k->rata ?? 0;

                                    $rp_pakan = ($k->pcs_hrga ?? 0) == 0 ? 0 : $k->ttl_rupiah_hrga / $k->pcs_hrga;
                                    $rp_pakan_tes =
                                        ($k->pcs_hrga ?? 0) == 0 ? 0 : round($k->ttl_rupiah_hrga / $k->pcs_hrga, 0);

                                    $ttl_tpl +=
                                        (round($rp_telur, 0) - $rp_pakan_tes * $fcr_kuml_plus) *
                                        round($ttl_kg_telur, 0);

                                    $ttl_tpl_kandang =
                                        (round($rp_telur, 0) - $rp_pakan_tes * $fcr_kuml_plus) *
                                        round($ttl_kg_telur, 0);

                                    $nomer1 = ($k->kuml_kg ?? 0) - ($k->kuml_pcs ?? 0) / 180;
                                    $nomer1 = round($nomer1, 1);

                                    $nomer2 = $k->rata ?? 0;

                                    $ttl_rp_pakan_baru = empty($k->kg_pakan_kuml)
                                        ? 0
                                        : round(($k->kg_pakan_kuml / 1000) * $hrga_stn_pkn, 1);

                                    $nomer3 = ($k->kuml_kg_kemarin ?? 0) - ($k->kuml_pcs_kemarin ?? 0) / 180;
                                    $nomer3 = round($nomer3, 1);

                                    $nomer4 = round($k->rata_kemarin ?? 0, 0);

                                    $A_kemarin = $nomer3 * $nomer4;

                                    $ttl_rp_pakan_kemarin = empty($k->kg_pakan_kuml_kemarin)
                                        ? 0
                                        : round(($k->kg_pakan_kuml_kemarin / 1000) * $hrga_stn_pkn, 1);

                                    $B_kemarin =
                                        $ttl_rp_pakan_kemarin +
                                        ($k->kuml_rp_vitamin_kemarin ?? 0) +
                                        ($k->kum_ttl_rp_vaksin_kemarin ?? 0) +
                                        ($k->rupiah ?? 0) +
                                        ($gjl_new_kemarin ?? 0);

                                    $tpl_hari_ini =
                                        $nomer1 * $nomer2 -
                                        ($ttl_rp_pakan_baru +
                                            ($k->kuml_rp_vitamin ?? 0) +
                                            ($k->kum_ttl_rp_vaksin ?? 0) +
                                            ($k->rupiah ?? 0) +
                                            ($gjl_new ?? 0));

                                    $tpl_kemarin = $A_kemarin - $B_kemarin;
                                @endphp



                                <br>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="rumus 1x2 | nomer1 = {{ $nomer1 }} | nomer2 = {{ $nomer2 }}">
                                    {{ number_format($nomer1 * $nomer2, 0) }}
                                </span>
                                <br>
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="total rp pkan vit dll">
                                    {{ number_format($ttl_rp_pakan_baru + $k->kuml_rp_vitamin + $k->kum_ttl_rp_vaksin + $k->rupiah + $gjl_new, 0) }}
                                </span>
                                <br>
                                <span
                                    class="{{ $nomer1 * $nomer2 - ($ttl_rp_pakan_baru + $k->kuml_rp_vitamin + $k->kum_ttl_rp_vaksin + $k->rupiah + $gjl_new) < 0 ? 'text-danger' : '' }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="kurangi a-b">
                                    {{ number_format($nomer1 * $nomer2 - ($ttl_rp_pakan_baru + $k->kuml_rp_vitamin + $k->kum_ttl_rp_vaksin + $k->rupiah + $gjl_new), 0) }}
                                </span>
                                <br>

                                @if ($k->kuml_kg - $k->kuml_pcs / 180 != 0)
                                    <span
                                        class="{{ $nomer1 * $nomer2 - ($ttl_rp_pakan_baru + $k->kuml_rp_vitamin + $k->kum_ttl_rp_vaksin + $k->rupiah + $gjl_new) < 0 ? 'text-danger' : '' }}"
                                        data-bs-toggle="tooltip" title="hasil c dibagi total telur kandang">
                                        {{ number_format(($nomer1 * $nomer2 - ($ttl_rp_pakan_baru + $k->kuml_rp_vitamin + $k->kum_ttl_rp_vaksin + $k->rupiah + $gjl_new)) / ($k->kuml_kg - $k->kuml_pcs / 180), 0) }}

                                    </span>
                                @else
                                    <span data-bs-toggle="tooltip" title="hasil c dibagi total telur kandang">
                                        0
                                    </span>
                                @endif
                                {{-- <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    class="{{ $ttl_tpl_kandang < 0 ? 'text-danger' : '' }}"
                                    title="fcr kuml = {{ $fcr_kuml_plus }} | rp pakan = {{ $rp_pakan_tes }} | rp telur = {{ round($rp_telur, 0) }} | ttl kg telur {{ round($ttl_kg_telur, 0) }}">
                                    {{ number_format((round($rp_telur, 0) - $rp_pakan_tes * $fcr_kuml_plus) * round($ttl_kg_telur, 0), 2) }}
                                </span>

                                <br>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    class="{{ $ttl_tpl_kandang / $ttl_kg_telur < 0 ? 'text-danger' : '' }}"
                                    title="tpl = {{ $ttl_tpl_kandang }} |  ttl kg telur {{ round($ttl_kg_telur, 0) }}">
                                    {{ number_format($ttl_tpl_kandang / $ttl_kg_telur, 2) }}
                                </span> --}}
                                <br>

                                <span
                                    class="{{ $tpl_hari_ini - $tpl_kemarin < 0 ? 'text-danger' : '' }}">{{ number_format($tpl_hari_ini - $tpl_kemarin, 0) }}</span>
                                <br>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="dhead freeze-cell1_td table_layer" colspan="2">Total</th>
                        <th class="dhead text-end table_layer">{{ $mati }} <br> {{ $jual }} <br>
                            {{ $dc_week }}</th>
                        <th class="dhead table_layer">
                            {{ number_format($ayam_awal, 0) }} <br>
                            {{ number_format($ayam_akhir, 0) }} <br>
                            {{ $ayam_awal != 0 ? number_format(($ayam_akhir / $ayam_awal) * 100, 0) . ' %' : '0 %' }}
                        </th>

                        <th class="dhead text-end table_layer">
                            {{ number_format($kg_total, 2) }}<br>
                            {{ number_format($pcs, 0) }}<br>
                            {{ number_format($kg_kotor, 2) }}
                        </th>
                        <th class="dhead table_layer">{{ $gr_butir / 4 }}</th>
                        <th class="dhead text-end table_layer">
                            {{ number_format($kg_today, 1) }}<br>
                            {{ number_format($butir, 0) }}
                        </th>

                        <th class="dhead table_layer">
                            {{ $ayam_akhir != 0 ? number_format(($pcs / $ayam_akhir) * 100, 0) : '0' }} <br><br>
                            {{ $ayam_awal != 0 ? number_format(($pcs / $ayam_awal) * 100, 0) : '0' }}
                        </th>
                        <th class="dhead table_layer"></th>
                        <th class="dhead table_layer">
                            @php
                                $fcr_day_total = $kg_total != 0 ? number_format($pakan / $kg_total, 1) : '0';
                                $fcr_day_total_plus =
                                    $kg_total != 0
                                        ? number_format(($pakan + $rp_vitamin + $rp_vaksin) / $kg_total, 1)
                                        : '0';
                            @endphp
                            {{ $fcr_day_total }} / {{ $fcr_day_total_plus }}
                        </th>
                        <th class="dhead table_layer"></th>
                        <th class="dhead table_layer">{{ number_format($pakan, 2) }} </th>

                        <th class="dhead table_layer">{{ number_format($pakan_kuml, 2) }} <br>
                            {{ number_format($telur_kuml, 2) }} </th>
                        <th class="dhead table_layer"></th>
                        <th class="dhead table_layer">{{ number_format($pakan, 1) }}</th>

                        <th class="dhead table_layer">
                            {{ $telur_kuml != 0 ? number_format($pakan_kuml / $telur_kuml, 1) : '0' }}<br>
                            @php
                                $plus = ($obat_kuml + $vaksin_kuml + $rp_ayam) / 7000;
                            @endphp
                            {{ $telur_kuml != 0 ? number_format(($pakan_kuml + $plus) / $telur_kuml, 1) : '0' }}
                        </th>

                        <th class="dhead table_layer">
                            {{ number_format($obat_kuml, 0) }} <br>
                            {{ number_format($vaksin_kuml, 0) }} <br> {{ number_format($rp_ayam, 0) }}
                            <br>{{ number_format($gjl_ttl, 0) }}
                        </th>
                        <th class="dhead table_layer">{{ number_format($ttl_tpl, 2) }}</th>
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


</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).on('click', '.rumus', function() {
        var rumus = $(this).attr('rumus');
        $.ajax({
            type: "get",
            url: "/rumus_layer?rumus=" + rumus,
            success: function(r) {
                // alert(r)
                $("#rumus_layer").html(r)
                $("#rumus").modal('show');

            }
        });
    });
    $(document).on('click', '.history', function() {
        var id_kandang = $(this).attr('id_kandang');
        var id_produk = $(this).attr('id_produk');

        $.ajax({
            type: "get",
            url: "{{ route('get_history_produk') }}",
            data: {
                id_kandang: id_kandang,
                id_produk: id_produk,
            },
            success: function(r) {
                $('#history_pemakaian').html(r)
                $('#tableahisory').DataTable({
                    "searching": false,
                    scrollY: '400px',
                    scrollX: false,
                    scrollCollapse: false,
                    "stateSave": true,
                    "autoWidth": true,
                    "paging": false,
                });
            }
        });
    });
    $(document).on('submit', '#history_produk', function(e) {
        e.preventDefault();
        var id_kandang = $('#id_kandang').val();
        var id_produk = $('#id_produk').val();
        var tgl1 = $("#tgl1").val();
        var tgl2 = $("#tgl2").val();
        $.ajax({
            type: "get",
            url: "{{ route('get_history_produk') }}",
            data: {
                id_kandang: id_kandang,
                id_produk: id_produk,
                tgl1: tgl1,
                tgl2: tgl2,
            },
            success: function(r) {
                $('#history_pemakaian').html(r)
                $('#tableahisory').DataTable({
                    "searching": false,
                    scrollY: '400px',
                    scrollX: false,
                    scrollCollapse: false,
                    "stateSave": true,
                    "autoWidth": true,
                    "paging": false,
                });
            }
        });
    });
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

</html>
