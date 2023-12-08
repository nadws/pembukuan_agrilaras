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
        {{-- @php
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
        @endphp --}}
        @php
            function getKas($tgl1, $tgl2, $klasifikasi)
            {
                $kas = \App\Models\NeracaAldi::GetKas($tgl1, $tgl2, $klasifikasi);
                $debit_kas = $kas->debit ?? 0;
                $kredit_kas = $kas->kredit ?? 0;
                return $debit_kas - $kredit_kas;
            }

            $total_per_bulan = [];
            foreach ($bulans as $d) {
                $bln = $d->bulan;
                $tgl1 = '2020-01-01';
                $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

                // Hitung total per bulan untuk KAS dan BANK
                $total_per_bulan[$bln]['kas'] = getKas($tgl1, $tgl2,1);
                $total_per_bulan[$bln]['bank'] = getKas($tgl1, $tgl2,2);
                $total_per_bulan[$bln]['piutang'] = getKas($tgl1, $tgl2,7);
                $total_per_bulan[$bln]['persediaan'] = getKas($tgl1, $tgl2,6);
            }

            // Menambahkan total keseluruhan untuk KAS dan BANK
            $ttlKas = array_sum(array_column($total_per_bulan, 'kas'));
            $ttlBank = array_sum(array_column($total_per_bulan, 'bank'));
            $ttlPiutang = array_sum(array_column($total_per_bulan, 'piutang'));
            $ttlPersediaan = array_sum(array_column($total_per_bulan, 'persediaan'));
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
                        <div style="cursor: pointer" @click="open1 = ! open1"><i class=" fas fa-caret-down"></i>
                            KAS
                        </div>
                    </th>
                    @foreach ($bulans as $d)
                        <th class="text-end">{{ number_format($total_per_bulan[$d->bulan]['kas'], 0) }}</th>
                    @endforeach
                    <th class="text-end">{{ number_format($ttlKas, 0) }}</th>
                </tr>
                <tr>

                </tr>
                <tr>

                    <th class="ps-4">
                        <div style="cursor: pointer" @click="open2 = ! open2"><i class=" fas fa-caret-down"></i>
                            BANK
                        </div>
                    </th>
                    @foreach ($bulans as $d)
                        <th class="text-end">{{ number_format($total_per_bulan[$d->bulan]['bank'], 0) }}</th>
                    @endforeach
                    <th class="text-end">{{ number_format($ttlBank, 0) }}</th>
                </tr>
                <tr>

                    <th class="ps-4">
                        <div style="cursor: pointer" @click="open3 = ! open3"><i class=" fas fa-caret-down"></i>
                            PIUTANG DAGANG
                        </div>
                    </th>
                    @foreach ($bulans as $d)
                        <th class="text-end">{{ number_format($total_per_bulan[$d->bulan]['piutang'], 0) }}</th>
                    @endforeach
                    <th class="text-end">{{ number_format($ttlPiutang, 0) }}</th>
                </tr>
                <tr>

                    <th class="ps-4">
                        <div style="cursor: pointer" @click="open4 = ! open4"><i class=" fas fa-caret-down"></i>
                            PERSEDIAAN
                        </div>
                    </th>
                    @foreach ($bulans as $d)
                        <th class="text-end">{{ number_format($total_per_bulan[$d->bulan]['persediaan'], 0) }}</th>
                    @endforeach
                    <th class="text-end">{{ number_format($ttlPersediaan, 0) }}</th>
                </tr>

            </thead>

        </table>
    </x-slot>

    @section('scripts')
    @endsection
</x-theme.app>
