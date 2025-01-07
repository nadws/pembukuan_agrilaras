<x-theme.app title="{{ $title }}" table="Y" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <style>
            .freeze-cell1_th {
                position: sticky;
                z-index: 30;
                background-color: #F2F7FF;
                top: 0;
                left: 0;
                border-right: 1px solid #d6ddf5;
                box-shadow: inset -1px 0 0 #F2F7FF !important;
            }

            .freeze-cell_th2 {
                position: sticky;
                z-index: 30;
                background-color: #F2F7FF;
                top: 0;
                left: 100px;
                box-shadow: inset -1px 0 0 #F2F7FF !important;
            }

            .freeze-cell_th3 {
                position: sticky;
                z-index: 30;
                background-color: #F2F7FF;
                top: 0;
                left: 189px;
                /* Sesuaikan */
                box-shadow: inset -1px 0 0 #F2F7FF !important;
                /* Mengganti border kiri */
                border: none !important;
                /* Hapus border asli jika perlu */
            }



            .freeze-cell1_td {
                position: sticky;
                z-index: 10;
                background-color: #F2F7FF !important;
                left: 0;
                box-shadow: inset -1px 0 0 #F2F7FF !important;
            }

            .freeze-cell2_td {
                position: sticky;
                z-index: 10;
                background-color: #F2F7FF !important;
                left: 100px;
                box-shadow: inset -1px 0 0 #F2F7FF !important;
            }

            .freeze-cell3_td {
                position: sticky;
                z-index: 10;
                background-color: #F2F7FF !important;
                left: 189px;
                box-shadow: inset -1px 0 0 #F2F7FF !important;
            }
        </style>

        <div class="row">
            <div class="col-lg-12">
                @include('budget.nav')
                <br>
                <br>
            </div>
            <div class="col-lg-6">
                <h5 class="float-start mt-1">Profit & Loss</h5>
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
            $thn1 = $thn;
            $thn_awal = '2020';
            $thn2 = $thn - 1;

            $totalsPerMonth = array_fill(0, count(array_keys(reset($data))), 0);
            $total_seluruh = 0;
            $ttl_saldo_p = 0;
            foreach ($data as $akun => $months) {
                $saldo_thn_pen = \App\Models\ProfitModel::saldo_t_lalu($thn_awal, $thn2, $akun);

                $totalPerAkun = 0;
                foreach ($months as $month => $nominal) {
                    $totalPerAkun += $nominal;
                    $totalsPerMonth[$month] = ($totalsPerMonth[$month] ?? 0) + $nominal;
                }
                $total_seluruh += $totalPerAkun;
                $ttl_saldo_p += $saldo_thn_pen->k_saldo;
            }

            $totalsPerMonth2 = array_fill(0, count(array_keys(reset($data2))), 0);
            $total_seluruh2 = 0;
            $ttl_saldo_b = 0;
            foreach ($data2 as $akun => $months) {
                $totalPerAkun2 = 0;
                $saldo_thn_bi = \App\Models\ProfitModel::saldo_t_lalu($thn_awal, $thn2, $akun);
                foreach ($months as $month => $nominal) {
                    $totalPerAkun2 += $nominal;
                    $totalsPerMonth2[$month] = ($totalsPerMonth2[$month] ?? 0) + $nominal;
                }
                $total_seluruh2 += $totalPerAkun2;
                $ttl_saldo_b += $saldo_thn_bi->d_saldo - $saldo_thn_bi->k_saldo;
            }

            $totalsPerMonth3 = array_fill(0, count(array_keys(reset($data3))), 0);
            $total_seluruh3 = 0;
            $ttl_saldo_bp = 0;
            foreach ($data3 as $akun => $months) {
                $totalPerAkun3 = 0;
                $saldo_thn_bp = \App\Models\ProfitModel::saldo_t_lalu($thn_awal, $thn2, $akun);
                foreach ($months as $month => $nominal) {
                    $totalPerAkun3 += $nominal;
                    $totalsPerMonth3[$month] = ($totalsPerMonth3[$month] ?? 0) + $nominal;
                }
                $total_seluruh3 += $totalPerAkun3;
                $ttl_saldo_bp += $saldo_thn_bp->d_saldo - $saldo_thn_bp->k_saldo;
            }

            $totalsPerMonth4 = array_fill(0, count(array_keys(reset($data4))), 0);
            $total_seluruh4 = 0;
            $ttl_saldo_bpa = 0;
            foreach ($data4 as $akun => $months) {
                $totalPerAkun4 = 0;
                $saldo_thn_bpa = \App\Models\ProfitModel::saldo_t_lalu($thn_awal, $thn2, $akun);
                foreach ($months as $month => $nominal) {
                    $totalPerAkun4 += $nominal;
                    $totalsPerMonth4[$month] = ($totalsPerMonth4[$month] ?? 0) + $nominal;
                }
                $total_seluruh4 += $totalPerAkun4;
                $ttl_saldo_bpa += $saldo_thn_bpa->d_saldo;
            }

            $totalsPerMonth5 = array_fill(0, count(array_keys(reset($data4))), 0);
            $total_seluruh5 = 0;
            $ttl_saldo_bpp = 0;
            foreach ($data5 as $akun => $months) {
                $totalPerAkun5 = 0;
                $saldo_thn_bpp = \App\Models\ProfitModel::saldo_t_lalu($thn_awal, $thn2, $akun);
                foreach ($months as $month => $nominal) {
                    $totalPerAkun5 += $nominal;
                    $totalsPerMonth5[$month] = ($totalsPerMonth5[$month] ?? 0) + $nominal;
                }
                $total_seluruh5 += $totalPerAkun5;
                $ttl_saldo_bpp += $saldo_thn_bpp->d_saldo;
            }

            $totalsPerMonth6 = array_fill(0, count(array_keys(reset($data7))), 0);
            $total_seluruh6 = 0;
            // $ttl_saldo_pullet = 0;
            foreach ($data7 as $akun => $months) {
                $totalPerAkun6 = 0;
                // $saldo_thn_pullet = \App\Models\ProfitModel::saldo_pullet($thn_awal, $thn2, $akun);
                foreach ($months as $month => $nominal) {
                    $totalPerAkun6 += $nominal;
                    $totalsPerMonth6[$month] = ($totalsPerMonth6[$month] ?? 0) + $nominal;
                }
                $total_seluruh6 += $totalPerAkun6;
                // $ttl_saldo_pullet += $saldo_thn_pullet->d_saldo;
            }
            // $totalsPerMonth7 = array_fill(0, count(array_keys(reset($data6))), 0);
            // $total_seluruh7 = 0;
            // // $ttl_saldo_pullet = 0;
            // foreach ($data6 as $akun => $months) {
            //     $totalPerAkun7 = 0;
            //     // $saldo_thn_pullet = \App\Models\ProfitModel::saldo_pullet($thn_awal, $thn2, $akun);
            //     foreach ($months as $month => $nominal) {
            //         $totalPerAkun7 += $nominal;
            //         $totalsPerMonth7[$month] = ($totalsPerMonth7[$month] ?? 0) + $nominal;
            //     }
            //     $total_seluruh7 += $totalPerAkun7;
            //     // $ttl_saldo_pullet += $saldo_thn_pullet->d_saldo;
            // }

            $saldo_thn_pullet = \App\Models\ProfitModel::saldo_pullet_thn_lalu($thn_awal, $thn2);

        @endphp
        <div class="row table-responsive">
            <table class="table table-bordered" x-data="{
                open_pendapatan: false,
                open_biaya: false,
                open_biaya_penyesuaian: false,
                open_biaya_disusutkan: false,
                open_biaya_beli_asset: false,
                open_biaya_penyesuaian_pullet: false,
            }">
                <thead>
                    <tr>
                        <th class="dhead freeze-cell1_th" width="20%">Akun</th>
                        <th class="dhead  text-end">Saldo Tahun lalu</th>
                        <th class="dhead  text-end ">Saldo Rata2</th>
                        @foreach (array_keys(reset($data)) as $month)
                            <th class="dhead text-end ">{{ $month }}</th>
                        @endforeach
                        <th class="dhead text-end ">Total</th>
                        <th class="dhead text-end ">Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold freeze-cell1_td">Pendapatan <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_pendapatan = ! open_pendapatan"><i class="fas fa-caret-down"></i></button>
                        </td>
                        <td class="fw-bold text-end ">{{ number_format($ttl_saldo_p, 0) }}</td>
                        <td class="fw-bold text-end ">{{ number_format($ttl_saldo_p / 12, 0) }}</td>

                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh, 0) }}</td>
                        <td class="fw-bold text-end">{{ number_format($total_seluruh + $ttl_saldo_p, 0) }}</td>
                    </tr>
                    @foreach ($data as $akun => $months)
                        @php
                            $totalPerAkun = 0;

                            $saldo_thn_pen = \App\Models\ProfitModel::saldo_t_lalu($thn_awal, $thn2, $akun);
                        @endphp
                        <tr x-show="open_pendapatan">
                            <td class="freeze-cell1_td">
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                {{ $nm_akun->nm_akun }}
                            </td>
                            <td class="text-end ">
                                {{ number_format($saldo_thn_pen->k_saldo, 0) }}
                            </td>
                            <td class="text-end ">
                                {{ number_format($saldo_thn_pen->k_saldo / 12, 0) }}
                            </td>


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
                            <td class="text-end">{{ number_format($totalPerAkun + $saldo_thn_pen->k_saldo, 0) }}</td>

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
                        <td class="fw-bold freeze-cell1_td">Biaya <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end" @click="open_biaya = ! open_biaya"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        <td class="fw-bold text-end ">{{ number_format($ttl_saldo_b, 0) }}</td>
                        <td class="fw-bold text-end ">{{ number_format($ttl_saldo_b / 12, 0) }}</td>

                        @foreach (array_keys(reset($data2)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth2[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh2, 0) }}</td>
                        <td class="fw-bold text-end">{{ number_format($total_seluruh2 + $ttl_saldo_b, 0) }}</td>
                    </tr>
                    @foreach ($data2 as $akun => $months)
                        <tr x-show="open_biaya">
                            @php
                                $totalPerAkun2 = 0;
                                $saldo_thn_biy = \App\Models\ProfitModel::saldo_t_lalu($thn_awal, $thn2, $akun);
                            @endphp
                            <td class="freeze-cell1_td">
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                {{ $nm_akun->nm_akun }}
                            </td>
                            <td class="text-end ">
                                {{ number_format($saldo_thn_biy->d_saldo - $saldo_thn_biy->k_saldo, 0) }}
                            </td>
                            <td class="text-end ">
                                {{ number_format(($saldo_thn_biy->d_saldo - $saldo_thn_biy->k_saldo) / 12, 0) }}
                            </td>

                            @foreach ($months as $month => $nominal)
                                @php
                                    $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                    $tgl2 = date('Y-m-t', strtotime($tgl1));
                                @endphp
                                <td class="text-end">
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun2 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">
                                {{ number_format($totalPerAkun2, 0) }}
                            </td>
                            <td class="text-end">
                                {{ number_format($totalPerAkun2 + $saldo_thn_biy->d_saldo - $saldo_thn_biy->k_saldo, 0) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold freeze-cell1_td">Biaya Penyesuaian <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_biaya_penyesuaian = ! open_biaya_penyesuaian"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        <td class="fw-bold text-end ">{{ number_format($ttl_saldo_bp, 0) }}</td>
                        <td class="fw-bold text-end ">{{ number_format($ttl_saldo_bp / 12, 0) }}</td>

                        @foreach (array_keys(reset($data3)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth3[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh3, 0) }}</td>
                        <td class="fw-bold text-end">{{ number_format($total_seluruh3 + $ttl_saldo_bp, 0) }}</td>
                    </tr>
                    @foreach ($data3 as $akun => $months)
                        <tr x-show="open_biaya_penyesuaian">
                            @php
                                $totalPerAkun3 = 0;
                                $saldo_thn_bip = \App\Models\ProfitModel::saldo_t_lalu($thn_awal, $thn2, $akun);
                            @endphp
                            <td class="freeze-cell1_td">
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                {{ $nm_akun->nm_akun }} dsadas
                            </td>
                            <td class="text-end ">
                                {{ number_format($saldo_thn_bip->d_saldo - $saldo_thn_bip->k_saldo, 0) }}
                            </td>
                            <td class="text-end ">
                                {{ number_format(($saldo_thn_bip->d_saldo - $saldo_thn_bip->k_saldo) / 12, 0) }}
                            </td>

                            @foreach ($months as $month => $nominal)
                                @php
                                    $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                    $tgl2 = date('Y-m-t', strtotime($tgl1));
                                @endphp
                                <td class="text-end">
                                    <a target="_blank"
                                        href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}</a>
                                </td>
                                @php
                                    $totalPerAkun3 += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">
                                {{ number_format($totalPerAkun3, 0) }}
                            </td>
                            <td class="text-end">
                                {{ number_format($totalPerAkun3 + $saldo_thn_bip->d_saldo - $saldo_thn_bip->k_saldo, 0) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold freeze-cell1_td">Biaya Penyesuaian Pullet <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_biaya_penyesuaian_pullet = ! open_biaya_penyesuaian_pullet"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        <td class="fw-bold text-end ">
                            {{ number_format($saldo_thn_pullet->d_saldo, 0) }}
                        </td>
                        <td class="fw-bold text-end ">
                            {{ number_format($saldo_thn_pullet->d_saldo / 12, 0) }}</td>

                        @foreach (array_keys(reset($data7)) as $month)
                            <td class="fw-bold text-end">{{ number_format($totalsPerMonth6[$month], 0) }}</td>
                        @endforeach
                        <td class="fw-bold text-end">{{ number_format($total_seluruh6, 0) }}</td>
                        <td class="fw-bold text-end">
                            {{ number_format($total_seluruh6 + $saldo_thn_pullet->d_saldo, 0) }}</td>
                    </tr>
                    @foreach ($data7 as $akun => $months)
                        <tr x-show="open_biaya_penyesuaian_pullet">
                            @php
                                $totalPerAkunpullet = 0;
                                $saldo_thn_pullet_akun = \App\Models\ProfitModel::saldo_pullet_thn_lalu_akun(
                                    $thn_awal,
                                    $thn2,
                                    $akun,
                                );
                            @endphp


                            <td class="freeze-cell1_td">
                                @php
                                    $nm_akun = DB::table('kandang')->where('id_kandang', $akun)->first();
                                @endphp
                                {{ $nm_akun->nm_kandang }}
                            </td>
                            <td class="text-end ">
                                {{ number_format($saldo_thn_pullet_akun->d_saldo, 0) }}
                            </td>
                            <td class="text-end ">
                                {{ number_format($saldo_thn_pullet_akun->d_saldo / 12, 0) }}
                            </td>

                            @foreach ($months as $month => $nominal)
                                @php
                                    $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                    $tgl2 = date('Y-m-t', strtotime($tgl1));
                                @endphp
                                <td class="text-end">
                                    <a target="_blank" href="#" data-bs-toggle="modal"
                                        data-bs-target="#Get_detail_pullet" class="detail_pullet"
                                        id_kandang="{{ $akun }}" bulan="{{ $loop->iteration }}"
                                        tahun="{{ $thn }}" href="#">{{ number_format($nominal, 0) }}
                                    </a>
                                </td>
                                @php
                                    $totalPerAkunpullet += $nominal;
                                @endphp
                            @endforeach
                            <td class="text-end">
                                {{ number_format($totalPerAkunpullet, 0) }}
                            </td>
                            <td class="text-end">
                                {{ number_format($totalPerAkunpullet, 0) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold dhead freeze-cell1_th">LABA KOTOR</td>
                        <td class="fw-bold text-end dhead ">
                            {{ number_format($ttl_saldo_p - $ttl_saldo_b - $ttl_saldo_bp - $saldo_thn_pullet->d_saldo, 0) }}
                        </td>
                        <td class="fw-bold text-end dhead ">
                            {{ number_format(($ttl_saldo_p - $ttl_saldo_b - $ttl_saldo_bp - $saldo_thn_pullet->d_saldo) / 12, 0) }}
                        </td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end dhead">
                                {{ number_format($totalsPerMonth[$month] - $totalsPerMonth2[$month] - $totalsPerMonth3[$month] - $totalsPerMonth6[$month], 0) }}

                            </td>
                        @endforeach
                        <td class="fw-bold text-end dhead">
                            {{ number_format($total_seluruh - $total_seluruh2 - $total_seluruh3 - $total_seluruh6, 0) }}
                        </td>
                        <td class="fw-bold text-end dhead">
                            {{ number_format($total_seluruh - $total_seluruh2 - $total_seluruh3 - $total_seluruh6 + ($ttl_saldo_p - $ttl_saldo_b - $ttl_saldo_bp - $saldo_thn_pullet->d_saldo), 0) }}

                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold freeze-cell1_td">Biaya Disusutkan <button type="button"
                                class="btn btn-primary btn-sm btn-buka float-end"
                                @click="open_biaya_disusutkan = ! open_biaya_disusutkan"><i
                                    class="fas fa-caret-down"></i></button>
                        </td>
                        <td class="fw-bold text-end ">
                            {{ number_format($ttl_saldo_bpa, 0) }}</td>
                        <td class="fw-bold text-end ">
                            {{ number_format($ttl_saldo_bpa / 12, 0) }}</td>
                        @foreach (array_keys(reset($data4)) as $month)
                            <td class="fw-bold text-end">
                                {{ number_format($totalsPerMonth4[$month], 0) }}</td>
                        @endforeach
                        @php
                            $saldo_pullet_kurangin_atas = \App\Models\ProfitModel::saldo_pullet_thn_lalu($thn, $thn);
                        @endphp
                        <td class="fw-bold text-end">
                            {{ number_format($total_seluruh4 - $saldo_pullet_kurangin_atas->d_saldo, 0) }}</td>
                        <td class="fw-bold text-end">
                            {{ number_format($total_seluruh4 - $saldo_pullet_kurangin_atas->d_saldo + $ttl_saldo_bpa - $saldo_thn_pullet->d_saldo, 0) }}
                        </td>
                    </tr>
                    @foreach ($data4 as $akun => $months)
                        <tr x-show="open_biaya_disusutkan">
                            @php
                                $totalPerAkun4 = 0;
                                $saldo_thn_bpa = \App\Models\ProfitModel::saldo_t_lalu($thn_awal, $thn2, $akun);
                            @endphp


                            <td class="freeze-cell1_td">
                                @php
                                    $nm_akun = DB::table('akun')->where('id_akun', $akun)->first();
                                @endphp
                                {{ $nm_akun->nm_akun }}
                            </td>

                            <td class="text-end ">
                                {{ number_format($saldo_thn_bpa->d_saldo, 0) }}
                            </td>
                            <td class="text-end ">
                                {{ number_format($saldo_thn_bpa->d_saldo / 12, 0) }}
                            </td>

                            @php
                                $saldo_pullet_kurangin_ttl = 0;
                            @endphp

                            @foreach ($months as $month => $nominal)
                                @php
                                    $tgl1 = $thn . '-' . $loop->iteration . '-01';
                                    $tgl2 = date('Y-m-t', strtotime($tgl1));
                                @endphp
                                <td class="text-end">
                                    @if ($akun == '51')
                                        <a target="_blank" href="#" data-bs-toggle="modal"
                                            data-bs-target="#detail_aktiva" class="detail_aktiva" akun='51'
                                            tgl1="{{ $tgl1 }}"
                                            tgl2="{{ $tgl2 }}">{{ number_format($nominal, 0) }}
                                        </a>
                                        @php
                                            $totalPerAkun4 += $nominal;
                                        @endphp
                                    @else
                                        <a target="_blank" href="#" data-bs-toggle="modal"
                                            data-bs-target="#detail_aktiva" class="detail_aktiva" akun='58'
                                            tgl1="{{ $tgl1 }}"
                                            tgl2="{{ $tgl2 }}">{{ number_format($nominal, 0) }}

                                        </a>
                                        @php
                                            $totalPerAkun4 += $nominal - $totalsPerMonth6[$month];
                                        @endphp

                                        {{-- <a target="_blank"
                                            href="{{ route('summary_buku_besar.detail', ['id_akun' => $akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ number_format($nominal, 0) }}
                                        </a> --}}
                                    @endif

                                </td>
                            @endforeach


                            <td class="text-end">{{ number_format($totalPerAkun4, 0) }}</td>

                            <td class="text-end">{{ number_format($totalPerAkun4 + $saldo_thn_bpa->d_saldo, 0) }}
                            </td>

                        </tr>
                    @endforeach
                    <tr>

                        <td class="fw-bold dhead freeze-cell1_th">LABA BERSIH</td>
                        <td class="fw-bold text-end dhead ">
                            {{ number_format($ttl_saldo_p - $ttl_saldo_b - $ttl_saldo_bp - $ttl_saldo_bpa, 0) }}
                        </td>
                        <td class="fw-bold text-end dhead ">
                            {{ number_format(($ttl_saldo_p - $ttl_saldo_b - $ttl_saldo_bp - $ttl_saldo_bpa) / 12, 0) }}
                        </td>
                        @foreach (array_keys(reset($data)) as $month)
                            <td class="fw-bold text-end dhead">
                                {{ number_format($totalsPerMonth[$month] - $totalsPerMonth2[$month] - $totalsPerMonth3[$month] - $totalsPerMonth6[$month] - $totalsPerMonth4[$month], 0) }}

                            </td>
                        @endforeach
                        <td class="fw-bold text-end dhead">
                            {{ number_format($total_seluruh - $total_seluruh2 - $total_seluruh3 - $total_seluruh4 - $total_seluruh6, 0) }}
                        </td>
                        <td class="fw-bold text-end dhead">
                            {{ number_format($total_seluruh - $total_seluruh2 - $total_seluruh3 - $total_seluruh4 + ($ttl_saldo_p - $ttl_saldo_b - $ttl_saldo_bp - $ttl_saldo_bpa), 0) }}
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>


        {{-- --}}

        <x-theme.modal title="Detail Penyusutan" size="modal-lg" idModal="detail_aktiva" btnSave='T'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="get_dep_aktiva"></div>
                </div>
            </div>

        </x-theme.modal>
        <x-theme.modal title="Detail Pullet" size="modal-lg" idModal="Get_detail_pullet" btnSave='T'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="pash_detail_pullet"></div>
                </div>
            </div>

        </x-theme.modal>
    </x-slot>
    @section('scripts')
        <script>
            $(document).ready(function() {
                $('.detail_aktiva').click(function(e) {
                    e.preventDefault();
                    var tgl1 = $(this).attr('tgl1');
                    var tgl2 = $(this).attr('tgl2');
                    var akun = $(this).attr('akun');
                    $.ajax({
                        type: "get",
                        url: "{{ route('get_depresiasi') }}",
                        data: {
                            tgl1: tgl1,
                            tgl2: tgl2,
                            akun: akun,
                        },
                        success: function(response) {
                            $('.get_dep_aktiva').html(response);
                            $('#table_detail_akt').DataTable({
                                "paging": true,
                                "pageLength": 10,
                                "lengthChange": true,
                                "stateSave": true,
                                "searching": true,
                            });
                        }
                    });

                });
                $('.detail_pullet').click(function(e) {
                    e.preventDefault();
                    var id_kandang = $(this).attr('id_kandang');
                    var bulan = $(this).attr('bulan');
                    var tahun = $(this).attr('tahun');
                    $.ajax({
                        type: "get",
                        url: "{{ route('getPopulasi') }}",
                        data: {
                            id_kandang: id_kandang,
                            bulan: bulan,
                            tahun: tahun,
                        },
                        success: function(response) {
                            $('.pash_detail_pullet').html(response);
                            $('#table_pullet').DataTable({
                                "paging": true,
                                "pageLength": 10,
                                "lengthChange": true,
                                "stateSave": true,
                                "searching": true,
                            });
                        }
                    });

                });
            });
        </script>
    @endsection
</x-theme.app>
