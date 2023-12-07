<x-theme.app title="{{ $title }}" table="Y" sizeCard="8" cont="container-fluid">
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
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="dhead"></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </x-slot>

</x-theme.app>
