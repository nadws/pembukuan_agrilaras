@php
    $file = 'Aktiva.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=$file");
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Aktiva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
</head>
<style>
    .tengah {
        vertical-align: middle;
        text-align: center
    }
</style>

<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <h4 class="text-center fw-bold">DAFTAR PENYUSUTAN AKTIVA TETAP</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="2" class="tengah">NO</th>
                            <th rowspan="2" class="tengah">JENIS AKTIVA</th>
                            <th class="tengah">TAHUN</th>
                            <th class="tengah">HARGA</th>
                            <th rowspan="2" class="tengah">TARIF</th>
                            <th class="tengah">AKUMULASI</th>
                            <th class="tengah">NILAI BUKU</th>
                            <th class="tengah">AKUMULASI</th>
                            <th class="tengah">NILAI BUKU</th>
                        </tr>
                        <tr>
                            <th class="tengah">PEROLEHAN</th>
                            <th class="tengah">PEROLEHAN</th>
                            <th class="tengah">PENYUSUTAN {{ date('Y', strtotime($tahun2)) }}</th>
                            <th class="tengah">{{ date('Y', strtotime($tahun2)) }}</th>
                            <th class="tengah">PENYUSUTAN {{ date('Y', strtotime($tahun1)) }}</th>
                            <th class="tengah">{{ date('Y', strtotime($tahun1)) }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td>HARTA BERWUJUD</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        @foreach ($kelompok as $no => $k)
                            @php
                                $aktiva = DB::select("SELECT a.*, b.beban1, c.beban2, d.nilai1, e.nilai2
                        FROM aktiva as a
                        left join(
                        SELECT sum(c.b_penyusutan) as beban1 , c.id_aktiva
                        FROM depresiasi_aktiva as c
                        where c.tgl between '$tahun2' and '$tahun2_1'
                        group by c.id_aktiva
                        ) as b on b.id_aktiva = a.id_aktiva

                        left join(
                        SELECT sum(c.b_penyusutan) as beban2 , c.id_aktiva
                        FROM depresiasi_aktiva as c
                        where c.tgl between '$tahun1' and '$tahun1_1'
                        group by c.id_aktiva
                        ) as c on c.id_aktiva = a.id_aktiva

                        left join(
                        SELECT sum(c.b_penyusutan) as nilai1 , c.id_aktiva
                        FROM depresiasi_aktiva as c
                        where c.tgl between '2017-01-01' and '$tahun2_1'
                        group by c.id_aktiva
                        ) as d on d.id_aktiva = a.id_aktiva

                        left join(
                        SELECT sum(c.b_penyusutan) as nilai2 , c.id_aktiva
                        FROM depresiasi_aktiva as c
                        where c.tgl between '2017-01-01' and '$tahun1_1'
                        group by c.id_aktiva
                        ) as e on e.id_aktiva = a.id_aktiva

                        where a.id_kelompok = '$k->id_kelompok'
                        ");
                            @endphp
                            <tr>
                                <td>{{ $no + 1 }}</td>
                                <td>{{ $k->nm_kelompok }} :</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            @foreach ($aktiva as $a)
                                <tr>
                                    <td></td>
                                    <td>{{ $a->nm_aktiva }} </td>
                                    <td align="center">{{ date('Y-m', strtotime($a->tgl)) }}</td>
                                    <td align="right">{{ number_format($a->h_perolehan, 0) }}</td>
                                    <td align="right" style="white-space: nowrap">{{ $k->tarif * 100 }}%</td>
                                    <td align="right">{{ empty($a->beban1) ? '0' : number_format($a->beban1, 0) }}
                                    </td>
                                    @php
                                        $tgl = date('Y', strtotime($a->tgl));
                                        $tgl2 = date('Y', strtotime($tahun2_1));
                                    @endphp
                                    <td align="right">
                                        {{ $tgl > $tgl2 ? '0' : number_format($a->h_perolehan - $a->nilai1, 0) }}
                                    </td>
                                    <td align="right">{{ number_format($a->beban2, 0) }}</td>
                                    <td align="right">{{ number_format($a->h_perolehan - $a->nilai2, 0) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</body>

<script>
    window.print()
</script>

</html>
