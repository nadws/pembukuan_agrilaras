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
        .persentage {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

        .translucent-text {
            color: rgba(0, 0, 0, 0.5);
            /* Warna teks dengan tingkat transparansi 0.5 */
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
                            <th class="dhead" rowspan="2">Kdg</th>
                            <th class="dhead">Umur <br> 85 mgg</th>
                            <th class="dhead" colspan="2">Populasi</th>
                            <th class="dhead" colspan="7">Data Telur</th>
                            <th class="dhead" colspan="2">Pakan</th>
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
                            <th class="dhead">pop <br> awal / akhir</th>
                            <th class="dhead">D/C <br>
                                <i class="fas text-white fa-question-circle rumus" rumus="d_c"
                                    style="cursor: pointer"></i>
                            </th>
                            {{-- Populasi --}}

                            {{-- Data Telur --}}
                            <th class="dhead">kg<br>

                            </th>
                            <th class="dhead">selisih butir<br>
                                <i class="fas text-white fa-question-circle rumus" rumus="butir"
                                    style="cursor: pointer"></i>
                            </th>
                            <th class="dhead">kg <br> today - yesterday
                                <i class="fas text-white fa-question-circle rumus" rumus="kg_today"
                                    style="cursor: pointer"></i>
                            </th>
                            <th class="dhead">gr / p <br> (butir) <br>
                                <i class="fas text-white fa-question-circle rumus" rumus="gr_butir"
                                    style="cursor: pointer"></i>
                            </th>
                            <th class="dhead">hd / p / hh (%)<br>
                                <i class="fas text-white fa-question-circle rumus" rumus="hd_day"
                                    style="cursor: pointer"></i>
                            </th>
                            <th class="dhead">hd present / hd past <br> week (%)
                                <i class="fas text-white fa-question-circle rumus" rumus="hd_week"
                                    style="cursor: pointer"></i>
                            </th>
                            <th class="dhead">FCR D / FCR W / FCR+ <br> (week)
                                <i class="fas text-white fa-question-circle rumus" rumus="fcr_week"
                                    style="cursor: pointer"></i>
                            </th>
                            {{-- Data Telur --}}

                            {{-- pakan --}}
                            <th class="dhead">kg</th>
                            <th class="dhead">(gr/ekor) / p <br>(day)</th>
                            {{-- <th class="dhead">gr <br>(week)</th>
                            <th class="dhead">gr <br>(past week)</th> --}}
                            {{-- pakan --}}

                            {{-- KUML --}}
                            <th class="dhead">pakan(kg)</th>
                            <th class="dhead">telur(kg)</th>
                            <th class="dhead">fcr k / fcr k+ (7,458)</th>
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
                            
                            // kuml
                            $pakan_kuml = 0;
                            $telur_kuml = 0;
                            $obat_kuml = 0;
                            $vaksin_kuml = 0;
                            
                            $mati = 0;
                            $jual = 0;
                        @endphp
                        @foreach ($kandang as $k)
                            @php
                                $kg += empty($k->pcs) ? '0' : $k->kg - $k->pcs / 180;
                                $kg_kotor += empty($k->pcs) ? '0' : $k->kg;
                                $gr_butir += empty($k->pcs) ? '0' : number_format((($k->kg - $k->pcs / 180) * 1000) / $k->pcs, 0);
                                $pakan += empty($k->kg_pakan) ? '0' : $k->kg_pakan / 1000;
                                $butir += $k->pcs - $k->pcs_past;
                                $kg_today += $k->kg - $k->pcs / 180 - ($k->kg_past - $k->pcs_past / 180);
                                
                                // kuml
                                $pakan_kuml += $k->kg_pakan_kuml / 1000;
                                $telur_kuml += $k->kuml_kg - $k->kuml_pcs / 180;
                                $obat_kuml += $k->kuml_rp_vitamin;
                                $vaksin_kuml += $k->kum_ttl_rp_vaksin;
                                
                                $ayam_awal += $k->stok_awal;
                                $ayam_akhir += $k->stok_awal - $k->pop_kurang;
                                
                                $mati += empty($k->mati) ? '0' : $k->mati;
                                $jual += empty($k->jual) ? '0' : $k->jual;
                            @endphp
                            <tr>
                                <td align="center" class="kandang">{{ $k->nm_kandang }}</td>
                                <!-- Umur -->
                                <td align="center" class="mgg {{ $k->mgg >= '85' ? 'bg-danger text-white' : '' }}">
                                    {{ $k->mgg }} ({{ number_format(($k->mgg / 85) * 100, 0) }}%)
                                </td>
                                {{-- <td align="center" class="hari">{{$k->hari}}</td>
                            <td align="center" class="afkir 80 minggu">{{number_format(($k->mgg / 80) * 100,0)}}%</td>
                            --}}
                                <!-- umur -->

                                <!-- populasi -->
                                <td align="center" class="pop awal">
                                            {{ $k->stok_awal }} 
                                            <br>
                                            {{ $k->stok_awal - $k->pop_kurang }}
                                            ({{ number_format((($k->stok_awal - $k->pop_kurang) / $k->stok_awal) * 100, 1) }}
                                            %)
                                    </div>

                                </td>
                                {{-- <td align="center"
                                class="% {{(($k->stok_awal - $k->pop_kurang) / $k->stok_awal) * 100 <= 85 ? 'bg-danger text-white' : ''}}">
                            </td> --}}
                                @php
                                    $tot_ayam_mati = empty($k->mati) ? '0' : $k->mati;
                                    $tot_ayam_jual = empty($k->jual) ? '0' : $k->jual;
                                    $tot_ayam_semua_hilang = $tot_ayam_mati;
                                @endphp
                                <td align="center"
                                    class="D/C {{ $tot_ayam_semua_hilang > 3 ? 'bg-danger text-white' : '' }}">
                                    {{ empty($k->mati) ? '0' : $k->mati }} / {{ empty($k->jual) ? '0' : $k->jual }}
                                </td>
                                <!-- populasi -->

                                <!-- data telur -->
                                {{-- <td align="center"
                                class="butir / today - yesterday {{$k->pcs - $k->pcs_past < '-60' ? 'bg-danger text-white' : ''}}">
                                {{number_format($k->pcs,0)}} / ({{number_format($k->pcs - $k->pcs_past,0)}})
                            </td> --}}
                                <!-- mencari ikat  1 ikat = 1kg  -->
                                <td align="right" class="kg telur">
                                    <dt>{{ number_format($k->kg - $k->pcs / 180, 1) }}</dt>
                                    {{ number_format($k->kg, 1) }}
                                </td>
                                <td align="center"
                                    class="butir {{ $k->pcs - $k->pcs_past < 0 ? 'bg-danger text-white' : '' }} ">
                                    {{ number_format($k->pcs - $k->pcs_past, 0) }}
                                </td>
                                <td align="center"
                                    class="kg / today - yesterday {{ $k->kg - $k->pcs / 180 - ($k->kg_past - $k->pcs_past / 180) < 0 ? 'bg-danger text-white' : '' }} ">

                                    {{ number_format($k->kg - $k->pcs / 180 - ($k->kg_past - $k->pcs_past / 180), 1) }}
                                </td>
                                {{-- <td align="center">{{number_format(($k->kg - ($k->pcs/180)) * 1000,2)}}</td> --}}

                                <td align="right" class="gr per butir">
                                    {{ empty($k->pcs) ? '0' : number_format((($k->kg - $k->pcs / 180) * 1000) / $k->pcs, 0) }}
                                    <br>{{ $k->t_peforma }}
                                </td>
                                </td>
                                <td align="center" class="hd perday (%)">
                                    {{-- {{$k->pcs}} --}}
                                    {{ number_format(($k->pcs / ($k->stok_awal - $k->pop_kurang)) * 100, 0) }} /
                                    {{ $k->p_hd }} /
                                    {{ number_format(($k->pcs / $k->stok_awal) * 100, 0) }}
                                </td>


                                <td align="center" class="hd week">
                                    {{-- ({{$k->pcs_telur_week}} {{$k->jlh_hari}}) --}}
                                    {{ empty($k->pcs_telur_week) || empty($k->jlh_hari) || empty($k->pop_kurang)
                                        ? '0'
                                        : number_format(($k->pcs_telur_week / $k->jlh_hari / ($k->stok_awal - $k->pop_kurang)) * 100, 0) }}
                                    /
                                    {{ empty($k->pcs_telur_week_past) || empty($k->jlh_hari_past) || empty($k->pop_kurang_past)
                                        ? '0'
                                        : number_format(($k->pcs_telur_week_past / $k->jlh_hari_past / ($k->stok_awal - $k->pop_kurang_past)) * 100, 0) }}

                                </td>

                                <!-- (12777) / (3) / (2296) -->
                                @php
                                    $fcr = empty($k->kg_p_week) || empty($k->kg_telur_week) || empty($k->pcs_telur_week) ? '0' : $k->kg_p_week / 1000 / ($k->kg_telur_week - $k->pcs_telur_week / 180);
                                    
                                    $vitamin = empty($k->rp_vitamin) ? '0' : $k->rp_vitamin / 7000;
                                    $vaksin = empty($k->ttl_rp_vaksin) ? '0' : $k->ttl_rp_vaksin / 7000;
                                    $fcr_plus = empty($k->kg_p_week) || empty($k->kg_telur_week) ? '0' : number_format(($k->kg_p_week / 1000 + $vitamin + $vaksin) / ($k->kg_telur_week - $k->pcs_telur_week / 180), 1);
                                    
                                    $fcr_day = empty($k->kg_pakan) || empty($k->pcs) ? '0' : number_format($k->kg_pakan / 1000 / ($k->kg - $k->pcs / 180), 1);
                                @endphp

                                <td align="center " class="FCR(week) {{ $fcr >= 2.2 ? 'bg-danger text-white' : '' }} ">
                                    {{-- {{$k->kg_telur_week}} / {{$k->pcs_telur_week}} / {{$k->kg_p_week}} <br> --}}
                                    {{ $fcr_day }} / {{ number_format($fcr, 2) }} / {{ $fcr_plus }}
                                </td>



                                <!-- data telur -->


                                <!-- pakan -->
                                <td align="center" class="kg">{{ number_format($k->kg_pakan / 1000, 1) }}</td>
                                <td align="right"
                                    class="(gr/ekor) / p(day) {{ $k->kg_pakan < 100 ? 'bg-danger text-white' : '' }}">
                                    {{ number_format($k->kg_pakan / ($k->stok_awal - $k->pop_kurang), 0) }}
                                    <br>{{ $k->feed }}
                                </td>
                                {{-- <td align="center" class="gr(week)">{{number_format(($k->kg_p_week/1000))}}</td>

                            <td align="center" class="gr(past week)">{{number_format(($k->kg_pp_week/1000))}}</td> --}}


                                <!-- pakan -->


                                <!-- kuml -->
                                <td align="center" class="pakan(kg)">
                                    {{ number_format(empty($k->kg_pakan_kuml) ? '0' : $k->kg_pakan_kuml / 1000, 1) }}
                                </td>
                                <td align="center" class="telur(kg)">
                                    {{ number_format($k->kuml_kg - $k->kuml_pcs / 180, 1) }}
                                </td>
                                <td align="center" class="fcr k / fcr k+ (7,458)">

                                    {{ empty($k->kg_pakan_kuml) || empty($k->kuml_pcs)
                                        ? '0'
                                        : number_format($k->kg_pakan_kuml / 1000 / ($k->kuml_kg - $k->kuml_pcs / 180), 2) }}
                                    /
                                    {{ empty($k->kg_pakan_kuml) || empty($k->kuml_pcs)
                                        ? '0'
                                        : number_format(
                                            ($k->kg_pakan_kuml / 1000 + $k->kuml_rp_vitamin / 7000 + $k->kum_ttl_rp_vaksin / 7000) /
                                                ($k->kuml_kg - $k->kuml_pcs / 180),
                                            2,
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
                            <th class="dhead">{{ $mati }} / {{ $jual }}</th>
                            <th class="dhead">{{ number_format($kg, 2) }} <br> {{ number_format($kg_kotor, 2) }}
                            </th>
                            <th class="dhead">{{ number_format($butir, 0) }}</th>
                            <th class="dhead">{{ number_format($kg_today, 2) }}</th>
                            <th class="dhead">{{ $gr_butir / 4 }}</th>
                            <th class="dhead"></th>
                            <th class="dhead"></th>
                            <th class="dhead"></th>
                            <th class="dhead">{{ number_format($pakan, 2) }}</th>
                            <th class="dhead"></th>
                            <th class="dhead">{{ number_format($pakan_kuml, 2) }}</th>
                            <th class="dhead">{{ number_format($telur_kuml, 2) }}</th>
                            <th class="dhead"></th>
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
