<x-theme.app title="{{ $title }}" table="Y" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <style>
            .freeze-cell1_th {
                position: sticky;
                z-index: 30;
                background-color: #F2F7FF;
                top: 0;
                left: 0;
            }
        </style>

        <div class="row">
            <div class="col-lg-12">
                @include('budget.nav')
                <br>
                <br>
            </div>
            <div class="col-lg-6">
                <h5 class="float-start mt-1">Cashflow</h5>
            </div>
        </div>
        <h6 class="float-start mt-1">Periode : 1 Januari {{ $thn }} sampai 31 Desember {{ $thn }} </h6>
        <div class="row justify-content-end">
            <div class="col-lg-12">
                <x-theme.button modal="Y" idModal="view" icon="fa-calendar-week" addClass="float-end"
                    teks="View" />
                <form action="" method="get">
                    <x-theme.modal title="Filter Tahun" size="modal-sm" idModal="view">
                        <div class="row">
                            <div class="col-lg-12 mt-2">
                                <label for="">Tahun</label>
                                <select name="tahun" id="" class="selectView tahun">
                                    @foreach ($tahun as $t)
                                        <option value="{{ $t->tahun }}" {{ $thn == $t->tahun ? 'SELECTED' : '' }}>
                                            {{ $t->tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </x-theme.modal>
                </form>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        @php
            $tahun2 = '2023';

            if (is_array($data) && !empty($data)) {
                $totalsPerMonth = array_fill(0, count(array_keys(reset($data))), 0);
                $total_seluruh = 0;

                foreach ($data as $akun => $months) {
                    $totalPerAkun = 0;

                    foreach ($months as $month => $nominal) {
                        $totalPerAkun += $nominal;
                        $totalsPerMonth[$month] = ($totalsPerMonth[$month] ?? 0) + $nominal;
                    }
                    $total_seluruh += $totalPerAkun;
                }

                // Lanjutan kode Anda...
            } else {
                // Handle kasus di mana $data bukan array atau array kosong
                $total_seluruh = 0;
            }

            if (is_array($data2) && !empty($data2)) {
                $totalsPerMonth2 = array_fill(0, count(array_keys(reset($data2))), 0);
                $total_seluruh2 = 0;

                foreach ($data2 as $akun => $months) {
                    $totalPerAkun2 = 0;
                    $id_akun4 = ['19', '103'];
                    $id_buku = ['7', '14'];
                    foreach ($months as $month => $nominal) {
                        $totalPerAkun2 += $nominal;
                        $totalsPerMonth2[$month] = ($totalsPerMonth2[$month] ?? 0) + $nominal;
                    }
                    $total_seluruh2 += $totalPerAkun2;
                }
            } else {
                foreach ($months as $month => $nominal) {
                    $totalsPerMonth2[$month] = 0;
                }
                $total_seluruh2 = 0;
            }

            if (is_array($data3) && !empty($data3)) {
                $totalsPerMonth3 = array_fill(0, count(array_keys(reset($data3))), 0);
                $total_seluruh3 = 0;

                foreach ($data3 as $akun => $months) {
                    $totalPerAkun3 = 0;
                    foreach ($months as $month => $nominal) {
                        $totalPerAkun3 += $nominal;
                        $totalsPerMonth3[$month] = ($totalsPerMonth3[$month] ?? 0) + $nominal;
                    }
                    $total_seluruh3 += $totalPerAkun3;
                }
            } else {
                foreach ($months as $month => $nominal) {
                    $totalsPerMonth3[$month] = 0;
                }
                $total_seluruh3 = 0;
            }

            if (is_array($data4) && !empty($data4)) {
                $totalsPerMonth4 = array_fill(0, count(array_keys(reset($data4))), 0);
                $total_seluruh4 = 0;
                foreach ($data4 as $akun => $months) {
                    $totalPerAkun4 = 0;
                    foreach ($months as $month => $nominal) {
                        $totalPerAkun4 += $nominal;
                        $totalsPerMonth4[$month] = ($totalsPerMonth4[$month] ?? 0) + $nominal;
                    }
                    $total_seluruh4 += $totalPerAkun4;
                }
            } else {
                foreach ($months as $month => $nominal) {
                    $totalsPerMonth4[$month] = 0;
                }
                $total_seluruh4 = 0;
            }

            if (is_array($data5) && !empty($data5)) {
                $totalsPerMonth5 = array_fill(0, count(array_keys(reset($data5))), 0);
                $total_seluruh5 = 0;

                foreach ($data5 as $akun => $months) {
                    $totalPerAkun5 = 0;
                    foreach ($months as $month => $nominal) {
                        $totalPerAkun5 += $nominal;
                        $totalsPerMonth5[$month] = ($totalsPerMonth5[$month] ?? 0) + $nominal;
                    }
                    $total_seluruh5 += $totalPerAkun5;
                }
            } else {
                $total_seluruh5 = 0;
            }

            if (is_array($data6) && !empty($data6)) {
                $totalsPerMonth6 = array_fill(0, count(array_keys(reset($data6))), 0);
                $total_seluruh6 = 0;

                foreach ($data6 as $akun => $months) {
                    $totalPerAkun6 = 0;

                    foreach ($months as $month => $nominal) {
                        $totalPerAkun6 += $nominal;
                        $totalsPerMonth6[$month] = ($totalsPerMonth6[$month] ?? 0) + $nominal;
                    }
                    $total_seluruh6 += $totalPerAkun6;
                }
            } else {
                foreach ($months as $month => $nominal) {
                    $totalsPerMonth6[$month] = 0;
                }
                $total_seluruh6 = 0;
            }

            if (is_array($data7) && !empty($data7)) {
                $totalsPerMonth7 = array_fill(0, count(array_keys(reset($data7))), 0);
                $total_seluruh7 = 0;
                foreach ($data7 as $akun => $months) {
                    $totalPerAkun7 = 0;
                    foreach ($months as $month => $nominal) {
                        $totalPerAkun7 += $nominal;
                        $totalsPerMonth7[$month] = ($totalsPerMonth7[$month] ?? 0) + $nominal;
                    }
                    $total_seluruh7 += $totalPerAkun7;
                }
            } else {
                foreach ($months as $month => $nominal) {
                    $totalsPerMonth7[$month] = 0;
                }
                $total_seluruh7 = 0;
            }

            if (is_array($data8) && !empty($data8)) {
                $totalsPerMonth8 = array_fill(0, count(array_keys(reset($data8))), 0);
                $total_seluruh8 = 0;

                foreach ($data8 as $akun => $months) {
                    $totalPerAkun8 = 0;

                    foreach ($months as $month => $nominal) {
                        $totalPerAkun8 += $nominal;
                        $totalsPerMonth8[$month] = ($totalsPerMonth8[$month] ?? 0) + $nominal;
                    }
                    $total_seluruh8 += $totalPerAkun8;
                }
            } else {
                foreach ($months as $month => $nominal) {
                    $totalsPerMonth8[$month] = 0;
                }
                $total_seluruh8 = 0;
            }

        @endphp


        <div class="row">
            <table class="table table-bordered" x-data="{
                open_pendapatan: false,
                open_hutang: false,
                open_biaya_cost: false,
                open_biaya_proyek: false,
            
                open_penjualan: false,
                open_piutang: false,
                open_bank: false,
                open_bayar: false,
                open_admin: false,
            }">
                <thead>
                    <tr>
                        <th class="dhead freeze-call1_th">Saldo Tahun Lalu</th>
                        <th class="dhead freeze-call1_th">Saldo Rata2</th>
                        <th class="dhead freeze-cell1_th">Akun</th>
                        @foreach (array_keys(reset($data)) as $month)
                            <th class="dhead text-end freeze-cell1_th">{{ $month }}</th>
                        @endforeach
                        <th class="dhead text-end freeze-cell1_th">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold text-end">
                            {{ number_format($ttl1 + $ttl2 + $ttl3 - $ttl4, 0) }}
                        </td>
                        <td class="fw-bold text-end">
                            {{ number_format($ttl1 + $ttl2 + $ttl3 - $ttl4 == 0 ? 0 : ($ttl1 + $ttl2 + $ttl3 - $ttl4) / 12, 0) }}
                        </td>
                        <td class="fw-bold">Uang Masuk <a type="button" class="float-end"
                                @click="open_pendapatan = ! open_pendapatan"><i class="fas fa-caret-down"></i></a>
                        </td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end">
                                {{ number_format($totalsPerMonth[$month] + $totalsPerMonth5[$month] + $totalsPerMonth6[$month] - $totalsPerMonth8[$month], 0) }}
                            </td>
                        @endforeach

                        <td class="fw-bold text-end">
                            {{ number_format($ttl1 + $ttl2 + $ttl3 - $ttl4 + $total_seluruh + $total_seluruh5 + $total_seluruh6 - $total_seluruh8, 0) }}
                        </td>
                    </tr>
                    <tr x-show="open_pendapatan">
                        <td class="text-end">{{ number_format($ttl1, 0) }}</td>
                        <td class="text-end">{{ number_format($ttl1 == 0 ? 0 : $ttl1 / 12, 0) }}</td>
                        <td class="fw-bold">&nbsp; &nbsp;Penjualan cash <a type="button" class="float-end"
                                @click="open_penjualan = ! open_penjualan"><i class="fas fa-caret-down"></i></a>
                        </td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class=" text-end">{{ number_format($totalsPerMonth[$month], 0) }}</td>
                        @endforeach
                        <td class=" text-end">{{ number_format($total_seluruh + $ttl1, 0) }}</td>
                    </tr>
                    @foreach ($data as $akun => $months)
                        <tr x-show="open_penjualan && open_pendapatan">
                            <td></td>
                            <td></td>
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                {{ $nm_akun->nm_akun }}
                            </td>
                            @php
                                $totalPerAkun = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">

                                    @php
                                        $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                        $tgl2 = date('Y-m-t', strtotime($tgl1));
                                    @endphp
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr x-show="open_pendapatan">
                        <td class="text-end">{{ number_format($ttl2, 0) }}</td>
                        <td class="text-end">{{ number_format($ttl2 == 0 ? 0 : $ttl2 / 12, 0) }}</td>
                        <td class="fw-bold">&nbsp; &nbsp;Piutang dibayar <a type="button" class="float-end"
                                @click="open_piutang = ! open_piutang"><i class="fas fa-caret-down"></i></a>
                        </td>
                        @foreach (array_keys(reset($data5)) as $month)
                            <td class=" text-end">{{ number_format($totalsPerMonth5[$month], 0) }}</td>
                        @endforeach
                        <td class=" text-end">{{ number_format($total_seluruh5 + $ttl2, 0) }}</td>
                    </tr>
                    @foreach ($data5 as $akun => $months)
                        <tr x-show="open_piutang && open_pendapatan">
                            <td></td>
                            <td></td>
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                &nbsp; &nbsp;&nbsp;{{ $nm_akun->nm_akun }}
                            </td>
                            @php
                                $totalPerAkun = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">

                                    @php
                                        $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                        $tgl2 = date('Y-m-t', strtotime($tgl1));
                                    @endphp
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun, 0) }}</td>
                        </tr>
                    @endforeach

                    <tr x-show="open_pendapatan">
                        <td class="text-end">{{ number_format($ttl3, 0) }}</td>
                        <td class="text-end">{{ number_format($ttl3 == 0 ? 0 : $ttl3 / 12, 0) }}</td>
                        <td class="fw-bold">&nbsp; &nbsp;Bunga Bank <a type="button" class="float-end"
                                @click="open_bank = ! open_bank"><i class="fas fa-caret-down"></i></a>
                        </td>
                        @if (is_array($data6) && !empty($data6))
                            @foreach (array_keys(reset($data6)) as $month)
                                <td class="text-end">{{ number_format($totalsPerMonth6[$month] ?? 0, 0) }}</td>
                            @endforeach
                        @else
                            @foreach (array_keys(reset($data)) as $month)
                                <td class="text-end">0</td>
                            @endforeach
                        @endif
                        <td class=" text-end">{{ number_format($total_seluruh6 + $ttl3, 0) }}</td>
                    </tr>
                    @foreach ($data6 as $akun => $months)
                        <tr x-show="open_bank && open_pendapatan">
                            <td></td>
                            <td></td>
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                &nbsp; &nbsp;&nbsp;{{ $nm_akun->nm_akun }}
                            </td>
                            @php
                                $totalPerAkun6 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">

                                    @php
                                        $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                        $tgl2 = date('Y-m-t', strtotime($tgl1));
                                    @endphp
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun6 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun6, 0) }}</td>
                        </tr>
                    @endforeach

                    <tr x-show="open_pendapatan">
                        <td class="text-end">{{ number_format($ttl4, 0) }}</td>
                        <td class="text-end">{{ number_format($ttl4 == 0 ? 0 : $ttl4 / 12, 0) }}</td>
                        <td class="fw-bold">&nbsp; &nbsp;Biaya Admin <a type="button" class="float-end"
                                @click="open_admin = ! open_admin"><i class="fas fa-caret-down"></i></a>
                        </td>
                        @if (is_array($data8) && !empty($data8))
                            @foreach (array_keys(reset($data8)) as $month)
                                <td class=" text-end">{{ number_format($totalsPerMonth8[$month], 0) }}</td>
                            @endforeach
                        @else
                            @foreach (array_keys(reset($data)) as $month)
                                <td class="text-end">0</td>
                            @endforeach

                        @endif
                        <td class=" text-end">{{ number_format($total_seluruh8 + $ttl4, 0) }}</td>
                    </tr>
                    @foreach ($data8 as $akun => $months)
                        <tr x-show="open_admin && open_pendapatan">
                            <td></td>
                            <td></td>
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                &nbsp; &nbsp;&nbsp;{{ $nm_akun->nm_akun }}
                            </td>
                            @php
                                $totalPerAkun8 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">

                                    @php
                                        $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                        $tgl2 = date('Y-m-t', strtotime($tgl1));
                                    @endphp
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun8 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun8, 0) }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td class="fw-bold text-end">
                            {{ number_format($ttl5, 0) }}
                        </td>
                        <td class="fw-bold text-end">
                            {{ number_format($ttl5 == 0 ? 0 : $ttl5 / 12, 0) }}
                        </td>
                        <td class="fw-bold">Hutang <a type="button" class=" float-end"
                                @click="open_hutang = ! open_hutang"><i class="fas fa-caret-down"></i></a>
                        </td>
                        @if (is_array($data2) && !empty($data2))
                            @foreach (array_keys(reset($data2)) as $month)
                                <td class="fw-bold text-end">{{ number_format($totalsPerMonth2[$month], 0) }}</td>
                            @endforeach
                        @else
                            @foreach (array_keys(reset($data)) as $month)
                                <td class="text-end">0</td>
                            @endforeach
                        @endif
                        <td class="fw-bold text-end">{{ number_format($total_seluruh2 + $ttl5, 0) }}</td>
                    </tr>

                    @foreach ($data2 as $akun => $months)
                        <tr x-show="open_hutang ">
                            <td></td>
                            <td></td>
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                &nbsp; &nbsp;&nbsp;{{ $nm_akun->nm_akun }}
                            </td>
                            @php
                                $totalPerAkun2 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">

                                    @php
                                        $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                        $tgl2 = date('Y-m-t', strtotime($tgl1));
                                    @endphp
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun2 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun2, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold text-end dhead">
                            {{ number_format($ttl1 + $ttl2 + $ttl3 - $ttl4 + $ttl5, 0) }}</td>
                        <td class="fw-bold text-end dhead">
                            {{ number_format($ttl1 + $ttl2 + $ttl3 - $ttl4 + $ttl5 == 0 ? 0 : $ttl1 + $ttl2 + $ttl3 - $ttl4 + $ttl5, 0) }}
                        </td>
                        <td class="fw-bold dhead">Total Pemasukan</td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end dhead">
                                {{ number_format($totalsPerMonth[$month] + $totalsPerMonth2[$month] + $totalsPerMonth5[$month] + $totalsPerMonth6[$month] - $totalsPerMonth8[$month], 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end dhead">
                            {{ number_format($ttl1 + $ttl2 + $ttl3 - $ttl4 + $ttl5 + $total_seluruh + $total_seluruh2 + $total_seluruh5 + $total_seluruh6 - $total_seluruh8, 0) }}
                        </td>
                    </tr>



                    <tr>
                        <td class="text-end fw-bold">{{ number_format($ttl6, 0) }}</td>
                        <td class="text-end fw-bold">{{ number_format($ttl6 == 0 ? 0 : $ttl6 / 12, 0) }}</td>
                        <td class="fw-bold">Bayar Hutang <a type="button" class="float-end"
                                @click="open_bayar = ! open_bayar"><i class="fas fa-caret-down"></i></a>
                        </td>
                        @if (is_array($data7) && !empty($data7))
                            @foreach (array_keys(reset($data7)) as $month)
                                <td class="fw-bold text-end">{{ number_format($totalsPerMonth7[$month], 0) }}</td>
                            @endforeach
                        @else
                            @foreach (array_keys(reset($data)) as $month)
                                <td class="text-end">0</td>
                            @endforeach
                        @endif

                        <td class="fw-bold text-end">{{ number_format($total_seluruh7 + $ttl6, 0) }}</td>
                    </tr>
                    @foreach ($data7 as $akun => $months)
                        <tr x-show="open_bayar">
                            <td></td>
                            <td></td>
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                {{ $nm_akun->nm_akun }}
                            </td>
                            @php
                                $totalPerAkun3 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">

                                    @php
                                        $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                        $tgl2 = date('Y-m-t', strtotime($tgl1));
                                    @endphp
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun7 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun7, 0) }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td class="text-end fw-bold">{{ number_format($ttl7, 0) }}</td>
                        <td class="text-end fw-bold">{{ number_format($ttl7 == 0 ? 0 : $ttl7 / 12, 0) }}</td>
                        <td class="fw-bold">Cost <a type="button" class="float-end"
                                @click="open_biaya_cost = ! open_biaya_cost"><i class="fas fa-caret-down"></i></a>
                        </td>
                        @if (is_array($data3) && !empty($data3))
                            @foreach (array_keys(reset($data3)) as $month)
                                <td class="fw-bold text-end">{{ number_format($totalsPerMonth3[$month], 0) }}</td>
                            @endforeach
                        @else
                            @foreach (array_keys(reset($data)) as $month)
                                <td class="text-end">0</td>
                            @endforeach
                        @endif

                        <td class="fw-bold text-end">{{ number_format($total_seluruh3 + $ttl7, 0) }}</td>
                    </tr>
                    @foreach ($data3 as $akun => $months)
                        <tr x-show="open_biaya_cost">
                            <td></td>
                            <td></td>
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                {{ $nm_akun->nm_akun }}
                            </td>
                            @php
                                $totalPerAkun3 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">

                                    @php
                                        $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                        $tgl2 = date('Y-m-t', strtotime($tgl1));
                                    @endphp
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun3 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun3, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="text-end fw-bold">{{ number_format($ttl8, 0) }}</td>
                        <td class="text-end fw-bold">{{ number_format($ttl8 == 0 ? 0 : $ttl8, 0) }}</td>
                        <td class="fw-bold">Proyek <a class="float-end"
                                @click="open_biaya_proyek = ! open_biaya_proyek"><i class="fas fa-caret-down"></i></a>
                        </td>
                        @if (is_array($data4) && !empty($data4))
                            @foreach (array_keys(reset($data4)) as $month)
                                <td class="fw-bold text-end">{{ number_format($totalsPerMonth4[$month], 0) }}</td>
                            @endforeach
                        @else
                            @foreach (array_keys(reset($data)) as $month)
                                <td class="text-end">0</td>
                            @endforeach
                        @endif

                        <td class="fw-bold text-end">{{ number_format($total_seluruh4 + $ttl8, 0) }}</td>
                    </tr>
                    @foreach ($data4 as $akun => $months)
                        <tr x-show="open_biaya_proyek">
                            <td></td>
                            <td></td>
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                {{ $nm_akun->nm_akun }}
                            </td>
                            @php
                                $totalPerAkun4 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">

                                    @php
                                        $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                        $tgl2 = date('Y-m-t', strtotime($tgl1));
                                    @endphp
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun4 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun4, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="text-end fw-bold dhead">{{ number_format($ttl6 + $ttl7 + $ttl8, 0) }}</td>
                        <td class="text-end fw-bold dhead">
                            {{ number_format($ttl6 + $ttl7 + $ttl8 == 0 ? 0 : ($ttl6 + $ttl7 + $ttl8) / 12, 0) }}</td>
                        <td class="fw-bold dhead">Total Pengeluaran</td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end dhead">
                                {{ number_format($totalsPerMonth3[$month] + $totalsPerMonth4[$month] + $totalsPerMonth7[$month], 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end dhead">
                            {{ number_format($ttl6 + $ttl7 + $ttl8 + $total_seluruh3 + $total_seluruh4 + $total_seluruh7, 0) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-end fw-bold dhead">
                            {{ number_format($ttl1 + $ttl2 + $ttl3 - $ttl4 + $ttl5 - ($ttl6 + $ttl7 + $ttl8), 0) }}
                        </td>
                        <td class="text-end fw-bold dhead">
                            {{ number_format(
                                $ttl1 + $ttl2 + $ttl3 - $ttl4 + $ttl5 - ($ttl6 + $ttl7 + $ttl8) == 0
                                    ? 0
                                    : ($ttl1 + $ttl2 + $ttl3 - $ttl4 + $ttl5 - ($ttl6 + $ttl7 + $ttl8)) / 12,
                                0,
                            ) }}
                        </td>
                        <td class="fw-bold dhead">Net Cashflow</td>
                        @php
                            $net_cashflow = 0;
                            $saldo_tahun_lalu = $ttl1 + $ttl2 + $ttl3 - $ttl4 + $ttl5 - ($ttl6 + $ttl7 + $ttl8);
                        @endphp
                        @foreach (array_keys(reset($data)) as $month)
                            @php
                                $net_cashflow += $totalsPerMonth[$month] + $totalsPerMonth2[$month] + $totalsPerMonth5[$month] - $totalsPerMonth3[$month] + $totalsPerMonth6[$month] - $totalsPerMonth4[$month] - $totalsPerMonth7[$month] - $totalsPerMonth8[$month];
                            @endphp
                            <td class="fw-bold text-end dhead">
                                {{ number_format($saldo_tahun_lalu + $net_cashflow, 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end dhead">
                            {{ number_format($saldo_tahun_lalu + $total_seluruh + $total_seluruh2 + $total_seluruh5 + $total_seluruh6 - $total_seluruh8 - ($total_seluruh3 + $total_seluruh4 + $total_seluruh7), 0) }}
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>


        {{-- --}}
    </x-slot>
    @section('scripts')
    @endsection
</x-theme.app>
