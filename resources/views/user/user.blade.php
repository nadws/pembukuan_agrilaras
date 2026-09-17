<x-theme.app title="{{ $title }}" table="Y" sizeCard="8">
    <x-slot name="cardHeader">
        <h3 class="float-start mt-1">{{ $title }}</h3>
        @if(!empty($tambah))
        <x-theme.button modal="Y" idModal="tambahModal" icon="fa-plus" addClass="float-end" teks="Tambah" />
        @endif
    </x-slot>

    <x-slot name="cardBody">
        <section class="row">
            <table class="table" id="table1">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user as $no => $d)
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td>{{ ucwords($d->name) }}</td>
                        <td>{{ ucwords($d->posisi->nm_posisi) }}</td>
                        <td>
                            @if(!empty($hapus))
                            <x-theme.button hapus="Y" href="{{ route('user.delete', ['id_user' => $d->id]) }}"
                                icon="fa-trash" addClass="float-end" teks="" variant="danger" />
                            @endif
                            @if(!empty($edit))
                            <x-theme.button modal="Y" idModal="edit-modal" icon="fa-pen"
                                addClass="me-1 float-end edit-btn" teks=""
                                data="url={{ route('user.edit', ['id' => $d->id]) }}" />
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- ALL MODAL --}}
        <form action="{{ route('user.create') }}" method="post">
            @csrf
            <x-theme.modal idModal="tambahModal" title="tambah user" btnSave="Y" size="modal-lg">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Nama</label>
                            <input type="text" name="name" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Posisi</label>
                            <select name="posisi_id" class="form-control" id="">
                                <option value="">- Pilih Posisi -</option>
                                @foreach ($posisi as $p)
                                <option value="{{ $p->id_posisi }}">{{ $p->nm_posisi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Password</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                    </div>
                </div>
            </x-theme.modal>
        </form>

        <form action="{{ route('user.update') }}" method="post">
            @csrf
            <x-theme.modal idModal="edit-modal" title="tambah user" btnSave="Y" size="modal-lg">
                <div id="editBody"></div>
            </x-theme.modal>
        </form>

    </x-slot>

    @section('scripts')
    <script>
        $(document).ready(function() {
                $(document).on('click', '.edit-btn', function() {
                    var url = $(this).attr('url')
                    $('#editBody').html('<p class="text-muted">Memuat...</p>');
                    $.ajax({
                        type: "GET",
                        url: url,
                        success: function(response) {
                            $('#editBody').html(response);
                        },
                        error: function() {
                            $('#editBody').html('<div class="alert alert-danger">Gagal memuat form edit. Silakan coba lagi.</div>');
                        }
                    });

                })
            });
    </script>
    @endsection
</x-theme.app>