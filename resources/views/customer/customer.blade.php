<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <x-theme.button modal="Y" idModal="tambah" icon="fa-plus" addClass="float-end" teks="Tambah" />
    </x-slot>

    <x-slot name="cardBody">
        <section class="row">


            <table class="table" id="table1">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>NPWP</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Telepon</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customer as $no => $d)
                        <tr>
                            <td>{{ $no + 1 }}</td>

                            <td>{{ $d->npwp }}</td>
                            <td>{{ ucwords($d->nm_customer) }}</td>
                            <td>{{ ucwords($d->alamat) }}</td>
                            <td>{{ $d->no_telp }}</td>
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
        {{-- tambah customer --}}
        <form action="{{ route('customer.create') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Tambah Baru" idModal="tambah">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">Nama</label>
                            <input type="text" name="nm_customer" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">Alamat</label>
                            <input type="text" name="alamat" class="form-control">
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">Telepon</label>
                            <input type="text" name="telepon" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">Npwp</label>
                            <input type="text" name="npwp" class="form-control">
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
