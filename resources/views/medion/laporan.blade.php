<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,400;6..12,500;6..12,600;6..12,700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
        }

        .table_layer {
            border: 0.5px solid white;
            font-size: 10px;
            padding: 10px;

        }

        .td_layer {
            border: 1px solid black;
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
            background-color: #595959 !important;
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

        .bg_diisi {
            background-color: #FFE699;
            color: black;
            text-align: center;
            font-weight: bold
        }

        .bg_diisi_td {
            background-color: #FFE699;
            color: black;
            text-align: center;
        }

        .bg_diisi2 {
            background-color: #ED7D31;
            color: black;
            font-weight: bold;
            text-align: center
        }

        .table_judul {
            width: 100%;
            padding: 13px;
        }

        thead {
            position: sticky;
            top: 0;
            background-color: #f1f1f1;
            /* Warna latar belakang header yang tetap */
            z-index: 1;
        }
    </style>
</head>

<body>
    <nav class="navbar elemen-hilang" style="background: #FFFFFF; border: #435EBE">
        <div class="container">

        </div>
    </nav>
    <div class="container-fluid mt-2">
        <div class="row ">
            <div class="col-lg-2">
                <a href="{{ route('export_pullet_medion', ['id_kandang' => 8]) }}" class="btn btn-success"><i
                        class="fas fa-file-excel"></i>
                    Export</a>
            </div>
            <div class="col-lg-12">
                <hr>
            </div>
            <div class="col-2 mb-4 elemen-hilang">
                <a class="navbar-brand mb-2" href="#">
                    <img src="/assets/login/img/agri_laras2.png" alt="Bootstrap" width="60" height="60">
                </a>
            </div>
            <div class="col-12 col-lg-10 ">
                <h3 class="text-center fw-bold">CATATAN HARIAN PEMELIHARAAN LAYER PRODUKSI</h3>
            </div>
            <div class="col-6 col-lg-3">
                <table class="table_judul">
                    <tr>
                        <td>Farm</td>
                        <td>:</td>
                        <td class="bg_diisi">CV.AGRI LARAS</td>
                    </tr>
                    <tr>
                        <td>Owner</td>
                        <td>:</td>
                        <td class="bg_diisi"></td>
                    </tr>
                </table>
            </div>
            <div class="col-6 col-lg-3">
                <table class="table_judul">
                    <tr>
                        <td>Nama Operator</td>
                        <td>:</td>
                        <td class="bg_diisi"></td>
                    </tr>
                    <tr>
                        <td>Strain</td>
                        <td>:</td>
                        <td class="bg_diisi2">{{ $kandang->nm_strain }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6 col-lg-3">
                <table class="table_judul">
                    <tr>
                        <td>Jumlah ayam</td>
                        <td>:</td>
                        <td class="bg_diisi">{{ $kandang->stok_awal }}</td>
                    </tr>
                    <tr>
                        <td>Umur (minggu)</td>
                        <td>:</td>
                        <td class="bg_diisi2"></td>
                    </tr>
                </table>
            </div>
            <div class="col-6 col-lg-3">
                <table class="table_judul">
                    <tr>
                        <td>Tgl. chick in</td>
                        <td>:</td>
                        <td class="bg_diisi">{{ date('d-M-Y', strtotime('+ 1 dayes', strtotime($kandang->chick_in))) }}
                        </td>
                    </tr>

                </table>
            </div>


        </div>

        <div class="table-responsive table-container mt-4">
            <table style="text-align: center; " class="table_layer" width="100%">
                <thead style="border: 1px solid white">
                    <tr>
                        <th class="dhead  table_layer " rowspan="3">Umur <br> (minggu)</th>
                        <th class="dhead  table_layer " rowspan="3">Umur <br> (hari)</th>
                        <th class="dhead  table_layer "></th>
                        <th class="dhead  table_layer " colspan="7">Jumlah Ayam</th>
                        <th class="dhead  table_layer " colspan="3">Konsumsi Pakan</th>
                        <th class="dhead  table_layer " colspan="2">Rataan berat badan</th>
                        <th class="dhead  table_layer " colspan="9">Produksi Telur</th>
                        <th class="dhead  table_layer " rowspan="3">Fcr</th>
                        <th class="dhead  table_layer " colspan="3">Keterangan & OVK</th>
                    </tr>
                    <tr>
                        <th class="dhead  table_layer ">Tanggal</th>
                        <th class="dhead  table_layer ">Mati</th>
                        <th class="dhead  table_layer ">Culling</th>
                        <th class="dhead  table_layer ">Afkir</th>
                        <th class="dhead  table_layer ">Hidup</th>
                        <th class="dhead  table_layer " rowspan="2">Deplesi <br> (%)</th>
                        <th class="dhead  table_layer " rowspan="2">Deplesi Komulatif <br> (%)</th>
                        <th class="dhead  table_layer " rowspan="2">Standar</th>
                        <th class="dhead  table_layer ">Total</th>
                        <th class="dhead  table_layer ">Per ekor</th>
                        <th class="dhead  table_layer ">Standar</th>
                        <th class="dhead  table_layer ">Per ekor</th>
                        <th class="dhead  table_layer ">Standar</th>
                        <th class="dhead  table_layer ">utuh</th>
                        <th class="dhead  table_layer ">retak</th>
                        <th class="dhead  table_layer ">pecah</th>
                        <th class="dhead  table_layer ">total</th>
                        <th class="dhead  table_layer " rowspan="2">Berat <br> telur(kg)</th>
                        <th class="dhead  table_layer " colspan="2">henday (%)</th>
                        <th class="dhead  table_layer " colspan="2">Berat telur/butir <br> (gram)</th>
                        <th class="dhead  table_layer " colspan="3">(obat/vitamin/vaksin)</th>
                    </tr>
                    <tr>
                        <th class="dhead  table_layer "></th>
                        <th class="dhead  table_layer " colspan="4">Ekor</th>
                        <th class="dhead  table_layer ">kg/hari</th>
                        <th class="dhead  table_layer " colspan="2">g/ekor/hari</th>
                        <th class="dhead  table_layer " colspan="2">gram</th>
                        <th class="dhead  table_layer " colspan="4">butir</th>
                        <th class="dhead  table_layer ">real</th>
                        <th class="dhead  table_layer ">standar</th>
                        <th class="dhead  table_layer ">real</th>
                        <th class="dhead  table_layer ">standar</th>
                        <th class="dhead  table_layer " colspan="3"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $deplesi_kum = 0;
                    @endphp
                    @foreach ($medion as $key => $m)
                        @php
                            $deplesi_kum += empty($m->deplesi) ? 0 : $m->deplesi;
                        @endphp
                        <tr>
                            <td class="td_layer">{{ $m->umur_minggu }}</td>
                            <td class="td_layer">{{ $m->umur_hari }}</td>
                            <td class="td_layer">{{ date('d-M-y', strtotime($m->tgl)) }}</td>
                            <td class="td_layer bg_diisi_td">{{ $m->mati }}</td>
                            <td class="td_layer bg_diisi_td">{{ $m->jual }}</td>
                            <td class="td_layer bg_diisi_td">0</td>
                            <td class="td_layer ">{{ $m->hidup }}</td>
                            <td class="td_layer ">{{ empty($m->deplesi) ? 0 : $m->deplesi }}</td>
                            <td class="td_layer ">{{ $deplesi_kum }}</td>
                            <td class="td_layer "></td>
                            <td class="td_layer bg_diisi_td">{{ number_format($m->kg_pakan, 2) }}</td>
                            <td class="td_layer">{{ number_format($m->gr_perekor, 1) }}</td>
                            <td class="td_layer">{{ empty($m->feed) ? '#N/A' : $m->feed }}</td>
                            <td class="td_layer">0</td>
                            <td class="td_layer">0</td>
                            <td class="td_layer bg_diisi_td">{{ empty($m->normalPcs) ? 0 : $m->normalPcs }}</td>
                            <td class="td_layer bg_diisi_td">0</td>
                            <td class="td_layer bg_diisi_td">{{ empty($m->abnormalPcs) ? 0 : $m->abnormalPcs }}</td>
                            @php
                                $normal_pcs = empty($m->normalPcs) ? 0 : $m->normalPcs;
                                $abnormal_pcs = empty($m->abnormalPcs) ? 0 : $m->abnormalPcs;

                                $normal_kg = empty($m->normalKg) ? 0 : $m->normalKg - $normal_pcs / 180;
                                $abnormal_kg = empty($m->abnormalKg) ? 0 : $m->abnormalKg - $abnormal_pcs / 180;

                                $ttl_pcs = $normal_pcs + $abnormal_pcs;
                                $ttl_kg = $normal_kg + $abnormal_kg;
                            @endphp
                            <td class="td_layer ">{{ $normal_pcs + $abnormal_pcs }}</td>
                            <td class="td_layer bg_diisi_td">{{ number_format($normal_kg + $abnormal_kg, 2) }}</td>
                            <td class="td_layer ">
                                {{ $ttl_pcs == 0 || $m->hidup == 0 ? 0 : number_format($ttl_pcs / $m->hidup, 2) }}
                            </td>
                            <td class="td_layer ">{{ empty($m->hd) ? '#N/A' : $m->hd }}</td>
                            <td class="td_layer ">{{ $ttl_pcs == 0 ? 0 : number_format($ttl_kg / $ttl_pcs, 2) }}
                            </td>
                            <td class="td_layer ">{{ empty($m->berat_telur) ? '#N/A' : $m->berat_telur }}</td>
                            <td class="td_layer ">{{ $ttl_kg == 0 ? 0 : number_format($m->kg_pakan / $ttl_kg, 1) }}
                            </td>
                            <td class="td_layer bg_diisi_td">{{ $m->nama_obat }}</td>

                        </tr>
                    @endforeach

                </tbody>


            </table>
        </div>

    </div>



</body>


</html>
