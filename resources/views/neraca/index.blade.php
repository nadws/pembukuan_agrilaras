<x-theme.app title="{{ $title }}" table="Y" sizeCard="7" cont="container-fluid">
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
            @php
                function getNmBulan($bulanV)
                {
                    return DB::table('bulan')
                        ->where('bulan', $bulanV)
                        ->first()->nm_bulan;
                }
            @endphp
            <div class="col-lg-12">
                <h6 class="float-start mt-1">{{ $title }} {{ getNmBulan($bulan) }} {{ $tahun }}</h6>
                <button type="button" data-bs-toggle="modal" data-bs-target="#view"
                    class="btn btn-primary btn-sm float-end me-2"><i class="fas fa-calendar-week"></i> View
                </button>

                <form action="">
                    <x-theme.modal title="View Bulan" idModal="view">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="">Bulan</label>
                                    <select name="bulan1" class="form-control selectView" id="">
                                        <option value="">- Pilih Bulan -</option>
                                        @foreach ($bulans as $b)
                                            <option value="{{ $b->bulan }}">{{ strtoupper($b->nm_bulan) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="">Tahun</label>
                                    <select name="tahun" class="form-control selectView" id="">
                                        <option value="">- Pilih Tahun -</option>
                                        @php
                                            $tahun = [2022, 2023];
                                        @endphp
                                        @foreach ($tahun as $d)
                                            <option {{ $d == date('Y') ? 'selected' : '' }} value="{{ $d }}">
                                                {{ $d }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </x-theme.modal>
                </form>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">

        <div class="row">
            <table class="table table-bordered table-hover table-striped" x-data="{
                open1: false,
                open2: false,
                open3: false,
                open4: false,
            }">
                <thead>
                    <tr>
                        <th class="dhead">Aktiva</th>
                    </tr>
                    <tr>
                        <th class="ps-4">Aktiva Lancar</th>
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

                        <td class="ps-5">
                            <div style="cursor: pointer" class="float-start me-2" @click="open1 = ! open1"><i
                                    class=" fas fa-caret-down"></i>
                                KAS
                            </div>
                            <span class="float-end me-5">Rp {{ number_format($ttl_kas, 0) }}</span>

                        </td>
                    </tr>
                    @foreach ($kas as $k)
                        <tr  x-show="open1">
                            <td class="ps-5">
                                <a target="_blank"
                                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}
                                </a>
                                <span
                                    class="float-end me-5">{{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}</span>


                            </td>

                        </tr>
                    @endforeach
                    <tr>

                        <td class="ps-5">
                            <div style="cursor: pointer" class="float-start me-2" @click="open2 = ! open2"><i
                                    class=" fas fa-caret-down"></i>
                                BANK
                            </div>
                            <span class="float-end me-5">Rp {{ number_format($ttl_bank, 0) }}</span>

                        </td>
                    </tr>
                    @foreach ($bank as $k)
                        <tr  x-show="open2">
                            <td class="ps-5">
                                <a target="_blank"
                                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}
                                </a>
                                <span
                                    class="float-end me-5">{{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}</span>
                            </td>

                        </tr>
                    @endforeach
                    <tr>

                        <td class="ps-5">
                            <div style="cursor: pointer" class="float-start me-2" @click="open2 = ! open2"><i
                                    class=" fas fa-caret-down"></i>
                                PIUTANG DAGANG
                            </div>
                            <span class="float-end me-5">Rp {{ number_format($ttl_bank, 0) }}</span>

                        </td>
                    </tr>
                    @foreach ($piutang as $k)
                        <tr  x-show="open2">
                            <td class="ps-5">
                                <a target="_blank"
                                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $k->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ $k->nm_akun }}
                                </a>
                                <span
                                    class="float-end me-5">{{ number_format($k->debit + $k->debit_saldo - $k->kredit - $k->kredit_saldo, 0) }}</span>
                            </td>

                        </tr>
                    @endforeach
                    <tr>
                        <th class="ps-4">Total Aktiva Lancar</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </x-slot>

</x-theme.app>

