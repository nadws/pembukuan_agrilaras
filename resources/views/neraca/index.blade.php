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
                <h5 class="float-start mt-1">{{ $title }}</h5>
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
        {{-- ini koding sum kalsifikasi --}}
        @php
            $klasifkasi = [
                1 => 'kas',
                2 => 'bank',
                7 => 'piutang',
                6 => 'persediaan',
            ];

            $total_per_bulan = [];
            foreach ($bulans as $d) {
                $bln = $d->bulan;
                $tgl1 = '2020-01-01';
                $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));
                foreach ($klasifkasi as $k => $i) {
                    $kas = \App\Models\NeracaAldi::GetKas($tgl1, $tgl2, $k);
                    $debit_kas = $kas->debit ?? 0;
                    $kredit_kas = $kas->kredit ?? 0;

                    $total_per_bulan[$bln][$i] = $debit_kas - $kredit_kas;
                }
            }

            // Menambahkan total keseluruhan untuk KAS dan BANK

            $totalPerBulan = [];
            foreach ($bulans as $d) {
                $bln = $d->bulan;
                $totalPerBulan[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
            }

            // Hitung total per bulan
            foreach ($total_per_bulan as $bulan => $nilai) {
                $totalPerBulan[$bulan] += $nilai['kas'] + $nilai['bank'] + $nilai['piutang'] + $nilai['persediaan'];
            }
        @endphp

        {{-- ini koding per akun nya --}}
        @php
            $totalPerAkun = [];
            foreach ($bulans as $d) {
                $bln = $d->bulan;
                $tgl1 = '2020-01-01';
                $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

                foreach ($klasifkasi as $k => $i) {
                    $akun = \App\Models\NeracaAldi::getAkun($tgl1, $tgl2, $k);
                    foreach ($akun as $a) {
                        $totalPerAkun[$bln][$i][$a->nm_akun] = $a->debit - $a->kredit;
                    }
                }
            }

        @endphp

        <table class="table table-bordered" x-data="{
            open1: false,
            open2: false,
            open3: false,
            open4: false,
        }">
            <thead>
                <tr>
                    <th class="dhead freeze-cell1_th">
                        Aktiva
                    </th>
                    @foreach ($bulans as $d)
                        <th class="dhead text-center freeze-cell1_th">{{ $d->nm_bulan }}</th>
                    @endforeach
                    {{-- <th class="dhead text-center freeze-cell1_th">Total</th> --}}
                </tr>
                <tr>
                    <th class="dhead ps-3" colspan="13">Aktiva Lancar</th>
                </tr>
                <tr>
                    <th class="ps-4">
                        <div style="cursor: pointer" @click="open1 = ! open1">
                            KAS
                            <i class=" fas fa-caret-down float-end"></i>
                        </div>
                    </th>
                    @foreach ($bulans as $d)
                        <th class="text-end">{{ number_format($total_per_bulan[$d->bulan]['kas'], 0) }}</th>
                    @endforeach
                    {{-- <th class="text-end">{{ number_format($ttlKas, 0) }}</th> --}}
                </tr>

                @foreach ($totalPerAkun[1]['kas'] as $d => $i)
                    <tr x-show="open1">
                        <td class="ps-4">{{ $d }}</td>
                        @php
                            $total = 0;
                        @endphp
                        @foreach ($bulans as $b)
                            <td class="ps-4 text-end">
                                @php
                                    $duit = $totalPerAkun[$b->bulan]['kas'][$d];
                                    $total += $duit;
                                @endphp
                                {{ number_format($duit, 0) }}
                            </td>
                        @endforeach
                        {{-- <td class="text-end">
                        {{ number_format($total, 0) }}
                    </td> --}}
                    </tr>
                @endforeach

                <tr>

                    <th class="ps-4">
                        <div style="cursor: pointer" @click="open2 = ! open2">
                            BANK
                            <i class=" fas fa-caret-down float-end"></i>

                        </div>
                    </th>
                    @foreach ($bulans as $d)
                        <th class="text-end">{{ number_format($total_per_bulan[$d->bulan]['bank'], 0) }}</th>
                    @endforeach
                    {{-- <th class="text-end">{{ number_format($ttlBank, 0) }}</th> --}}
                </tr>
                @foreach ($totalPerAkun[1]['bank'] as $d => $i)
                    <tr x-show="open2">
                        @php
                            $total = 0;
                        @endphp
                        <td class="ps-4">{{ $d }}</td>
                        @foreach ($bulans as $b)
                            @php
                                $total += $totalPerAkun[$b->bulan]['bank'][$d];
                            @endphp
                            <td class="ps-4 text-end">
                                {{ number_format($totalPerAkun[$b->bulan]['bank'][$d], 0) }}
                            </td>
                        @endforeach
                        {{-- <td class="text-end">
                        {{ number_format($total, 0) }}
                    </td> --}}
                    </tr>
                @endforeach
                <tr>

                    <th class="ps-4">
                        <div style="cursor: pointer" @click="open3 = ! open3">
                            PIUTANG DAGANG
                            <i class=" fas fa-caret-down float-end"></i>
                        </div>
                    </th>
                    @foreach ($bulans as $d)
                        <th class="text-end">{{ number_format($total_per_bulan[$d->bulan]['piutang'], 0) }}</th>
                    @endforeach
                    {{-- <th class="text-end">{{ number_format($ttlPiutang, 0) }}</th> --}}
                </tr>
                @foreach ($totalPerAkun[1]['piutang'] as $d => $i)
                    <tr x-show="open3">
                        @php
                            $total = 0;
                        @endphp
                        <td class="ps-4">{{ $d }}</td>
                        @foreach ($bulans as $b)
                            @php
                                $total += $totalPerAkun[$b->bulan]['piutang'][$d];
                            @endphp
                            <td class="ps-4 text-end">
                                {{ number_format($totalPerAkun[$b->bulan]['piutang'][$d], 0) }}
                            </td>
                        @endforeach
                        {{-- <td class="text-end">
                        {{ number_format($total, 0) }}
                    </td> --}}
                    </tr>
                @endforeach
                <tr>

                    <th class="ps-4">
                        <div style="cursor: pointer" @click="open4 = ! open4">
                            PERSEDIAAN
                            <i class=" fas fa-caret-down float-end"></i>
                        </div>
                    </th>
                    @foreach ($bulans as $d)
                        <th class="text-end">{{ number_format($total_per_bulan[$d->bulan]['persediaan'], 0) }}</th>
                    @endforeach
                    {{-- <th class="text-end">{{ number_format($ttlPersediaan, 0) }}</th> --}}
                </tr>
                @foreach ($totalPerAkun[1]['persediaan'] as $d => $i)
                    <tr x-show="open4">
                        <td class="ps-4">{{ $d }}</td>
                        @php
                            $total = 0;
                        @endphp
                        @foreach ($bulans as $b)
                            @php
                                $total += $totalPerAkun[$b->bulan]['persediaan'][$d];
                            @endphp
                            <td class="ps-4 text-end">
                                {{ number_format($totalPerAkun[$b->bulan]['persediaan'][$d], 0) }}
                            </td>
                        @endforeach
                        {{-- <td class="text-end">
                        {{ number_format($total, 0) }}
                    </td> --}}
                    </tr>
                @endforeach
                <tr>
                    <th class="dhead ps-3">Total Aktiva Lancar</th>
                    @foreach ($bulans as $d)
                        <th class="text-end dhead">{{ number_format($totalPerBulan[$d->bulan], 0) }}</th>
                    @endforeach
                </tr>

                {{-- aktiva tetap --}}
                @php
                    $klasifkasi = [
                        16 => 'peralatan',
                        9 => 'aktiva',
                        43 => 'aktivaGantung',
                        61 => 'peralatanGantung',
                        76 => 'pulletGantung',
                        52 => 'akumulasiAktiva',
                        59 => 'akumulasiPeralatan',
                    ];

                    $total_per_bulan = [];
                    foreach ($bulans as $d) {
                        $bln = $d->bulan;
                        $tgl1 = '2020-01-01';
                        $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));
                        foreach ($klasifkasi as $k => $i) {
                            $kas = \App\Models\NeracaAldi::Getakumulasi($tgl1, $tgl2, $k);
                            $debit_kas = $kas->debit ?? 0;
                            $kredit_kas = $kas->kredit ?? 0;
                            $debit_saldo_kas = $kas->debit_saldo ?? 0;
                            $kredit_saldo_kas = $kas->kredit_saldo ?? 0;

                            $total_per_bulan[$bln][$i] = $debit_kas + $debit_saldo_kas - $kredit_kas - $kredit_saldo_kas;
                        }
                    }

                    // Menambahkan total keseluruhan untuk KAS dan BANK

                    $totalPerBulanTetap = [];
                    foreach ($bulans as $d) {
                        $bln = $d->bulan;
                        $totalPerBulanTetap[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
                    }

                    // Hitung total per bulan
                    foreach ($total_per_bulan as $bulan => $nilai) {
                        $totalPerBulanTetap[$bulan] += $nilai['peralatan'] + $nilai['aktiva'] + $nilai['aktivaGantung'] + $nilai['peralatanGantung'] + $nilai['pulletGantung'] + ($nilai['akumulasiAktiva'] + $nilai['akumulasiPeralatan']);
                    }

                @endphp

                <tr>
                    <th class="dhead ps-3" colspan="13">Aktiva Tetap</th>
                </tr>
                @foreach ($klasifkasi as $i => $k)
                    <tr>
                        <th class="ps-4">
                            <div style="cursor: pointer">
                                {{ ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $k)) }}
                            </div>
                        </th>
                        @foreach ($bulans as $d)
                            <th class="text-end">{{ number_format($total_per_bulan[$d->bulan][$k], 0) }}</th>
                        @endforeach
                        {{-- <th class="text-end">{{ number_format(${'ttl' . ucwords($k)}, 0) }}</th> --}}
                    </tr>
                @endforeach
                <tr>
                    <th class="dhead ps-3">Nilai Buku</th>
                    @foreach ($bulans as $d)
                        <th class="text-end dhead">{{ number_format($totalPerBulanTetap[$d->bulan], 0) }}</th>
                    @endforeach
                    {{-- <th class="text-end dhead">{{ number_format($totalSemua, 0) }}</th> --}}
                </tr>
                <tr>
                    <th class="dhead"><b>Jumlah Aktiva</b></th>
                    @php
                        $totalSemuaAktiva = 0;
                    @endphp
                    @foreach ($bulans as $d)
                        @php
                            $totalSemuaAktiva += $totalPerBulanTetap[$d->bulan] + $totalPerBulan[$d->bulan];
                        @endphp
                        <th class="text-end dhead">
                            {{ number_format($totalPerBulanTetap[$d->bulan] + $totalPerBulan[$d->bulan], 0) }}</th>
                    @endforeach
                    {{-- <th class="text-end dhead">{{ number_format($totalSemuaAktiva, 0) }}</th> --}}
                </tr>
                <tr>
                    <th colspan="13"></th>
                </tr>

                {{-- ================================================== --}}
                <tr>
                    <th class="dhead"><b>Total Passiva</b></th>
                    @php

                        $totalPerAkunHutang = [];
                        foreach ($bulans as $d) {
                            $bln = $d->bulan;
                            $tgl1 = '2020-01-01';
                            $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

                            $akun = \App\Models\NeracaAldi::GetAkun($tgl1, $tgl2, 9);
                            foreach ($akun as $a) {
                                $totalPerAkunHutang[$bln][59][$a->nm_akun] = $a->kredit - $a->debit;
                            }
                        }

                        $totalPerBulanHutang = [];
                        foreach ($bulans as $d) {
                            $bln = $d->bulan;
                            $totalPerBulanHutang[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
                        }
                        foreach ($totalPerAkunHutang as $bulan => $nilai) {
                            $totalPerBulanHutang[$bulan] += array_sum($nilai[59]);
                        }

                        $totalPerAkunEkuitas2 = [];
                        foreach ($bulans as $d) {
                            $bln = $d->bulan;
                            $tgl1 = '2020-01-01';
                            $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

                            $ekuitas2 = \App\Models\NeracaAldi::GetKas3($tgl1, $tgl2);
                            $laba_pendapatan = \App\Models\NeracaAldi::laba_berjalan_pendapatan($tgl1, $tgl2);
                            $laba_biaya = \App\Models\NeracaAldi::laba_berjalan_biaya($tgl1, $tgl2);

                            $laba_berjalan_sebelum_penutup = $laba_pendapatan->pendapatan - $laba_biaya->biaya;
                            $totalPerAkunEkuitas2[$bln]['total'] = $ekuitas2->kredit + $ekuitas2->kredit_saldo - $ekuitas2->debit - $ekuitas2->debit_saldo + $laba_berjalan_sebelum_penutup;
                            // $totalPerAkun[$bln]['labaBerjalan'] = $ekuitas2->kredit - $ekuitas2->debit +
                            $laba_berjalan_sebelum_penutup;
                        }

                        $totalPerBulanEkuitas = [];
                        foreach ($bulans as $d) {
                            $bln = $d->bulan;
                            $totalPerBulanEkuitas[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
                        }


                        $totalPerAkun = [];
                        foreach ($bulans as $d) {
                            $bln = $d->bulan;
                            $tgl1 = '2020-01-01';
                            $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

                            $akun = \App\Models\NeracaAldi::GetKas2($tgl1, $tgl2);
                            foreach ($akun as $a) {
                                $totalPerAkun[$bln][$i][$a->nm_akun] = $a->kredit - $a->debit;
                            }
                        }
                        $totalPerBulanEkuitas = [];
                        foreach ($bulans as $d) {
                            $bln = $d->bulan;
                            $totalPerBulanEkuitas[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
                        }
                        foreach ($totalPerAkun as $bulan => $nilai) {
                            $totalPerBulanEkuitas[$bulan] += array_sum($nilai[59]);
                        }
                        $totalSemuaPassiva = 0;
                    @endphp
                    @foreach ($bulans as $b)
                        @php
                            $totalPerBulan = $totalPerBulanHutang[$b->bulan] + $totalPerAkunEkuitas2[$b->bulan]['total'] + $totalPerBulanEkuitas[$b->bulan];
                            $totalSemuaPassiva += $totalPerBulan;
                        @endphp
                        <th class="text-end dhead">
                            {{ number_format($totalPerBulan, 0) }}
                        </th>
                    @endforeach
                </tr>
                {{-- ================================================== --}}




                {{-- pasiva --}}
                <tr>
                    <th class="dhead" colspan="13"><b>Passiva</b></th>

                </tr>
                <tr>
                    <th class="dhead ps-3" colspan="13">Hutang</th>
                </tr>
                @php

                    $totalPerAkunHutang = [];
                    foreach ($bulans as $d) {
                        $bln = $d->bulan;
                        $tgl1 = '2020-01-01';
                        $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

                        $akun = \App\Models\NeracaAldi::GetAkun($tgl1, $tgl2, 9);
                        foreach ($akun as $a) {
                            $totalPerAkunHutang[$bln][$i][$a->nm_akun] = $a->kredit - $a->debit;
                        }
                    }

                    $totalPerBulanHutang = [];
                    foreach ($bulans as $d) {
                        $bln = $d->bulan;
                        $totalPerBulanHutang[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
                    }
                    foreach ($totalPerAkunHutang as $bulan => $nilai) {
                        $totalPerBulanHutang[$bulan] += array_sum($nilai[59]);
                    }

                @endphp
                @foreach ($totalPerAkunHutang[1]['59'] as $d => $i)
                    <tr>

                        <th class="ps-4">{{ $d }}</th>

                        @php
                            $total = 0;
                        @endphp
                        @foreach ($bulans as $b)
                            @php
                                $total += $totalPerAkunHutang[$b->bulan]['59'][$d];
                            @endphp
                            <td class="ps-4 text-end">
                                {{ number_format($totalPerAkunHutang[$b->bulan]['59'][$d], 0) }}
                            </td>
                        @endforeach
                        {{-- <td class="text-end">
                        {{ number_format($total, 0) }}
                    </td> --}}
                    </tr>
                @endforeach
                <tr>
                    <th class="dhead"><b>Jumlah Kewajiban Lancar</b></th>
                    @php
                        $totalSemuaHutang = 0;
                    @endphp
                    @foreach ($bulans as $d)
                        @php
                            $totalSemuaHutang += $totalPerBulanHutang[$d->bulan];
                        @endphp
                        <th class="text-end dhead">
                            {{ number_format($totalPerBulanHutang[$d->bulan], 0) }}</th>
                    @endforeach
                    {{-- <th class="text-end dhead">{{ number_format($totalSemuaHutang, 0) }}</th> --}}
                </tr>
                <tr>
                    <th class="dhead ps-3" colspan="13">Ekuitas</th>
                </tr>

                @php
                    $totalPerAkun = [];
                    foreach ($bulans as $d) {
                        $bln = $d->bulan;
                        $tgl1 = '2020-01-01';
                        $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

                        $akun = \App\Models\NeracaAldi::GetKas2($tgl1, $tgl2);
                        foreach ($akun as $a) {
                            $totalPerAkun[$bln][$a->nm_akun] = $a->kredit - $a->debit;
                        }
                    }
                    $totalPerBulanEkuitas = [];
                    foreach ($bulans as $d) {
                        $bln = $d->bulan;
                        $totalPerBulanEkuitas[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
                    }
                    foreach ($totalPerAkun as $bulan => $nilai) {
                        $totalPerBulanEkuitas[$bulan] += array_sum($nilai);
                    }
                @endphp

                @foreach ($totalPerAkun['0'] as $d => $i)
                    <tr>

                        <th class="ps-4">{{ ucwords($d) }}</th>

                        @php
                            $total = 0;
                        @endphp
                        @foreach ($bulans as $b)
                            @php
                                $total += $totalPerAkun[$b->bulan]['0'][$d];
                            @endphp
                            <td class="ps-4 text-end">
                                {{ number_format($totalPerAkun[$b->bulan]['0'][$d], 0) }}
                            </td>
                        @endforeach
                        {{-- <td class="text-end">
                        {{ number_format($total, 0) }}
                    </td> --}}
                    </tr>
                @endforeach

                @php
                    $totalPerAkunEkuitas2 = [];
                    foreach ($bulans as $d) {
                        $bln = $d->bulan;
                        $tgl1 = '2020-01-01';
                        $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

                        $ekuitas2 = \App\Models\NeracaAldi::GetKas3($tgl1, $tgl2);
                        $laba_pendapatan = \App\Models\NeracaAldi::laba_berjalan_pendapatan($tgl1, $tgl2);
                        $laba_biaya = \App\Models\NeracaAldi::laba_berjalan_biaya($tgl1, $tgl2);

                        $laba_berjalan_sebelum_penutup = $laba_pendapatan->pendapatan - $laba_biaya->biaya;
                        $totalPerAkunEkuitas2[$bln]['total'] = $ekuitas2->kredit + $ekuitas2->kredit_saldo - $ekuitas2->debit - $ekuitas2->debit_saldo + $laba_berjalan_sebelum_penutup;
                        // $totalPerAkun[$bln]['labaBerjalan'] = $ekuitas2->kredit - $ekuitas2->debit +
                        $laba_berjalan_sebelum_penutup;
                    }

                @endphp
                <tr>

                    <th class="ps-4">{{ ucwords($ekuitas2->nm_akun) }}</th>

                    @php
                        $total = 0;
                    @endphp
                    @foreach ($bulans as $b)
                        @php
                            $total += $totalPerAkunEkuitas2[$b->bulan]['total'];
                        @endphp
                        <td class="ps-4 text-end">
                            {{ number_format($totalPerAkunEkuitas2[$b->bulan]['total'], 0) }}
                        </td>
                    @endforeach
                    {{-- <td class="text-end">
                        {{ number_format($total, 0) }}
                    </td> --}}
                </tr>

                <tr>
                    <th class="dhead"><b>Total Ekuitas</b></th>
                    @php
                        $totalSemuaEkuitas = 0;
                    @endphp
                    @foreach ($bulans as $b)
                        @php
                            $totalSemuaEkuitas += $totalPerAkunEkuitas2[$b->bulan]['total'] + $totalPerBulanEkuitas[$b->bulan];
                        @endphp
                        <th class="text-end dhead">
                            {{ number_format($totalPerAkunEkuitas2[$b->bulan]['total'] + $totalPerBulanEkuitas[$b->bulan], 0) }}
                        </th>
                    @endforeach
                    {{-- <th class="text-end dhead">{{ number_format($totalSemuaEkuitas, 0) }}</th> --}}
                </tr>

                {{-- <tr>
                    <th class="dhead"><b>Total Passiva</b></th>
                    @php
                        $totalSemuaPassiva = 0;
                    @endphp
                    @foreach ($bulans as $b)
                        @php
                            $totalSemuaPassiva += $totalPerBulanHutang[$b->bulan] + $totalPerAkunEkuitas2[$b->bulan]['total'] + $totalPerBulanEkuitas[$b->bulan];
                        @endphp
                        <th class="text-end dhead">
                            {{ number_format(
                                $totalPerBulanHutang[$b->bulan] + $totalPerAkunEkuitas2[$b->bulan]['total'] + $totalPerBulanEkuitas[$b->bulan],
                                0,
                            ) }}
                        </th>
                    @endforeach
                     <th class="text-end dhead">{{ number_format($totalSemuaPassiva, 0) }}</th> 
                </tr> --}}

            </thead>
        </table>
    </x-slot>


    @section('scripts')
        <script>
            $.ajax({
                type: "GET",
                url: "{{ route('load_pasiva') }}",
                success: function(r) {
                    $("#tb_baris").html(r);
                }
            });
        </script>
    @endsection
</x-theme.app>
