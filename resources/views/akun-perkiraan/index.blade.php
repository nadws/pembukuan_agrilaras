<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <h5 class="float-start mt-1">{{ $title }}</h5>
        <div class="row ">
            <div class="col-lg-12">
                <a href="#" data-bs-toggle="modal" data-bs-target="#importbiaya" class="float-end btn btn-success"><i
                        class="fas fa-download"></i> Import biaya</a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#importhpp"
                    class="float-end btn btn-success me-2"><i class="fas fa-download"></i> Import hpp</a>
            </div>
        </div>


    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-hover" id="table">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Kode Perkiraan</th>
                        <th>Nama</th>
                        <th>Tipe Akun</th>
                        <th>Saldo</th>
                    </tr>
                </thead>

                <tbody>
                    @php $no = 1; @endphp

                    @foreach ($akun as $a)
                        @php
                            // $akun2 = DB::table('akun_accurate')->where('akun_induk', $a->kode)->get();
                            $akun2 = DB::select("SELECT a.kode, a.nama, b.debit, b.kredit,a.tipe_akun
                            FROM akun_accurate as a 
                                left join (
                                    SELECT b.kode , sum(b.debit) as debit , sum(b.kredit) as kredit
                                    FROM jurnal_accurate as b
                                    group by b.kode
                                ) as b on b.kode = a.kode
                                where a.akun_induk = '$a->kode'
                                ");
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $a->kode }}</td>
                            <td><strong>{{ $a->nama }}</strong></td>
                            <td>{{ $a->tipe_akun }}</td>
                            <td class="text-end">{{ number_format($a->debit - $a->kredit, 0) }}</td>
                        </tr>
                        @foreach ($akun2 as $a2)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td style="padding-left: 20px;">{{ $a2->kode }}</td>
                                <td>{{ $a2->nama }}</td>
                                <td>{{ $a2->tipe_akun }}</td>
                                <td class="text-end">{{ number_format($a2->debit - $a2->kredit, 0) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>


            </table>
        </section>

        <form action="{{ route('importHpp') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Import biaya hpp" idModal="importhpp">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">File</label>
                        <input type="file" name="file" class="form-control" id="">
                    </div>
                    <div class="col-lg-6 mt-2">
                        <label for="">Bulan</label>
                        <select name="bulan" class="form-control" id="">
                            <option value="">- Pilih Bulan -</option>
                            @foreach ($bulan as $b)
                                <option value="{{ $b->bulan }}" @selected(date('m') == $b->bulan)>{{ $b->nm_bulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-6 mt-2">
                        <label for="">Tahun</label>
                        <select name="tahun" class="form-control" id="">
                            <option value="">- Pilih Tahun -</option>
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                            <option value="{{ date('Y', strtotime('+1 year')) }}">
                                {{ date('Y', strtotime('+1 year')) }}
                            </option>

                        </select>
                    </div>
                </div>
            </x-theme.modal>
        </form>
        <form action="{{ route('importBiaya') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Import biaya" idModal="importbiaya">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">File</label>
                        <input type="file" name="file" class="form-control" id="">
                    </div>
                    <div class="col-lg-6 mt-2">
                        <label for="">Bulan</label>
                        <select name="bulan" class="form-control" id="">
                            <option value="">- Pilih Bulan -</option>
                            @foreach ($bulan as $b)
                                <option value="{{ $b->bulan }}" @selected(date('m') == $b->bulan)>{{ $b->nm_bulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-6 mt-2">
                        <label for="">Tahun</label>
                        <select name="tahun" class="form-control" id="">
                            <option value="">- Pilih Tahun -</option>
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                            <option value="{{ date('Y', strtotime('+1 year')) }}">
                                {{ date('Y', strtotime('+1 year')) }}
                            </option>

                        </select>
                    </div>
                </div>
            </x-theme.modal>
        </form>




    </x-slot>

</x-theme.app>
