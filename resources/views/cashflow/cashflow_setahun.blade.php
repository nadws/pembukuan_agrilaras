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

            $totalsPerMonth2 = array_fill(0, count(array_keys(reset($data2))), 0);
            $total_seluruh2 = 0;

            foreach ($data2 as $akun => $months) {
                $totalPerAkun2 = 0;
                foreach ($months as $month => $nominal) {
                    $totalPerAkun2 += $nominal;
                    $totalsPerMonth2[$month] = ($totalsPerMonth2[$month] ?? 0) + $nominal;
                }
                $total_seluruh2 += $totalPerAkun2;
            }
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

            $totalsPerMonth4 = array_fill(0, count(array_keys(reset($data3))), 0);
            $total_seluruh4 = 0;
            foreach ($data4 as $akun => $months) {
                $totalPerAkun4 = 0;
                foreach ($months as $month => $nominal) {
                    $totalPerAkun4 += $nominal;
                    $totalsPerMonth4[$month] = ($totalsPerMonth4[$month] ?? 0) + $nominal;
                }
                $total_seluruh4 += $totalPerAkun3;
            }

            $totalsPerMonth5 = array_fill(0, count(array_keys(reset($data3))), 0);
            $total_seluruh5 = 0;
            foreach ($data5 as $akun => $months) {
                $totalPerAkun5 = 0;
                foreach ($months as $month => $nominal) {
                    $totalPerAkun5 += $nominal;
                    $totalsPerMonth5[$month] = ($totalsPerMonth5[$month] ?? 0) + $nominal;
                }
                $total_seluruh5 += $totalPerAkun5;
            }
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
            }">
                <thead>
                    <tr>
                        <th class="dhead freeze-cell1_th">Akun</th>
                        @foreach (array_keys(reset($data)) as $month)
                            <th class="dhead text-end freeze-cell1_th">{{ $month }}</th>
                        @endforeach
                        <th class="dhead text-end freeze-cell1_th">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">Uang Masuk <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_pendapatan = ! open_pendapatan"><i class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end">
                                {{ number_format($totalsPerMonth[$month] + $totalsPerMonth5[$month] + $totalsPerMonth2[$month] + $totalsPerMonth6[$month], 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end">
                            {{ number_format($total_seluruh + $total_seluruh2 + $total_seluruh5, 0) }}</td>
                    </tr>
                    <tr x-show="open_pendapatan">
                        <td class="fw-bold">&nbsp; &nbsp;Penjualan <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_penjualan = ! open_penjualan"><i class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class=" text-end">{{ number_format($totalsPerMonth[$month], 0) }}</td>
                        @endforeach
                        <td class=" text-end">{{ number_format($total_seluruh, 0) }}</td>
                    </tr>
                    @foreach ($data as $akun => $months)
                        <tr x-show="open_penjualan && open_pendapatan">
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')
                                        ->where('id_akun', $akun)
                                        ->first();
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
                        <td class="fw-bold">&nbsp; &nbsp;Piutang <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_piutang = ! open_piutang"><i class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data5)) as $month)
                            <td class=" text-end">{{ number_format($totalsPerMonth5[$month], 0) }}</td>
                        @endforeach
                        <td class=" text-end">{{ number_format($total_seluruh5, 0) }}</td>
                    </tr>
                    @foreach ($data5 as $akun => $months)
                        <tr x-show="open_piutang && open_pendapatan">
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')
                                        ->where('id_akun', $akun)
                                        ->first();
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
                        <td class="fw-bold">&nbsp; &nbsp;Bunga Bank <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end" @click="open_bank = ! open_bank"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data6)) as $month)
                            <td class=" text-end">{{ number_format($totalsPerMonth6[$month], 0) }}</td>
                        @endforeach
                        <td class=" text-end">{{ number_format($total_seluruh6, 0) }}</td>
                    </tr>
                    @foreach ($data6 as $akun => $months)
                        <tr x-show="open_bank && open_pendapatan">
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')
                                        ->where('id_akun', $akun)
                                        ->first();
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
                        <td class="fw-bold">&nbsp; &nbsp;Hutang <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_hutang = ! open_hutang"><i class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data2)) as $month)
                            <td class=" text-end">{{ number_format($totalsPerMonth2[$month], 0) }}</td>
                        @endforeach
                        <td class=" text-end">{{ number_format($total_seluruh2, 0) }}</td>
                    </tr>
                    @foreach ($data2 as $akun => $months)
                        <tr x-show="open_hutang && open_pendapatan">
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')
                                        ->where('id_akun', $akun)
                                        ->first();
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
                        <td class="fw-bold dhead">Total Pemasukan</td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end dhead">
                                {{ number_format($totalsPerMonth[$month] + $totalsPerMonth2[$month] + $totalsPerMonth5[$month] + $totalsPerMonth6[$month], 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end dhead">
                            {{ number_format($total_seluruh + $total_seluruh2 + $total_seluruh5 + $total_seluruh6, 0) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Pengeluaran Cost <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_biaya_cost = ! open_biaya_cost"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data3)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth3[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh3, 0) }}</td>
                    </tr>
                    @foreach ($data3 as $akun => $months)
                        <tr x-show="open_biaya_cost">
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')
                                        ->where('id_akun', $akun)
                                        ->first();
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
                        <td class="fw-bold">Pengeluaran Proyek <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_biaya_proyek = ! open_biaya_proyek"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data4)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth4[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh4, 0) }}</td>
                    </tr>
                    @foreach ($data4 as $akun => $months)
                        <tr x-show="open_biaya_proyek">
                            <td>
                                @php
                                    $nm_akun = DB::table('akun')
                                        ->where('id_akun', $akun)
                                        ->first();
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
                        <td class="fw-bold dhead">Total Pengeluaran</td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end dhead">
                                {{ number_format($totalsPerMonth3[$month] + $totalsPerMonth4[$month], 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end dhead">
                            {{ number_format($total_seluruh3 + $total_seluruh4, 0) }}</td>
                    </tr>
                    {{-- <tr>
                        <td class="fw-bold dhead">Net Cashflow</td>
                        @php
                            $net_cashflow = 0;
                        @endphp
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end dhead">
                                {{ number_format($totalsPerMonth[$month] + $totalsPerMonth2[$month] - $totalsPerMonth3[$month] - $totalsPerMonth4[$month], 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end dhead">
                            {{ number_format($total_seluruh + $total_seluruh2 - $total_seluruh3 - $total_seluruh4, 0) }}
                        </td>
                    </tr> --}}
                    <tr>
                        <td class="fw-bold dhead">Net Cashflow</td>
                        @php
                            $net_cashflow = 0;
                        @endphp
                        @foreach (array_keys(reset($data)) as $month)
                            @php
                                $net_cashflow += $totalsPerMonth[$month] + $totalsPerMonth2[$month] + $totalsPerMonth5[$month] - $totalsPerMonth3[$month] + $totalsPerMonth6[$month] - $totalsPerMonth4[$month];
                            @endphp
                            <td class="fw-bold text-end dhead">
                                {{ number_format($net_cashflow, 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end dhead">
                            {{ number_format($total_seluruh + $total_seluruh2 + $total_seluruh5 + $total_seluruh6 - ($total_seluruh3 + $total_seluruh4), 0) }}
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
