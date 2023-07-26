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
        <form action="{{ route('save_jurnal') }}" method="post" class="save_jurnal">
            @csrf
            <section class="row">

                <div class="col-lg-3">
                    <label for="">Tanggal</label>
                    <input type="date" class="form-control" name="tgl" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-lg-3">
                    <label for="">No Urut Jurnal Umum</label>
                    <input type="text" class="form-control" name="no_nota" value="JU-{{ $max }}" readonly>
                </div>
                <div class="col-lg-3">
                    <label for="">Proyek</label>
                    <select name="id_proyek" id="select2">
                        <option value="">Pilih</option>
                        @foreach ($proyek as $p)
                        <option value="{{ $p->id_proyek }}">{{ $p->nm_proyek }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label for="">Suplier</label>
                    <select name="id_suplier" class="select2suplier form-control">
                        <option value="">- Pilih Suplier -</option>
                        @foreach ($suplier as $p)
                        <option value="{{ $p->id_suplier }}">{{ $p->nm_suplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-12">
                    <hr style="border: 1px solid black">
                </div>
                <div class="col-lg-12">
                    <div id="load_menu"></div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        {{-- <x-theme.toggle name="Pilihan Lainnya">

                        </x-theme.toggle>
                        <div class="col-lg-12"></div>
                        <div class="col-lg-6 pilihan_l">
                            <label for="">No Dokumen</label>
                            <input type="text" class="form-control inp-lain" name="no_dokumen">
                        </div>
                        <div class="col-lg-6 pilihan_l">
                            <label for="">Tanggal Dokumen</label>
                            <input type="date" class="form-control inp-lain" name="tgl_dokumen">
                        </div> --}}

                    </div>
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
    </x-slot>



    @section('scripts')
    <script>
        $(".select2suplier").select2()
            $(document).ready(function() {
                load_menu();

                function load_menu() {
                    $.ajax({
                        method: "GET",
                        url: "/load_menu",
                        dataType: "html",
                        success: function(hasil) {
                            $("#load_menu").html(hasil);
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

                $(document).on("click", ".remove_baris", function() {
                    var delete_row = $(this).attr("count");
                    $(".baris" + delete_row).remove();

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });
                    var totalRupiah_debit = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    var debit = $(".total").text(totalRupiah_debit);

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var totalRupiah = total_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalRupiah);

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

                var count = 3;
                $(document).on("click", ".tbh_baris", function() {
                    count = count + 1;
                    $.ajax({
                        url: "/tambah_baris_jurnal?count=" + count,
                        type: "Get",
                        success: function(data) {
                            $("#tb_baris").append(data);
                            $(".select").select2();
                        },
                    });
                });


                $(document).on("keyup", ".debit_rupiah", function() {
                    var count = $(this).attr("count");
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
                        },
                    });
                });
                $(document).on("change", ".pilih_akun", function() {
                    var count = $(this).attr("count");
                    var id_akun = $(".pilih_akun" + count).val();
                    $.ajax({
                        url: "/get_post?id_akun=" + id_akun,
                        type: "Get",
                        success: function(data) {
                            $(".post" + count).html(data);
                        },
                    });
                });
            });
    </script>
    @endsection
</x-theme.app>