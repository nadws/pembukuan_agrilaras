<x-theme.app cont="container-fluid" title="{{ $title }}" cont="container-fluid" table="Y" sizeCard="5">
    <x-slot name="cardHeader">
        <div class="col-lg-12">
            <h4>Pertanggal: {{ tanggal($tanggal) }}</h4>
            <a href="#" data-bs-toggle="modal" data-bs-target="#import" class="btn btn-sm btn-primary float-end"><i
                    class="fas fa-download"></i> import biaya</a>
            <a href="#" data-bs-toggle="modal" data-bs-target="#import2"
                class="btn btn-sm btn-primary float-end me-2"><i class="fas fa-download"></i> import biaya hpp</a>
            <a href="#" data-bs-toggle="modal" data-bs-target="#view"
                class="btn btn-sm btn-primary float-end me-2"><i class="fas fa-calendar-week"></i> view</a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th class="text-end">Populasi</th>
                        <th class="text-end">Biaya : {{ number_format(sumBk($biaya, 'total_biaya')) }}</th>
                        <th class="text-end">Biaya Hpp</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $ttlbiaya = 0;
                    @endphp
                    @foreach ($populasi as $p)
                        <tr>
                            <td>{{ $p->nm_kandang }}</td>
                            <td class="text-end">{{ number_format($p->stok_awal - $p->mati - $p->jual - $p->afkir, 0) }}
                            </td>
                            @php
                                $pop = $p->stok_awal - $p->mati - $p->jual - $p->afkir;
                                $ttlpop =
                                    sumBk($populasi, 'stok_awal') -
                                    sumBk($populasi, 'mati') -
                                    sumBk($populasi, 'jual') -
                                    sumBk($populasi, 'afkir');
                                $ttlbiaya += ($pop / $ttlpop) * sumBk($biaya, 'total_biaya');
                            @endphp
                            <td class="text-end">{{ number_format(($pop / $ttlpop) * sumBk($biaya, 'total_biaya'), 0) }}
                            </td>
                            <td class="text-end">{{ number_format($p->biaya, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th class="text-end">
                            {{ number_format(sumBk($populasi, 'stok_awal') - sumBk($populasi, 'mati') - sumBk($populasi, 'jual') - sumBk($populasi, 'afkir'), 0) }}
                        </th>
                        <th class="text-end">{{ number_format($ttlbiaya, 0) }}</th>
                        <th class="text-end">{{ number_format(sumBk($populasi, 'biaya'), 0) }}</th>
                    </tr>
                </tfoot>
            </table>
        </section>



        <form action="{{ route('import_biaya') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Import Biaya" idModal="import">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">File</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="col-lg-12">
                        <label for="">Bulan</label>
                        <select name="bulan" id="" class="form-control" required>
                            <option value="">Pilih Bulan</option>
                            @foreach ($bulan as $item)
                                <option value="{{ $item->bulan }}">{{ $item->nm_bulan }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-lg-12">
                        <label for="">Tahun</label>
                        <select name="tahun" id="" class="form-control" required>
                            <option value="">Pilih Tahun</option>
                            <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
                            <option value="{{ date('Y', strtotime('+1 year')) }}">
                                {{ date('Y', strtotime('+1 year')) }}</option>
                        </select>

                    </div>
                </div>
            </x-theme.modal>
        </form>
        <form action="{{ route('import_biaya_hpp') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Import Biaya" idModal="import2">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">File</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="col-lg-12">
                        <label for="">Bulan</label>
                        <select name="bulan" id="" class="form-control" required>
                            <option value="">Pilih Bulan</option>
                            @foreach ($bulan as $item)
                                <option value="{{ $item->bulan }}">{{ $item->nm_bulan }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-lg-12">
                        <label for="">Tahun</label>
                        <select name="tahun" id="" class="form-control" required>
                            <option value="">Pilih Tahun</option>
                            <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
                            <option value="{{ date('Y', strtotime('+1 year')) }}">
                                {{ date('Y', strtotime('+1 year')) }}</option>
                        </select>

                    </div>
                </div>
            </x-theme.modal>
        </form>
        <form action="#" method="GET">

            <x-theme.modal title="View data" idModal="view">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">Bulan</label>
                        <select name="bulan" id="" class="form-control" required>
                            <option value="">Pilih Bulan</option>
                            @foreach ($bulan as $item)
                                <option value="{{ $item->bulan }}">{{ $item->nm_bulan }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-lg-12">
                        <label for="">Tahun</label>
                        <select name="tahun" id="" class="form-control" required>
                            <option value="">Pilih Tahun</option>
                            <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
                            <option value="{{ date('Y', strtotime('+1 year')) }}">
                                {{ date('Y', strtotime('+1 year')) }}</option>
                        </select>

                    </div>
                </div>
            </x-theme.modal>
        </form>
    </x-slot>

</x-theme.app>
