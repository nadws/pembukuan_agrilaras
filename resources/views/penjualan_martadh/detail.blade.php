<div class="row">
    <div class="col-lg-6">
        <table class="table">
            <tr>
                <th class="dhead">Tanggal</th>
                <th class="dhead">No Nota</th>
                <th class="dhead">Customer</th>
                <th class="dhead">HP</th>
            </tr>
            <tr>
                <td>
                    {{ tanggal($invoice2->tgl) }}
                </td>
                <td>
                    {{ $invoice2->no_nota }}
                </td>
                <td>
                    {{$invoice2->customer}}
                </td>
                <td>
                    {{$invoice2->no_hp}}
                </td>
            </tr>
        </table>
    </div>
    <div class="col-lg-12">
        <table class="table table-striped table-bordered" style="white-space: nowrap;">
            <thead>
                <tr>
                    <th class="dhead" width="10%" rowspan="2">Produk </th>
                    <th style="text-align: center" class="dhead abu" colspan="3">Penjualan per pcs</th>
                    <th style="text-align: center" class="dhead putih" colspan="3">Penjualan per ikat</th>
                    <th style="text-align: center" class="dhead abuGelap" colspan="4">Penjualan per rak</th>
                    <th rowspan="2" class="dhead" width="10%" style="text-align: center; white-space: nowrap;">Total
                        Rp
                    </th>
                </tr>
                <tr>

                    {{-- <th class="dhead" width="10%">Produk </th> --}}
                    <th class="dhead abu" width="7%" style="text-align: center">Pcs</th>
                    <th class="dhead abu" width="7%" style="text-align: center">Kg</th>
                    <th class="dhead abu" width="10%" style="text-align: center;">Rp Pcs</th>

                    <th class="dhead putih" width="7%" style="text-align: center;">Ikat</th>
                    <th class="dhead putih" width="7%" style="text-align: center;">Kg</th>
                    <th class="dhead putih" width="10%" style="text-align: center;">Rp Ikat</th>

                    <th class="abuGelap" width="7%" style="text-align: center;">Pcs</th>
                    <th class="abuGelap" width="7%" style="text-align: center;">Kg Kotor</th>
                    <th class="abuGelap" width="7%" style="text-align: center;">Kg Bersih</th>
                    <th class="abuGelap" width="10%" style="text-align: center;">Rp Rak</th>

                    {{-- <th class="dhead" width="10%" style="text-align: center; white-space: nowrap;">Total Rp
                    </th> --}}
                </tr>
            </thead>
            <tbody>
                @php
                $total_semua = 0;
                @endphp
                @foreach ($invoice as $i)

                <tr>
                    <td>{{$i->nm_telur}}</td>
                    <td align="right">{{$i->pcs_pcs}}</td>
                    <td align="right">{{$i->kg_pcs}}</td>
                    <td align="right">Rp. {{number_format($i->rp_pcs,0)}}</td>
                    <!-- Jual Ikat -->
                    <td align="right">{{$i->ikat}}</td>
                    <td align="right">{{$i->kg_ikat}}</td>
                    <td align="right">Rp. {{number_format($i->rp_ikat,0)}}</td>
                    <!-- Jual Ikat -->
                    <!-- Jual Kg -->
                    <td align="right">{{$i->pcs_kg}}</td>
                    <td align="right">{{$i->kg_kg_kotor}}</td>
                    <td align="right">{{$i->kg_kg}}</td>
                    {{-- <td align="right">{{$i->rak_kg}}</td> --}}
                    <td align="right">Rp. {{number_format($i->rp_kg,0)}}</td>
                    <!-- Jual Kg -->
                    <td align="right">
                        @php
                        $rp_pcs = $i->pcs_pcs * $i->rp_pcs;
                        $rp_ikat = ($i->kg_ikat - $i->ikat) * $i->rp_ikat;
                        $rak_kali = round($i->rak_kg * 0.12,1);
                        $rp_kg = $i->kg_kg * $i->rp_kg;
                        $total_rp = $rp_pcs + $rp_ikat + $rp_kg;

                        @endphp
                        Rp. {{number_format($total_rp,0)}}
                    </td>
                </tr>
                @php
                $total_semua += $total_rp;
                @endphp
                @endforeach


            </tbody>
            <tfoot>
                <tr>
                    <td colspan="10"></td>
                    <th>Total</th>
                    <th style="text-align: right">Rp. {{number_format($total_semua,0)}}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>