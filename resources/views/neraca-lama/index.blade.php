<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="col-lg-6">
            <h6 class="float-start mt-1">{{ $title }} : {{tanggal($tgl1)}} ~
                {{tanggal($tgl2)}}</h6>
        </div>
        <x-theme.button modal="T" icon="fa-print" href="/print_neraca" variant="success" addClass="float-end"
            teks="Print" />
        <x-theme.button modal="Y" idModal="daftarakun" icon="fa-book" variant="primary" addClass="float-end view_akun"
            teks="Daftar Akun" />
        <x-theme.btn_filter />
    </x-slot>
    <x-slot name="cardBody">
        <div class="row">
            <div class="col-lg-12">
                <div id="loadneraca"></div>
            </div>
        </div>





        {{-- form sub kategori --}}

        <x-theme.modal title="Kategori" size="modal-lg" btnSave='T' idModal="modalTambahAkun">
            <div id="loadInputAkun"></div>
        </x-theme.modal>


        <x-theme.modal title="Tambah Sub Kategori" size="modal-lg" btnSave='T' idModal="modalSubkategori">
            <div id="loadInputSub"></div>
        </x-theme.modal>

        <x-theme.modal title="Daftar Akun" size="modal-lg" btnSave='T' idModal="daftarakun">
            <div id="viewdaftarakun"></div>
        </x-theme.modal>



        <x-theme.modal title="Tambah Akun" size="modal-lg" btnSave='T' idModal="modalAkunControl">
            <div id="loadAkunControl"></div>
        </x-theme.modal>


        {{-- end form sub kategori --}}
    </x-slot>

    @section('scripts')
    <script>
        load_neraca()
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
        function load_neraca(tgl1 = "{{$tgl1}}", tgl2 = "{{ $tgl2 }}") {
            $.ajax({
                type: "GET",
                url: "{{ route('loadNeraca') }}",
                data: {
                    tgl1: tgl1,
                    tgl2: tgl2,
                },
                success: function(r) {
                    $("#loadneraca").html(r);

                }
            });
        }

        function loadInputSubkategori(kategori) {
            $.ajax({
                type: "GET",
                url: "{{ route('loadinputSub_neraca') }}",
                data: {
                    kategori:kategori,
                },
                success: function(r) {
                    $("#loadInputSub").html(r);
                    // $('.jenisSub').val(jenis)

                    $('.select').select2({
                        dropdownParent: $('#modalAkunControl .modal-content')
                    });
                }
            });
        }
        function loadInputAkunNeraca(id_sub_kategori,tgl1 = "{{$tgl1}}", tgl2 = "{{ $tgl2 }}") {
            $.ajax({
                type: "GET",
                url: "{{ route('loadinputAkun_neraca') }}",
                data: {
                    id_sub_kategori:id_sub_kategori,
                    tgl1:tgl1,
                    tgl2:tgl2,
                },
                success: function(r) {
                    $("#loadInputAkun").html(r);
                    // $('.jenisSub').val(jenis)

                    $('.select').select2({
                        dropdownParent: $('#modalTambahAkun .modal-content')
                    });
                }
            });
        }

        $(document).on('click', '.tmbhsub_kategori', function() {
            var kategori = $(this).attr('kategori');
            // var jenis = $(this).attr('jenis');
            $("#modalSubkategori").modal('show');
            loadInputSubkategori(kategori);
        });
        $(document).on('submit', '#formTambahSubkatgeori', function(e) {
            e.preventDefault()
            var data = $("#formTambahSubkatgeori").serialize()
            var kategori = $('.kategori').val();
            $.ajax({
                type: "GET",
                url: "{{ route('saveSub_neraca') }}?" + data,
                success: function(response) {
                    toast('Berhasil tambah Akun')
                    loadInputSubkategori(kategori);
                    load_neraca()
                    // $("#modalSubKategori").modal('hide')
                }
            });
        });
        $(document).on('click', '.tmbhakun_neraca', function() {
            var id_sub_kategori = $(this).attr('id_sub_kategori');
            // var jenis = $(this).attr('jenis');
            $("#modalTambahAkun").modal('show');
            loadInputAkunNeraca(id_sub_kategori);
        });

        $(document).on('submit', '#formTambahAkun', function(e) {
            e.preventDefault()
            var data = $("#formTambahAkun").serialize()
            var id_sub_kategori = $('.id_sub_kategori').val();
            $.ajax({
                type: "GET",
                url: "{{ route('saveAkunNeraca') }}?" + data,
                success: function(response) {
                    toast('Berhasil tambah Akun')
                    loadInputAkunNeraca(id_sub_kategori);
                    load_neraca()
                    // $("#modalSubKategori").modal('hide')
                }
            });
        });

        $(document).on('click', '.delete_akun_neraca', function() {
            var id_sub_kategori = $(this).attr('id_sub_kategori');
            var id_akun_neraca = $(this).attr('id_akun_neraca');
            $.ajax({
                type: "GET",
                url: "{{ route('delete_akun_neraca') }}?id_akun_neraca=" + id_akun_neraca,
                success: function(response) {
                    toast('Akun berhasil di hapus')
                    loadInputAkunNeraca(id_sub_kategori);
                    load_neraca()
                }
            });
        });
        $(document).on('click', '.view_akun', function() {
            $.ajax({
                type: "GET",
                url: "{{ route('akun_neraca') }}",
                success: function(data) {
                    $("#viewdaftarakun").html(data);
                    $("#table2").DataTable({
                        "lengthChange": false,
                        "autoWidth": false,
                        "stateSave": true,
                    });
                }
            });  
        });
    </script>



    @endsection
</x-theme.app>