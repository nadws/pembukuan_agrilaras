<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <h5 class="float-start mt-1">{{ $title }}</h5>
        <div class="row ">
            <div class="col-lg-12">
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
                        <th>Kandang</th>
                        <th>Tanggal</th>
                        <th>Kode Perkiraan</th>
                        <th>Nama</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($jurnal as $j)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $j->nm_departemen }}</td>
                            <td>{{ tanggal($j->tgl) }}</td>
                            <td>{{ $j->kode }}</td>
                            <td>{{ $j->nama }}</td>
                            <td class="text-end">{{ number_format($j->total_debit, 0) }}</td>
                            <td class="text-end">{{ number_format($j->total_kredit, 0) }}</td>
                        </tr>
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
                    {{-- <div class="col-lg-6 mt-2">
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
                    </div> --}}
                </div>
            </x-theme.modal>
        </form>




    </x-slot>

</x-theme.app>
