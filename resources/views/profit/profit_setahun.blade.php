<x-theme.app title="{{ $title }}" table="Y" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <h6 class="float-start mt-1"></h6>
        <div class="row justify-content-end">
            <div class="col-lg-12">


                <x-theme.btn_filter />
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
        @endphp
        @php
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
        @endphp
        @php
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
        @endphp
        @php
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
        @endphp
        <div class="row">
            <table class="table table-bordered" x-data="{
                open_pendapatan: false,
                open_biaya: false,
                open_biaya_penyesuaian: false,
                open_biaya_disusutkan: false,
            }">
                <thead>
                    <tr>
                        <th class="dhead">Akun</th>
                        @foreach (array_keys(reset($data)) as $month)
                            <th class="dhead text-end">{{ $month }}</th>
                        @endforeach
                        <th class="dhead text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">Pendapatan <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_pendapatan = ! open_pendapatan"><i class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh, 0) }}</td>
                    </tr>
                    @foreach ($data as $akun => $months)
                        <tr x-transition x-show="open_pendapatan">
                            <td>{{ $akun }}</td>
                            @php
                                $totalPerAkun = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">{{ number_format($nominal, 0) }}</td>
                                @php
                                    $totalPerAkun += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun, 0) }}</td>
                        </tr>
                    @endforeach
                    {{-- <tr x-show="open_pendapatan">
                        <td class="fw-bold">Total</td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh, 0) }}</td>
                    </tr> --}}
                    {{-- <tr>
                        <td colspan="14">&nbsp;</td>
                    </tr> --}}
                    <tr>
                        <td class="fw-bold">Biaya <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end" @click="open_biaya = ! open_biaya"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data2)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth2[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh2, 0) }}</td>
                    </tr>
                    @foreach ($data2 as $akun => $months)
                        <tr x-show="open_biaya">
                            <td>{{ $akun }}</td>
                            @php
                                $totalPerAkun2 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">{{ number_format($nominal, 0) }}</td>
                                @php
                                    $totalPerAkun2 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun2, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold">Biaya Penyesuaian <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_biaya_penyesuaian = ! open_biaya_penyesuaian"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data3)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth3[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh3, 0) }}</td>
                    </tr>
                    @foreach ($data3 as $akun => $months)
                        <tr x-show="open_biaya_penyesuaian">
                            <td>{{ $akun }}</td>
                            @php
                                $totalPerAkun3 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">{{ number_format($nominal, 0) }}</td>
                                @php
                                    $totalPerAkun3 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun3, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold">LABA KOTOR</td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end">
                                {{ number_format($totalsPerMonth[$month] - $totalsPerMonth2[$month] - $totalsPerMonth3[$month], 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end">
                            {{ number_format($total_seluruh - $total_seluruh2 - $total_seluruh3, 0) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Biaya Disusutkan <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_biaya_disusutkan = ! open_biaya_disusutkan"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        @foreach (array_keys(reset($data4)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth4[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh4, 0) }}</td>
                    </tr>
                    @foreach ($data4 as $akun => $months)
                        <tr x-show="open_biaya_disusutkan">
                            <td>{{ $akun }}</td>
                            @php
                                $totalPerAkun4 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">{{ number_format($nominal, 0) }}</td>
                                @php
                                    $totalPerAkun4 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun4, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold">LABA BERSIH</td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end">
                                {{ number_format($totalsPerMonth[$month] - $totalsPerMonth2[$month] - $totalsPerMonth3[$month] - $totalsPerMonth4[$month], 0) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end">
                            {{ number_format($total_seluruh - $total_seluruh2 - $total_seluruh3 - $total_seluruh4, 0) }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="14">&nbsp;</td>
                    </tr>
                    @foreach ($data5 as $akun => $months)
                        <tr>
                            <td>{{ $akun }}</td>
                            @php
                                $totalPerAkun5 = 0;
                            @endphp
                            @foreach ($months as $month => $nominal)
                                <td class="text-end">{{ number_format($nominal, 0) }}</td>
                                @php
                                    $totalPerAkun5 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">{{ number_format($totalPerAkun5, 0) }}</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

        </div>


        {{-- --}}
    </x-slot>
    @section('scripts')
    @endsection
</x-theme.app>
