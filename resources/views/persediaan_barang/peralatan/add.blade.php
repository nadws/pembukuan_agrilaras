<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <h6 class="float-start">{{ $title }}</h6>
        <button data-bs-toggle="modal" data-bs-target="#tambah"
            class="btn btn-sm icon icon-left btn-primary me-2 float-end btn_bayar">Data
            Kelompok</button>
        <form action="{{ route('peralatan.save_kelompok') }}" method="post">
            @csrf
            <x-theme.modal size="modal-md" title="Data Kelompok Peralatan" idModal="tambah">
                <div x-data="{
                    open: false
                }">
                    <button x-on:click="open = ! open" class="mb-3 btn btn-sm btn-primary"><i class="fas fa-plus"></i>
                        Add</button>
                    <div class="row" x-show="open" x-transition>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">Nama Kelompok</label>
                                <input required type="text" name="nm_kelompok" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="">Umur</label>
                                <input required type="text" name="umur" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="">Peridode</label><br>
                                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                    <input type="radio" value="bulan" class="btn-check" name="periode" id="btnradio1"
                                        autocomplete="off" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="btnradio1">Bulan</label>

                                    <input type="radio" value="tahun" class="btn-check" name="periode" id="btnradio2"
                                        autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm" for="btnradio2">Tahun</label>

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="">Barang Kelompok</label>
                                <input type="text" name="barang_kelompok" class="form-control">
                            </div>
                        </div>
                    </div>
                    <hr><br>
                    <table class="table" id="table">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Nama Kelompok</th>
                                <th>Umur</th>
                                <th>Barang Kelompok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kelompok as $no => $d)
                            <tr>
                                <td>{{ $no + 1 }}</td>
                                <td>{{ $d->nm_kelompok }}</td>
                                <td>{{ $d->umur . ' ' . $d->periode }}</td>
                                <td>{{ $d->barang_kelompok }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <span class="btn btn-sm" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v text-primary"></i>
                                        </span>
                                        <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                            <li><a class="dropdown-item text-primary edit_akun"
                                                    id_kelompok="{{ $d->id_kelompok }}"><i
                                                        class="me-2 fas fa-pen"></i>Edit</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger delete_nota"
                                                    no_nota="{{ $d->id_kelompok }}" href="#" data-bs-toggle="modal"
                                                    data-bs-target="#delete"><i class="me-2 fas fa-trash"></i>Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-theme.modal>
        </form>

        <form action="{{ route('peralatan.edit_kelompok') }}" action="post">
            @csrf
            <x-theme.modal size="modal-md" title="Data Kelompok Peralatan" idModal="edit">
                <div id="load_edit"></div>

            </x-theme.modal>
        </form>
    </x-slot>
    <x-slot name="cardBody">
        <form action="{{ route('peralatan.save_aktiva') }}" method="post" class="save_jurnal">
            @csrf
            <section class="row">
                <div class="col-lg-12">
                    <div id="load_aktiva"></div>
                </div>

            </section>
    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary ">Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        <a href="{{ route('aktiva') }}" class="float-end btn btn-outline-primary me-2">Batal</a>
        </form>
    </x-slot>

    @section('scripts')
    <script>
        $(document).ready(function() {
                $(document).on('click', '.edit_akun', function(){
                    var id_kelompok = $(this).attr('id_kelompok')
                    $("#edit").modal('show')
                    $.ajax({
                        type: "GET",
                        url: "{{route('peralatan.load_edit')}}",
                        data: {
                            id_kelompok:id_kelompok
                        },
                        success: function (r) {
                            $("#load_edit").html(r);
                        }
                    });
                })
                load_menu();

                function load_menu() {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('peralatan.load_aktiva') }}",
                        dataType: "html",
                        success: function(hasil) {
                            $("#load_aktiva").html(hasil);
                            $('.select').select2({
                                language: {
                                    searching: function() {
                                        $('.select2-search__field').focus();
                                    }
                                }
                            });

                        },
                    });
                }

                var count = 3;
                $(document).on("click", ".tbh_baris_aktiva", function() {
                    count = count + 1;
                    $.ajax({
                        url: "/tambah_baris_aktiva?count=" + count,
                        type: "Get",
                        success: function(data) {
                            $("#tb_baris_aktiva").append(data);
                            $(".select").select2();
                        },
                    });
                });
                $(document).on("click", ".remove_baris", function() {
                    var delete_row = $(this).attr("count");
                    $(".baris" + delete_row).remove();


                });
                $(document).on("change", ".pilih_kelompok", function() {
                    var count = $(this).attr("count");
                    var id_kelompok = $('.pilih_kelompok' + count).val();
                    var nilai = $('.nilai_perolehan_biasa' + count).val()

                    $.ajax({
                        type: "GET",
                        url: "{{ route('peralatan.get_data_kelompok') }}",
                        data: {
                            id_kelompok: id_kelompok
                        },
                        dataType: "json",
                        success: function(data) {
                            $('.nilai_persen' + count).text(data['nilai_persen'] * 100 + ' %');
                            $('.inputnilai_persen' + count).val(data['nilai_persen']);
                            $('.umur' + count).text(data['tahun'] + ' ' + data['periode']);
                            $(".periode" + count).val(data['periode']);
                            $(".umurInput" + count).val(data['tahun']);
                            var tarif = $('.inputnilai_persen' + count).val();
                            var susut_bulan = data['periode'] === 'Bulan' ? (parseFloat(nilai) *
                                    parseFloat(tarif)) : (parseFloat(nilai) * parseFloat(tarif)) /
                                12;
                            var susut_rupiah = susut_bulan.toLocaleString("id-ID", {
                                style: "currency",
                                currency: "IDR",
                            });

                            if (nilai === '') {
                                $('.susut_bulan' + count).text('Rp.0');

                            } else {
                                $('.susut_bulan' + count).text(susut_rupiah);

                            }

                        }
                    });
                });

                $(document).on("keyup", ".nilai_perolehan", function() {
                    var count = $(this).attr("count");
                    var periode = $('.periode' + count).val()
                    var umur = $('.umurInput' + count).val()
                    var input = $(this).val();

                    console.log(

                    )

                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        var nilai = $('.nilai_perolehan_biasa' + count).val(0)
                    } else {
                        $(this).val("Rp " + input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        var nilai = $('.nilai_perolehan_biasa' + count).val(input2)

                    }
                    var tarif = $('.inputnilai_persen' + count).val();
                    var susut_bulan = periode === 'Bulan' ? input / umur : input / (umur * 12)

                    var susut_rupiah = susut_bulan.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    $('.susut_bulan' + count).text(susut_rupiah);


                });
                aksiBtn("form");
            });
    </script>
    @endsection
</x-theme.app>