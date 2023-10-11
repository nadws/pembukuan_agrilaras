<style>
    .dhead {
        background-color: #435EBE !important;
        color: white;
    }
</style>
<div class="row">
    <div class="col-lg-6">
        <table class="table table-bordered" x-data="{
            open1: false,
            open2: false,
            open3: false,
            open4: false,
        }">
            <thead>
                <tr>
                    <th colspan="2" width="50%" class="dhead">AKTIVA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" class="fw-bold">
                        <a href="#" onclick="event.preventDefault();" class="tmbhsub_kategori"
                            kategori='1'>AKTIVA
                            LANCAR</a>
                    </td>
                </tr>
                @php
                    $ttl_kas = 0;
                    $ttl_bank = 0;
                    $ttl_piutang = 0;
                    $ttl_persediaan = 0;

                    foreach ($kas as $k) {
                        $ttl_kas += $k->debit + $k->debit_saldo - ($k->kredit + $k->kredit_saldo);
                    }
                    foreach ($bank as $k) {
                        $ttl_bank += $k->debit + $k->debit_saldo - ($k->kredit + $k->kredit_saldo);
                    }

                    foreach ($piutang as $k) {
                        $ttl_piutang += $k->debit + $k->debit_saldo - ($k->kredit + $k->kredit_saldo);
                    }
                    foreach ($persediaan as $k) {
                        $ttl_persediaan += $k->debit + $k->debit_saldo - ($k->kredit + $k->kredit_saldo);
                    }
                @endphp


                <tr>

                    <td>
                        KAS
                        <a href="javascript:void(0);" class="float-end" @click="open1 = ! open1"><i
                                class=" fas fa-caret-down"></i></a>
                    </td>
                    <td align="right">Rp {{ number_format($ttl_kas, 0) }}</td>
                </tr>
                @foreach ($kas as $k)
                    <tr x-transition x-show="open1">
                        <td style="padding-left: 20px">
                            <a target="_blank"
                                href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}
                            </a>
                        </td>
                        <td align="right">Rp
                            {{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td>
                        BANK
                        <a href="javascript:void(0);" class="float-end" @click="open2 = ! open2"><i
                                class=" fas fa-caret-down"></i></a>
                    </td>
                    <td align="right">Rp {{ number_format($ttl_bank, 0) }}</td>
                </tr>
                @foreach ($bank as $k)
                    <tr x-transition x-show="open2">
                        <td style="padding-left: 20px">
                            <a target="_blank"
                                href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}
                            </a>
                        </td>
                        <td align="right">Rp
                            {{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td>
                        PIUTANG DAGANG
                        <a href="javascript:void(0);" class="float-end" @click="open3 = ! open3"><i
                                class=" fas fa-caret-down"></i></a>
                    </td>
                    <td align="right">Rp {{ number_format($ttl_piutang, 0) }}</td>
                </tr>
                @foreach ($piutang as $k)
                    <tr x-transition x-show="open3">
                        <td style="padding-left: 20px">
                            <a target="_blank"
                                href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}</a>
                        </td>
                        <td align="right">Rp
                            {{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td>
                        PERSEDIAAN
                        <a href="javascript:void(0);" class="float-end" @click="open4 = ! open4"><i
                                class=" fas fa-caret-down"></i></a>
                    </td>
                    <td align="right">Rp {{ number_format($ttl_persediaan, 0) }}</td>
                </tr>
                @foreach ($persediaan as $k)
                    <tr x-transition x-show="open4">
                        <td style="padding-left: 20px">
                            <a target="_blank"
                                href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}</a>
                        </td>
                        <td align="right">Rp
                            {{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}
                        </td>
                    </tr>
                @endforeach



                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @php
                    $ttl_aktiva_lancar = $ttl_kas + $ttl_bank + $ttl_piutang + $ttl_persediaan;
                @endphp
                <tr>
                    <td class="fw-bold">JUMLAH AKTIVA LANCAR</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($ttl_aktiva_lancar, 0) }}</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold">
                        <a href="#" onclick="event.preventDefault();" class="tmbhsub_kategori"
                            kategori='3'>AKTIVA
                            TETAP</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <a target="_blank"
                            href="{{ route('summary_buku_besar.detail', ['id_akun' => $peralatan->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">PERALATAN</a>
                    </td>
                    <td align="right">Rp {{ number_format($peralatan->debit + $peralatan->debit_saldo, 0) }}</td>
                </tr>
                <tr>
                    <td>
                        <a target="_blank"
                            href="{{ route('summary_buku_besar.detail', ['id_akun' => $peralatan->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">PERALATAN
                            GANTUNG</a>
                    </td>
                    <td align="right">Rp
                        {{ number_format($peralatan_gantung->debit + $peralatan_gantung->debit_saldo, 0) }}</td>
                </tr>
                <tr>
                    <td>
                        <a target="_blank"
                            href="{{ route('summary_buku_besar.detail', ['id_akun' => $aktiva->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">AKTIVA</a>
                    </td>
                    <td align="right">Rp {{ number_format($aktiva->debit + $aktiva->debit_saldo, 0) }}</td>
                </tr>
                <tr>
                    <td>
                        <a target="_blank"
                            href="{{ route('summary_buku_besar.detail', ['id_akun' => $aktiva->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">AKTIVA
                            GANTUNG</a>
                    </td>
                    <td align="right">Rp {{ number_format($aktiva_gantung->debit + $aktiva_gantung->debit_saldo, 0) }}
                    </td>
                </tr>
                <tr>
                    <td><a target="_blank"
                            href="{{ route('summary_buku_besar.detail', ['id_akun' => $akumulasi_peralatan->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">AKUMULASI
                            PENYUSUTAN PERALATAN(-)</a></td>
                    <td align="right">Rp
                        {{ number_format($akumulasi_peralatan->kredit + $akumulasi_peralatan->kredit_saldo) }}
                    </td>
                </tr>
                <tr>
                    <td> <a target="_blank"
                            href="{{ route('summary_buku_besar.detail', ['id_akun' => $akumulasi->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">AKUMULASI
                            PENYUSUTAN AKTIVA(-)</a></td>
                    <td align="right">Rp {{ number_format($akumulasi->kredit + $akumulasi->kredit_saldo) }}</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @php
                    $total_aktiva = $peralatan->debit + $peralatan->debit_saldo + ($aktiva->debit + $aktiva->debit_saldo) + ($peralatan_gantung->debit + $peralatan_gantung->debit_saldo) + ($aktiva_gantung->debit + $aktiva_gantung->debit_saldo) - ($akumulasi->kredit + $akumulasi->kredit_saldo) - ($akumulasi_peralatan->kredit + $akumulasi_peralatan->kredit_saldo);
                @endphp
                <tr>
                    <td class="fw-bold">NILAI BUKU</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($total_aktiva, 0) }}</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td class="fw-bold">JUMLAH AKTIVA</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($ttl_aktiva_lancar + $total_aktiva, 0) }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
    <div class="col-lg-6">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="2" width="50%" class="dhead">PASSIVA</th>
                </tr>
            </thead>
            <tbody>
                <tr>

                    <td colspan="2" class="fw-bold">
                        <a href="#" onclick="event.preventDefault();" class="tmbhsub_kategori"
                            kategori='2'>HUTANG</a>
                    </td>
                </tr>
                @php
                    $total2 = 0;
                @endphp
                @foreach ($hutang as $h)
                    @php
                        $total2 += $h->kredit + $h->kredit_saldo - ($h->debit + $h->debit_saldo);
                    @endphp
                    <tr>
                        <td>
                            <a target="_blank"
                                href="{{ route('summary_buku_besar.detail', ['id_akun' => $h->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $h->nm_akun }}</a>
                        </td>
                        <td align="right">Rp
                            {{ number_format($h->kredit + $h->kredit_saldo - ($h->debit + $h->debit_saldo), 0) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>

                <tr>
                    <td class="fw-bold">JUMLAH KEWAJIBAN LANCAR</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($total2, 0) }}</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold">
                        <a href="#" onclick="event.preventDefault();" class="tmbhsub_kategori"
                            kategori='4'>EKUITAS</a>
                    </td>
                </tr>
                @php
                    $total4 = 0;
                @endphp
                @foreach ($ekuitas as $h)
                    @php
                        $total4 += $h->kredit + $h->kredit_saldo - ($h->debit + $h->debit_saldo);
                    @endphp
                    <tr>
                        <td>
                            <a target="_blank"
                                href="{{ route('summary_buku_besar.detail', ['id_akun' => $h->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $h->nm_akun }}</a>
                        </td>
                        <td align="right">Rp
                            {{ number_format($h->kredit + $h->kredit_saldo - ($h->debit + $h->debit_saldo), 0) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td class="fw-bold">TOTAL EKUITAS</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($total4, 0) }}</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td class="fw-bold">JUMLAH PASSIVA</td>
                    <td class="fw-bold" align="right">Rp {{ number_format($total2 + $total4, 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
