<x-theme.app title="{{ $title }}" table="T" sizeCard="12" cont="container-fluid">
    <div class="row" x-data="{
        open: true
    }">

        <div class="row">

            <div class="col-lg-10">


                <form method="post" action="{{ route('budget.create') }}">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    @include('budget.nav')
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-5">
                                    <h6 for="" class="float-start">{{ $title }} {{ tanggal($tgl1) }} ~
                                        {{ tanggal($tgl2) }}</h6>
                                </div>
                                <div class="col-lg-7">
                                    <table class="float-end">
                                        <tr>
                                            <td>{{ count($biaya) }} Pencarian :</td>
                                            <td><input autofocus type="text" id="pencarian2"
                                                    class="form-control float-end">
                                            </td>
                                        </tr>
                                    </table>
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#view"
                                        class="btn btn-primary btn-sm float-end me-2"><i
                                            class="fas fa-calendar-week"></i> View</button>
                                    <button type="submit" class="btn btn-primary btn-sm float-end me-2">Simpan</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @php
                                $bulanInput1 = request()->get('bulan1') ?? $tglMundur1;
                                $bulanInput2 = request()->get('bulan2') ?? $tglMundur2;
                                $tahunInput = request()->get('tahun') ?? $tahun;
                            @endphp
                            <input type="hidden" name="bulan1" value="{{ $bulanInput1 }}">
                            <input type="hidden" name="bulan2" value="{{ $bulanInput2 }}">
                            <input type="hidden" name="tahun" value="{{ $tahunInput }}">
                            <table x-show="open" class="table table-sm table-bordered" id="tablealdi2"
                                x-data="{}">
                                <thead>
                                    @php
                                        function getBiayaPerakun($bulanInput1,$bulanInput2,$tahunInput,$id_akun, $bulanPilih)
                                        {
                                            $biayaPerakun = DB::selectOne("SELECT a.id_akun, a.no_nota, sum(a.debit) as debit FROM jurnal as a
                                                inner join ( SELECT b.no_nota , b.id_akun, c.nm_akun FROM jurnal as b 
                                                    inner join akun as c on c.id_akun = b.id_akun where b.id_akun in (
                                                    SELECT t.id_akun FROM akuncash_ibu as t 
                                                    where t.kategori in ('6','7')) and (YEAR(b.tgl) = '$tahunInput' AND MONTH(b.tgl) BETWEEN '$bulanInput1' AND '$bulanInput2') and b.kredit != 0 and b.id_buku in(2,10,12) 
                                                    GROUP by b.no_nota 
                                                    ) as b on b.no_nota = a.no_nota
                                            where a.id_buku in(2,10,12) and a.debit != 0 
                                            and (YEAR(a.tgl) = '$tahunInput' AND MONTH(a.tgl) BETWEEN '$bulanInput1' AND '$bulanInput2') AND a.id_akun = '$id_akun' AND MONTH(a.tgl) = '$bulanPilih'  
                                            and b.id_akun is not null group by a.id_akun");
                                            return $biayaPerakun;
                                        }

                                        // Inisialisasi array total per bulan
                                        $totalPerBulan = [];

                                        // Inisialisasi total debit awal
                                        $totalDebitTotal = 0;
                                        foreach ($biaya as $b) {
                                            foreach ($bulanView as $d) {
                                                $biayaPerakun = getBiayaPerakun($bulanInput1,$bulanInput2,$tahunInput,$b->id_akun, $d->bulan);
                                                // Tambahkan nilai debit ke total per bulan
                                                $totalPerBulan[$d->bulan] = ($totalPerBulan[$d->bulan] ?? 0) + ($biayaPerakun->debit ?? 0);

                                                // Tambahkan nilai debit ke total debit total
                                                $totalDebitTotal += $biayaPerakun->debit ?? 0;
                                            }
                                        }

                                    @endphp
                                    <tr>
                                        <th>Uraian</th>
                                        @foreach ($bulanView as $d)
                                            <th>{{ $d->nm_bulan }} {{ date('Y') }}</th>
                                            <th>%</th>
                                        @endforeach
                                        <th width="150">Budget</th>

                                    </tr>
                                    <tr>
                                        <th>Total</th>
                                        @foreach ($bulanView as $d)
                                            <th class="text-end">{{ number_format($totalPerBulan[$d->bulan], 1) }}</th>
                                            <th></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($biaya as $b)
                                        <tr>
                                            <td>{{ ucwords(strtolower($b->nm_akun)) }}</td>
                                            <input type="hidden" name="id_akun[]" value="{{ $b->id_akun }}">
                                            @php
                                                $budget = DB::table('budget')
                                                    ->where([['id_akun', $b->id_akun], ['tgl_hapus', null]])
                                                    ->first();
                                            @endphp
                                            @foreach ($bulanView as $d)
                                                @php
                                                    $biayaPerakun = getBiayaPerakun($bulanInput1,$bulanInput2,$tahunInput,$b->id_akun, $d->bulan);
                                                    $debit = $biayaPerakun->debit ?? 0;
                                                    $ttl = $totalPerBulan[$d->bulan] ?? 0;
                                                    // $persen = $ttl / $debit;
                                                @endphp

                                                <td class="text-end">
                                                    {{ number_format($debit, 1) }}
                                                </td>
                                                <td>{{ number_format(!empty($debit) ? (!empty($budget->rupiah) ? ($debit / $budget->rupiah) * 100 : 0) : 0, 0) }}%
                                                </td>
                                            @endforeach

                                            <td>
                                                <input type="text" value="{{ $budget->rupiah ?? 0 }}"
                                                    x-mask:dynamic="$money($input)" class="form-control text-end"
                                                    name="budget[]">
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>

                        </div>
                    </div>
                </form>

            </div>


        </div>

        <form action="">
            <x-theme.modal title="View Bulan" idModal="view">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="">Dari Bulan</label>
                            <select name="bulan1" class="form-control selectView" id="">
                                <option value="">- Pilih Bulan -</option>
                                @foreach ($bulan as $d)
                                    <option {{ $tglMundur1 == $d->bulan ? 'selected' : '' }}
                                        value="{{ $d->bulan }}">{{ $d->nm_bulan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="">Sampai Bulan</label>
                            <select name="bulan2" class="form-control selectView" id="">
                                <option value="">- Pilih Bulan -</option>
                                @foreach ($bulan as $d)
                                    <option {{ $tglMundur2 == $d->bulan ? 'selected' : '' }}
                                        value="{{ $d->bulan }}">{{ $d->nm_bulan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
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
        @section('scripts')
            <script>
                pencarian('pencarian2', 'tablealdi2')
                // loadHalaman()

                // function loadHalaman() {
                //     $.ajax({
                //         type: "GET",
                //         url: "{{ route('budget.halaman') }}",
                //         data: {
                //             tgl1: "{{ $tgl1 }}",
                //             tgl2: "{{ $tgl2 }}"
                //         },
                //         success: function(r) {
                //             $("#loadHalaman").html(r);
                //             pencarian('pencarian2', 'tablealdi2')
                //         }
                //     });
                // }
            </script>
        @endsection
</x-theme.app>
