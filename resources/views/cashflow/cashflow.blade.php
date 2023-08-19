<style>
    .dhead {
        background-color: #435EBE !important;
        color: white;
    }

    .dhead2 {
        background-color: #f01e2c !important;
        color: white;
    }
</style>
<div class="row">
    <div class="col-lg-6">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th class="dhead2">Akun</th>
                    <th class="dhead2">Rupiah</th>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold"><a href="#" onclick="event.preventDefault();"
                            class="tmbhakun_control" kategori='1'>Piutang Bulan Lalu</a>
                    </td>
                </tr>
                @php
                $total_pi = 0;
                $total_pe = 0;
                @endphp
                @foreach ($piutang as $p)
                @php
                $total_pi += $p->debit - $p->kredit;
                @endphp
                <tr>
                    <td>{{ucwords(strtolower($p->nm_akun))}} ({{date('F Y',strtotime($tgl_back))}}) </td>
                    <td align="right">Rp {{number_format($p->debit - $p->kredit,0)}}</td>
                </tr>

                @endforeach
                <tr>
                    <td class="fw-bold">Total</td>
                    <td class="fw-bold" align="right">RP. {{number_format($total_pi,0)}}</td>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold"><a href="#" onclick="event.preventDefault();"
                            class="tmbhakun_control" kategori='2'>Penjualan</a></td>
                </tr>
                @foreach ($penjualan as $p)
                @php
                $total_pe += $p->kredit;
                @endphp
                <tr>
                    <td>{{ucwords(strtolower($p->nm_akun))}} </td>
                    <td align="right">Rp {{number_format($p->kredit,0)}}</td>
                </tr>
                @endforeach
                {{-- <tr>
                    <td colspan="2" class="fw-bold">&nbsp;</td>
                </tr> --}}
                <tr>
                    <td class="fw-bold">Total</td>
                    <td class="fw-bold" align="right">Rp {{number_format($total_pe,0)}}</td>
                </tr>

                {{-- dasds --}}
                <tr>
                    <td colspan="2" class="fw-bold"><a href="#" onclick="event.preventDefault();"
                            class="tmbhakun_control" kategori='4'>Piutang Bulan Ini</a></td>
                </tr>
                @php
                $t_piutang = 0;
                @endphp
                @foreach ($piutang2 as $u)
                @php
                $t_piutang += $u->debit - $u->kredit ;
                @endphp
                <tr>
                    <td>{{ucwords(strtolower($u->nm_akun))}} ({{date('F Y',strtotime($tgl2))}})</td>
                    <td align="right">{{number_format($u->debit - $u->kredit,0)}} </td>
                </tr>
                @endforeach
                <tr>
                    <td>Biaya Kerugian Piutang</td>
                    <td align="right">{{number_format($kerugian->debit,0)}}</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <th style="text-align: right">{{number_format($t_piutang + $kerugian->debit,0)}}</th>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold">&nbsp;</td>
                </tr>
                <tr>
                    <td class="fw-bold">Grand Total</td>
                    <td class="fw-bold" align="right">Rp {{number_format(($total_pi + $total_pe) - ($t_piutang +
                        $kerugian->debit),0) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-lg-6">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="dhead2">Akun</th>
                    <th style="text-align: right" class="dhead2">Rupiah</th>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold"><a href="#" onclick="event.preventDefault();"
                            class="tmbhakun_control" kategori='3'>Uang Ditarik</a></td>
                </tr>
                @php
                $t_uang = 0;

                @endphp
                @foreach ($uang as $u)
                @php
                $t_uang += $u->debit - $u->kredit;
                @endphp

                <tr>
                    <td>{{ucwords(strtolower($u->nm_akun))}} </td>
                    <td align="right">{{number_format($u->debit - $u->kredit,0)}} </td>
                </tr>
                @endforeach
                <tr>
                    <th>Total</th>
                    <th style="text-align: right">{{number_format($t_uang,0)}}</th>
                </tr>

            </thead>
        </table>
    </div>
    <div class="col-lg-12">
        <hr style="border: 1">
    </div>
    <div class="col-lg-6 mt-2">
        @php
        $total_b = 0;
        @endphp
        @foreach ($biaya as $b)
        @php
        $total_b += $b->debit;
        @endphp
        @endforeach
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th class="dhead2">Total Uang Keluar</th>
                    <th class="dhead2 text-end" style="white-space: nowrap">Rp {{number_format($total_b,0)}}</th>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold"><a href="#" onclick="event.preventDefault();"
                            class="tmbhakun_control" kategori='5'>Biaya</a></td>
                </tr>
                @foreach ($biaya as $b)
                <tr>
                    <td>{{ucwords(strtolower($b->nm_akun))}} </td>
                    <td align="right">Rp {{number_format($b->debit ,0)}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-lg-6 mt-2">
        @php
        $total_bi = 0;
        @endphp
        @foreach ($uangbiaya as $b)
        @php
        $total_bi += $b->kredit;
        @endphp
        @endforeach
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th class="dhead2">Total Uang Keluar</th>
                    <th class="dhead2 text-end" style="white-space: nowrap">Rp {{number_format($total_bi,0)}}</th>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold"><a href="#" onclick="event.preventDefault();"
                            class="tmbhakun_control" kategori='6'>Uang Keluar</a></td>
                </tr>
                @foreach ($uangbiaya as $b)
                <tr>
                    <td>{{ucwords(strtolower($b->nm_akun))}} </td>
                    <td align="right">Rp {{number_format($b->kredit ,0)}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>