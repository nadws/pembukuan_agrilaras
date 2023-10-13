<x-theme.app title="{{ $title }}" table="T" sizeCard="12" cont="container-fluid">
    <div class="row" x-data="{
        open: true
    }">

        <div class="row">

            <div class="col-lg-9">
                <button type="button" class="btn btn-sm" :class="{ 'btn-primary': !open }"
                    @click="open = false">hotel</button>
                <button type="button" class="btn btn-sm" :class="{ 'btn-primary': open }"
                    @click="open = true">gugel</button>
                <form method="post" action="{{ route('budget.create') }}">
                    @csrf
                    <div class="card">
                        <div class="card-header">
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
                            
                            <table x-show="!open" :class="{ 'd-none': open }" class="table table-striped table-bordered" id="tablealdi2"
                                x-data="{}">
                                <thead>
                                    <tr>
                                        <th>TOTAL</th>
                                        <th class="text-end">{{ number_format(100000000, 0) }}</th>
                                        <th>&nbsp;</th>
                                        <th class="text-end">{{ number_format(100000000, 0) }}</th>
                                        <th>&nbsp;</th>
                                        <th class="text-end">{{ number_format(100000000, 0) }}</th>
                                        <th>&nbsp;</th>
                                        <th class="text-end">{{ number_format(100000000, 0) }}</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    <tr>
                                        <th>Uraian</th>
                                        <th class="text-center">Budget'19</th>
                                        <th>%</th>
                                        <th class="text-center">Budget'22</th>
                                        <th>%</th>
                                        <th class="text-center">Actual'19</th>
                                        <th>%</th>
                                        <th class="text-center">Actual'22</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($biaya as $i => $d)
                                        <tr>
                                            <td>{{ ucwords(strtolower($d->nm_akun)) }}</td>
                                            <td>
                                                <input value="5000000" type="text" x-mask:dynamic="$money($input)"
                                                    class="form-control text-end" name="budget[]">
                                            </td>
                                            <td class="">0.38</td>
                                            <td>
                                                <input value="5000000" type="text" x-mask:dynamic="$money($input)"
                                                    class="form-control text-end" name="budget[]">
                                            </td>
                                            <td class="">0.38</td>
                                            <td>
                                                <input value="5000000" type="text" x-mask:dynamic="$money($input)"
                                                    class="form-control text-end" name="budget[]">
                                            </td>
                                            <td class="">0.38</td>
                                            <td>
                                                <input value="5000000" type="text" x-mask:dynamic="$money($input)"
                                                    class="form-control text-end" name="budget[]">
                                            </td>
                                            <td class="">0.38</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <table x-show="open" :class="{ 'd-none': !open }" class="table table-sm table-bordered" id="tablealdi2"
                                x-data="{}">
                                <thead>
                                    <tr>
                                        <th>Uraian</th>
                                        <th width="150">Budget</th>
                                        @foreach ($bulanView as $d)
                                            <th>{{ $d->nm_bulan }} {{ date('Y') }}</th>
                                            {{-- <th>Selisih</th> --}}
                                        @endforeach
                                        {{-- <th>Total</th> --}}
                                        {{-- <th>%</th> --}}
                                        {{-- <th>Realisasi</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($biaya as $b)
                                        <tr>
                                            <td>{{ ucwords(strtolower($b->nm_akun)) }} </td>
                                            <input type="hidden" name="id_akun[]" value="{{ $b->id_akun }}">
                                            <td>
                                                <input type="text" value="0" x-mask:dynamic="$money($input)"
                                                    class="form-control text-end" name="budget_perbulan[]">
                                            </td>
                                            @foreach ($bulanView as $d)
                                               @php
                                                $tgl1b = date("Y-09-01");
                                                $tgl2b = date("Y-09-t");

                                                $biayaPerakun = DB::selectOne("SELECT a.id_akun, a.nm_akun, b.debit FROM akun as a 
                                                LEFT JOIN ( SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit FROM jurnal as a 
                                                            left join ( SELECT b.no_nota , b.id_akun, c.nm_akun FROM jurnal as b 
                                                                        left join akun as c on c.id_akun = b.id_akun where b.id_akun in 
                                                                        (SELECT t.id_akun FROM akuncash_ibu as t 
                                                                        where t.kategori in ('6','7')) and b.tgl BETWEEN '$tgl1b' and '$tgl2b' and b.kredit != 0 and b.id_buku in(2,10,12) GROUP by b.no_nota ) as b on b.no_nota = a.no_nota 
                                                                        left join akun as c on c.id_akun = a.id_akun 
                                                                        where a.id_buku in(2,10,12) and a.debit != 0 and a.tgl BETWEEN '$tgl1b' and '$tgl2b' AND a.id_akun = '$b->id_akun' and b.id_akun is not null group by a.id_akun ) AS b on b.id_akun = a.id_akun 
                                                                        where a.id_klasifikasi in('3','6','11','12')");
                                               @endphp
                                                {{-- @php
                                                $getBudget = DB::table('budget')->where([['id_akun', $b->id_akun],['tgl', "2023-$d->bulan-01"]])->first();
                                            @endphp --}}
                                                {{-- <input type="hidden" name="bulan[]" value="{{ $d->bulan }}"> --}}
                                                <td class="text-end">
                                                    {{ number_format($biayaPerakun->debit ?? 0, 1) }}
                                                </td>
                                                {{-- <td></td> --}}
                                            @endforeach
                                            {{-- <td class=" text-end">
                                                <b>{{ number_format(3000000, 0) }}</b>
                                            </td>
                                           
                                            @php
                                                $persen = ((433388150 - $b->debit) / 433388150) * 100
                                            @endphp
                                            <td class="">{{ number_format($persen,0) }}%</td>
                                            <td class=" text-end">
                                                <b>{{ number_format($b->debit, 1) }}</b>
                                            </td>
                                            <td class=" text-end">
                                                {{ number_format(433388150 - $b->debit, 0) }}
                                            </td> --}}
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
