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
                    href="{{ route('laporanlabakandang') }}">
                    Laporan Laba Rugi Kandang
                </a>
            </li>


        </ul>
    </nav>
    <div class="container-fluid mt-2">
        <div class="row">
            <div class="col-6 col-lg-8 elemen-hilang">
                <h6 class="mb-2">Laporan Laba Rugi Kandang {{ date('F Y', strtotime($tgl)) }}</h6>
            </div>
            <form action="">
                <div class="col-12 col-lg-4 float-end d-flex align-items-center">

                    <select name="bulan" id="" class="form-control">
                        <option value="">Pilih Bulan</option>
                        @foreach ($bulan_array as $b)
                            <option value="{{ $b->bulan }}" @selected($b->bulan == $bulan)>{{ $b->nm_bulan }}
                            </option>
                        @endforeach
                    </select>
                    &nbsp;
                    <select name="tahun" id="" class="form-control">
                        <option value="">Pilih Tahun</option>
                        @foreach ($tahun_array as $b)
                            <option value="{{ $b->tahun }}" @selected($b->tahun == $tahun)>{{ $b->tahun }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm ms-2">Filter</button>
                </div>
            </form>
            <div class="col-lg-12">
                <br>

            </div>
        </div>



        <div class="table-responsive table-container">
            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th class="text-center dhead freeze-cell1_th  text-white align-middle">DESKRIPSI</th>
                        @foreach ($kandang as $k)
                            <th class="text-center dhead th_atas text-white align-middle">Kandang <br>
                                {{ $k->nm_kandang }}
                            </th>
                        @endforeach
                        <th class="text-end dhead th_atas text-white align-middle">Total</th>
                    </tr>

                </thead>
                <tbody>
                    {{-- <tr>
                        <th class="freeze-cell1_td ">PENDAPATAN</th>
                    </tr> --}}
                    <tr>
                        <th class="freeze-cell1_td ">Pendapatan Operasional</th>
                    </tr>
                    <tr>
                        <td class="freeze-cell1_td ">Penjualan Telur</td>
                        @php
                            $total = 0;
                        @endphp
                        @foreach ($kandang as $k)
                            @php
                                $total += ($k->kg - $k->pcs / 180) * $harga_telur->harga;
                            @endphp
                            <td class="text-end">{{ number_format(($k->kg - $k->pcs / 180) * $harga_telur->harga, 0) }}
                                {{-- /{{ $k->kg - $k->pcs / 180 }} --}}
                            </td>
                        @endforeach
                        <td class="text-end">{{ number_format($total, 0) }}</td>
                    </tr>
                    <tr>
                        <td class="freeze-cell1_td ">Penjualan Ayam</td>
                        @php
                            $total1 = 0;
                        @endphp
                        @foreach ($kandang as $k)
                            @php
                                $ayam = DB::table('jurnal_accurate')
                                    ->where('kode', '400002')
                                    ->where('nm_departemen', $k->nm_kandang)
                                    ->whereMonth('tgl', $bulan)
                                    ->whereYear('tgl', $tahun)

                                    ->first();
                                $total1 += $ayam->kredit ?? 0;
                            @endphp
                            <td class="text-end">{{ number_format($ayam->kredit ?? 0, 0) }}</td>
                        @endforeach
                        <td class="text-end">{{ number_format($total1, 0) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold freeze-cell1_td ">Jumlah Pendapatan</td>
                        @foreach ($kandang as $k)
                            @php
                                $ayam = DB::table('jurnal_accurate')
                                    ->where('kode', '400002')
                                    ->where('nm_departemen', $k->nm_kandang)
                                    ->whereMonth('tgl', $bulan)
                                    ->whereYear('tgl', $tahun)
                                    ->first();
                            @endphp
                            <td class="text-end fw-bold">
                                {{ number_format(($k->kg - $k->pcs / 180) * $harga_telur->harga + ($ayam->kredit ?? 0), 0) }}
                            </td>
                        @endforeach
                        <td class="text-end fw-bold">{{ number_format($total1 + $total, 0) }}</td>
                    </tr>
                    {{-- <tr>
                        <th class="freeze-cell1_td ">BEBAN POKOK PENJUALAN</th>
                    </tr> --}}
                    <tr>
                        <th class="freeze-cell1_td ">Biaya Pokok Penjualan</th>
                    </tr>

                    @php
                        $total_per_kandang = [];
                        $ttl_ayam = 0;
                        foreach ($kandang as $k) {
                            $total_per_kandang[$k->nm_kandang] = 0;
                            $ttl_ayam += $k->ttl_ayam;
                        }

                    @endphp

                    @foreach ($biaya_pokok as $b)
                        <tr>
                            <td class="freeze-cell1_td ">{{ $b->nama }}</td>
                            @php
                                $total2 = 0;
                            @endphp
                            @foreach ($kandang as $k)
                                @php
                                    $pokok = DB::table('jurnal_accurate')
                                        ->where('kode', $b->kode)
                                        ->where('nm_departemen', $k->nm_kandang)
                                        ->whereMonth('tgl', $bulan)
                                        ->whereYear('tgl', $tahun)
                                        ->first();

                                    $nilai = $pokok->debit ?? 0;
                                    $total2 += $nilai;
                                    $total_per_kandang[$k->nm_kandang] += $nilai;
                                @endphp
                                <td class="text-end">{{ number_format($nilai, 0) }}</td>
                            @endforeach
                            <td class="text-end fw-bold">{{ number_format($total2, 0) }}</td>
                        </tr>
                    @endforeach
                    @php
                        $total_per_kandang2 = [];
                        foreach ($kandang as $k) {
                            $total_per_kandang2[$k->nm_kandang] = 0;
                        }

                    @endphp
                    @php
                        $total_per_kandang_pokok = [];
                        foreach ($kandang as $k) {
                            $total_per_kandang_pokok[$k->nm_kandang] = 0;
                        }

                    @endphp
                    @foreach ($biaya_pokok2 as $o)
                        <tr>
                            <td class="freeze-cell1_td">{{ $o->nama }}</td>
                            @php
                                $total_pokok = 0;
                            @endphp
                            @foreach ($kandang as $k)
                                @php
                                    $total_pokok += ($o->debit / $ttl_ayam) * $k->ttl_ayam;
                                    $nilai2 = ($o->debit / $ttl_ayam) * $k->ttl_ayam;
                                    $total_per_kandang_pokok[$k->nm_kandang] += $nilai2;
                                @endphp
                                <td class="text-end">{{ number_format(($o->debit / $ttl_ayam) * $k->ttl_ayam, 0) }}
                                    {{-- <br>
                                    {{ $ttl_ayam }} / {{ $k->ttl_ayam }}
                                    <br>
                                    {{ $o->debit }} --}}
                                </td>
                            @endforeach
                            <td class="text-end fw-bold">{{ number_format($total_pokok, 0) }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <th class="freeze-cell1_td ">Jumlah Beban Pokok Penjualan</th>
                        @foreach ($kandang as $k)
                            <th class="text-end">
                                {{ number_format(($total_per_kandang[$k->nm_kandang] ?? 0) + ($total_per_kandang_pokok[$k->nm_kandang] ?? 0), 0) }}
                            </th>
                        @endforeach
                        <th class="text-end fw-bold">
                            {{ number_format(array_sum($total_per_kandang) + array_sum($total_per_kandang_pokok), 0) }}
                        </th>
                    </tr>

                    <tr>
                        <th class="freeze-cell1_td ">LABA KOTOR</th>
                        @foreach ($kandang as $k)
                            @php
                                $ayam = DB::table('jurnal_accurate')
                                    ->where('kode', '400002')
                                    ->where('nm_departemen', $k->nm_kandang)
                                    ->whereMonth('tgl', $bulan)
                                    ->whereYear('tgl', $tahun)
                                    ->first();
                            @endphp
                            <th class="text-end">
                                {{ number_format(($k->kg - $k->pcs / 180) * $harga_telur->harga + ($ayam->kredit ?? 0) - ($total_per_kandang[$k->nm_kandang] ?? 0) - ($total_per_kandang_pokok[$k->nm_kandang] ?? 0), 0) }}
                            </th>
                        @endforeach
                        <td class="text-end fw-bold">
                            {{ number_format($total1 + $total - array_sum($total_per_kandang) - array_sum($total_per_kandang_pokok), 0) }}
                        </td>
                    </tr>
                    {{-- <tr>
                        <th class="freeze-cell1_td ">BEBAN OPERASIONAL</th>
                    </tr> --}}
                    <tr>
                        <th class="freeze-cell1_td">Biaya Operasional</th>
                    </tr>
                    @php
                        $total_per_kandang2 = [];
                        foreach ($kandang as $k) {
                            $total_per_kandang2[$k->nm_kandang] = 0;
                        }

                    @endphp
                    @foreach ($operasional as $o)
                        <tr>
                            <td class="freeze-cell1_td">{{ $o->nama }}</td>
                            @php
                                $total3 = 0;
                            @endphp
                            @foreach ($kandang as $k)
                                @php
                                    $total3 += ($o->debit / $ttl_ayam) * $k->ttl_ayam;
                                    $nilai2 = ($o->debit / $ttl_ayam) * $k->ttl_ayam;
                                    $total_per_kandang2[$k->nm_kandang] += $nilai2;
                                @endphp
                                <td class="text-end">{{ number_format(($o->debit / $ttl_ayam) * $k->ttl_ayam, 0) }}
                                    {{-- <br>
                                    {{ $ttl_ayam }} / {{ $k->ttl_ayam }}
                                    <br>
                                    {{ $o->debit }} --}}
                                </td>
                            @endforeach
                            <td class="text-end fw-bold">{{ number_format($total3, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th class="freeze-cell1_td">Jumlah Beban Operasional</th>
                        @foreach ($kandang as $k)
                            <th class="text-end">{{ number_format($total_per_kandang2[$k->nm_kandang] ?? 0, 0) }}</th>
                        @endforeach
                        <th class="text-end fw-bold">
                            {{ number_format(array_sum($total_per_kandang2), 0) }}
                        </th>
                    </tr>

                    <tr>
                        <th class="freeze-cell1_td">LABA/RUGI</th>
                        @foreach ($kandang as $k)
                            @php
                                $ayam = DB::table('jurnal_accurate')
                                    ->where('kode', '400002')
                                    ->where('nm_departemen', $k->nm_kandang)
                                    ->whereMonth('tgl', $bulan)
                                    ->whereYear('tgl', $tahun)
                                    ->first();
                            @endphp
                            <th class="text-end">
                                {{ number_format(($k->kg - $k->pcs / 180) * $harga_telur->harga + ($ayam->kredit ?? 0) - ($total_per_kandang[$k->nm_kandang] ?? 0) - ($total_per_kandang2[$k->nm_kandang] ?? 0) - ($total_per_kandang_pokok[$k->nm_kandang] ?? 0), 0) }}
                            </th>
                        @endforeach
                        <td class="text-end fw-bold">
                            {{ number_format($total1 + $total - array_sum($total_per_kandang) - array_sum($total_per_kandang_pokok) - array_sum($total_per_kandang2), 0) }}
                        </td>
                    </tr>



                </tbody>


            </table>

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
