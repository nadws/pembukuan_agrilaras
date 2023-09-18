<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <h5 class="float-start mt-1">{{ $title }}</h5>
        <div class="row justify-content-end">


            <div class="col-lg-12">
                @if (!empty($export))
                    <x-theme.button modal="Y" idModal="import" icon="fa-file-excel"
                        addClass="float-end btn btn-success me-2" teks="Export / Import" />
                @endif

                @if (!empty($create))
                    <x-theme.button modal="Y" idModal="tambah" icon="fa-plus" addClass="float-end"
                        teks="Buat Baru" />
                @endif

                <x-theme.akses :halaman="$halaman" route="akun" />
            </div>
        </div>


    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-hover" id="table">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Kode</th>
                        <th>Inisial</th>
                        <th>Nama Akun</th>
                        <th>Subklasifikasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($akun as $no => $a)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ $a->kode_akun }}</td>
                            <td>{{ ucwords($a->inisial) }}</td>
                            <td>{{ ucwords(strtolower($a->nm_akun)) }}</td>
                            <td>
                                {{ $a->nm_subklasifikasi }}
                            </td>
                            <td>
                                @php
                                    $badge = $a->is_active != 'Y' ? 'danger' : 'success';
                                    $aktif = $a->is_active != 'Y' ? 'Tidak Aktif' : 'Aktif';
                                @endphp
                                <a href="" class=" badge bg-{{ $badge }}">
                                    {{ $aktif }}</a>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <span class="btn btn-sm" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v text-primary"></i>
                                    </span>
                                    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                        @php
                                            $emptyKondisi = [$edit, $subAkun];
                                        @endphp
                                        <x-theme.dropdown_kosong :emptyKondisi="$emptyKondisi" />
                                        @if (!empty($edit))
                                            <li>
                                                <a class="dropdown-item text-info edit_akun" href="#"
                                                    data-bs-toggle="modal" data-bs-target="#edit"
                                                    id_akun="{{ $a->id_akun }}"><i
                                                        class="me-2 fas fa-pen"></i>Edit</a>
                                            </li>
                                        @endif
                                        @if (!empty($subAkun))
                                            <li>
                                                <a class="dropdown-item text-info sub-akun" href="#"
                                                    data-bs-toggle="modal" data-bs-target="#sub-akun"
                                                    id_akun="{{ $a->id_akun }}"><i
                                                        class="me-2 fas fa-layer-group"></i>Sub
                                                    Akun</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <form action="{{ route('akun') }}" method="post">
            @csrf
            <x-theme.modal title="Tambah Akun" idModal="tambah">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Subklasifikasi</label>
                            <select name="id_klasifikasi" id="" class="select2 get_kode">
                                <option value="">Pilih Subklasifikasi</option>
                                @foreach ($subklasifikasi as $s)
                                    <option value="{{ $s->id_subklasifikasi_akun }}">{{ $s->nm_subklasifikasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="">Kode Akun</label>
                            <input type="text" class="form-control kode" name="kode_akun" required>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="">Inisial</label>
                            <input type="text" class="form-control" name="inisial" required>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">Nama Akun</label>
                            <input type="text" name="nm_akun" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">Iktisar Laba Rugi</label>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-check">
                            <input value="Y" required class="form-check-input" type="radio" name="iktisar"
                                id="check1">
                            <label class="form-check-label" for="check1">
                                Ya
                            </label>
                        </div>

                    </div>
                    <div class="col-lg-2">
                        <div class="form-check">
                            <input value="T" required class="form-check-input" type="radio" name="iktisar"
                                id="check2">
                            <label class="form-check-label" for="check2">
                                Tidak
                            </label>
                        </div>
                    </div>
                </div>
            </x-theme.modal>
        </form>

        <form action="{{ route('importAkun') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Import Akun" size="modal-lg" idModal="import">
                <div class="row">
                    <table>
                        <tr>
                            <td width="100" class="pl-2">
                                <img width="80px" src="{{ asset('img/') }}/1.png" alt="">
                            </td>
                            <td>
                                <span style="font-size: 20px;"><b> Download Excel template</b></span><br>
                                File ini memiliki kolom header dan isi yang sesuai dengan data produk
                            </td>
                            {{-- <td>
                            <a href="{{ route('exportPaket') }}" class="btn btn-primary btn-sm"><i
                                    class="fa fa-download"></i> DOWNLOAD TEMPLATE</a>
                        </td> --}}
                            <td>
                                <a href="{{ route('export_akun') }}" class="btn btn-primary btn-sm"><i
                                        class="fa fa-download"></i> DOWNLOAD
                                    TEMPLATE</a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <hr>
                            </td>
                        </tr>

                        <tr>
                            <td width="100" class="pl-2">
                                <img width="80px" src="{{ asset('img/') }}/2.png" alt="">
                            </td>
                            <td>
                                <span style="font-size: 20px;"><b> Upload Excel template</b></span><br>
                                Setelah mengubah, silahkan upload file.
                            </td>
                            <td>
                                <input type="file" name="file" class="form-control">
                            </td>
                        </tr>
                    </table>

                </div>
            </x-theme.modal>
        </form>

        {{-- edit --}}
        <form action="{{ route('akun.update') }}" method="post">
            @csrf
            <x-theme.modal title="Edit Akun" idModal="edit">
                <div id="get_edit">

                </div>
            </x-theme.modal>
        </form>
        {{-- end edit --}}

        {{-- sub akun --}}
        <x-theme.modal title="Edit Akun" idModal="sub-akun" size="modal-lg">
            <div id="load-sub-akun">
            </div>
        </x-theme.modal>
        {{-- end sub akun --}}

    </x-slot>
    @section('scripts')
        <script>
            $(document).ready(function() {
                $(document).on("change", ".get_kode", function() {
                    var id_sub = $(this).val();
                    $.ajax({
                        type: "get",
                        url: "/get_kode?id_sub=" + id_sub,
                        success: function(data) {
                            $('.kode').val(data.kode);
                            $('.kode2').val(data.kode_max);
                        }
                    });
                });

                function toast(pesan) {
                    Toastify({
                        text: pesan,
                        duration: 3000,
                        style: {
                            background: "#EAF7EE",
                            color: "#7F8B8B"
                        },
                        close: true,
                        avatar: "https://cdn-icons-png.flaticon.com/512/190/190411.png"
                    }).showToast();
                }
                edit('edit_akun', 'id_akun', 'get_edit_akun', 'get_edit')

                function loadSubAkun(id) {
                    $.ajax({
                        type: "GET",
                        url: `load_sub_akun/${id}`,
                        success: function(r) {
                            $(`#load-sub-akun`).html(r);
                            $('.select2-edit').select2({
                                dropdownParent: $('#edit .modal-content')
                            });
                            $('#table-edit').DataTable({
                                "paging": true,
                                "pageLength": 10,
                                "lengthChange": true,
                                "ordering": true,
                                "searching": true,
                            });
                        }
                    });
                }

                $(document).on('click', `.sub-akun`, function() {
                    var id = $(this).attr('id_akun')
                    loadSubAkun(id)
                })

                // edit('sub-akun', 'id_akun', 'load_sub_akun', 'load-sub-akun')

                $(document).on('click', '.save-sub', function() {
                    var name = $("#name-sub").val()
                    var id_akun = $(this).attr('id_akun')
                    $.ajax({
                        type: "GET",
                        url: "{{ route('akun.add_sub') }}",
                        data: {
                            nm_post: name,
                            id_akun: id_akun
                        },
                        success: function(r) {
                            toast('Berhasil tambah sub akun')
                            $("#name-sub").val(null)
                            loadSubAkun(id_akun)
                        }
                    });

                })

                $(document).on('click', '.remove-sub', function() {
                    var id = $(this).attr('id_sub_akun')
                    var id_akun = $(this).attr('id_akun')
                    if (confirm('Yakin dihapus')) {
                        $.ajax({
                            type: "GET",
                            url: "{{ route('akun.remove_sub') }}",
                            data: {
                                id: id
                            },
                            success: function(r) {
                                toast('Berhasil hapus sub akun')
                                loadSubAkun(id_akun)
                            }
                        });
                    }
                })
            });
        </script>
    @endsection
</x-theme.app>
