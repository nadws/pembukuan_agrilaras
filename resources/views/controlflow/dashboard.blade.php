<x-theme.app title="{{ $title }}" table="T" sizeCard="12" cont="container-fluid">
    <div class="row" x-data="{
        stok: false,
        profit: true,
        neraca: true,
    }">
        <div class="col-lg-12 mb-2">
            <x-theme.btn_filter />
            <a href="#" data-bs-toggle="modal" data-bs-target="#persenhd"
                class="btn btn-sm btn-primary icon icon-left me-2 float-end persen_hd">Rencana penjualan</a>
        </div>

        <h5 class="">&nbsp;&nbsp;&nbsp; Stok Kandang Mtd
            <button class="btn btn-primary btn-sm btn-buka" @click="stok = ! stok">Buka <i
                    class="fas fa-caret-down"></i></button>
        </h5>
        <hr>

        <div class="row" x-show="stok">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <h6 class="float-strat">Telur Selisih {{ tanggal($tgl1) }} ~ {{ tanggal($tgl2) }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="dhead">#</th>
                                    <th class="dhead" width="40%">Nama</th>
                                    <th class="dhead text-end">Pcs Selisih</th>
                                    <th class="dhead text-end">Kg Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($telur_selisih as $no => $d)
                                    <tr>
                                        <td>{{ $no + 1 }}</td>
                                        <td>{{ $d->nm_telur }}</td>
                                        <td align="right">{{ number_format($d->pcs_selisih, 0) }}</td>
                                        <td align="right">{{ number_format($d->kg_selisih, 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-center" colspan="2">Total</th>
                                    <th class="text-end">2</th>
                                    <th class="text-end">2</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <h6 class="float-strat">Pakan Selisih {{ tanggal($tgl1) }} ~ {{ tanggal($tgl2) }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="dhead">#</th>
                                    <th class="dhead">Nama</th>
                                    <th style="text-align: right" class="dhead">Stok Selisih</th>
                                    <th style="text-align: right" class="dhead">Harga Satuan</th>
                                    <th style="text-align: right" class="dhead">Rupiah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $ttlRp = 0;
                                @endphp
                                @foreach ($pakanSelisih as $no => $d)
                                    @php
                                        $stokProgram = $d->stok - $d->pcs + $d->pcs_kredit;
                                        $selisih = $d->stok - $stokProgram;
                                        if ($d->sum_ttl_rp != 0) {
                                            $hargaSatuan = $d->sum_ttl_rp / $d->pcs_sum_ttl_rp;
                                        } else {
                                            $hargaSatuan = 0;
                                        }

                                        $selisihRupiah = $hargaSatuan * $selisih;
                                    $ttlRp += $selisih < 0 ? $selisihRupiah * -1 : $selisihRupiah; @endphp <tr>
                                        <td>{{ $no + 1 }}</td>
                                        <td>{{ $d->nm_produk }}</td>
                                        <td align="right">{{ number_format($d->stok - $stokProgram, 1) }}
                                            {{ $d->nm_satuan }}
                                        </td>
                                        <td align="right">{{ number_format($hargaSatuan, 1) }}</td>
                                        <td align="right">
                                            {{ number_format($selisih < 0 ? $selisihRupiah * -1 : $selisihRupiah, 0) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-center" colspan="4">Total </th>
                                    <th class="text-end">{{ number_format($ttlRp, 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <h6 class="float-strat">Vitamin Selisih {{ tanggal($tgl1) }} ~ {{ tanggal($tgl2) }}
                                </h6>
                            </div>

                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="dhead">#</th>
                                    <th class="dhead">Nama</th>
                                    <th style="text-align: right" class="dhead">Stok Selisih</th>
                                    <th style="text-align: right" class="dhead">Harga Satuan</th>
                                    <th style="text-align: right" class="dhead">Rupiah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $ttlRp = 0;
                                @endphp
                                @foreach ($vitaminSelisih as $no => $d)
                                    @php
                                        $stokProgram = $d->stok - $d->pcs + $d->pcs_kredit;
                                        $selisih = $d->stok - $stokProgram;
                                        if ($d->sum_ttl_rp != 0) {
                                            $hargaSatuan = $d->sum_ttl_rp / $d->pcs_sum_ttl_rp;
                                        } else {
                                            $hargaSatuan = 0;
                                        }
                                        $selisihRupiah = $hargaSatuan * $selisih;
                                    $ttlRp += $selisih < 0 ? $selisihRupiah * -1 : $selisihRupiah; @endphp @if ($d->stok - $stokProgram == 0 && $hargaSatuan == 0)
                                        @php
                                            continue;
                                        @endphp
                                    @else
                                        <tr>
                                            <td>{{ $no + 1 }}</td>
                                            <td>{{ $d->nm_produk }}</td>
                                            <td align="right">{{ number_format($d->stok - $stokProgram, 1) }}
                                                {{ $d->nm_satuan }}</td>
                                            <td align="right">{{ number_format($hargaSatuan, 1) }}</td>
                                            <td align="right">
                                                {{ number_format($selisih < 0 ? $selisihRupiah * -1 : $selisihRupiah, 0) }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-center" colspan="4">Total </th>
                                    <th class="text-end">{{ number_format($ttlRp, 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <h5 class="">&nbsp;&nbsp;&nbsp; Profit & Uang Ditarik
            <button class="btn btn-primary btn-sm btn-buka" @click="profit = ! profit">Buka <i
                    class="fas fa-caret-down"></i></button>
        </h5>
        <hr>
        <div class="row" x-show="profit">

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-8">

                                <h6 for="">Profit & Loss {{ tanggal($tgl1) }} ~ {{ tanggal($tgl2) }}</h6>
                            </div>
                            <div class="col-lg-4">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="tableLoad">

                        </div>
                        <form action="{{ route('profit.save_akun_profit_new') }}" method="post">
                            @csrf
                            <x-theme.modal btnSave="Y" title="Tambah Akun" idModal="tambah-profit" size="modal-lg">
                                <div id="modalLoad"></div>
                            </x-theme.modal>
                        </form>

                        <form action="" id="formUraian">
                            <x-theme.modal btnSave="T" title="Tambah Uraian" idModal="tambah-uraian"
                                size="modal-lg">
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
                            <div class="col-lg-6">

                                <h6 class="float-strat">Control Uang Ditarik <br>
                                    Populasi per {{ tanggal($tgl2) }} :
                                    {{ number_format(sumBk($populasi, 'stok_awal') - sumBk($populasi, 'mati') - sumBk($populasi, 'jual') - sumBk($populasi, 'afkir'), 0) }}

                                </h6>
                            </div>
                            <div class="col-lg-6">
                                <a href="{{ route('export_uang_ditarik', ['tgl1' => $tgl1, 'tgl2' => $tgl2]) }}"
                                    class="btn btn-sm btn-success float-end ms-2"><i class="fas fa-file-excel"></i>
                                    Export</a>
                                <a href="{{ route('print_uang_ditarik', ['tgl1' => $tgl1, 'tgl2' => $tgl2]) }}"
                                    target="_blank" class="btn btn-sm btn-primary float-end"><i
                                        class="fas fa-print"></i>
                                    Print</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="loadcashflow_ibu"></div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="">&nbsp;&nbsp;&nbsp; Neraca
            <button class="btn btn-primary btn-sm btn-buka" @click="neraca = ! neraca">Buka <i
                    class="fas fa-caret-down"></i></button>
        </h5>
        <hr>
        <div class="row" x-show="neraca">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-4">
                                <h6 for="">Laporan Neraca</h6>
                            </div>
                            <div class="col-lg-8">
                                <x-theme.button modal="T" icon="fa-print" target="_blank"
                                    href="{{ route('print_neraca', ['tgl2' => $tgl2]) }}" addClass="float-end"
                                    teks="Print" />
                                {{-- <button data-bs-toggle="modal" data-bs-target="#daftarakun" type="button"
                                    class="btn btn-sm  icon icon-left me-2 float-end btn-primary view_akun">
                                    <i class="fas fa-book"></i>
                                    Sisa Akun
                                    <span class="badge sisa_akunNeraca"></span>
                                </button> --}}
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="loadneraca"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
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
    <x-theme.modal title="Kategori" size="modal-lg" btnSave='T' idModal="modalPendapatan">
        <div id="loadPendapatan"></div>
    </x-theme.modal>


    <x-theme.modal title="Pilih Akun" size="modal-lg" btnSave='T' idModal="modalAkunPendapatan">
        <div id="loadAkunPendapatan"></div>
    </x-theme.modal>

    <x-theme.modal title="Daftar Akun yang belum terdaftar" size="modal-lg" btnSave='T' idModal="daftarakun">
        <div id="viewdaftarakun"></div>
    </x-theme.modal>



    <x-theme.modal title="Tambah Akun" size="modal-lg" btnSave='T' idModal="modalAkunibu">
        <div id="loadAkunControl"></div>
    </x-theme.modal>



    <form action="{{ route('seleksi_cash_flow_ditarik') }}" method="post">
        @csrf
        <x-theme.modal title="Daftar Akun Cashflow" size="modal-lg" btnSave='T' idModal="daftarakuncashflow">
            <div id="loadAkuncashflow"></div>
        </x-theme.modal>
    </form>

    <form action="{{ route('seleksi_akun_control_ditarik') }}" method="post">
        @csrf
        <x-theme.modal title="Daftar Akun Cashflow" size="modal-lg" btnSave='T' idModal="daftaruangditarik">
            <div id="loadAkunditarik"></div>
        </x-theme.modal>
    </form>
    <form action="{{ route('seleksi_akun_profit') }}" method="post">
        @csrf
        <x-theme.modal title="Daftar Akun Profit & Loss" size="modal-lg" btnSave='T' idModal="daftarprofit">
            <div id="loadAkunprofit"></div>
        </x-theme.modal>
    </form>

    <form id="save_percen_budget">
        <x-theme.modal title="Persen Hd" size="modal-lg" btnSave='Y' idModal="persenhd">
            <div id="data_persen_hd"></div>
        </x-theme.modal>
    </form>


    @section('scripts')
        <script>
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

            // Nanda

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
                loadTabel();

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

                akun_cash_flow();

                function akun_cash_flow(jenis) {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('total_cash_flow') }}",
                        success: function(r) {
                            $(".ttl_akun_cashflow").text(r);
                        }
                    });
                }

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
                $(document).on('click', '.delete_akun', function() {
                    var id_akuncontrol = $(this).attr('id_akuncontrol');
                    var id_kategori_akun = $(this).attr('id_kategori');

                    $.ajax({
                        type: "GET",
                        url: "{{ route('deleteSubAkunCashflow') }}?id_akuncontrol=" + id_akuncontrol,
                        success: function(response) {
                            toast('Akun berhasil di hapus')
                            loadInputsub(id_kategori_akun);

                            loadTabel()
                            akun_cash_flow();
                            // $("#modalSubKategori").modal('hide')
                        }
                    });
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
                            loadTabel();
                            akun_cash_flow();
                            // $("#modalSubKategori").modal('hide')
                        }
                    });
                });
            });
        </script>
        <script>
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

            load_cash_ibu()

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
            akun_cash_ibu();
            akun_cash_profit();

            function akun_cash_ibu() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('total_cash_ibu') }}",
                    success: function(r) {
                        $(".ttl_akun_ibu").text(r);
                    }
                });
            }

            function akun_cash_profit() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('total_cash_profit') }}",
                    success: function(r) {
                        $(".ttl_akun_profit").text(r);
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
                        load_cash_ibu();
                        akun_cash_ibu();
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
                        load_cash_ibu();
                        akun_cash_ibu();
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
                        load_cash_ibu();
                        akun_cash_ibu();
                        // $("#modalSubKategori").modal('hide')
                    }
                });
            });
            $(document).on('click', '.iktisar', function() {
                var urutan = $(this).attr('urutan');
                if ($(this).is(":checked")) {
                    $('.hasil_iktisar' + urutan).val('H')
                } else {
                    $('.hasil_iktisar' + urutan).val('T')
                }
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
                        akun_cash_profit()
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
                        $('#table_sc').DataTable({
                            "searching": false,
                            scrollY: '350px',
                            scrollX: false,
                            scrollCollapse: false,
                            "stateSave": true,
                            "autoWidth": false,
                            "paging": false,
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
                    success: function(r) {
                        $("#loadAkuncashflow").html(r)
                        $('.tableScroll').DataTable({
                            "searching": true,
                            scrollY: '400px',
                            scrollX: true,
                            scrollCollapse: true,
                            "autoWidth": true,
                            "paging": false,
                        });
                    }
                });
            });
            $(document).on('click', '.d_uangditarik', function() {
                $.ajax({
                    type: "get",
                    url: "/akunuangditarik",
                    success: function(r) {
                        $("#loadAkunditarik").html(r)
                        $('#tableScroll').DataTable({
                            "searching": true,
                            scrollY: '400px',
                            scrollX: true,
                            scrollCollapse: true,
                            "autoWidth": true,
                            "paging": false,
                        });
                    }
                });
            });
            $(document).on('click', '.d_profit', function() {
                $.ajax({
                    type: "get",
                    url: "/akunprofit",
                    success: function(r) {
                        $("#loadAkunprofit").html(r)
                        $('#table1').DataTable({
                            "searching": true,
                            scrollY: '400px',
                            scrollX: true,
                            scrollCollapse: true,
                            "paging": false,
                        });
                        $('#table2').DataTable({
                            "searching": true,
                            scrollY: '400px',
                            scrollX: true,
                            scrollCollapse: true,
                            "paging": false,
                        });
                        $('#table3').DataTable({
                            "searching": true,
                            scrollY: '400px',
                            scrollX: true,
                            scrollCollapse: true,
                            "paging": false,
                        });
                        $('#table4').DataTable({
                            "searching": true,
                            scrollY: '400px',
                            scrollX: true,
                            scrollCollapse: true,
                            "paging": false,
                        });

                    }
                });
            });
            $(document).on('click', '.klikCek', function() {
                var urutan = $(this).attr('urutan');
                if ($(this).is(":checked")) {
                    $('.hasil_iktisar' + urutan).val('H')
                } else {
                    $('.hasil_iktisar' + urutan).val('T')
                }
            });

            $(document).on('click', '.klikCek', function() {
                var count = $(this).attr('count')
                var id_akun = $(this).attr('id_akun')
                var nilai = $(this).val()

                if ($(this).is(":checked")) {
                    $('.klikCek' + count).val('Y')
                } else {
                    $('.klikCek' + count).val('T')
                }
            });

            function loadpersenbudget() {
                $.ajax({
                    type: "get",
                    url: "{{ route('persen_pendapatan') }}",
                    success: function(r) {
                        $("#data_persen_hd").html(r)
                    }
                });
            }
            $(document).on('click', '.persen_hd', function() {
                loadpersenbudget();
            });
            var count = 3;
            $(document).on("click", ".tbh_baris", function() {
                count = count + 1;
                $.ajax({
                    url: "/tambah_baris_budget_persen?count=" + count,
                    type: "Get",
                    success: function(data) {
                        $("#tb_baris").append(data);
                        $(".select").select2();
                    },
                });
            });

            $(document).on("click", ".remove_baris", function() {
                var delete_row = $(this).attr("count");
                $(".baris" + delete_row).remove();
            });
            $(document).on('submit', '#save_percen_budget', function(event) {
                event.preventDefault();
                $('#loading').show();
                $(".loading-hide").hide();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var formData = $(this).serialize();
                formData += "&_token=" + csrfToken;
                $.ajax({
                    type: "POST",
                    url: "{{ route('save_persen_pendapatan') }}",
                    data: formData,
                    success: function(response) {
                        setTimeout(function() {
                            $('#loading').hide();
                            toast('Data berhasil di simpan')
                            $(".loading-hide").show();
                            loadpersenbudget();
                        }, 1000);
                    },
                });
            });

            $(document).on("keyup", ".budget_uang", function() {
                var total_budget = 0;
                $(".budget_uang").each(function() {
                    // Hapus pemisah ribuan dan tanda mata uang
                    var value = $(this).val().replace(/\D/g, '');
                    total_budget += parseFloat(value);
                });
                var total_budget2 = total_budget.toLocaleString("id-ID", {
                    style: "currency",
                    currency: "IDR",
                });
                $('.total_budget').text(total_budget2)
            });

            $(document).on('submit', '#save_budget', function(event) {
                event.preventDefault();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var formData = $(this).serialize();
                formData += "&_token=" + csrfToken;
                $.ajax({
                    type: "POST",
                    url: "{{ route('save_budget') }}",
                    data: formData,
                    success: function(response) {
                        loadTabel()
                        load_cash_ibu()
                        toast('Data berhasil di simpan')
                    },
                    error: function(xhr, status, error) {
                        toast('gagal')
                    }
                });
            });
            pencarian('pencarian', 'table_sc')

            $(document).keydown(function(e) {
                // Periksa apakah tombol Ctrl dan panah kanan ditekan bersamaan
                var bulan = "{{ request()->get('bulan') }}"
                var tahun = "{{ request()->get('tahun') }}"
                var period = "{{ request()->get('period') }}"
                if (period == 'mounthly') {
                    if (e.ctrlKey && e.keyCode == 37) {
                        window.location.href =
                            `controlflow/?period=mounthly&bulan=${parseFloat(bulan)-1}&tahun=${parseFloat(tahun)}`;
                    }
                    if (e.ctrlKey && e.keyCode == 39) {

                        window.location.href =
                            `controlflow/?period=mounthly&bulan=${parseFloat(bulan)+1}&tahun=${parseFloat(tahun)}`;
                    }
                }
            });
        </script>
    @endsection
</x-theme.app>
