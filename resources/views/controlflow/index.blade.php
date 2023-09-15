<x-theme.app title="{{ $title }}" table="Y" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="col-lg-6">
            <h6 class="float-start mt-1">{{ $title }}: {{ tanggal($tgl1) }} ~
                {{ tanggal($tgl2) }}</h6>
        </div>
        {{--
        <x-theme.button modal="T" icon="fa-print" href="/print_cashflow?tgl1={{ $tgl1 }}&tgl2={{ $tgl2 }}"
            variant="success" addClass="float-end" teks="Print" /> --}}
        {{--
        <x-theme.button modal="Y" idModal="daftarakun" icon="fa-book" variant="primary" addClass="float-end view_akun"
            teks="Daftar Akun" />
        <x-theme.button modal="T" icon="fa-plus" href="/jurnal-add" variant="primary" addClass="float-end"
            teks="Tambah Jurnal" />
        <x-theme.button modal="T" icon="fa-book" href="/summary_buku_besar" variant="primary" addClass="float-end"
            teks="Buku besar" /> --}}

        <div class="dropdown float-end">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1"
                data-bs-toggle="dropdown" aria-expanded="false">
                Aksi
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                <li><a class="dropdown-item" href="/jurnal-add">Jurnal Umum</a></li>
                <li><a class="dropdown-item" href="/summary_buku_besar">Buku besar</a></li>
                <li><a class="dropdown-item" href="#">Penyesuaian Stok</a></li>
                <li><a class="dropdown-item" href="/penutup">Jurnal Penutup</a></li>
            </ul>
        </div>
        <x-theme.btn_filter />
    </x-slot>
    <x-slot name="cardBody">
        <div class="row">
            <div class="col-lg-4" style="border: 1px solid black; padding: 10px">
                <h6 for="">Cash Flow</h6>
                <div id="loadcontrolflow"></div>
            </div>
            <div class="col-lg-8" style="border: 1px solid black; padding: 10px">
                <h6 for="">Control Uang Ditarik</h6>
                <div id="loadcashflow_ibu"></div>
            </div>
        </div>
        {{-- form sub kategori --}}

        <x-theme.modal title="Kategori" size="modal-lg" btnSave='T' idModal="modalPendapatan">
            <div id="loadPendapatan"></div>
        </x-theme.modal>


        <x-theme.modal title="Pilih Akun" size="modal-lg" btnSave='T' idModal="modalAkunPendapatan">
            <div id="loadAkunPendapatan"></div>
        </x-theme.modal>

        <x-theme.modal title="Daftar Akun yang belum terdaftar" size="modal-lg" btnSave='T' idModal="daftarakun">
            <div id="viewdaftarakun"></div>
        </x-theme.modal>



        <x-theme.modal title="Tambah Akun" size="modal-lg" btnSave='T' idModal="modalAkunControl">
            <div id="loadAkunControl"></div>
        </x-theme.modal>


        {{-- end form sub kategori --}}
    </x-slot>

    @section('scripts')
        <script>
            loadTabel()

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

            function loadTabel(tgl1 = "{{ $tgl1 }}", tgl2 = "{{ $tgl2 }}") {
                $.ajax({
                    type: "GET",
                    url: "{{ route('loadcontrolflow') }}",
                    data: {
                        tgl1: tgl1,
                        tgl2: tgl2,
                    },
                    success: function(r) {
                        $("#loadcontrolflow").html(r);

                    }
                });
            }

            function loadInputAkun(jenis) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('loadInputAkunCashflow') }}",
                    data: {
                        jenis: jenis
                    },
                    success: function(r) {
                        $("#loadPendapatan").html(r);
                        $('.jenisSub').val(jenis)
                        $('.select').select2({
                            dropdownParent: $('#modalPendapatan .modal-content')
                        });
                    }
                });
            }

            function loadInputsub(id_kategori, tgl1 = "{{ $tgl1 }}", tgl2 = "{{ $tgl2 }}") {
                $.ajax({
                    type: "GET",
                    url: "{{ route('loadInputsub') }}",
                    data: {
                        id_kategori: id_kategori,
                        tgl1: tgl1,
                        tgl2: tgl2
                    },
                    success: function(r) {
                        $("#loadAkunPendapatan").html(r);
                        // $('.jenisSub').val(jenis)

                        $('.select').select2({
                            dropdownParent: $('#modalAkunPendapatan .modal-content')
                        });
                    }
                });
            }

            $(document).on('click', '.input_pendapatan', function() {
                $("#modalPendapatan").modal('show')
                var jenis = $(this).attr('jenis');
                loadInputAkun(jenis)
            });

            $(document).on('submit', '#formTambahAkun', function(e) {
                e.preventDefault()
                var data = $("#formTambahAkun").serialize()
                var jenis = $('.jenis').val();
                $.ajax({
                    type: "GET",
                    url: "{{ route('save_kategoriCashcontrol') }}?" + data,
                    success: function(response) {
                        toast('Berhasil tambah Kategori')
                        loadInputAkun(jenis)
                        loadTabel()
                        // $("#modalSubKategori").modal('hide')
                    }
                });
            });
            $(document).on('submit', '#Editinputanakun', function(e) {
                e.preventDefault()
                var data = $("#Editinputanakun").serialize()
                var jenis = $('.jenis').val();
                $.ajax({
                    type: "GET",
                    url: "{{ route('edit_kategoriCashcontrol') }}?" + data,
                    success: function(response) {
                        toast('Berhasil edit Kategori')
                        loadInputAkun(jenis)
                        loadTabel()
                        // $("#modalSubKategori").modal('hide')
                    }
                });
            });


            $(document).on('click', '.delete_akun', function() {
                var id_akuncontrol = $(this).attr('id_akuncontrol');
                var id_kategori = $(this).attr('id_kategori');
                $.ajax({
                    type: "GET",
                    url: "{{ route('deleteSubAkunCashflow') }}?id_akuncontrol=" + id_akuncontrol,
                    success: function(response) {
                        toast('Akun berhasil di hapus')
                        loadInputsub(id_kategori);
                        loadTabel()
                        // $("#modalSubKategori").modal('hide')
                    }
                });
            });
            $(document).on('click', '.delete_kategori_akun', function() {
                var id_kategori = $(this).attr('id_kategori_cashcontrol');
                var jenis = $(this).attr('jenis');
                $.ajax({
                    type: "GET",
                    url: "{{ route('deleteAkunCashflow') }}?id_kategori=" + id_kategori,
                    success: function(response) {
                        toast('Akun berhasil di hapus')
                        loadInputAkun(jenis);
                        loadTabel()
                        // $("#modalSubKategori").modal('hide')
                    }
                });
            });
            $(document).on('click', '.view_akun', function() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('view_akun') }}",
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
            $(document).ready(function() {
                function loadInputsub(id_kategori_akun, tgl1 = "{{ $tgl1 }}", tgl2 = "{{ $tgl2 }}") {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('loadInputsub') }}",
                        data: {
                            id_kategori_akun: id_kategori_akun,
                            tgl1: tgl1,
                            tgl2: tgl2
                        },
                        success: function(r) {

                            $("#loadAkunPendapatan").html(r);
                            // $('.jenisSub').val(jenis)

                            $('.select').select2({
                                dropdownParent: $('#modalAkunPendapatan .modal-content')
                            });
                        }
                    });
                }
                $(document).on('click', '.tmbhakun', function() {
                    var id_kategori_akun = $(this).attr('id_kategori_akun');
                    // var jenis = $(this).attr('jenis');
                    $("#modalAkunPendapatan").modal('show');
                    loadInputsub(id_kategori_akun);
                });
                $(document).on('submit', '#formTambahSubAkun', function(e) {
                    e.preventDefault()
                    var data = $("#formTambahSubAkun").serialize()
                    var id_kategori_akun = $('.id_kategori_akun').val();
                    $.ajax({
                        type: "GET",
                        url: "{{ route('SaveSubAkunCashflow') }}?" + data,
                        success: function(response) {
                            toast('Berhasil tambah Akun')
                            loadInputsub(id_kategori_akun);
                            loadTabel()
                            // $("#modalSubKategori").modal('hide')
                        }
                    });
                });
            });
        </script>
        <script>
            load_cash_ibu()

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

            function load_cash_ibu(tgl1 = "{{ $tgl1 }}", tgl2 = "{{ $tgl2 }}") {
                $.ajax({
                    type: "GET",
                    url: "{{ route('cashflow_ibu') }}",
                    data: {
                        tgl1: tgl1,
                        tgl2: tgl2,
                    },
                    success: function(r) {
                        $("#loadcashflow_ibu").html(r);

                    }
                });
            }

            function loadInputControl(kategori, tgl1 = "{{ $tgl1 }}", tgl2 = "{{ $tgl2 }}") {
                $.ajax({
                    type: "GET",
                    url: "{{ route('loadInputKontrol') }}",
                    data: {
                        kategori: kategori,
                        tgl1: tgl1,
                        tgl2: tgl2
                    },
                    success: function(r) {
                        $("#loadAkunControl").html(r);
                        // $('.jenisSub').val(jenis)

                        $('.select').select2({
                            dropdownParent: $('#modalAkunControl .modal-content')
                        });
                    }
                });
            }

            $(document).on('click', '.tmbhakun_control', function() {
                var kategori = $(this).attr('kategori');
                // var jenis = $(this).attr('jenis');
                $("#modalAkunControl").modal('show');
                loadInputControl(kategori);
            });

            $(document).on('submit', '#Formtabahakuncontrol', function(e) {
                e.preventDefault()
                var data = $("#Formtabahakuncontrol").serialize()
                var kategori = $('.kategori').val();
                $.ajax({
                    type: "GET",
                    url: "{{ route('save_akun_ibu') }}?" + data,
                    success: function(response) {
                        toast('Berhasil tambah Akun')
                        loadInputControl(kategori);
                        load_cash_ibu()
                        // $("#modalSubKategori").modal('hide')
                    }
                });
            });

            $(document).on('click', '.delete_akun_ibu', function() {
                var kategori = $(this).attr('kategori');
                var id_akuncashibu = $(this).attr('id_akuncashibu');
                $.ajax({
                    type: "GET",
                    url: "{{ route('delete_akun_ibu') }}?id_akuncashibu=" + id_akuncashibu,
                    success: function(response) {
                        toast('Akun berhasil di hapus')
                        loadInputControl(kategori);
                        load_cash_ibu()
                    }
                });
            });

            $(document).on('submit', '#Editinputakunibu', function(e) {
                e.preventDefault()
                var data = $("#Editinputakunibu").serialize()
                var kategori = $('.kategori').val();
                $.ajax({
                    type: "GET",
                    url: "{{ route('edit_akun_ibu') }}?" + data,
                    success: function(response) {
                        toast('Berhasil tambah Akun')
                        loadInputControl(kategori);
                        load_cash_ibu()
                        // $("#modalSubKategori").modal('hide')
                    }
                });
            });

            
        </script>
    @endsection
</x-theme.app>
