<x-theme.app title="{{ $title }}" table="T" sizeCard="12" cont="container-fluid">
    <style>
        table,
        th {
            border: 1px solid white;
            font-size: 10px;
            padding: 10px;
            white-space: nowrap
        }

        td {
            border: 1px solid #435EBE;
            font-size: 10px;
            padding: 10px;

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
    </style>
    <div class="row">
        <div class="col-lg-12">
            <form action="">
                <div class="row mb-2">
                    <div class="col-lg-6">
                        <h6 class="mb-2">Laporan Layer {{ tanggal($tgl) }}</h6>
                    </div>
                    <div class="col-lg-3">

                    </div>
                    <div class="col-lg-3 float-end d-flex align-items-center">

                        <input type="date" class="form-control" name="tgl" value="{{ $tgl }}">
                        <button type="submit" class="btn btn-primary btn-sm ms-2">Filter</button>
                    </div>

                </div>
            </form>
            <div class="table-responsive">


                <table style="text-align: center; " width="100%">
                    <thead style="border: 1px solid white">
                        <tr>
                            <th class="dhead" rowspan="2">Kdg <br> chick in <br> Afkir <br> chick in2 <br>
                                {{date('d/m/y')}}</th>
                            <th class="dhead">Umur <br> 85 mgg</th>
                            <th class="dhead" width="10%" colspan="2">Populasi</th>
                            <th class="dhead" colspan="7">Data Telur</th>
                            <th class="dhead">Pakan</th>
                            {{-- <th class="dhead" colspan="2">Berat Badan</th> --}}
                            <th class="dhead" colspan="5">KUML</th>
                        </tr>
                        <tr>
                            {{-- Umur --}}
                            <th class="dhead">mgg <br>
                                <i class="fas text-white fa-question-circle rumus" rumus="mgg"
                                    style="cursor: pointer"></i>
                            </th>
                            {{-- Umur --}}

                            {{-- Populasi --}}
                            <th class="dhead">pop <br>awal/akhir</th>
                            <th class="dhead">D <br>C <br>Week<br>
                                <i class="fas text-white fa-question-circle rumus" rumus="d_c"
                                    style="cursor: pointer"></i>
                            </th>
                            {{-- Populasi --}}

                            {{-- Data Telur --}}
                            <th class="dhead">butir <br>kg bersih <br> kg kotor

                            </th>
                            <th class="dhead">selisih <br> (butir/kg)<br>
                                <i class="fas text-white fa-question-circle rumus" rumus="butir"
                                    style="cursor: pointer"></i>
                            </th>
                            <th class="dhead">
                                ttl <br> selisih <br> (butir/kg)<br> 1 minggu
                            </th>
                            {{-- <th class="dhead">kg <br> today - yesterday
                                <i class="fas text-white fa-question-circle rumus" rumus="kg_today"
                                    style="cursor: pointer"></i>
                            </th> --}}
                            <th class="dhead">gr / p <br> (butir) <br>
                                <i class="fas text-white fa-question-circle rumus" rumus="gr_butir"
                                    style="cursor: pointer"></i>
                            </th>
                            <th class="dhead">hd / p / hh (%)<br>
                                <i class="fas text-white fa-question-circle rumus" rumus="hd_day"
                                    style="cursor: pointer"></i>
                            </th>
                            <th class="dhead">hd/ hd<br>present/past<br>week(%)
                                <i class="fas text-white fa-question-circle rumus" rumus="hd_week"
                                    style="cursor: pointer"></i>
                            </th>
                            <th class="dhead">FCR <br> D / W / + <br>(week)
                                <i class="fas text-white fa-question-circle rumus" rumus="fcr_week"
                                    style="cursor: pointer"></i>
                            </th>
                            {{-- Data Telur --}}

                            {{-- pakan --}}
                            <th class="dhead">kg <br> (gr/ekor) / p <br>(day)</th>
                            {{-- <th class="dhead"></th> --}}
                            {{-- <th class="dhead">gr <br>(week)</th>
                            <th class="dhead">gr <br>(past week)</th> --}}
                            {{-- pakan --}}

                            {{-- KUML --}}
                            <th class="dhead">pakan(kg) <br> telur(kg)</th>
                            {{-- <th class="dhead">telur(kg)</th> --}}
                            <th class="dhead">fcr <br> k&k+ <br> (7,458)</th>
                            <th class="dhead">obat/vit</th>
                            <th class="dhead">vaksin</th>
                            {{-- KUML --}}
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $ayam_awal = 0;
                        $ayam_akhir = 0;

                        $kg = 0;
                        $kg_kotor = 0;
                        $gr_butir = 0;
                        $pakan = 0;
                        $butir = 0;
                        $kg_today = 0;
                        $pcs = 0;

                        // kuml
                        $pakan_kuml = 0;
                        $telur_kuml = 0;
                        $obat_kuml = 0;
                        $vaksin_kuml = 0;

                        $mati = 0;
                        $jual = 0;

                        $butir_minggu = 0;
                        $kg_minggu =0;
                        @endphp
                        @foreach ($kandang as $k)
                        @php
                        $kg += empty($k->pcs) ? '0' : $k->kg - $k->pcs / 180;
                        $kg_kotor += empty($k->pcs) ? '0' : $k->kg;
                        $gr_butir += empty($k->pcs) ? '0' : number_format((($k->kg - $k->pcs / 180) * 1000) / $k->pcs,
                        0);
                        $pakan += empty($k->kg_pakan) ? '0' : $k->kg_pakan / 1000;
                        $butir += $k->pcs - $k->pcs_past;
                        $kg_today += ($k->kg - ($k->pcs / 180)) - ($k->kg_past - ($k->pcs_past / 180));

                        // kuml
                        $pakan_kuml += $k->kg_pakan_kuml / 1000;
                        $telur_kuml += $k->kuml_kg - $k->kuml_pcs / 180;
                        $obat_kuml += $k->kuml_rp_vitamin;
                        $vaksin_kuml += $k->kum_ttl_rp_vaksin;

                        $ayam_awal += $k->stok_awal;
                        $ayam_akhir += $k->stok_awal - $k->pop_kurang;

                        $mati += empty($k->mati) ? '0' : $k->mati;
                        $jual += empty($k->jual) ? '0' : $k->jual;


                        $butir_minggu += $k->pcs_satu_minggu - $k->pcs_minggu_sebelumnya;
                        $kg_minggu += ($k->kg_satu_minggu - ($k->pcs_satu_minggu / 180)) -
                        ($k->kg_minggu_sebelumnya - ($k->pcs_minggu_sebelumnya / 180));

                        $pcs += $k->pcs;

                        @endphp
                        <tr>
                            <td align="center" class="kandang">{{ $k->nm_kandang }} <br>
                                {{date('d/m/y',strtotime($k->chick_in))}} <br>

                                @php
                                $chick_in_next = date('Y-m-d', strtotime($k->chick_out . ' +1 month'));
                                $merah = date('Y-m-d', strtotime($chick_in_next . ' -15 weeks'));
                                $tgl_hari_ini = date('Y-m-d');
                                $afkir = date('Y-m-d', strtotime($k->chick_out . ' -4 weeks'));
                                $ckin2 = date('Y-m-d', strtotime($k->tgl_masuk . ' -20 weeks'));


                                @endphp

                                <span class="{{$tgl_hari_ini >= $afkir ? 'text-danger fw-bold' : ''}}">
                                    {{date('d/m/y',strtotime($k->chick_out))}} </span> <br>
                                {{-- <span class="{{ $tgl_hari_ini >= $merah ? 'text-danger fw-bold' : ''}}">
                                    {{date('d/m/y', strtotime($k->chick_out . ' +1 month'))}}</span><br> --}}
                                <span class="{{$tgl_hari_ini >= $ckin2 ? 'text-danger fw-bold' : ''}}">
                                    {{date('d/m/y',strtotime($k->tgl_masuk))}}
                                </span>

                            </td>
                            <!-- Umur -->
                            <td align="center" class="mgg {{ $k->mgg >= '85' ? 'text-danger fw-bold' : '' }}">
                                {{ $k->mgg }} <br> ({{ number_format(($k->mgg / 85) * 100, 0) }}%)
                            </td>
                            {{-- <td align="center" class="hari">{{$k->hari}}</td>
                            <td align="center" class="afkir 80 minggu">{{number_format(($k->mgg / 80) * 100,0)}}%</td>
                            --}}
                            <!-- umur -->

                            <!-- populasi -->
                            <td align="center" class="pop awal">
                                <table border="0" class="table-two">
                                    <tr>
                                        <td>{{ $k->stok_awal }}</td>
                                        <td></td>
                                        <td></td>
                                        <td rowspan="2" style="vertical-align:middle; text-align: right">
                                            {{ number_format((($k->stok_awal - $k->pop_kurang) / $k->stok_awal) * 100,
                                            1) }}%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ $k->stok_awal - $k->pop_kurang }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>


                            </td>
                            {{-- <td align="center"
                                class="% {{(($k->stok_awal - $k->pop_kurang) / $k->stok_awal) * 100 <= 85 ? 'text-danger fw-bold' : ''}}">
                            </td> --}}
                            @php
                            $tot_ayam_mati = empty($k->mati) ? '0' : $k->mati;
                            $tot_ayam_jual = empty($k->jual) ? '0' : $k->jual;
                            $tot_ayam_semua_hilang = $tot_ayam_mati;
                            @endphp
                            <td align="right" class="D/C {{ $tot_ayam_semua_hilang > 3 ? 'text-danger fw-bold' : '' }}">
                                {{ empty($k->mati) ? '0' : $k->mati }} <br> {{ empty($k->jual) ? '0' : $k->jual }} <br>
                                {{$k->mati_week + $k->jual_week}}
                            </td>
                            <!-- populasi -->

                            <!-- data telur -->
                            {{-- <td align="center"
                                class="butir / today - yesterday {{$k->pcs - $k->pcs_past < '-60' ? 'text-danger fw-bold' : ''}}">
                                {{number_format($k->pcs,0)}} / ({{number_format($k->pcs - $k->pcs_past,0)}})
                            </td> --}}
                            <!-- mencari ikat  1 ikat = 1kg  -->
                            <td align="right" class="kg telur">
                                {{number_format($k->pcs,0)}} <br>
                                <dt>{{ number_format($k->kg - $k->pcs / 180, 1) }}</dt>
                                {{ number_format($k->kg, 1) }}
                            </td>
                            <td align="right"
                                class="butir {{ $k->pcs - $k->pcs_past < 0 ? 'text-danger fw-bold' : '' }} ">
                                {{ number_format($k->pcs - $k->pcs_past, 0) }} <br>
                                {{ number_format(($k->kg - ($k->pcs / 180)) - ($k->kg_past - ($k->pcs_past / 180)), 1)
                                }}
                            </td>

                            <td align="right" class="butir">
                                {{ number_format($k->pcs_satu_minggu - $k->pcs_minggu_sebelumnya, 0) }} <br>
                                {{ number_format(($k->kg_satu_minggu - ($k->pcs_satu_minggu / 180)) -
                                ($k->kg_minggu_sebelumnya - ($k->pcs_minggu_sebelumnya / 180)), 1) }}
                            </td>
                            {{-- <td align="center"
                                class="kg / today - yesterday {{ $k->kg - $k->pcs / 180 - ($k->kg_past - $k->pcs_past / 180) < 0 ? 'text-danger fw-bold' : '' }} ">

                                {{ number_format($k->kg - $k->pcs / 180 - ($k->kg_past - $k->pcs_past / 180), 1) }}
                            </td> --}}
                            {{-- <td align="center">{{number_format(($k->kg - ($k->pcs/180)) * 1000,2)}}</td> --}}

                            <td align="right" class="gr per butir">
                                {{ empty($k->pcs) ? '0' : number_format((($k->kg - $k->pcs / 180) * 1000) / $k->pcs, 0)
                                }}
                                <br>{{ empty($k->t_peforma) ? 'NA' : $k->t_peforma }}
                            </td>
                            </td>
                            <td align="center" class="hd perday (%)">
                                {{-- {{$k->pcs}} --}}
                                <table border="0" class="table-two">
                                    <tr>
                                        <td>{{ $k->stok_awal - $k->pop_kurang == 0 ? 0 : number_format(($k->pcs /
                                            ($k->stok_awal - $k->pop_kurang)) * 100, 0) }}
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td rowspan="2" style="vertical-align:middle; text-align: right">
                                            {{ empty($k->p_hd) ? 'NA' : $k->p_hd }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ number_format(($k->pcs / $k->stok_awal) * 100, 0) }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </td>


                            <td align="center" class="hd week">
                                {{-- ({{$k->pcs_telur_week}} {{$k->jlh_hari}}) --}}
                                {{ empty($k->pcs_telur_week) || empty($k->jlh_hari) || empty($k->pop_kurang) ||
                                $k->stok_awal - $k->pop_kurang == 0
                                ? '0'
                                : number_format(($k->pcs_telur_week / $k->jlh_hari / ($k->stok_awal - $k->pop_kurang)) *
                                100, 0) }}
                                <br>
                                {{ empty($k->pcs_telur_week_past) || empty($k->jlh_hari_past) ||
                                empty($k->pop_kurang_past) || k->stok_awal -
                                $k->pop_kurang_past == 0
                                ? '0'
                                : number_format(($k->pcs_telur_week_past / $k->jlh_hari_past / ($k->stok_awal -
                                $k->pop_kurang_past)) * 100, 0) }}

                            </td>

                            <!-- (12777) / (3) / (2296) -->
                            @php
                            $fcr = empty($k->kg_p_week) || empty($k->kg_telur_week) || empty($k->pcs_telur_week) ? '0' :
                            $k->kg_p_week / 1000 / ($k->kg_telur_week - $k->pcs_telur_week / 180);

                            $vitamin = empty($k->rp_vitamin) ? '0' : $k->rp_vitamin / 7000;
                            $vaksin = empty($k->ttl_rp_vaksin) ? '0' : $k->ttl_rp_vaksin / 7000;
                            $fcr_plus = empty($k->kg_p_week) || empty($k->kg_telur_week) ? '0' :
                            number_format(($k->kg_p_week / 1000 + $vitamin + $vaksin) / ($k->kg_telur_week -
                            $k->pcs_telur_week / 180), 1);

                            $fcr_day = empty($k->kg_pakan) || empty($k->pcs) ? '0' : number_format($k->kg_pakan / 1000 /
                            ($k->kg - $k->pcs / 180), 1);
                            @endphp

                            <td align="right" class="FCR(week) {{ $fcr >= 2.2 ? 'text-danger fw-bold' : '' }} ">
                                <table border="0" class="table-two">
                                    <tr>
                                        <td>{{ $fcr_day }}
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td rowspan="2" style="vertical-align:middle; text-align: right">
                                            {{ $fcr_plus }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ number_format($fcr, 2) }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </td>



                            <!-- data telur -->


                            <!-- pakan -->
                            <td align="right" class="kg w_pakan">
                                <table border="0" class="table-two_pakan">
                                    <tr>
                                        <td>{{ number_format($k->kg_pakan / 1000, 1) }}
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td rowspan="2" style="vertical-align:middle; text-align: right">
                                            {{ empty($k->feed) ? 'NA' : $k->feed }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{number_format($k->kg_pakan / ($k->stok_awal - $k->pop_kurang), 0) }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </td>
                            {{-- <td align="right"
                                class="(gr/ekor) / p(day) {{ $k->kg_pakan < 100 ? 'text-danger fw-bold' : '' }} w_pakan">

                            </td> --}}
                            {{-- <td align="center" class="gr(week)">{{number_format(($k->kg_p_week/1000))}}</td>

                            <td align="center" class="gr(past week)">{{number_format(($k->kg_pp_week/1000))}}</td> --}}


                            <!-- pakan -->


                            <!-- kuml -->
                            <td align="center" class="pakan(kg)">
                                {{ number_format(empty($k->kg_pakan_kuml) ? '0' : $k->kg_pakan_kuml / 1000, 1) }} <br>
                                {{ number_format($k->kuml_kg - $k->kuml_pcs / 180, 1) }}
                            </td>
                            {{-- <td align="center" class="telur(kg)">
                                {{ number_format($k->kuml_kg - $k->kuml_pcs / 180, 1) }}
                            </td> --}}
                            <td align="center" class="fcr k / fcr k+ (7,458)">

                                {{ empty($k->kg_pakan_kuml) || empty($k->kuml_pcs)
                                ? '0'
                                : number_format($k->kg_pakan_kuml / 1000 / ($k->kuml_kg - $k->kuml_pcs / 180), 1) }}
                                <br>
                                {{ empty($k->kg_pakan_kuml) || empty($k->kuml_pcs)
                                ? '0'
                                : number_format(
                                ($k->kg_pakan_kuml / 1000 + $k->kuml_rp_vitamin / 7000 + $k->kum_ttl_rp_vaksin / 7000) /
                                ($k->kuml_kg - $k->kuml_pcs / 180),
                                1,
                                ) }}
                            </td>
                            <!--(144,502.2 , 60,920.9 , 864,183.0)-->
                            <td align="center" class="obat/vit">{{ number_format($k->kuml_rp_vitamin, 0) }} </td>
                            <td align="center" class="vaksin">{{ number_format($k->kum_ttl_rp_vaksin, 0) }}</td>
                            <!-- kuml -->
                            <!-- listrik -->

                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="dhead" colspan="2">Total</th>
                            <th class="dhead">{{ number_format($ayam_awal, 0) }}/{{ number_format($ayam_akhir, 0) }}
                                ({{ number_format(($ayam_akhir / $ayam_awal) * 100, 0) }} %)</th>
                            <th class="dhead text-end">{{ $mati }} <br> {{ $jual }}</th>
                            <th class="dhead text-end">
                                {{number_format($pcs,0)}}
                                <br>
                                {{ number_format($kg, 2) }}
                                <br>
                                {{ number_format($kg_kotor, 2) }}
                            </th>
                            <th class="dhead text-end">
                                {{ number_format($butir, 0) }} <br>
                                {{ number_format($kg_today, 1) }}
                            </th>
                            <th class="dhead text-end">
                                {{ number_format($butir_minggu, 0) }} <br>
                                {{ number_format($kg_minggu, 1) }}
                            </th>
                            {{-- <th class="dhead"></th> --}}
                            <th class="dhead">{{ $gr_butir / 4 }} </th>
                            <th class="dhead"> </th>
                            <th class="dhead"></th>
                            {{-- <th class="dhead"></th> --}}
                            <th class="dhead"></th>
                            <th class="dhead">{{ number_format($pakan, 2) }}</th>
                            <th class="dhead">{{ number_format($pakan_kuml, 2) }} <br> {{ number_format($telur_kuml, 2)
                                }} </th>
                            {{-- <th class="dhead">{{ number_format($telur_kuml, 2) }}</th> --}}
                            <th class="dhead">{{number_format($pakan_kuml/$telur_kuml,1)}}</th>
                            <th class="dhead">{{ number_format($obat_kuml, 0) }}</th>
                            <th class="dhead">{{ number_format($vaksin_kuml, 0) }}</th>
                        </tr>
                    </tfoot>

                </table>
            </div>



        </div>
    </div>

    <x-theme.modal title="Rumus" btnSave='T' idModal="rumus">
        <div id="rumus_layer"></div>
    </x-theme.modal>

    @section('scripts')
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
    </script>
    @endsection
</x-theme.app>