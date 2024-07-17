<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
</head>
<style>
    .table1 {
        font-family: sans-serif;
        color: #232323;
        border-collapse: collapse;
    }

    .table1,
    th,
    td {
        border: 1px solid #999;
        padding: 1px 20px;
        font-size: 9px;
    }
</style>

<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <h5 class=" fw-bold mb-4" style="color: #787878">INVOICE SETORAN TELUR</h5>
                <table class="table1">
                    <thead style="background-color: #E9ECEF;">
                        <tr>
                            <th>CFM Setor</th>
                            <th>Tanggal Setor</th>
                            <th>Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $invo->nota_setor }}</td>
                            <td>{{ tanggal($invo->tgl) }}</td>
                            <td>{{ Auth::user()->name }}</td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <table class="table1 table-bordered" width="100%">
                    <thead style="background-color: #E9ECEF;">
                        <tr>
                            <th class="dhead" width="5">#</th>
                            <th class="dhead">Tanggal</th>
                            <th class="dhead">No Nota</th>
                            <th class="dhead">Pembayaran</th>
                            <th class="dhead">Customers</th>
                            {{-- <th class="dhead">Keterangan</th> --}}
                            <th class="dhead" style="text-align: right">Total Rp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = 0;
                        @endphp
                        @foreach ($invoice as $no => $i)
                            @php
                                $total += $i->nominal;
                            @endphp
                            <tr>
                                <td>{{ $no + 1 }}</td>
                                <td style="white-space: nowrap">{{ tanggal($i->tgl) }}</td>
                                <td style="white-space: nowrap">{{ $i->no_nota_jurnal }}</td>
                                <td style="white-space: nowrap">{{ ucwords(strtolower($i->nm_akun)) }}</td>
                                <td>
                                    @if ($i->id_customer != '0' || !empty($i->id_customer))
                                        {{ $i->nm_customer . $i->urutan_customer }}
                                    @endif

                                    @if ($i->id_customer == '0' || empty($i->id_customer))
                                        {{ empty($i->customer) ? $i->ket : $i->customer }}
                                    @endif

                                </td>
                                {{-- <td>{{$i->id_customer != '0' ? $i->nm_customer . $i->urutan_customer : (
                                $i->id_customer !==
                                null ? $i->ket : $i->customer . $i->urutan_customer)}}</td> --}}
                                {{-- <td>{{$i->ket}}</td> --}}
                                <td align="right" style="white-space: nowrap">Rp {{ number_format($i->nominal, 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" style="text-align: center;white-space: nowrap">Total</th>
                            <th style="text-align: right; white-space: nowrap">Rp {{ number_format($total, 0) }}</th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div>

</body>

<script>
    window.print()
</script>

</html>
