<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-end gap-2">
            <a href="{{ route('customer.template-import') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-file-excel me-1"></i> Format Import
            </a>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importCustomer">
                <i class="fas fa-file-import me-1"></i> Import Data
            </button>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambah">
                <i class="fas fa-plus me-1"></i> Tambah Customer
            </button>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        @if ($errors->any())
            <div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>
        @endif
        <section class="row">


            <table class="table" id="table1">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Kode</th>
                        <th>NPWP</th>
                        <th>KTP</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customer as $no => $d)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ $d->kode_customer ?: '-' }}</td>
                            <td>{{ $d->npwp }}</td>
                            <td>{{ $d->ktp }}</td>
                            <td>{{ ucwords($d->nm_customer) }}</td>
                            <td>{{ ucwords($d->alamat) }}</td>
                            <td>{{ $d->no_telp }}</td>
                            <td><span class="badge {{ $d->active === 'Y' ? 'bg-success' : 'bg-secondary' }}">{{ $d->active === 'Y' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td align="center">
                                <div class="btn-group dropstart mb-1">
                                    <span class="btn btn-lg" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v text-primary"></i>
                                    </span>
                                    <div class="dropdown-menu">
                                        <a id_customer="{{ $d->id_customer }}" data-bs-toggle="modal"
                                            data-bs-target="#edit" class="dropdown-item text-primary edit"
                                            href="#"><i class="me-2 fas fa-pen"></i>
                                            Edit</a>
                                        <a class="dropdown-item text-danger" onclick="return confirm('Yakin dihapus ?')"
                                            href="{{ route('customer.delete', $d->id_customer) }}"><i
                                                class="me-2 fas fa-trash"></i> Delete</a>
                                        <a class="dropdown-item text-info" href="#"><i
                                                class="me-2 fas fa-search"></i>
                                            Detail</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </section>
        <form action="{{ route('customer.import') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Import Master Customer" idModal="importCustomer">
                <div class="alert alert-info py-2">
                    Unduh <a href="{{ route('customer.template-import') }}" class="fw-bold">Format Import</a>, isi sheet Data Customer, lalu unggah kembali di sini.
                </div>
                <div class="form-group">
                    <label class="form-label">File Excel atau CSV</label>
                    <input type="file" name="file_customer" class="form-control" accept=".xlsx,.xls,.csv,.txt" required>
                    <small class="text-muted">Kode customer boleh dikosongkan agar dibuat otomatis. Ukuran maksimal 5 MB.</small>
                </div>
            </x-theme.modal>
        </form>
        {{-- tambah customer --}}
        <form action="{{ route('customer.create') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Tambah Baru" idModal="tambah">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Nama</label>
                            <input type="text" name="nm_customer" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Alamat</label>
                            <input type="text" name="alamat" class="form-control">
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Telepon</label>
                            <input type="text" name="telepon" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Npwp</label>
                            <input type="text" name="npwp" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">KTP</label>
                            <input type="text" name="ktp" class="form-control">
                        </div>
                    </div>
                </div>
            </x-theme.modal>
        </form>
        {{-- ------ --}}

        {{-- edit customer --}}
        <form action="{{ route('customer.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal size="modal-lg" title="Edit Customer" idModal="edit">
                <div id="load-edit"></div>
            </x-theme.modal>
        </form>
    </x-slot>
    @section('scripts')
        <script>
            $(document).ready(function() {
                edit('edit', 'id_customer', 'customer/edit', 'load-edit')
            });
        </script>
    @endsection
</x-theme.app>
