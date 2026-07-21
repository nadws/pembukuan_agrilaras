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

        .laba-rugi.detail-kandang-link {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 3px;
            padding: 5px 7px;
            border: 1px solid #cfd8f6;
            border-radius: 8px;
            background: #f4f7ff;
            color: #2949ad;
            text-decoration: none;
            transition: background-color 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
        }

        .laba-rugi.detail-kandang-link:hover,
        .laba-rugi.detail-kandang-link:focus-visible {
            border-color: #435ebe;
            background: #e9eeff;
            color: #203d99;
        }

        .laba-rugi.detail-kandang-link:active {
            transform: scale(0.98);
        }

        .detail-kandang-link .kandang-nama {
            font-weight: 800;
        }

        .detail-kandang-link .detail-hint {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #5369b7;
            font-size: 9px;
            font-weight: 700;
            line-height: 1;
        }

        .detail-kandang-link .detail-hint::after {
            content: '\203A';
            font-size: 14px;
            line-height: 0.7;
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

        /* Tampilan tablet dan HP */
        @media screen and (max-width: 991.98px) {
            html,
            body {
                width: 100%;
                height: 100%;
                overflow: hidden;
                background: #f4f7fc;
            }

            .elemen-hilang {
                display: none !important;
            }

            .navbar-laporan {
                display: flex !important;
                height: 52px;
                padding: 6px 12px;
                border: 0 !important;
                border-bottom: 1px solid #dfe5f2 !important;
                box-shadow: 0 2px 10px rgba(42, 61, 110, 0.08);
            }

            .navbar-laporan .container {
                max-width: none;
                padding: 0;
            }

            .navbar-laporan .navbar-brand {
                display: flex;
                align-items: center;
                gap: 9px;
                margin: 0;
                padding: 0;
                color: #243b7a;
                font-size: 14px;
                font-weight: 700;
            }

            .navbar-laporan .navbar-brand img {
                width: 36px;
                height: 36px;
            }

            .laporan-layer-page {
                display: flex;
                flex-direction: column;
                width: 100%;
                height: calc(100dvh - 52px);
                min-height: 0;
                margin-top: 0 !important;
                padding: 8px;
                padding-bottom: max(8px, env(safe-area-inset-bottom));
                overflow: hidden;
            }

            .filter-laporan {
                display: block !important;
                flex: 0 0 auto;
                margin-bottom: 8px;
                padding: 8px;
                border: 1px solid #e1e7f2;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 3px 12px rgba(42, 61, 110, 0.07);
            }

            .filter-laporan>.row {
                gap: 8px;
                margin: 0;
            }

            .filter-laporan .col-12,
            .filter-laporan .col-lg-4 {
                width: 100%;
                padding: 0;
                margin: 0 !important;
            }

            .filter-laporan input[type="date"] {
                min-height: 42px;
                border-color: #d9e0ee;
                border-radius: 9px;
                font-size: 16px;
            }

            .filter-laporan button[type="submit"] {
                min-width: 72px;
                min-height: 42px;
                border-radius: 9px;
                font-weight: 700;
            }

            .filter-laporan .nav {
                flex-wrap: nowrap;
                gap: 4px;
                padding: 3px;
                border-radius: 9px;
                background: #f0f3fa;
            }

            .filter-laporan .nav-link {
                min-height: 38px;
                padding: 9px 8px;
                border-radius: 7px;
                color: #52617e;
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
            }

            .filter-laporan .nav-link.active {
                background: #435ebe;
                box-shadow: 0 2px 7px rgba(67, 94, 190, 0.25);
            }

            .laporan-layer-page>.table-container {
                flex: 1 1 auto;
                width: 100%;
                min-height: 0;
                max-height: none !important;
                overflow: auto !important;
                border: 1px solid #dfe6f2;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 5px 18px rgba(42, 61, 110, 0.09);
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }

            .laporan-layer-page>.table-container>.table_layer {
                min-width: 660px;
                border: 0;
                border-collapse: separate;
                border-spacing: 0;
            }

            .laporan-layer-page>.table-container>.table_layer>thead th {
                padding: 11px 9px;
                font-size: 11px;
                letter-spacing: 0.15px;
            }

            .laporan-layer-page>.table-container>.table_layer>tbody>tr>td {
                padding: 9px;
                font-size: 11px;
                line-height: 1.45;
                vertical-align: top;
            }

            .laporan-layer-page .freeze-cell1_th,
            .laporan-layer-page .freeze-cell1_td {
                width: 168px;
                min-width: 168px;
                max-width: 168px;
            }

            .laporan-layer-page .freeze-cell1_td {
                white-space: normal;
                box-shadow: 4px 0 9px rgba(32, 48, 88, 0.14);
            }

            .laporan-layer-page .kandang table {
                table-layout: fixed;
            }

            .laporan-layer-page .kandang table td {
                padding: 2px 1px;
                border: 0;
                line-height: 1.35;
                overflow-wrap: anywhere;
            }

            .laporan-layer-page .kandang table td:first-child {
                width: 47%;
                color: #66738c;
            }

            .laporan-layer-page .laba-rugi {
                width: 100%;
                min-height: 44px;
                justify-content: center;
                padding: 7px 9px;
                border-color: #bdcaf2;
                background: #eef2ff;
                box-shadow: 0 2px 6px rgba(47, 80, 189, 0.1);
            }

            .laporan-layer-page .detail-kandang-link .detail-hint {
                font-size: 10px;
            }

            .laporan-layer-page>.table-container>.table_layer>tbody>tr:nth-child(even)>td:not(.freeze-cell1_td) {
                background: #f8faff;
            }

            .modal-dialog.modal-lg-max {
                width: calc(100% - 12px);
                max-width: none;
                height: calc(100dvh - 12px);
                margin: 6px auto;
            }

            #laba-rugi .modal-content {
                height: 100%;
                overflow: hidden;
                border: 0;
                border-radius: 15px;
                box-shadow: 0 10px 35px rgba(26, 39, 75, 0.22);
            }

            #laba-rugi .modal-header {
                flex: 0 0 auto;
                min-height: 52px;
                padding: 10px 12px;
                border-bottom-color: #e5e9f2;
            }

            #laba-rugi .modal-title {
                max-width: calc(100% - 44px);
                overflow: hidden;
                font-size: 15px !important;
                font-weight: 800;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            #laba-rugi .modal-body {
                display: flex;
                flex: 1 1 auto;
                flex-direction: column;
                min-height: 0;
                padding: 0 10px 10px;
                overflow: hidden;
            }

            #detail-kandang-tab {
                flex: 0 0 auto;
                flex-wrap: nowrap;
                margin: 0 -10px 8px !important;
                padding: 7px 10px 0;
                overflow-x: auto;
                border-bottom-color: #dfe5f0;
                background: #fff;
                scrollbar-width: none;
            }

            #detail-kandang-tab::-webkit-scrollbar {
                display: none;
            }

            #detail-kandang-tab .nav-item {
                flex: 1 0 auto;
            }

            #detail-kandang-tab .nav-link {
                width: 100%;
                min-height: 43px;
                padding: 10px 12px;
                color: #5b6780;
                font-size: 12px;
                font-weight: 800;
                white-space: nowrap;
            }

            #detail-kandang-tab .nav-link.active {
                color: #3553b7;
                background: #f5f7ff;
                border-bottom-color: #f5f7ff;
            }

            #detail-kandang-tab-content,
            #detail-kandang-tab-content>.tab-pane.active {
                flex: 1 1 auto;
                min-height: 0;
                height: 100%;
            }

            #detail-kandang-tab-content>.tab-pane.active {
                overflow: auto;
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
            }

            #data-telur-pane>.table-container {
                height: 100%;
                max-height: none;
                overflow: auto !important;
                border: 1px solid #e1e6f0;
                border-radius: 10px;
            }

            #data-telur-pane .hd-tiga-minggu-wrapper {
                max-height: none !important;
                overflow: visible !important;
            }

            #data-telur-pane .hd-tiga-minggu {
                margin-bottom: 0;
                font-size: 11px;
                font-variant-numeric: tabular-nums;
                border-collapse: separate !important;
                border-spacing: 0;
            }

            #data-telur-pane .hd-tiga-minggu th,
            #data-telur-pane .hd-tiga-minggu td {
                min-width: 48px;
                padding: 8px 6px;
                border-color: #e8ecf4;
                text-align: center;
                vertical-align: middle;
            }

            /* Kolom tanda ':' tidak dibutuhkan pada layar sempit. */
            #data-telur-pane .hd-tiga-minggu thead tr:first-child>th:nth-child(2),
            #data-telur-pane .hd-tiga-minggu tr>td:nth-child(2) {
                display: none;
            }

            #data-telur-pane .hd-tiga-minggu .sticky-column {
                left: 0 !important;
                z-index: 25 !important;
                width: 96px !important;
                min-width: 96px !important;
                max-width: 96px !important;
                padding: 8px 7px;
                overflow: hidden;
                border-right: 1px solid #ced7ea !important;
                background: #fbfcff !important;
                color: #34415f;
                font-weight: 700;
                text-align: left;
                text-overflow: ellipsis;
                white-space: normal;
                overflow-wrap: anywhere;
                box-shadow: 5px 0 10px rgba(42, 61, 110, 0.08);
            }

            #data-telur-pane .hd-tiga-minggu thead th {
                position: sticky;
                top: 0;
                z-index: 15;
                min-width: 48px;
                background: #f1f4fb;
                color: #34415f;
                font-size: 10px;
                font-weight: 800;
                box-shadow: inset 0 -1px 0 #dce3f0;
            }

            #data-telur-pane .hd-tiga-minggu thead tr:nth-child(2)>th {
                top: 31px;
            }

            #data-telur-pane .hd-tiga-minggu .week-age-header {
                height: 31px;
                padding: 7px 8px;
                border-bottom: 1px solid #cdd7ee;
                background: linear-gradient(135deg, #e9eeff, #f5f7ff) !important;
                color: #3553b7;
                font-size: 10px;
                font-weight: 900;
                letter-spacing: 0.25px;
            }

            #data-telur-pane .hd-tiga-minggu thead .sticky-column {
                z-index: 35 !important;
                background: #435ebe !important;
                color: #fff;
                box-shadow: 5px 0 10px rgba(31, 48, 108, 0.18);
            }

            #data-telur-pane .hd-tiga-minggu thead small {
                margin-top: 2px;
                color: #7a869f;
                font-size: 8px;
            }

            #data-telur-pane .hd-tiga-minggu tbody tr:nth-child(even)>td:not(.sticky-column) {
                background: #fafbfe;
            }

            #data-telur-pane .hd-tiga-minggu tbody tr:hover>td {
                background: #eef3ff;
            }

            #data-telur-pane .hd-tiga-minggu tbody tr:hover>.sticky-column {
                background: #e8eeff !important;
                color: #2949ad;
            }

            #data-telur-pane .hd-tiga-minggu tbody th[colspan],
            #data-telur-pane .hd-tiga-minggu tbody th.sticky-column {
                padding: 9px 10px;
                border: 0;
                background: linear-gradient(90deg, #435ebe, #617bd2) !important;
                color: #fff;
                font-size: 10px;
                font-weight: 800;
                letter-spacing: 0.45px;
                text-align: left;
                text-transform: uppercase;
            }

            #data-telur-pane .hd-tiga-minggu thead tr:nth-child(2) th:nth-child(7),
            #data-telur-pane .hd-tiga-minggu thead tr:nth-child(2) th:nth-child(14),
            #data-telur-pane .hd-tiga-minggu tbody tr>td:nth-child(9),
            #data-telur-pane .hd-tiga-minggu tbody tr>td:nth-child(16) {
                border-right: 3px solid #cbd5f1 !important;
            }

            #data-telur-pane .text-primary {
                color: #315ddb !important;
            }

            #data-telur-pane .text-danger {
                color: #e53e55 !important;
            }

            #laba-rugi-pane>.table,
            #buku-besar-pane .table {
                margin-bottom: 10px;
                font-size: 12px;
            }
        }

        @media screen and (max-width: 380px) {
            .laporan-layer-page {
                padding: 6px;
            }

            .filter-laporan {
                padding: 6px;
            }

            .filter-laporan .nav-link,
            #detail-kandang-tab .nav-link {
                font-size: 11px;
            }

            .laporan-layer-page .freeze-cell1_th,
            .laporan-layer-page .freeze-cell1_td {
                width: 152px;
                min-width: 152px;
                max-width: 152px;
            }
        }

        @media screen and (orientation: landscape) and (max-height: 520px) {
            .navbar-laporan {
                display: none !important;
            }

            .laporan-layer-page {
                height: 100dvh;
            }

            .filter-laporan {
                padding: 5px 7px;
            }

            .filter-laporan>.row {
                flex-direction: row;
                flex-wrap: nowrap;
            }

            .filter-laporan .col-12,
            .filter-laporan .col-lg-4 {
                width: calc(50% - 4px);
            }
        }
    </style>

</head>

<body>
    <nav class="navbar navbar-laporan" style="background: #FFFFFF; border: #435EBE">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="/assets/login/img/agri_laras2.png" alt="Bootstrap" width="40" height="40">
                <span class="d-lg-none">Laporan Layer</span>
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-2 laporan-layer-page">

        <form action="" class="filter-laporan">
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
                                        <td><a href="#" data-bs-toggle="modal" data-bs-target="#laba-rugi"
                                                class="laba-rugi detail-kandang-link"
                                                id_kandang="{{ $k->id_kandang }}"
                                                data-nama-kandang="{{ $k->nm_kandang }}"
                                                data-tgl="{{ $tgl }}"
                                                data-hd-url="{{ route('laporan_layer.hd_tiga_minggu') }}"
                                                aria-label="Lihat detail kandang {{ $k->nm_kandang }}">
                                                <span class="kandang-nama">{{ $k->nm_kandang }}</span>
                                                <span class="detail-hint">Lihat detail</span>
                                            </a></td>
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
                    <h1 class="modal-title fs-5" id="exampleModalLabel">
                        Detail Kandang <span id="modal-nama-kandang"></span>
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="detail-kandang-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="data-telur-tab" data-bs-toggle="tab"
                                data-bs-target="#data-telur-pane" type="button" role="tab"
                                aria-controls="data-telur-pane" aria-selected="true">
                                Data Telur
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="laba-rugi-tab" data-bs-toggle="tab"
                                data-bs-target="#laba-rugi-pane" type="button" role="tab"
                                aria-controls="laba-rugi-pane" aria-selected="false">
                                Laba Rugi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="buku-besar-tab" data-bs-toggle="tab"
                                data-bs-target="#buku-besar-pane" type="button" role="tab"
                                aria-controls="buku-besar-pane" aria-selected="false">
                                Buku Besar
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="detail-kandang-tab-content">
                        <div class="tab-pane fade show active" id="data-telur-pane" role="tabpanel"
                            aria-labelledby="data-telur-tab" tabindex="0">
                            <div class="table-container">
                                <div class="js-hd-tiga-minggu">
                                    <span class="text-muted">Memuat data telur...</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="laba-rugi-pane" role="tabpanel"
                            aria-labelledby="laba-rugi-tab" tabindex="0">
                            <div id="laba-rugi_kandang"></div>
                        </div>
                        <div class="tab-pane fade" id="buku-besar-pane" role="tabpanel"
                            aria-labelledby="buku-besar-tab" tabindex="0">
                            <div id="buku-besar_kandang"></div>
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
        function loadHdTigaMinggu(container) {
            var $containers = container ? $(container) : $('.js-hd-tiga-minggu');

            $containers.each(function() {
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
                    url: $container.attr('data-url'),
                    data: {
                        id_kandang: $container.attr('data-id-kandang'),
                        tgl: $container.attr('data-tgl')
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

            var $trigger = $(this);
            var $dataTelur = $('#data-telur-pane .js-hd-tiga-minggu');

            $('#modal-nama-kandang').text('- ' + $trigger.attr('data-nama-kandang'));
            $dataTelur
                .attr('data-url', $trigger.attr('data-hd-url'))
                .attr('data-id-kandang', $trigger.attr('id_kandang'))
                .attr('data-tgl', $trigger.attr('data-tgl'))
                .removeData('loading loaded')
                .html('<span class="text-muted">Memuat data telur...</span>');

            if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                bootstrap.Tab.getOrCreateInstance(
                    document.getElementById('data-telur-tab')
                ).show();
            }

            $('#laba-rugi_kandang').html(
                '<div class="text-muted p-2">' +
                '<span class="spinner-border spinner-border-sm me-1"></span>' +
                'Memuat data laba rugi...' +
                '</div>'
            );
            $('#buku-besar_kandang').html(
                '<div class="text-muted p-2">' +
                '<span class="spinner-border spinner-border-sm me-1"></span>' +
                'Memuat data buku besar...' +
                '</div>'
            );

            loadHdTigaMinggu($dataTelur);

            $.ajax({
                type: 'GET',
                url: "{{ route('labaRugiKandang') }}",
                data: {
                    id_kandang: $trigger.attr('id_kandang')
                },
                success: function(response) {
                    var $response = $('<div>').html(response);
                    var labaRugi = $response.find('#home').html();
                    var bukuBesar = $response.find('#profile').html();

                    $('#laba-rugi_kandang').html(labaRugi || response);
                    $('#buku-besar_kandang').html(
                        bukuBesar ||
                        '<div class="alert alert-warning">Data buku besar tidak tersedia.</div>'
                    );

                    aktifkanTooltip(document.getElementById('laba-rugi-pane'));
                },
                error: function() {
                    $('#laba-rugi_kandang').html(
                        '<div class="alert alert-danger">' +
                        'Gagal memuat data laba rugi.' +
                        '</div>'
                    );
                    $('#buku-besar_kandang').html(
                        '<div class="alert alert-danger">' +
                        'Gagal memuat data buku besar.' +
                        '</div>'
                    );
                }
            });
        });

        $(document).on('shown.bs.tab', '#data-telur-tab', function() {
            loadHdTigaMinggu('#data-telur-pane .js-hd-tiga-minggu');
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
    });
</script>


</html>
