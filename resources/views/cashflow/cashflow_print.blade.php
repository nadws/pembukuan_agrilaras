<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>{{ $title }}</title>
</head>

<body>
    <style>
        .dhead {
            background-color: #435EBE !important;
            color: white;
        }

        .dhead2 {
            background-color: #f01e2c !important;
            color: white;
        }

        body {
            font-size: 12px;
        }

        @media print {
            .page-break {
                page-break-before: always;
            }
        }
    </style>

    <div class="row ">
        <div class="col-12">
            <h3 class="text-center">Cashflow </h3>
            <h4 class="text-center">Tanggal : {{ tanggal($tgl1) }} ~ {{ tanggal($tgl2) }}</h4>
        </div>
        <div class="col-6 ">
            @php
                $total_pi = 0;

                foreach ($piutang as $p) {
                    $total_pi += $p->debit - $p->kredit;
                }
                $total_pen = 0;
                foreach ($penjualan as $p) {
                    $total_pen += $p->kredit;
                }
                $ttl_piut = 0;
                foreach ($piutang2 as $u) {
                    $ttl_piut += $u->debit - $u->kredit;
                }

                $ttl_cost_balance = 0;
                foreach ($uangbiayacoshbalance as $u) {
                    $ttl_cost_balance += $u->debit - $u->kredit;
                }
                $ttl_proyek_balance = 0;
                foreach ($uangbiayaproyekbalance as $u) {
                    $ttl_proyek_balance += $u->debit - $u->kredit;
                }
            @endphp
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th class="dhead2">Akun</th>
                        <th class="dhead2">Rupiah</th>
                    </tr>
                    {{-- <tr>
                        <td class="fw-bold"><a target="_blank"
                                href="{{ route('jurnal.add', ['id_buku' => '7', 'id_akun' => $hutang_herry->id_akun]) }}">{{ $hutang_herry->nm_akun }}</a>
                        </td>
                        <td class="fw-bold" align="right"><a target="_blank"
                                href="{{ route('summary_buku_besar.detail', ['id_akun' => $hutang_herry->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">Rp
                                {{ number_format($hutang_herry->kredit, 0) }}</a>
                        </td>
                    </tr> --}}
                    <tr>
                        <td class="fw-bold">Piutang Bulan Lalu</td>
                        <td class="text-end fw-bold">Rp {{ number_format($total_pi + $kerugian->debit, 0) }}</td>
                    </tr>
                    @php
                        // $total_pi = 0;
                        $total_pe = 0;
                    @endphp
                    @foreach ($piutang as $p)
                        {{-- @php
                            $total_pi += $p->debit - $p->kredit;
                        @endphp --}}
                        <tr>
                            <td>{{ ucwords(strtolower($p->nm_akun)) }} ({{ date('F Y', strtotime($tgl_back)) }}) </td>
                            <td align="right">Rp {{ number_format($p->debit - $p->kredit, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>Biaya Kerugian Piutang</td>
                        <td align="right">Rp. {{ number_format($kerugian->debit, 0) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Total</td>
                        <td class="fw-bold" align="right">RP {{ number_format($total_pi + $kerugian->debit, 0) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Penjualan</td>
                        <td class="text-end fw-bold">Rp {{ number_format($total_pen, 0) }}</td>
                    </tr>
                    @foreach ($penjualan as $p)
                        @php
                            $total_pe += $p->kredit;
                        @endphp
                        <tr>
                            <td>{{ ucwords(strtolower($p->nm_akun)) }} </td>
                            <td align="right">Rp {{ number_format($p->kredit, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold">Total</td>
                        <td class="fw-bold" align="right">Rp {{ number_format($total_pe, 0) }}</td>
                    </tr>

                    {{-- dasds --}}
                    <tr>
                        <td class="fw-bold">Piutang Bulan Ini</td>
                        <td class="text-end fw-bold">Rp {{ number_format($ttl_piut + $kerugianBulanIni->debit, 0) }}
                        </td>
                    </tr>
                    @php
                        $t_piutang = 0;
                    @endphp
                    @foreach ($piutang2 as $u)
                        @php
                            $t_piutang += $u->debit - $u->kredit;
                        @endphp
                        <tr>
                            <td>{{ ucwords(strtolower($u->nm_akun)) }} ({{ date('F Y', strtotime($tgl2)) }})</td>
                            <td align="right">Rp. {{ number_format($u->debit - $u->kredit, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>Biaya Kerugian Piutang</td>
                        <td align="right">Rp. {{ number_format($kerugianBulanIni->debit, 0) }}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <th style="text-align: right">Rp. {{ number_format($t_piutang + $kerugianBulanIni->debit, 0) }}

                        </th>
                    </tr>
                    {{-- <tr>
                        <td colspan="2" class="fw-bold">&nbsp;</td>
                    </tr> --}}
                    {{-- <tr>
                        <td class="fw-bold">Grand Total</td>
                        <td class="fw-bold" align="right">Rp {{number_format(($total_pi + $total_pe) - ($t_piutang +
                            $kerugian->debit),0) }}
                        </td>
                    </tr> --}}
                </tbody>
            </table>
        </div>

        <div class="col-6">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="dhead2">Akun</th>
                        <th style="text-align: right" class="dhead2">Rupiah</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="fw-bold">Uang Ditarik (Piutang & Penjualan yg
                            ditarik)</td>
                    </tr>
                    @php
                        $t_uang = 0;

                    @endphp
                    @foreach ($uang as $u)
                        @php
                            $t_uang += $u->debit;
                        @endphp

                        <tr>
                            <td>{{ ucwords(strtolower($u->nm_akun)) }} </td>
                            <td align="right">Rp. {{ number_format($u->debit, 0) }} </td>
                        </tr>
                    @endforeach
                    {{-- <tr>
                        <th>Total</th>
                        <th style="text-align: right">{{number_format($t_uang,0)}}</th>
                    </tr> --}}

                </thead>
            </table>
        </div>

        <div class="col-6">
            <table class="table table-bordered">
                <tr>
                    <td class="fw-bold bg-warning ">Total </td>
                    <td class="fw-bold bg-warning {{ $t_uang - ($total_pe + $total_pi + $kerugian->debit - ($t_piutang + $kerugianBulanIni->debit)) != 0 ? 'text-danger' : '' }}"
                        align="right">Rp
                        {{ number_format($total_pe + $total_pi + $kerugian->debit - ($t_piutang + $kerugianBulanIni->debit), 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">Bunga Bank</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($bunga_bank->kredit ?? 0, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">Biaya Administrasi</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($biaya_admin->debit ?? 0, 0) }}
                    </td>
                </tr>
                <tr>
                    @php
                        $bg_bank = $bunga_bank->kredit ?? 0;
                    @endphp
                    <td class="fw-bold">Grand Total</td>
                    <td class="fw-bold" align="right">Rp
                        {{ number_format($total_pi + $kerugian->debit + $kerugianBulanIni->debit + $total_pe - $t_piutang - $biaya_admin->debit + $bg_bank, 0) }}
                    </td>
                </tr>
                {{-- <tr>
                    <td class="fw-bold">&nbsp;</td>
                    <td class="fw-bold">&nbsp;</td>
                </tr>
                <tr>
                    <td class="fw-bold">Grand Total + Hutang</td>
                    <td class="fw-bold" align="right">Rp
                        {{ number_format($total_pi + $total_pe - ($t_piutang + $kerugian->debit) - $biaya_admin->debit + $hutang_herry->kredit, 0) }}
                    </td>
                </tr> --}}

            </table>
        </div>
        <div class="col-6">
            <table class="table table-bordered">
                <tr>
                    <td class="fw-bold bg-warning ">Total</td>
                    <td class="fw-bold bg-warning {{ $t_uang - ($total_pe + $total_pi + $kerugian->debit - ($t_piutang + $kerugianBulanIni->debit)) != 0 ? 'text-danger' : '' }}"
                        style="text-align: right">{{ number_format($t_uang, 0) }}</td>
                </tr>

                <tr>
                    <td class="fw-bold">Bunga Bank</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($bunga_bank->kredit ?? 0, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">Biaya Administrasi</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($biaya_admin->debit, 0) }}
                    </td>
                </tr>
                <tr>
                    @php
                        $bg_bank = $bunga_bank->kredit ?? 0;
                    @endphp
                    <td class="fw-bold">Grand Total</td>
                    <td class="fw-bold" align="right">Rp
                        {{ number_format($t_uang + $bg_bank - $biaya_admin->debit, 0) }}
                    </td>
                </tr>
            </table>
        </div>


    </div>
    <hr class="page-break">
    <div class="row ">
        <div class="col-12">
            <h3 class="text-center">Cashflow </h3>
            <h4 class="text-center">Tanggal : {{ tanggal($tgl1) }} ~ {{ tanggal($tgl2) }}</h4>
        </div>
        <div class="col-6 mt-2">
            @php
                $total_b = 0;
                $ttl_budget = 0;
            @endphp
            @foreach ($biaya as $b)
                @php
                    $total_b += $b->debit;
                @endphp
            @endforeach
            @php
                $total_b_p = 0;

            @endphp
            @foreach ($biaya_proyek as $b)
                @php
                    $total_b_p += $b->debit;
                @endphp
            @endforeach

            {{-- <input type="hidden" name="tgl" value="{{ $tgl1 }}"> --}}
            <table class="table table-bordered">
                <tbody>

                    <tr>
                        <th class="dhead2">Total Uang Keluar</th>
                        {{-- <th class="dhead2 text-end">Rp {{ number_format($ttl_budget, 0) }}</th> --}}
                        <th class="dhead2 text-end" style="white-space: nowrap">Rp
                            {{ number_format($total_b + $total_b_p, 0) }}
                        </th>
                    </tr>
                    <tr>
                        <td class="fw-bold">Biaya Cost</td>
                        <td class="text-end fw-bold">Rp {{ number_format($total_b, 0) }}</td>
                    </tr>
                    {{-- @foreach ($biaya as $b)
                        <tr>
                            <td>{{ ucwords(strtolower($b->nm_akun)) }}</td>
                            <td align="right">Rp {{ number_format($b->debit, 1) }}</td>
                        </tr>
                    @endforeach --}}
                    <tr>
                        <td class="fw-bold">Biaya Proyek</td>
                        <td class=" text-end fw-bold">Rp {{ number_format($total_b_p, 0) }} </td>
                    </tr>
                    @foreach ($biaya_proyek as $b)
                        <tr>
                            <td>{{ ucwords(strtolower($b->nm_akun)) }}</td>
                            <td align="right">Rp {{ number_format($b->debit, 0) }}</td>
                        </tr>
                        @php
                            $detail = DB::select("SELECT a.id_post_center, b.nm_post, sum(a.debit) as debit
                                    FROM jurnal as a 
                                    left join tb_post_center as b on b.id_post_center = a.id_post_center
                                    where a.id_akun ='$b->id_akun' and a.id_akun != '8' and a.tgl BETWEEN '$tgl1' and '$tgl2' and a.debit != '0'
                                    group by a.id_post_center;
                                ");
                        @endphp
                        @foreach ($detail as $d)
                            <tr>
                                <td style="padding-left: 20px">{{ $d->nm_post }}</td>
                                <td class="text-end">Rp {{ number_format($d->debit, 0) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    {{-- <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="fw-bold"><a target="_blank"
                                    href="{{ route('jurnal.add', ['id_buku' => '7', 'id_akun' => $hutang_herry->id_akun]) }}">{{ $hutang_herry->nm_akun }}</a>
                            </td>
                            <td class="fw-bold" align="right"><a target="_blank"
                                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $hutang_herry->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">Rp
                                    {{ number_format($hutang_herry->debit, 0) }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">&nbsp;</td>
                            <td class="fw-bold">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="fw-bold"></td>
                            <td class="fw-bold"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Total Uang Keluar + Hutang</a>
                            </td>
                            <td class="fw-bold" align="right">Rp
                                {{ number_format($hutang_herry->debit + $total_b + $total_b_p, 0) }}</a>
                            </td>
                        </tr> --}}
                </tbody>
            </table>

        </div>
        <div class="col-6 mt-2">

            @php
                $total_bi = 0;
                foreach ($uangbiayacosh as $b) {
                    $total_bi += $b->kredit;
                }
                $total_biproyek = 0;
                foreach ($uangbiayaproyek as $c) {
                    $total_biproyek += $c->kredit;
                }
            @endphp
            <table class="table table-bordered">
                <tbody>

                    <tr>
                        <th class="dhead2">Total Uang Keluar</th>
                        <th class="dhead2 text-end" style="white-space: nowrap">Rp
                            {{ number_format($total_bi + $total_biproyek, 0) }}</th>
                    </tr>
                    <tr>
                        <td class="fw-bold">Cost</td>
                        <td align="right " class="fw-bold">Rp {{ number_format($total_bi, 0) }}</td>
                    </tr>
                    @foreach ($uangbiayacosh as $b)
                        <tr>
                            <td>{{ ucwords(strtolower($b->nm_akun)) }}</td>
                            <td align="right">Rp {{ number_format($b->kredit, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold">Proyek</td>
                        <td align="right " class="fw-bold">Rp {{ number_format($total_biproyek, 0) }}</td>
                    </tr>
                    @foreach ($uangbiayaproyek as $b)
                        <tr>
                            <td>{{ ucwords(strtolower($b->nm_akun)) }}</td>
                            <td align="right">Rp {{ number_format($b->kredit, 0) }}</td>
                        </tr>
                    @endforeach
                    {{-- <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="fw-bold"><a href="#" onclick="event.preventDefault();"
                                class="tmbhakun_control me-3" kategori='6'>Cost Balance</a>
                            <button class="btn btn-primary btn-sm btn-buka float-end"
                                @click="openUangcostbalance = ! openUangcostbalance"> <i
                                    class="fas fa-caret-down"></i></button>
    
                        </td>
                        <td align="right">Rp {{ number_format($ttl_cost_balance, 1) }}</td>
                    </tr>
    
                    @foreach ($uangbiayacoshbalance as $b)
                        <tr x-show="openUangcostbalance">
                            <td><a target="_blank"
                                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $b->id_akun, 'tgl1' => '2022-12-31', 'tgl2' => $tgl2]) }}">{{ ucwords(strtolower($b->nm_akun)) }}</a>
                            </td>
                            <td align="right">Rp {{ number_format($b->debit - $b->kredit, 1) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold"><a href="#" onclick="event.preventDefault();"
                                class="tmbhakun_control me-3" kategori='6'>Proyek Balance</a>
                            <button class="btn btn-primary btn-sm btn-buka float-end"
                                @click="openUangproyekbalance = ! openUangproyekbalance"> <i
                                    class="fas fa-caret-down"></i></button>
    
                        </td>
                        <td align="right">Rp {{ number_format($ttl_proyek_balance, 1) }}</td>
                    </tr>
    
                    @foreach ($uangbiayaproyekbalance as $b)
                        <tr x-show="openUangproyekbalance">
                            <td><a target="_blank"
                                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $b->id_akun, 'tgl1' => '2022-12-31', 'tgl2' => $tgl2]) }}">{{ ucwords(strtolower($b->nm_akun)) }}</a>
                            </td>
                            <td align="right">Rp {{ number_format($b->debit - $b->kredit, 1) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold">Total Uang Keluar + Balance </a>
                        </td>
                        <td class="fw-bold" align="right">Rp
                            {{ number_format($total_bi + $total_biproyek + $ttl_cost_balance + $ttl_proyek_balance, 1) }}
                        </td>
                    </tr> --}}
                </tbody>
            </table>
        </div>
    </div>


    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script>
        window.print();
    </script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
    -->
</body>

</html>
