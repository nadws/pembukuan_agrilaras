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

<div class="row" x-data="{
    openPbl:false,
    openPnjl:false,
    openPbi:false,
    openBiaya:false,
    openUangKeluar:false,
}">
    <div class="col-lg-6">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th class="dhead2">Akun</th>
                    <th class="dhead2">Rupiah</th>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold"><a href="#" onclick="event.preventDefault();"
                            class="tmbhakun_control me-3" kategori='1'>Piutang Bulan Lalu</a>
                        <button class="btn btn-primary btn-sm btn-buka float-end" @click="openPbl = ! openPbl">Buka <i
                                class="fas fa-caret-down"></i></button>
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
                <tr x-show="openPbl">
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
                            class="tmbhakun_control me-3" kategori='2'>Penjualan</a>
                        <button class="btn btn-primary btn-sm btn-buka float-end" @click="openPnjl = ! openPnjl">Buka <i
                                class="fas fa-caret-down"></i></button>

                    </td>
                </tr>
                @foreach ($penjualan as $p)
                @php
                $total_pe += $p->kredit;
                @endphp
                <tr x-show="openPnjl">
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
                            class="tmbhakun_control me-3" kategori='4'>Piutang Bulan Ini</a>
                        <button class="btn btn-primary btn-sm btn-buka float-end" @click="openPbi = ! openPbi">Buka <i
                                class="fas fa-caret-down"></i></button>

                    </td>
                </tr>
                @php
                $t_piutang = 0;
                @endphp
                @foreach ($piutang2 as $u)
                @php
                $t_piutang += $u->debit - $u->kredit ;
                @endphp
                <tr x-show="openPbi">
                    <td>{{ucwords(strtolower($u->nm_akun))}} ({{date('F Y',strtotime($tgl2))}})</td>
                    <td align="right">Rp. {{number_format($u->debit - $u->kredit,0)}} </td>
                </tr>
                @endforeach
                <tr x-show="openPbi">
                    <td>Biaya Kerugian Piutang</td>
                    <td align="right">Rp. {{number_format($kerugian->debit,0)}}</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <th style="text-align: right">Rp. {{number_format($t_piutang + $kerugian->debit,0)}}</th>
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

    <div class="col-lg-6">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="dhead2">Akun</th>
                    <th style="text-align: right" class="dhead2">Rupiah</th>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold"><a href="#" onclick="event.preventDefault();"
                            class="tmbhakun_control" kategori='3'>Uang Ditarik (Piutang & Penjualan yg ditarik)</a>

                    </td>
                </tr>
                @php
                $t_uang = 0;

                @endphp
                @foreach ($uang as $u)
                @php
                $t_uang += $u->debit ;
                @endphp

                <tr>
                    <td>{{ucwords(strtolower($u->nm_akun))}} </td>
                    <td align="right">Rp. {{number_format($u->debit ,0)}} </td>
                </tr>
                @endforeach
                {{-- <tr>
                    <th>Total</th>
                    <th style="text-align: right">{{number_format($t_uang,0)}}</th>
                </tr> --}}

            </thead>
        </table>
    </div>
    @php
    $biaya_admin = DB::selectOne("SELECT sum(a.debit) as debit FROM jurnal as a where a.id_akun = '8' and a.tgl between
    '$tgl1' and '$tgl2' and a.id_buku = '6' ")

    @endphp
    <div class="col-lg-6">
        <table class="table table-bordered">
            <tr>
                <td class="fw-bold">Grand Total </td>
                <td class="fw-bold" align="right">Rp {{number_format(($total_pi + $total_pe) - ($t_piutang +
                    $kerugian->debit),0) }}
                </td>
            </tr>
            <tr>
                <td class="fw-bold">Biaya Administrasi</td>
                <td class="fw-bold" align="right">Rp {{number_format($biaya_admin->debit,0)}}
                </td>
            </tr>
            <tr>
                <td class="fw-bold">Total</td>
                <td class="fw-bold" align="right">Rp {{number_format(($total_pi + $total_pe) - ($t_piutang +
                    $kerugian->debit) - $biaya_admin->debit,0) }}
                </td>
            </tr>
        </table>
    </div>
    <div class="col-lg-6">
        <table class="table table-bordered">
            <tr>
                <td class="fw-bold">Grand Total</td>
                <td class="fw-bold" style="text-align: right">{{number_format($t_uang,0)}}</td>
            </tr>

            <tr>
                <td class="fw-bold">Biaya Administrasi</td>
                <td class="fw-bold" align="right">Rp {{number_format($biaya_admin->debit,0)}}
                </td>
            </tr>
            <tr>
                <td class="fw-bold">Total</td>
                <td class="fw-bold" align="right">Rp {{number_format($t_uang - $biaya_admin->debit,0)}}
                </td>
            </tr>
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
                    <th class="dhead2 text-end" style="white-space: nowrap">Rp {{number_format($total_b,1)}}</th>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold">Biaya
                        <button class="btn btn-primary btn-sm btn-buka float-end" @click="openBiaya = ! openBiaya">Buka
                            <i class="fas fa-caret-down"></i></button>

                    </td>
                </tr>
                @foreach ($biaya as $b)
                <tr x-show="openBiaya">
                    <td><a target="_blank"
                            href="{{ route('summary_buku_besar.detail', ['id_akun' => $b->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{
                            ucwords(strtolower($b->nm_akun)) }}</a></td>
                    <td align="right">Rp {{number_format($b->debit ,1)}}</td>
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
                    <th class="dhead2 text-end" style="white-space: nowrap">Rp {{number_format($total_bi,1)}}</th>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold"><a href="#" onclick="event.preventDefault();"
                            class="tmbhakun_control me-3" kategori='6'>Uang Keluar</a>
                        <button class="btn btn-primary btn-sm btn-buka float-end"
                            @click="openUangKeluar = ! openUangKeluar">Buka <i class="fas fa-caret-down"></i></button>

                    </td>
                </tr>
                @foreach ($uangbiaya as $b)
                <tr x-show="openUangKeluar">
                    <td><a target="_blank"
                            href="{{ route('summary_buku_besar.detail', ['id_akun' => $b->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{
                            ucwords(strtolower($b->nm_akun)) }}</a></td>
                    <td align="right">Rp {{number_format($b->kredit ,1)}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>