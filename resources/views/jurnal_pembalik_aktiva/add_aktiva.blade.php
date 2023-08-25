<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">

    <x-slot name="cardHeader">
        <div class="row justify-content-end">

            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}</h6>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('controlflow') }}" class="btn btn-primary float-end"><i class="fas fa-home"></i></a>
            </div>

        </div>

    </x-slot>
    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #000000;
            line-height: 36px;
            /* font-size: 12px; */
            width: 150px;

        }
    </style>

    <x-slot name="cardBody">
        <form action="{{ route('save_jurnal_aktiva') }}" method="post" class="save_jurnal">
            @csrf
            <input type="hidden" name="id_buku" value="{{ $id_buku }}">
            <section class="row">
                <div class="col-lg-3">
                    <label for="">Tanggal</label>
                    <input type="date" class="form-control" name="tgl" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-lg-3">
                    <label for="">No Urut Jurnal Umum</label>
                    <input type="text" class="form-control" name="no_nota" value="JU-{{ $max }}" readonly>
                </div>
                {{-- @if ($id_buku == '12')
                <div class="col-lg-3">
                    <label for="">Proyek</label>
                    <select name="id_proyek" id="select2" class="proyek proyek_berjalan">

                    </select>
                </div>
                @endif --}}

                {{-- <div class="col-lg-3">
                    <label for="">Suplier</label>
                    <select name="id_suplier" class="select2suplier form-control">
                        <option value="">- Pilih Suplier -</option>
                        @foreach ($suplier as $p)
                        <option value="{{ $p->id_suplier }}">{{ $p->nm_suplier }}</option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="col-lg-12">
                    <hr style="border: 1px solid black">
                </div>
                <div class="col-lg-12">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th width="2%">#</th>
                                <th width="14%">Akun</th>
                                <th width="10%">Sub Akun</th>
                                <th width="18%">Keterangan</th>
                                <th width="12%" style="text-align: right;">Debit</th>
                                <th width="12%" style="text-align: right;">Kredit</th>
                                {{-- <th width="12%" style="text-align: right;">Saldo</th> --}}
                                {{-- <th width="5%">Aksi</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="baris1">
                                <td style="vertical-align: top;">
                                    {{-- <button type="button" data-bs-toggle="collapse" href=".join1"
                                        class="btn rounded-pill " count="1"><i class="fas fa-angle-down"></i>
                                    </button> --}}
                                </td>
                                <td style="vertical-align: top;">
                                    <input type="hidden" name="id_akun[]" value="{{$akun_gantung->id_akun}}">
                                    <input type="text" class="form-control" value="{{$akun_gantung->nm_akun}} "
                                        readonly>
                                    <div class="">
                                        <label for="" class="mt-2 ">Urutan Pengeluaran</label>
                                        <input type="text" class="form-control " name="no_urut[]">
                                    </div>

                                </td>
                                <td style="vertical-align: top;">
                                    <select name="id_post" id="" class="select2_add post_center post_center1" count="1">
                                        <option value="">-Pilih Post-</option>
                                        @foreach ($post as $p)
                                        <option value="{{$p->id_post_center}}">{{$p->nm_post}}</option>
                                        @endforeach

                                    </select>
                                </td>

                                <td style="vertical-align: top;">
                                    <input type="text" name="keterangan[]" class="form-control"
                                        style="vertical-align: top" placeholder="nama barang, qty, @rp">

                                </td>
                                <td style="vertical-align: top;">
                                    <input type="text" class="form-control debit_rupiah text-end" value="Rp 0" count="1"
                                        readonly>
                                    <input type="hidden" class="form-control debit_biasa debit_biasa1" value="0"
                                        name="debit[]">
                                    <p class="peringatan_debit1 mt-2 text-danger" hidden>Data yang dimasukkan salah
                                        harap cek kembali !!
                                    </p>
                                </td>
                                <td style="vertical-align: top;">
                                    <input type="text" class="form-control kredit_rupiah kredit_rupiah1 text-end"
                                        value="Rp 0" count="1" readonly>
                                    <input type="hidden" class="form-control kredit_biasa kredit_biasa1" value="0"
                                        name="kredit[]">
                                    <input type="hidden" class="form-control id_klasifikasi1" value="0"
                                        name="id_klasifikasi[]">
                                    <p class="peringatan1 mt-2 text-danger" hidden>Apakah anda yakin ingin memasukkan
                                        biaya disebelah kredit
                                    </p>
                                </td>
                                {{-- <td style="vertical-align: top;">
                                    <p class="saldo_akun1 text-end" style="font-size: 12px"></p>
                                </td> --}}
                                {{-- <td style="vertical-align: top;">
                                    <button type="button" class="btn rounded-pill remove_baris" count="1"><i
                                            class="fas fa-trash text-danger"></i>
                                    </button>
                                </td> --}}
                            </tr>


                            <tr class="baris2">
                                <td style="vertical-align: top;">
                                    {{-- <button type="button" data-bs-toggle="collapse" href=".join2"
                                        class="btn rounded-pill " count="1"><i class="fas fa-angle-down"></i>
                                    </button> --}}
                                </td>
                                <td style="vertical-align: top;">
                                    <input type="hidden" name="id_akun[]" value="{{$akun_aktiva->id_akun}}">
                                    <input type="text" class="form-control" value="{{$akun_aktiva->nm_akun}} " readonly>
                                    <div class="">
                                        <label for="" class="mt-2 ">Urutan Pengeluaran</label>
                                        <input type="text" class="form-control " name="no_urut[]">
                                    </div>
                                </td>
                                <td style="vertical-align: top;">
                                    {{-- <select name="id_post[]" id="" class="select2_add post2">

                                    </select> --}}
                                </td>


                                <td style="vertical-align: top;">
                                    <input type="text" name="keterangan[]" class="form-control"
                                        placeholder="nama barang, qty, @rp">

                                </td>
                                <td style="vertical-align: top;">
                                    <input type="text" class="form-control debit_rupiah2 debit_rupiah text-end"
                                        value="Rp 0" count="2" readonly>
                                    <input type="hidden" class="form-control debit_biasa debit_biasa2" value="0"
                                        name="debit[]">
                                    <p class="peringatan_debit2 mt-2 text-danger" hidden>Data yang dimasukkan salah
                                        harap cek kembali !!
                                    </p>

                                </td>
                                <td style="vertical-align: top;">
                                    <input type="text" class="form-control kredit_rupiah text-end" value="Rp 0"
                                        count="2" readonly>
                                    <input type="hidden" class="form-control kredit_biasa kredit_biasa2" value="0"
                                        name="kredit[]">
                                    <input type="hidden" class="form-control id_klasifikasi2" value="0"
                                        name="id_klasifikasi[]">
                                    <p class="peringatan2 mt-2 text-danger" hidden>Apakah anda yakin ingin memasukkan
                                        biaya disebelah kredit
                                    </p>
                                </td>
                                {{-- <td style="vertical-align: top;">
                                    <p class="saldo_akun2 text-end" style="font-size: 12px"></p>
                                </td> --}}
                                {{-- <td style="vertical-align: top;">
                                    <button type="button" class="btn rounded-pill remove_baris" count="2"><i
                                            class="fas fa-trash text-danger"></i>
                                    </button>
                                </td> --}}
                            </tr>
                        </tbody>



                    </table>
                </div>
                <div class="col-lg-6">

                </div>
                <div class="col-lg-6">
                    <hr style="border: 1px solid blue">
                    <table class="" width="100%">
                        <tr>
                            <td width="20%">Total</td>
                            <td width="40%" class="total" style="text-align: right;">Rp.0</td>
                            <td width="40%" class="total_kredit" style="text-align: right;">Rp.0</td>
                        </tr>
                        <tr>
                            <td class="cselisih" colspan="2">Selisih</td>
                            <td style="text-align: right;" class="selisih cselisih">Rp.0</td>
                        </tr>
                    </table>

                </div>
            </section>

    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary button-save" hidden>Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        <a href="{{ route('jurnal') }}" class="float-end btn btn-outline-primary me-2">Batal</a>
        </form>


        <x-theme.modal title="Tambah Proyek" idModal="tambah">
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">Kode Proyek</label>
                        <input type="text" class="form-control" name="kd_proyek">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">Tanggal Proyek</label>
                        <input type="date" class="form-control " name="tgl">
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="form-group">
                        <label for="">Nama Proyek</label>
                        <input type="text" class="form-control" name="nm_proyek">
                    </div>
                </div>

                {{-- <div class="col-lg-6">
                    <div class="form-group">
                        <label for="">Tanggal Estimasi Selesai</label>
                        <input type="date" class="form-control " name="tgl_estimasi">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="">Manager Proyek</label>
                        <input type="text" name="manager_proyek" class="form-control">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="">Estimasi Biaya</label>
                        <input type="text" class="form-control b_estimasi" style="text-align: right">
                        <input type="hidden" name="biaya_estimasi" class="form-control b_estimasi_biasa"
                            style="text-align: right">
                    </div>
                </div> --}}
            </div>
        </x-theme.modal>

    </x-slot>





    @section('scripts')
    <script>
        $(".select2suplier").select2()
            $(document).ready(function() {



                $(document).on("keyup", ".debit_rupiah", function() {
                    var count = $(this).attr("count");
                    var id_klasifikasi = $('.id_klasifikasi' + count).val();
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");


                    if (input === "") {
                        $(this).val("");
                        $('.debit_biasa' + count).val(0)
                    } else {
                        $(this).val("Rp " + input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.debit_biasa' + count).val(input2)

                    }
                    if (id_klasifikasi === '1') {
                        $('.peringatan_debit1' + count).attr("hidden", false);
                    } else {
                        $('.peringatan_debit1' + count).attr("hidden", true);
                    }


                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var totalRupiah = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    var debit = $(".total").text(totalRupiah);

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var selisih = total_debit - total_kredit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (selisih === 0) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);




                });



                $(document).on("keyup", ".kredit_rupiah", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    var id_klasifikasi = $('.id_klasifikasi' + count).val();



                    if (id_klasifikasi === '3') {
                        $('.peringatan' + count).attr("hidden", false);
                    } else {
                        $('.peringatan' + count).attr("hidden", true);
                    }

                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.kredit_biasa' + count).val(0)
                    } else {
                        $(this).val("Rp " + input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.kredit_biasa' + count).val(input2)

                    }

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });

                    var totalRupiah = total_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    var debit = $(".total_kredit").text(totalRupiah);

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });
                    var selisih = total_debit - total_kredit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (total_debit === total_kredit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);
                });

                $("form").on("keypress", function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        return false;
                    }
                });

                $(".pilihan_l").hide();

                $(document).on("click", "#Pilihan_Lainnya", function() {
                    if ($(this).prop("checked") == true) {
                        $(".pilihan_l").show();
                        $(".inp-lain").removeAttr("disabled");
                    } else if ($(this).prop("checked") == false) {
                        $(".pilihan_l").hide();
                    }
                });


                aksiBtn("form");
            });
    </script>
    <script>
        $(document).ready(function() {
                $(document).on("change", ".pilih_akun", function() {
                    var count = $(this).attr("count");
                    var id_akun = $(".pilih_akun" + count).val();
                    var kredit_biasa = $('.kredit_biasa' + count).val();
                    var debit_biasa = $('.debit_biasa' + count).val();


                    $.ajax({
                        url: "/saldo_akun?id_akun=" + id_akun,
                        type: "Get",
                        dataType: "json",
                        success: function(data) {
                            var id_klasifikasi = $(".id_klasifikasi" + count).val(data[
                                'id_klasifikasi']);

                            if (id_klasifikasi == 3) {
                                if (kredit_biasa != '0') {
                                    $('.peringatan' + count).attr("hidden", false);
                                } else {
                                    $('.peringatan' + count).attr("hidden", true);
                                }

                            } else {
                                $('.peringatan' + count).attr("hidden", true);
                            }
                            if (id_klasifikasi == 1) {
                                if (debit_biasa != '0') {
                                    $('.peringatan' + count).attr("hidden", false);
                                } else {
                                    $('.peringatan' + count).attr("hidden", true);
                                }


                            } else {
                                $('.peringatan' + count).attr("hidden", true);
                            }
                        },
                    });
                });
                $(document).on("change", ".pilih_akun", function() {
                    var count = $(this).attr("count");
                    var id_akun = $(".pilih_akun" + count).val();
                    $.ajax({
                        url: "/get_post2?id_akun=" + id_akun,
                        type: "Get",
                        success: function(data) {
                            $(".post" + count).html(data);
                        },
                    });
                });
                $(document).on("change", ".post_center", function() {
                    var count = $(this).attr("count");
                    var id_post = $(".post_center" + count).val();


                    $.ajax({
                        url: "/get_total_post?id_post=" + id_post,
                        type: "Get",
                        success: function(data) {
                            $(".kredit_rupiah" + count).val(data.format);
                            $(".kredit_biasa" + count).val(data.biasa);

                            $(".debit_rupiah2").val(data.format);
                            $(".debit_biasa2").val(data.biasa);

                            $(".total").text(data.format);
                            $(".total_kredit").text(data.format);
                            $(".cselisih").css("color", "green");
                            $(".button-save").removeAttr("hidden");
                        },
                    });
                });
            });
    </script>
    <script>
        $(document).ready(function() {
                $(document).on("change", ".proyek", function() {
                    var tambah = $(this).val();
                    if (tambah == 'tambah_proyek') {
                        $('#tambah').modal('show');

                    }
                });
                load_proyek();

                function load_proyek() {
                    $.ajax({
                        method: "GET",
                        url: "/get_proyek",
                        dataType: "html",
                        success: function(hasil) {
                            $(".proyek_berjalan").html(hasil);

                        },
                    });
                }

                $(".button-save-modal").click(function() {
                    // Ambil nilai dari input
                    var kd_proyek = $("input[name='kd_proyek']").val();
                    var tgl = $("input[name='tgl']").val();
                    var nm_proyek = $("input[name='nm_proyek']").val();
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');

                    // Kirim data ke server melalui AJAX
                    $.ajax({
                        type: "POST", // atau sesuaikan dengan metode yang Anda gunakan
                        url: "{{ route('proyek_add') }}", // ganti dengan URL target server
                        data: {
                            _token: csrfToken,
                            kd_proyek: kd_proyek,
                            tgl: tgl,
                            nm_proyek: nm_proyek
                        },
                        success: function(response) {
                            alert("Data berhasil disimpan!");
                            $("#tambah").modal('hide');

                            // Reset nilai input
                            $("input[name='kd_proyek']").val('');
                            $("input[name='tgl']").val('');
                            $("input[name='nm_proyek']").val('');

                            // Kosongkan pesan sukses
                            $(".success-message").text('');

                            load_proyek();
                        },
                        error: function(xhr, status, error) {
                            // Aksi jika terjadi kesalahan, misalnya menampilkan pesan error
                            alert("Terjadi kesalahan: " + error);
                        }
                    });
                });
            });
    </script>
    @endsection
</x-theme.app>