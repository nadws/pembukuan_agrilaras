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
            $ttlKas = array_sum(array_column($total_per_bulan, 'kas'));
            $ttlBank = array_sum(array_column($total_per_bulan, 'bank'));
            $ttlPiutang = array_sum(array_column($total_per_bulan, 'piutang'));
            $ttlPersediaan = array_sum(array_column($total_per_bulan, 'persediaan'));
            $totalPerBulan = [];
            foreach ($bulans as $d) {
                $bln = $d->bulan;
                $totalPerBulan[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
            }

            // Hitung total per bulan
            foreach ($total_per_bulan as $bulan => $nilai) {
                $totalPerBulan[$bulan] += $nilai['kas'] + $nilai['bank'] + $nilai['piutang'] + $nilai['persediaan'];
            }
            $totalSemua = $ttlKas + $ttlBank + $ttlPiutang + $ttlPersediaan;

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
                    <th class="dhead">
                        Aktiva
                    </th>
                    @foreach ($bulans as $d)
                        <th class="dhead text-center">{{ $d->nm_bulan }}</th>
                    @endforeach
                    <th class="dhead text-center">Total</th>
                </tr>
                <tr>
                    <th class="dhead ps-3" colspan="14">Aktiva Lancar</th>
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
                    <th class="text-end">{{ number_format($ttlKas, 0) }}</th>
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
                        <td class="text-end">
                            {{ number_format($total, 0) }}
                        </td>
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
                    <th class="text-end">{{ number_format($ttlBank, 0) }}</th>
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
                        <td class="text-end">
                            {{ number_format($total, 0) }}
                        </td>
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
                    <th class="text-end">{{ number_format($ttlPiutang, 0) }}</th>
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
                        <td class="text-end">
                            {{ number_format($total, 0) }}
                        </td>
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
                    <th class="text-end">{{ number_format($ttlPersediaan, 0) }}</th>
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
                        <td class="text-end">
                            {{ number_format($total, 0) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th class="dhead ps-3">Total Aktiva Lancar</th>
                    @foreach ($bulans as $d)
                        <th class="text-end dhead">{{ number_format($totalPerBulan[$d->bulan], 0) }}</th>
                    @endforeach
                    <th class="text-end dhead">{{ number_format($totalSemua, 0) }}</th>
                </tr>
            </thead>

        </table>
    </x-slot>

    @section('scripts')
    @endsection
</x-theme.app>
