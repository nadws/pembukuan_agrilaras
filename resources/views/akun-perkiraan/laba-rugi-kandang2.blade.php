<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laba rugi kandang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            background: #f8f9fa;
        }

        button,
        input,
        select,
        textarea,
        .form-control,
        .btn,
        .nav-link,
        .table_layer {
            font-family: Arial, Helvetica, sans-serif;
        }

        .page-wrap {
            padding: 10px;
        }

        .filter-row {
            row-gap: 8px;
        }

        .table-container {
            width: 100%;
            max-height: calc(100vh - 145px);
            overflow: auto;
            border: 1px solid #d6ddf5;
            background: white;
            -webkit-overflow-scrolling: touch;
        }

        .table_layer {
            border-collapse: separate;
            border-spacing: 0;
            width: max-content;
            min-width: 100%;
            font-size: 10px;
            white-space: nowrap;
        }

        .table_layer th,
        .table_layer td {
            border: 0.5px solid #d6ddf5;
            padding: 8px;
            vertical-align: middle;
        }

        .td_layer {
            background: white;
        }

        .dhead {
            background-color: #435EBE !important;
            color: white;
        }

        .table_layer thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #435EBE !important;
            color: white;
        }

        .table_layer th:first-child,
        .table_layer td:first-child {
            position: sticky;
            left: 0;
            z-index: 15;
            background: #F2F7FF;
            font-weight: 600;
        }

        .table_layer thead th:first-child {
            z-index: 30;
            background: #435EBE !important;
            color: white;
        }

        @media screen and (max-width: 768px) {
            body {
                font-size: 12px;
            }

            .elemen-hilang {
                display: none;
            }

            .container-fluid {
                padding-left: 8px;
                padding-right: 8px;
            }

            .nav-pills {
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                gap: 6px;
                padding-bottom: 4px;
            }

            .nav-pills .nav-link {
                white-space: nowrap;
                font-size: 12px;
                padding: 6px 10px;
            }

            .table-container {
                max-height: calc(100vh - 175px);
            }

            .table_layer th,
            .table_layer td {
                font-size: 10px;
                padding: 6px;
            }
        }
    </style>

    <style>
        :root {
            --report-primary: #435ebe;
            --report-primary-dark: #2d478f;
            --report-bg: #f3f6fb;
            --report-border: #dfe5f0;
            --report-text: #293750;
            --report-muted: #6d7890;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            display: flex;
            flex-direction: column;
            background: var(--report-bg);
            color: var(--report-text);
        }

        .navbar-laporan {
            flex: 0 0 60px;
            height: 60px;
            padding: 8px 20px;
            border-bottom: 1px solid var(--report-border);
            background: #fff;
            box-shadow: 0 3px 14px rgba(35, 52, 94, 0.07);
        }

        .navbar-laporan .container-fluid {
            height: 100%;
            padding: 0;
        }

        .navbar-laporan .navbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            padding: 0;
            color: var(--report-primary-dark);
            font-size: 15px;
            font-weight: 800;
        }

        .navbar-laporan .navbar-brand img {
            width: 42px;
            height: 42px;
        }

        .page-wrap {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
            margin-top: 0 !important;
            padding: 16px 20px 20px;
            overflow: hidden;
        }

        .report-toolbar {
            display: grid;
            flex: 0 0 auto;
            grid-template-columns: minmax(220px, 1fr) minmax(320px, 380px) minmax(430px, 520px);
            gap: 14px;
            align-items: center;
            margin-bottom: 14px;
            padding: 13px 15px;
            border: 1px solid var(--report-border);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 5px 18px rgba(35, 52, 94, 0.07);
        }

        .report-title h1 {
            margin: 0 0 3px;
            color: #263b78;
            font-size: 17px;
            font-weight: 800;
        }

        .report-title p {
            margin: 0;
            color: var(--report-muted);
            font-size: 11px;
        }

        .report-toolbar .nav {
            gap: 5px;
            padding: 4px;
            border-radius: 10px;
            background: #f0f3f9;
        }

        .report-toolbar .nav-link {
            min-height: 38px;
            padding: 9px 10px;
            border-radius: 8px;
            color: #59667e;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .report-toolbar .nav-link:hover {
            color: #3652ad;
            background: #e8edfb;
        }

        .report-toolbar .nav-link.active {
            background: var(--report-primary);
            box-shadow: 0 3px 9px rgba(67, 94, 190, 0.25);
        }

        .period-filter {
            display: grid;
            grid-template-columns: minmax(140px, 1fr) minmax(140px, 1fr) 86px;
            gap: 8px;
            align-items: end;
        }

        .date-field label {
            display: block;
            margin-bottom: 4px;
            color: var(--report-muted);
            font-size: 10px;
            font-weight: 700;
        }

        .date-field .form-control {
            height: 40px;
            border-color: #d9e1ef;
            border-radius: 9px;
            font-size: 13px;
        }

        .period-filter .btn {
            height: 40px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 800;
        }

        #myTabContent {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        #myTabContent>.tab-pane {
            width: 100%;
            height: 100%;
            min-height: 0;
            overflow: hidden;
        }

        #myTabContent>.tab-pane.active {
            display: flex;
            flex-direction: column;
        }

        .table-guide {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            margin: 0 2px 7px;
            color: var(--report-muted);
            font-size: 10px;
            font-weight: 700;
        }

        .table-guide .guide-icon {
            display: inline-grid;
            width: 22px;
            height: 22px;
            place-items: center;
            border-radius: 7px;
            background: #e9eeff;
            color: var(--report-primary);
            font-size: 14px;
        }

        .table-container {
            flex: 1 1 auto;
            width: 100%;
            height: 100%;
            min-height: 0;
            max-height: none;
            overflow-x: auto !important;
            overflow-y: auto !important;
            border: 1px solid var(--report-border);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 7px 24px rgba(35, 52, 94, 0.08);
            overscroll-behavior: contain;
            touch-action: pan-x pan-y;
            -webkit-overflow-scrolling: touch;
            scrollbar-color: #bdc8df transparent;
            scrollbar-width: thin;
        }

        .table_layer {
            border: 0;
            font-size: 11px;
            font-variant-numeric: tabular-nums;
        }

        .table_layer th,
        .table_layer td {
            min-width: 135px;
            padding: 10px 12px;
            border-color: #e4e9f2;
        }

        .table_layer thead th {
            height: 46px;
            border-color: #566fc4;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.2px;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.15);
        }

        .table_layer th:first-child,
        .table_layer td:first-child {
            width: 190px;
            min-width: 190px;
            max-width: 190px;
            color: #34415f;
            box-shadow: 5px 0 11px rgba(35, 52, 94, 0.09);
        }

        .table_layer thead th:first-child {
            color: #fff;
        }

        .table_layer thead th:last-child,
        .table_layer tbody td:last-child,
        .table_layer tbody th:last-child {
            min-width: 170px;
        }

        .table_layer tbody tr:nth-child(even):not(.section-row)>td {
            background: #fafbfe;
        }

        .table_layer tbody tr:hover:not(.section-row)>td {
            background: #eef3ff;
        }

        .table_layer tbody tr:hover:not(.section-row)>td:first-child {
            background: #e8eeff;
            color: #2949ad;
        }

        .table_layer .section-row th {
            padding: 9px 12px;
            border-color: #536dc3;
            background: linear-gradient(90deg, #435ebe, #617bd2) !important;
            color: #fff;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-align: left;
            text-transform: uppercase;
        }

        .table_layer .section-row th:first-child {
            z-index: 16;
        }

        .table_layer .summary-row th {
            border-color: #d5def3;
            background: #eef2ff;
            color: #2e478e;
            font-weight: 900;
        }

        .table_layer .summary-row th:first-child {
            background: #e5ebff;
        }

        .table_layer .profit-row th {
            position: sticky;
            bottom: 0;
            z-index: 18;
            border-color: #304b9d;
            background: #354f9e;
            color: #fff;
            font-weight: 900;
            box-shadow: 0 -4px 12px rgba(35, 52, 94, 0.13);
        }

        .table_layer .profit-row th:first-child {
            z-index: 19;
            background: #294383;
            color: #fff;
        }

        .profit-value {
            display: inline-block;
            min-width: 92px;
            padding: 5px 8px;
            border-radius: 7px;
            text-align: right;
        }

        .profit-value.is-positive {
            background: #dff8e9;
            color: #157347;
        }

        .profit-value.is-negative {
            background: #ffe4e8;
            color: #c52b42;
        }

        @media screen and (max-width: 1199.98px) and (min-width: 769px) {
            .report-toolbar {
                grid-template-columns: minmax(200px, 1fr) minmax(290px, 340px);
            }

            .report-title {
                grid-column: 1;
            }

            .report-toolbar .report-nav {
                grid-column: 2;
            }

            .period-filter {
                grid-column: 1 / -1;
            }
        }

        @media screen and (max-width: 768px) {
            body {
                font-size: 12px;
            }

            .navbar-laporan {
                display: flex !important;
                flex-basis: 52px;
                height: 52px;
                padding: 6px 10px;
            }

            .navbar-laporan .navbar-brand {
                font-size: 14px;
            }

            .navbar-laporan .navbar-brand img {
                width: 36px;
                height: 36px;
            }

            .page-wrap {
                padding: 7px;
                padding-bottom: max(7px, env(safe-area-inset-bottom));
            }

            .report-toolbar {
                display: flex;
                flex-direction: column;
                gap: 5px;
                align-items: stretch;
                margin-bottom: 6px;
                padding: 6px;
                border-radius: 10px;
            }

            .report-title {
                display: none;
            }

            .report-toolbar .nav {
                flex-wrap: nowrap;
                overflow: visible;
                gap: 3px;
                padding: 2px;
                border-radius: 8px;
            }

            .report-toolbar .nav-item {
                flex: 1 1 50%;
            }

            .report-toolbar .nav-link {
                width: 100%;
                min-height: 32px;
                padding: 6px 5px;
                border-radius: 6px;
                font-size: 10px;
            }

            .period-filter {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 62px;
                gap: 5px;
            }

            .date-field label {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }

            .date-field {
                min-width: 0;
            }

            .date-field .form-control {
                width: 100%;
                height: 36px;
                min-width: 0;
                padding: 5px 6px;
                border-radius: 7px;
                font-size: 13px;
            }

            .period-filter .btn {
                grid-column: auto;
                height: 36px;
                padding: 5px 7px;
                border-radius: 7px;
                font-size: 10px;
            }

            .table-guide {
                justify-content: flex-start;
                margin-bottom: 5px;
                font-size: 9px;
            }

            .table-container {
                border-radius: 11px;
            }

            .table_layer {
                font-size: 10px;
            }

            .table_layer th,
            .table_layer td {
                min-width: 112px;
                padding: 8px 7px;
            }

            .table_layer th:first-child,
            .table_layer td:first-child {
                width: 116px;
                min-width: 116px;
                max-width: 116px;
                white-space: normal;
            }

            .table_layer thead th:last-child,
            .table_layer tbody td:last-child,
            .table_layer tbody th:last-child {
                min-width: 138px;
            }

            .table_layer .section-row th,
            .table_layer .summary-row th,
            .table_layer .profit-row th {
                padding: 8px 7px;
                font-size: 9px;
            }
        }

        @media screen and (max-width: 380px) {
            .report-toolbar {
                padding: 5px;
            }

            .table_layer th:first-child,
            .table_layer td:first-child {
                width: 106px;
                min-width: 106px;
                max-width: 106px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-laporan">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="/assets/login/img/agri_laras2.png" alt="Agri Laras" width="40" height="40">
                <span>Agri Laras &middot; Laba Rugi Kandang</span>
            </a>
        </div>
    </nav>

    <div class="container-fluid page-wrap">
        <div class="report-toolbar">
            <div class="report-title">
                <h1>Laba Rugi Kandang</h1>
                <p>Periode {{ tanggal($tgl1) }} sampai {{ tanggal($tgl2) }}</p>
            </div>

            <div class="report-nav">
                <ul class="nav nav-pills nav-fill">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('laporan_layer') ? 'active' : '' }}"
                            href="{{ route('laporan_layer') }}">Laporan layer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('labaRugiKandang2') ? 'active' : '' }}"
                            href="{{ route('labaRugiKandang2') }}">Laba rugi kandang</a>
                    </li>
                </ul>
            </div>

            <form action="{{ route('labaRugiKandang2') }}" method="get" class="period-filter">
                <div class="date-field">
                    <label for="tgl1">Dari tanggal</label>
                    <input type="date" id="tgl1" class="form-control" name="tgl1" value="{{ $tgl1 }}">
                </div>
                <div class="date-field">
                    <label for="tgl2">Sampai tanggal</label>
                    <input type="date" id="tgl2" class="form-control" name="tgl2" value="{{ $tgl2 }}">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <div class="table-guide">
                    <span class="guide-icon">&#8596;</span>
                    Geser tabel untuk melihat seluruh kandang
                </div>
                <div class="table-container">
                    <table class="table_layer">
                        <thead>
                            <tr>
                                <th class="text-center dhead">Ket</th>
                                @foreach ($kandang as $k)
                                    <th class="text-center dhead">{{ $k->nm_kandang }}</th>
                                @endforeach
                                <th class="text-center dhead">Rata-rata Penjualan</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td class="td_layer">Ayam Awal</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">{{ number_format($k->stok_awal, 0) }}</td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr>
                                <td class="td_layer">Total Telur</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ empty($totalTelur[$k->id_kandang]->kuml_pcs) ? '0' : number_format($totalTelur[$k->id_kandang]->kuml_kg - $totalTelur[$k->id_kandang]->kuml_pcs / 180, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer">
                                    {{ number_format($hargaRataTelur, 0) }}
                                </td>
                            </tr>

                            <tr class="section-row">
                                <th class="td_layer fw-bold">Ayam</th>
                                <th colspan="100" class="td_layer"></th>
                            </tr>

                            <tr>
                                <td class="td_layer">Mati</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ number_format($populasi[$k->id_kandang]->mati, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr>
                                <td class="td_layer">Culling</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ number_format($populasi[$k->id_kandang]->jual + $populasi[$k->id_kandang]->afkir, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer">
                                    {{ number_format($hargaRataAyam, 0) }}
                                </td>
                            </tr>

                            <tr class="section-row">
                                <th class="td_layer">Pendapatan</th>
                                <th colspan="100" class="td_layer"></th>
                            </tr>

                            <tr>
                                <td class="td_layer">Jual Telur</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ empty($totalTelur[$k->id_kandang]->kuml_pcs) ? '0' : number_format(($totalTelur[$k->id_kandang]->kuml_kg - $totalTelur[$k->id_kandang]->kuml_pcs / 180) * $hargaRataTelur, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr>
                                <td class="td_layer">Jual Ayam</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ number_format($hargaRataAyam * ($populasi[$k->id_kandang]->jual + $populasi[$k->id_kandang]->afkir), 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr class="summary-row">
                                <th class="td_layer fw-bold">Total Pendapatan</th>
                                @foreach ($kandang as $k)
                                    @php
                                        $telur = empty($totalTelur[$k->id_kandang]->kuml_pcs)
                                            ? 0
                                            : ($totalTelur[$k->id_kandang]->kuml_kg -
                                                    $totalTelur[$k->id_kandang]->kuml_pcs / 180) *
                                                $hargaRataTelur;

                                        $ayam =
                                            $hargaRataAyam *
                                            ($populasi[$k->id_kandang]->jual + $populasi[$k->id_kandang]->afkir);
                                    @endphp
                                    <th class="td_layer text-end">{{ number_format($telur + $ayam, 0) }}</th>
                                @endforeach
                                <th class="text-end td_layer"></th>
                            </tr>

                            <tr class="section-row">
                                <th class="td_layer">Biaya</th>
                                <th colspan="100" class="td_layer"></th>
                            </tr>

                            <tr>
                                <td class="td_layer">Pakan</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ empty($biaya_pakan[$k->nm_kandang]->ttl_rp) ? 0 : number_format($biaya_pakan[$k->nm_kandang]->ttl_rp, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr>
                                <td class="td_layer">Vitamin</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ empty($biaya_vitamin[$k->nm_kandang]->ttl_rp) ? 0 : number_format($biaya_vitamin[$k->nm_kandang]->ttl_rp, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr>
                                <td class="td_layer">Vaksin</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ empty($vaksin[$k->id_kandang]->ttl_rp) ? 0 : number_format($vaksin[$k->id_kandang]->ttl_rp, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr>
                                <td class="td_layer">Rak Telur</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ empty($totalTelur[$k->id_kandang]->kuml_pcs) ? '0' : number_format(($totalTelur[$k->id_kandang]->kuml_pcs / 180) * 6 * 820, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer">820</td>
                            </tr>

                            <tr>
                                <td class="td_layer">Biaya operasional</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ number_format($stokAwalTotal > 0 ? ($biayaOperasionalTotal / $stokAwalTotal) * $k->stok_awal : 0, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr class="summary-row">
                                <th class="td_layer fw-bold">Total Biaya</th>
                                @foreach ($kandang as $k)
                                    @php
                                        $pakan = empty($biaya_pakan[$k->nm_kandang]->ttl_rp)
                                            ? 0
                                            : $biaya_pakan[$k->nm_kandang]->ttl_rp;
                                        $vitamin = empty($biaya_vitamin[$k->nm_kandang]->ttl_rp)
                                            ? 0
                                            : $biaya_vitamin[$k->nm_kandang]->ttl_rp;
                                        $vaksinValue = empty($vaksin[$k->id_kandang]->ttl_rp)
                                            ? 0
                                            : $vaksin[$k->id_kandang]->ttl_rp;
                                        $rak = empty($totalTelur[$k->id_kandang]->kuml_pcs)
                                            ? 0
                                            : ($totalTelur[$k->id_kandang]->kuml_pcs / 180) * 6 * 820;
                                        $operasional =
                                            $stokAwalTotal > 0
                                                ? ($biayaOperasionalTotal / $stokAwalTotal) * $k->stok_awal
                                                : 0;

                                        $ttl_biaya = $pakan + $vitamin + $vaksinValue + $rak + $operasional;
                                    @endphp
                                    <th class="td_layer text-end">{{ number_format($ttl_biaya, 0) }}</th>
                                @endforeach
                                <th class="text-end td_layer"></th>
                            </tr>

                            <tr class="profit-row">
                                <th class="td_layer fw-bold">Pendapatan Biaya</th>
                                @foreach ($kandang as $k)
                                    @php
                                        $pakan = empty($biaya_pakan[$k->nm_kandang]->ttl_rp)
                                            ? 0
                                            : $biaya_pakan[$k->nm_kandang]->ttl_rp;
                                        $vitamin = empty($biaya_vitamin[$k->nm_kandang]->ttl_rp)
                                            ? 0
                                            : $biaya_vitamin[$k->nm_kandang]->ttl_rp;
                                        $vaksinValue = empty($vaksin[$k->id_kandang]->ttl_rp)
                                            ? 0
                                            : $vaksin[$k->id_kandang]->ttl_rp;
                                        $rak = empty($totalTelur[$k->id_kandang]->kuml_pcs)
                                            ? 0
                                            : ($totalTelur[$k->id_kandang]->kuml_pcs / 180) * 6 * 820;
                                        $operasional =
                                            $stokAwalTotal > 0
                                                ? ($biayaOperasionalTotal / $stokAwalTotal) * $k->stok_awal
                                                : 0;

                                        $telur = empty($totalTelur[$k->id_kandang]->kuml_pcs)
                                            ? 0
                                            : ($totalTelur[$k->id_kandang]->kuml_kg -
                                                    $totalTelur[$k->id_kandang]->kuml_pcs / 180) *
                                                $hargaRataTelur;

                                        $ayam =
                                            $hargaRataAyam *
                                            ($populasi[$k->id_kandang]->jual + $populasi[$k->id_kandang]->afkir);

                                        $ttl_biaya =
                                            $telur + $ayam - ($pakan + $vitamin + $vaksinValue + $rak + $operasional);
                                    @endphp
                                    <th class="td_layer text-end">
                                        <span
                                            class="profit-value {{ $ttl_biaya >= 0 ? 'is-positive' : 'is-negative' }}">
                                            {{ number_format($ttl_biaya, 0) }}
                                        </span>
                                    </th>
                                @endforeach
                                <th class="text-end td_layer"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</html>
