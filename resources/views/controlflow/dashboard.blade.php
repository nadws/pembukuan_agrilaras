<x-theme.app title="{{$title}}" table="T" sizeCard="12" cont="container-fluid">
    <div class="row">
        <div class="col-lg-12 mb-2">
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
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <h6 class="float-strat">Cash Flow</h6>
                        </div>
                        <div class="col-lg-6">
                            <button data-bs-toggle="modal" data-bs-target="#daftarakuncashflow"
                                class="btn btn-sm btn-primary d_akuncashflow float-end"><i
                                    class="fas fa-clipboard-list"></i> List
                                Akun
                                <span class="badge bg-danger">{{$akun_cashflow->total_akun}}</span>
                            </button>
                        </div>
                    </div>


                </div>
                <div class="card-body">
                    <div id="loadcontrolflow"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <h6 class="float-strat">Control Uang Ditarik</h6>
                        </div>
                        <div class="col-lg-6">
                            <button data-bs-toggle="modal" data-bs-target="#daftaruangditarik"
                                class="btn btn-sm btn-primary float-end d_uangditarik"><i
                                    class="fas fa-clipboard-list"></i> List
                                Akun
                                <span class="badge bg-danger">{{$akun_ibu->total_akun}}</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="loadcashflow_ibu"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <h6 for="">Profit & Loss</h6>
                        </div>
                        <div class="col-lg-8">
                            <x-theme.button modal="T" href="/profit/print?tgl1={{ $tgl1 }}&tgl2={{ $tgl2 }}"
                                icon="fa-print" addClass="float-end" teks="Print" />

                            <button data-bs-toggle="modal" data-bs-target="#daftarakun1" type="button"
                                class="btn btn-sm  icon icon-left me-2 float-end btn-primary view_akun1">
                                <i class="fas fa-book"></i>
                                Sisa Akun
                                <span class="badge sisa_akun"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="tableLoad"></div>
                    <form id="formtabhAkun">
                        <x-theme.modal btnSave="T" title="Tambah Akun" idModal="tambah-profit" size="modal-lg">
                            <div id="modalLoad"></div>
                        </x-theme.modal>
                    </form>

                    <form action="" id="formUraian">
                        <x-theme.modal btnSave="T" title="Tambah Uraian" idModal="tambah-uraian" size="modal-lg">
                            <div class="uraian-modal"></div>
                        </x-theme.modal>
                    </form>

                    <x-theme.modal title="Daftar Akun yang belum terdaftar" size="modal-lg" btnSave='T'
                        idModal="daftarakun1">
                        <div id="viewdaftarakun1"></div>
                    </x-theme.modal>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <h6 for="">Laporan Neraca</h6>
                        </div>
                        <div class="col-lg-8">
                            <x-theme.button modal="T" icon="fa-print" href="#" addClass="float-end" teks="Print" />
                            <button data-bs-toggle="modal" data-bs-target="#daftarakun" type="button"
                                class="btn btn-sm  icon icon-left me-2 float-end btn-primary view_akun">
                                <i class="fas fa-book"></i>
                                Sisa Akun
                                <span class="badge sisa_akunNeraca"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="loadneraca"></div>
                    <x-theme.modal title="Kategori" size="modal-lg" btnSave='T' idModal="modalTambahAkun">
                        <div id="loadInputAkun"></div>
                    </x-theme.modal>


                    <x-theme.modal title="Tambah Sub Kategori" size="modal-lg" btnSave='T' idModal="modalSubkategori">
                        <div id="loadInputSub"></div>
                    </x-theme.modal>

                    <x-theme.modal title="Daftar Akun yang belum terdaftar Neraca" size="modal-lg" btnSave='T'
                        idModal="daftarakun">
                        <div id="viewdaftarakun"></div>
                    </x-theme.modal>

                    <x-theme.modal title="Tambah Akun" size="modal-lg" btnSave='T' idModal="modalAkunControl">
                        <div id="loadAkunControl"></div>
                    </x-theme.modal>
                </div>
            </div>
        </div>
    </div>

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

    <x-theme.modal title="Daftar Akun Cashflow" size="modal-lg" btnSave='T' idModal="daftarakuncashflow">
        <div id="loadAkuncashflow"></div>
    </x-theme.modal>
    <x-theme.modal title="Daftar Akun Cashflow" size="modal-lg" btnSave='T' idModal="daftaruangditarik">
        <div id="loadAkunditarik"></div>
    </x-theme.modal>


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
    <script>
        function loadSisa() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('profit.count_sisa') }}?jenis=profit",
                    success: function(r) {
                        $(".sisa_akun").text(r);
                        $(".sisa_akun").addClass(r < 1 ? 'bg-success' : 'bg-danger');
                    }
                });
            }

            $(document).on('click', '.view_akun1', function() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('profit.view_akun') }}",
                    success: function(data) {
                        $("#viewdaftarakun1").html(data);
                        $("#table2").DataTable({
                            "lengthChange": true,
                            "autoWidth": false,
                            "stateSave": true,
                        });
                    }
                });
            });

            loadTabel()
            loadSisa()

            function loadTabel(tgl1 = "{{ $tgl1 }}", tgl2 = "{{ $tgl2 }}") {
                $.ajax({
                    type: "GET",
                    url: "{{ route('profit.load') }}",
                    data: {
                        tgl1: tgl1,
                        tgl2: tgl2,
                    },
                    success: function(r) {
                        $("#tableLoad").html(r);
                    }
                });
            }

            function loadUraianModal(jenis) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('profit.load_uraian') }}",
                    data: {
                        jenis: jenis
                    },
                    success: function(r) {
                        $(".uraian-modal").html(r);
                        $('.jenisSub').val(jenis)
                    }
                });
            }

            function loadModal(id_kategori) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('profit.modal') }}",
                    data: {
                        'id_kategori': id_kategori
                    },
                    success: function(r) {
                        $("#modalLoad").html(r);
                        $('#kategori_idInput').val(id_kategori)
                        $('#table').DataTable({
                            "paging": true,
                            "pageLength": 10,
                            "lengthChange": true,
                            "stateSave": true,
                            "searching": true,
                        });
                        $('.select2-profit').select2({
                            dropdownParent: $('#tambah-profit .modal-content')
                        });
                    }
                });
            }

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

            $(document).on('click', '.klikModal', function(e) {
                e.preventDefault();
                var id_kategori = $(this).attr('id_kategori')

                loadModal(id_kategori)
                $("#tambah-profit").modal('show')
            })

            $(document).on('click', '.uraian', function() {
                var jenis = $(this).attr('jenis')
                loadUraianModal(jenis)
            })

            $(document).on('click', '#btnFormSubKategori', function() {
                var jenisSub = $('.jenisSub').val();
                var urutan = $('.urutanInput').val();
                var sub_kategori = $('.sub_kategoriInput').val();

                $.ajax({
                    method: "GET",
                    url: "{{ route('profit.save_subkategori') }}",
                    data: {
                        jenis: jenisSub,
                        urutan: urutan,
                        sub_kategori: sub_kategori
                    },
                    success: function(r) {
                        toast('Berhasil save kategori')
                        loadUraianModal(jenisSub)
                        loadTabel()
                        loadSisa()
                    }
                });
            })

            $(document).on('click', '.btnDeleteSubKategori', function() {
                var id = $(this).attr('id')
                var jenis = $(this).attr('id_jenis')
                if (confirm('Yakin ingin dihapus ? ')) {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('profit.delete_subkategori') }}",
                        data: {
                            id: id
                        },
                        success: function(r) {
                            toast('Berhasil hapus kategori')
                            loadUraianModal(jenis)
                            loadTabel()
                            loadSisa()
                        }
                    });
                }
            })

            $(document).on('click', '#btnSave', function() {
                var id_akun = $("#id_akun").val()
                var urutan = $("#urutan").val()
                var kategori_idInput = $("#kategori_idInput").val()

                $.ajax({
                    type: "GET",
                    url: "{{ route('profit.add') }}",
                    data: {
                        id_akun: id_akun,
                        urutan: urutan,
                        kategori_id: kategori_idInput,
                    },
                    success: function(r) {
                        $('#tambah-profit').off('hide.bs.modal');;
                        toast('Berhasil tambah akun')
                        loadModal(kategori_idInput)
                        loadTabel()
                        loadSisa()
                    }
                });
            })

            $(document).on('click', '.btnHapus', function() {
                var id_profit = $(this).attr("id_profit")
                var id_kategori = $(this).attr("id_kategori")

                $.ajax({
                    type: "GET",
                    url: "{{ route('profit.delete') }}",
                    data: {
                        id_profit: id_profit,
                    },
                    success: function(r) {
                        toast('Berhasil hapus akun')
                        loadModal(id_kategori)
                        loadTabel()
                        loadSisa()

                    }
                });
            })

            $(document).on('submit', '#formUraian', function(e) {
                e.preventDefault()
                var formVal = $("#formUraian").serialize()
                var jenisSub = $(".jenisSub").val()

                $.ajax({
                    type: "GET",
                    url: "{{ route('profit.update') }}?" + formVal,
                    success: function(r) {
                        toast('Berhasil update kategori')
                        loadUraianModal(jenisSub)
                        loadTabel()
                        loadSisa()
                    }
                });
            })
    </script>

    {{-- script neraca --}}
    <script>
        load_neraca()
            loadSisaNeraca()

            function loadSisaNeraca() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('profit.count_sisa') }}?jenis=neraca",
                    success: function(r) {
                        $(".sisa_akunNeraca").text(r);
                        $(".sisa_akunNeraca").addClass(r < 1 ? 'bg-success' : 'bg-danger');
                    }
                });
            }
            $(document).on('click', '.view_akun', function() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('view_akun_neraca') }}",
                    success: function(data) {
                        $("#viewdaftarakun").html(data);
                        $("#table3").DataTable({
                            "lengthChange": true,
                            "autoWidth": false,
                            "stateSave": true,
                        });
                    }
                });
            });

            function load_neraca(tgl1 = "{{ $tgl1 }}", tgl2 = "{{ $tgl2 }}") {
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
                        kategori: kategori,
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

            function loadInputAkunNeraca(id_sub_kategori, tgl1 = "{{ $tgl1 }}", tgl2 = "{{ $tgl2 }}") {
                $.ajax({
                    type: "GET",
                    url: "{{ route('loadinputAkun_neraca') }}",
                    data: {
                        id_sub_kategori: id_sub_kategori,
                        tgl1: tgl1,
                        tgl2: tgl2,
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
                        loadSisaNeraca()
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
                        loadSisaNeraca()
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
                        loadSisaNeraca()
                    }
                });
            });


            $(document).on('click', '.d_akuncashflow', function() {
                $.ajax({
                    type: "get",
                    url: "/akuncashflow",
                    success: function (r) {
                        $("#loadAkuncashflow").html(r)
                        $("#table3").DataTable({
                            "lengthChange": true,
                            "autoWidth": false,
                            "stateSave": true,
                        });
                    }
                });
            });
            $(document).on('click', '.d_uangditarik', function() {
                $.ajax({
                    type: "get",
                    url: "/akunuangditarik",
                    success: function (r) {
                        $("#loadAkunditarik").html(r)
                        $("#table3").DataTable({
                            "lengthChange": true,
                            "autoWidth": false,
                            "stateSave": true,
                        });
                    }
                });
            });
    </script>
    @endsection
</x-theme.app>