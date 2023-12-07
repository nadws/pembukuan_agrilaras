<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Profit & Loss</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        .table-bordered {
            border: 1px solid black;
        }
    </style>
</head>

<body style="font-size: 10px">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center fw-bold">CV.AGRILARAS</h3>
                        <h5 class="text-center">LAPORAN NERACA</h5>
                        <h5 class="mt-2 text-center">PER
                            {{ tanggal($tgl2) }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th colspan="2" width="50%" class="dhead text-center">AKTIVA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="2" class="fw-bold">
                                                AKTIVA LANCAR
                                            </td>
                                        </tr>
                                        @php
                                            $ttl_kas = 0;
                                            $ttl_bank = 0;
                                            $ttl_piutang = 0;
                                            $ttl_persediaan = 0;

                                            foreach ($kas as $k) {
                                                $ttl_kas += $k->debit + $k->debit_saldo - ($k->kredit + $k->kredit_saldo);
                                            }
                                            foreach ($bank as $k) {
                                                $ttl_bank += $k->debit + $k->debit_saldo - ($k->kredit + $k->kredit_saldo);
                                            }

                                            foreach ($piutang as $k) {
                                                $ttl_piutang += $k->debit + $k->debit_saldo - ($k->kredit + $k->kredit_saldo);
                                            }
                                            foreach ($persediaan as $k) {
                                                $ttl_persediaan += $k->debit + $k->debit_saldo - ($k->kredit + $k->kredit_saldo);
                                            }
                                        @endphp


                                        <tr>

                                            <td>KAS</td>
                                            <td align="right">Rp {{ number_format($ttl_kas, 0) }}</td>
                                        </tr>
                                        {{-- @foreach ($kas as $k)
                                            <tr x-transition x-show="open1">
                                                <td style="padding-left: 20px">
                                                    <a target="_blank"
                                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}
                                                    </a>
                                                </td>
                                                <td align="right">Rp
                                                    {{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}
                                                </td>
                                            </tr>
                                        @endforeach --}}

                                        <tr>
                                            <td>BANK</td>
                                            <td align="right">Rp {{ number_format($ttl_bank, 0) }}</td>
                                        </tr>
                                        {{-- @foreach ($bank as $k)
                                            <tr x-transition x-show="open2">
                                                <td style="padding-left: 20px">
                                                    <a target="_blank"
                                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}
                                                    </a>
                                                </td>
                                                <td align="right">Rp
                                                    {{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}
                                                </td>
                                            </tr>
                                        @endforeach --}}
                                        <tr>
                                            <td>PIUTANG DAGANG</td>
                                            <td align="right">Rp {{ number_format($ttl_piutang, 0) }}</td>
                                        </tr>
                                        {{-- @foreach ($piutang as $k)
                                            <tr x-transition x-show="open3">
                                                <td style="padding-left: 20px">
                                                    <a target="_blank"
                                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}</a>
                                                </td>
                                                <td align="right">Rp
                                                    {{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}
                                                </td>
                                            </tr>
                                        @endforeach --}}
                                        <tr>
                                            <td>PERSEDIAAN</td>
                                            <td align="right">Rp {{ number_format($ttl_persediaan, 0) }}</td>
                                        </tr>
                                        {{-- @foreach ($persediaan as $k)
                                            <tr x-transition x-show="open4">
                                                <td style="padding-left: 20px">
                                                    <a target="_blank"
                                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}</a>
                                                </td>
                                                <td align="right">Rp
                                                    {{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}
                                                </td>
                                            </tr>
                                        @endforeach --}}
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        @php
                                            $ttl_aktiva_lancar = $ttl_kas + $ttl_bank + $ttl_piutang + $ttl_persediaan;
                                        @endphp
                                        <tr>
                                            <td class="fw-bold">JUMLAH AKTIVA LANCAR</td>
                                            <td class="fw-bold" align="right">Rp
                                                {{ number_format($ttl_aktiva_lancar, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="fw-bold">
                                                AKTIVA TETAP
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PERALATAN</td>
                                            <td align="right">Rp
                                                {{ number_format($peralatan->debit + $peralatan->debit_saldo, 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PERALATAN GANTUNG
                                            </td>
                                            <td align="right">Rp
                                                {{ number_format($peralatan_gantung->debit + $peralatan_gantung->debit_saldo - $peralatan_gantung->kredit - $peralatan_gantung->kredit_saldo, 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PULLET GANTUNG</td>
                                            <td align="right">Rp
                                                {{ number_format($pullet_gantung->debit + $pullet_gantung->debit_saldo - $pullet_gantung->kredit - $pullet_gantung->kredit_saldo, 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>AKTIVA</td>
                                            <td align="right">Rp
                                                {{ number_format($aktiva->debit + $aktiva->debit_saldo, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>AKTIVA GANTUNG</td>
                                            <td align="right">Rp
                                                {{ number_format($aktiva_gantung->debit + $aktiva_gantung->debit_saldo - $aktiva_gantung->kredit - $aktiva_gantung->kredit_saldo, 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>AKUMULASI PENYUSUTAN PERALATAN(-)</td>
                                            <td align="right">(Rp
                                                {{ number_format($akumulasi_peralatan->kredit + $akumulasi_peralatan->kredit_saldo) }}
                                                )
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>AKUMULASI PENYUSUTAN AKTIVA(-)</td>
                                            <td align="right">(Rp
                                                {{ number_format($akumulasi->kredit + $akumulasi->kredit_saldo) }})
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        @php
                                            $total_aktiva = $peralatan->debit + $peralatan->debit_saldo + ($aktiva->debit + $aktiva->debit_saldo) + ($peralatan_gantung->debit + $peralatan_gantung->debit_saldo - $peralatan_gantung->kredit - $peralatan_gantung->kredit_saldo) + ($pullet_gantung->debit + $pullet_gantung->debit_saldo - $pullet_gantung->kredit - $pullet_gantung->kredit_saldo) + ($aktiva_gantung->debit + $aktiva_gantung->debit_saldo - $aktiva_gantung->kredit - $aktiva_gantung->kredit_saldo) - ($akumulasi->kredit + $akumulasi->kredit_saldo) - ($akumulasi_peralatan->kredit + $akumulasi_peralatan->kredit_saldo);
                                        @endphp
                                        <tr>
                                            <td class="fw-bold">NILAI BUKU</td>
                                            <td class="fw-bold" align="right">Rp
                                                {{ number_format($total_aktiva, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">JUMLAH AKTIVA</td>
                                            <td class="fw-bold" align="right">Rp
                                                {{ number_format($ttl_aktiva_lancar + $total_aktiva, 0) }}
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <div class="col-6">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th colspan="2" width="50%" class="dhead text-center">PASSIVA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>

                                            <td colspan="2" class="fw-bold">
                                                HUTANG
                                            </td>
                                        </tr>
                                        @php
                                            $total2 = 0;
                                        @endphp
                                        @foreach ($hutang as $h)
                                            @php
                                                $total2 += $h->kredit + $h->kredit_saldo - ($h->debit + $h->debit_saldo);
                                            @endphp
                                            <tr>
                                                <td>{{ strtoupper($h->nm_akun) }}</td>
                                                <td align="right">Rp
                                                    {{ number_format($h->kredit + $h->kredit_saldo - ($h->debit + $h->debit_saldo), 0) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>

                                        <tr>
                                            <td class="fw-bold">JUMLAH KEWAJIBAN LANCAR</td>
                                            <td class="fw-bold" align="right">Rp {{ number_format($total2, 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="fw-bold">
                                                EKUITAS
                                            </td>
                                        </tr>
                                        @php
                                            $total4 = 0;
                                        @endphp
                                        @foreach ($ekuitas as $h)
                                            @php
                                                $total4 += $h->kredit + $h->kredit_saldo - ($h->debit + $h->debit_saldo);
                                            @endphp
                                            <tr>
                                                <td>{{ strtoupper($h->nm_akun) }}</td>
                                                <td align="right">Rp
                                                    {{ number_format($h->kredit + $h->kredit_saldo - ($h->debit + $h->debit_saldo), 0) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td>{{ strtoupper($ekuitas2->nm_akun) }}</td>
                                            @php
                                                $laba_berjalan_sebelum_penutup = $laba_pendapatan->pendapatan - $laba_biaya->biaya;

                                            @endphp
                                            <td align="right">Rp
                                                {{ number_format($ekuitas2->kredit + $ekuitas2->kredit_saldo - ($ekuitas2->debit + $ekuitas2->debit_saldo) + $laba_berjalan_sebelum_penutup, 0) }}
                                            </td>
                                            @php
                                                $ttl_laba_berjalan = $ekuitas2->kredit + $ekuitas2->kredit_saldo - ($ekuitas2->debit + $ekuitas2->debit_saldo) + $laba_berjalan_sebelum_penutup;
                                            @endphp
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">TOTAL EKUITAS</td>
                                            <td class="fw-bold" align="right">Rp
                                                {{ number_format($total4 + $ttl_laba_berjalan, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">JUMLAH PASSIVA</td>
                                            <td class="fw-bold" align="right">Rp
                                                {{ number_format($total2 + $total4 + $ttl_laba_berjalan, 0) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

<script>
    window.print()
</script>

</html>
