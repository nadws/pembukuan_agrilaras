<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laba rugi kandang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Nunito Sans", Arial, Helvetica, sans-serif;
            font-size: 13px;
            background: #f8f9fa;
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
</head>

<body>
    <nav class="navbar elemen-hilang" style="background: #FFFFFF; border: #435EBE">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="/assets/login/img/agri_laras2.png" alt="Agri Laras" width="40" height="40">
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-2 page-wrap">
        <div class="row filter-row mb-2">
            <div class="col-lg-4 col-12">
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

            <div class="col-lg-8 col-12">
                <form action="{{ route('labaRugiKandang2') }}" method="get">
                    <div class="row filter-row">
                        <div class="col-md-4 col-6">
                            <input type="date" class="form-control form-control-sm" name="tgl1"
                                value="{{ $tgl1 }}">
                        </div>
                        <div class="col-md-4 col-6">
                            <input type="date" class="form-control form-control-sm" name="tgl2"
                                value="{{ $tgl2 }}">
                        </div>
                        <div class="col-md-4 col-12">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
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
                                    {{ number_format($rata_rata_telur->ttl_rp / $rata_rata_telur->kg_jual, 0) }}
                                </td>
                            </tr>

                            <tr>
                                <th class="td_layer">Ayam</th>
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
                                    {{ number_format($biaya_ayam->ttl_rp / $biaya_ayam->qty, 0) }}
                                </td>
                            </tr>

                            <tr>
                                <th class="td_layer">Pendapatan</th>
                                <th colspan="100" class="td_layer"></th>
                            </tr>

                            <tr>
                                <td class="td_layer">Jual Telur</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ empty($totalTelur[$k->id_kandang]->kuml_pcs) ? '0' : number_format(($totalTelur[$k->id_kandang]->kuml_kg - $totalTelur[$k->id_kandang]->kuml_pcs / 180) * ($rata_rata_telur->ttl_rp / $rata_rata_telur->kg_jual), 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr>
                                <td class="td_layer">Jual Ayam</td>
                                @foreach ($kandang as $k)
                                    <td class="td_layer text-end">
                                        {{ empty($biaya_ayam->ttl_rp) ? 0 : number_format(($biaya_ayam->ttl_rp / $biaya_ayam->qty) * ($populasi[$k->id_kandang]->jual + $populasi[$k->id_kandang]->afkir), 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr>
                                <th class="td_layer">Total Pendapatan</th>
                                @foreach ($kandang as $k)
                                    @php
                                        $telur = empty($totalTelur[$k->id_kandang]->kuml_pcs)
                                            ? 0
                                            : ($totalTelur[$k->id_kandang]->kuml_kg -
                                                    $totalTelur[$k->id_kandang]->kuml_pcs / 180) *
                                                ($rata_rata_telur->ttl_rp / $rata_rata_telur->kg_jual);

                                        $ayam = empty($biaya_ayam->ttl_rp)
                                            ? 0
                                            : ($biaya_ayam->ttl_rp / $biaya_ayam->qty) *
                                                ($populasi[$k->id_kandang]->jual + $populasi[$k->id_kandang]->afkir);
                                    @endphp
                                    <th class="td_layer text-end">{{ number_format($telur + $ayam, 0) }}</th>
                                @endforeach
                                <th class="text-end td_layer"></th>
                            </tr>

                            <tr>
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
                                        {{ number_format(($biaya_operasional->debit / $total_populasi->stok_awal) * $k->stok_awal, 0) }}
                                    </td>
                                @endforeach
                                <td class="text-end td_layer"></td>
                            </tr>

                            <tr>
                                <th class="td_layer">Total Biaya</th>
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
                                            ($biaya_operasional->debit / $total_populasi->stok_awal) * $k->stok_awal;

                                        $ttl_biaya = $pakan + $vitamin + $vaksinValue + $rak + $operasional;
                                    @endphp
                                    <th class="td_layer text-end">{{ number_format($ttl_biaya, 0) }}</th>
                                @endforeach
                                <th class="text-end td_layer"></th>
                            </tr>

                            <tr>
                                <th class="td_layer">Pendapatan Biaya</th>
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
                                            ($biaya_operasional->debit / $total_populasi->stok_awal) * $k->stok_awal;

                                        $telur = empty($totalTelur[$k->id_kandang]->kuml_pcs)
                                            ? 0
                                            : ($totalTelur[$k->id_kandang]->kuml_kg -
                                                    $totalTelur[$k->id_kandang]->kuml_pcs / 180) *
                                                ($rata_rata_telur->ttl_rp / $rata_rata_telur->kg_jual);

                                        $ayam = empty($biaya_ayam->ttl_rp)
                                            ? 0
                                            : ($biaya_ayam->ttl_rp / $biaya_ayam->qty) *
                                                ($populasi[$k->id_kandang]->jual + $populasi[$k->id_kandang]->afkir);

                                        $ttl_biaya =
                                            $telur + $ayam - ($pakan + $vitamin + $vaksinValue + $rak + $operasional);
                                    @endphp
                                    <th class="td_layer text-end">{{ number_format($ttl_biaya, 0) }}</th>
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
