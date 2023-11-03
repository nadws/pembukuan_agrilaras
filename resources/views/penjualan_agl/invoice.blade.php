<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">

    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-6">
                <x-theme.button modal="T" href="/produk_telur" icon="fa-home" addClass="float-end" teks="" />
            </div>
        </div>

    </x-slot>


    <x-slot name="cardBody">
        <form action="{{ route('save_penjualan_telur') }}" method="post" class="save_jurnal">
            @csrf
            <section class="row">

                <div class="col-lg-2 col-6">
                    <label for="">Tanggal</label>
                    <input type="date" class="form-control tgl_nota" name="tgl" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-lg-2 col-6">
                    <label for="">No Nota</label>
                    <input type="text" class="form-control nota_bk" name="no_nota" value="T{{ $nota }}"
                        readonly>
                </div>
                <div class="col-lg-2 col-6">
                    <label for="">Customer</label>
                    <select name="customer" id="select2" class="" required>
                        <option value="">Pilih Customer</option>
                        @foreach ($customer as $s)
                            <option value="{{ $s->id_customer }}">{{ $s->nm_customer }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-6">
                    <label for="">Tipe Penjualan</label>
                    <select name="tipe" class="select2_add pilih_tipe" required>
                        <option value="">Pilih Tipe Penjualan</option>
                        <option value="kg">Kg</option>
                        <option value="pcs">Pcs</option>
                    </select>
                </div>
                <div class="col-lg-2 col-6">
                    <label for="">Pengantar</label>
                    <input type="text" class="form-control" name="driver" required>
                </div>

                <div class="col-lg-12">
                    <hr style="border: 1px solid black">
                </div>
                <div class="col-lg-12">
                    <div id="loadkg"></div>
                    <div id="loadpcs"></div>
                </div>
                <div class="col-lg-5">

                </div>
                <div class="col-lg-7">

                    <hr style="border: 1px solid blue">


                    <div class="row">
                        <div class="col-lg-6">
                            <h6>Total</h6>
                        </div>
                        <div class="col-lg-6">
                            <h6 class="total float-end">Rp 0 </h6>
                            <input type="hidden" class="total_semua_biasa" name="total_penjualan">
                        </div>
                        <div class="col-lg-5 mt-2">
                            <label for="">Pilih Akun Pembayaran</label>
                            <select name="id_akun[]" id="" class="select2_add" required>
                                <option value="">-Pilih Akun-</option>
                                @foreach ($akun as $a)
                                    <option value="{{ $a->id_akun }}">{{ $a->nm_akun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <label for="">Debit</label>
                            <input type="text" class="form-control debit debit1" count="1"
                                style="text-align: right">
                            <input type="hidden" name="debit[]" class="form-control debit_biasa debit_biasa1"
                                value="0">
                        </div>
                        <div class="col-lg-3 mt-2">
                            <label for="">Kredit</label>
                            <input type="text" class="form-control kredit kredit1" count="1"
                                style="text-align: right">
                            <input type="hidden" name="kredit[]" class="form-control kredit_biasa kredit_biasa1"
                                value="0">
                        </div>
                        <div class="col-lg-1 mt-2">
                            <label for="">aksi</label> <br>
                            <button type="button" class="btn rounded-pill tbh_pembayaran" count="1">
                                <i class="fas fa-plus text-success"></i>
                            </button>
                        </div>
                    </div>
                    <div id="load_pembayaran"></div>

                    <div class="row">
                        <div class="col-lg-12">
                            <hr style="border: 1px solid blue">
                        </div>
                        <div class="col-lg-5">
                            <h6>Total Pembayaran</h6>
                        </div>
                        <div class="col-lg-3">
                            <h6 class="total_debit float-end">Rp 0</h6>
                        </div>
                        <div class="col-lg-3">
                            <h6 class="total_kredit float-end">Rp 0</h6>
                        </div>
                        <div class="col-lg-1"></div>
                        <div class="col-lg-5">
                            <h6 class="cselisih">Selisih</h6>
                        </div>
                        <div class="col-lg-3">
                        </div>
                        <div class="col-lg-3">
                            <h6 class="selisih float-end cselisih">Rp 0</h6>
                        </div>
                        <div class="col-lg-1"></div>
                    </div>


                </div>
            </section>
    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary button-save " hidden>Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        <a href="{{ route('penjualan_agrilaras') }}" class="float-end btn btn-outline-primary me-2">Batal</a>
        </form>
    </x-slot>



    @section('scripts')
        <script>
            $(document).ready(function() {

                function loadkg() {
                    $.ajax({
                        type: "get",
                        url: "/loadkginvoice",
                        success: function(data) {
                            $("#loadkg").html(data);
                            $(".select").select2();
                        }
                    });
                }

                function loadpcs() {
                    $.ajax({
                        type: "get",
                        url: "/loadpcsinvoice",
                        success: function(data) {
                            $("#loadpcs").html(data);
                            $(".select").select2();
                        }
                    });
                }
                $(document).on('change', ".pilih_tipe", function() {
                    var tipe = $(this).val();
                    if (tipe === 'kg') {
                        loadkg()
                        $("#loadpcs").html("");
                    } else {
                        loadpcs()
                        $("#loadkg").html("");
                    }
                });

                $(document).on("keyup", ".pcs", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.pcs_biasa' + count).val(0)
                    } else {
                        $(this).val(input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.pcs_biasa' + count).val(input2)
                    }
                    var kg = $('.kgbiasa' + count).val();
                    var ikat = parseFloat(input2) / 180;
                    var kg_jual = parseFloat(kg) - parseFloat(ikat.toFixed(1));

                    $('.ikat' + count).val(ikat.toFixed(1));
                    $('.kgminrak' + count).val(kg_jual.toFixed(1));
                    $('.kgminrakbiasa' + count).val(kg_jual.toFixed(1));

                    var rp_satuan = $('.rp_satuanbiasa' + count).val();

                    total = parseFloat(rp_satuan) * parseFloat(kg_jual);

                    var totalRupiah = total.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $('.ttl_rp' + count).text(totalRupiah);

                    $('.ttl_rpbiasa' + count).val(total);

                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });
                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;


                    var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    var totalRupiahall = total_all.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total").text(totalRupiahall);
                    $(".total_kredit").text(totalkreditall)
                    $(".total_semua_biasa").val(Math.round(total_all))
                    $(".debit1").val(totalRupiahall);
                    $(".debit_biasa1").val(Math.round(total_all));

                    // selisih
                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });
                    var totaldebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totaldebitall);


                    var selisih = Math.round(total_all + total_kredit) - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (Math.round(total_kredit + total_all) === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                });
                $(document).on("keyup", ".kg", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.kgbiasa' + count).val(0)
                    } else {
                        $(this).val(input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.kgbiasa' + count).val(input2)
                    }
                    var pcs = $('.pcs_biasa' + count).val();
                    var ikat = parseFloat(pcs) / 180;
                    var kg_jual = parseFloat(input2) - parseFloat(ikat.toFixed(1));
                    $('.kgminrak' + count).text(kg_jual.toFixed(1));
                    $('.kgminrakbiasa' + count).val(kg_jual.toFixed(1));

                    var rp_satuan = $('.rp_satuanbiasa' + count).val();

                    total = parseFloat(rp_satuan) * parseFloat(kg_jual);

                    var totalRupiah = total.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $('.ttl_rp' + count).text(totalRupiah);

                    $('.ttl_rpbiasa' + count).val(total);

                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });
                    var totalRupiahall = total_all.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;


                    var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    $(".total").text(totalRupiahall)
                    $(".total_kredit").text(totalkreditall)
                    $(".total_semua_biasa").val(Math.round(total_all))
                    $(".debit1").val(totalRupiahall);
                    $(".debit_biasa1").val(Math.round(total_all));


                    // selisih
                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });
                    var totaldebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totaldebitall);


                    var selisih = Math.round(total_all + total_kredit) - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (Math.round(total_kredit + total_all) === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);



                });

                $(document).on("keyup", ".rp_satuan", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.rp_satuanbiasa' + count).val(0)
                    } else {
                        $(this).val("Rp " + input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.rp_satuanbiasa' + count).val(input2)
                    }
                    var kg_jual = $('.kgminrakbiasa' + count).val();
                    total = parseFloat(input2) * parseFloat(kg_jual);
                    var totalRupiah = total.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $('.ttl_rp' + count).text(totalRupiah);

                    $('.ttl_rpbiasa' + count).val(total);

                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });
                    var totalRupiahall = total_all.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;


                    var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total").text(totalRupiahall)
                    $(".total_kredit").text(totalkreditall)
                    $(".total_semua_biasa").val(Math.round(total_all))
                    $(".debit1").val(totalRupiahall);
                    $(".debit_biasa1").val(Math.round(total_all));


                    // selisih
                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });
                    var totaldebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totaldebitall);


                    var selisih = Math.round(total_all + total_kredit) - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (Math.round(total_kredit + total_all) === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);


                });

                var count = 2;
                $(document).on("click", ".tbh_baris_kg", function() {
                    count = count + 1;
                    $.ajax({
                        url: "/tambah_baris_kg?count=" + count,
                        type: "Get",
                        success: function(data) {
                            $("#tb_baris_kg").append(data);
                            $(".select").select2();
                        },
                    });
                });

                $(document).on("click", ".remove_baris_kg", function() {
                    var delete_row = $(this).attr("count");
                    $(".baris" + delete_row).remove();
                    $('.ttl_rpbiasa' + count).val(total);
                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });
                    var totalRupiahall = total_all.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;


                    var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total").text(totalRupiahall)
                    $(".total_kredit").text(totalkreditall)

                    // selisih
                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });
                    var totaldebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totaldebitall);

                    var selisih = Math.round(total_all + total_kredit) - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (Math.round(total_kredit + total_all) === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                });

                aksiBtn("form");
                $("form").on("keypress", function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        return false;
                    }
                });




            });
        </script>

        <script>
            $(document).ready(function() {
                $(document).on("keyup", ".tipe_pcs", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.tipe_pcs_biasa' + count).val(0)
                    } else {
                        $(this).val(input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.tipe_pcs_biasa' + count).val(input2)
                    }

                    var rp_satuan = $('.tipe_rp_satuanbiasa' + count).val();

                    total = parseFloat(rp_satuan) * parseFloat(input2);

                    var totalRupiah = total.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $('.tipe_ttl_rp' + count).text(totalRupiah);

                    $('.tipe_ttl_rpbiasa' + count).val(total);

                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });
                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;


                    var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    var totalRupiahall = total_all.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total").text(totalRupiahall);
                    $(".total_kredit").text(totalkreditall)
                    $(".total_semua_biasa").val(total_all)

                    // selisih
                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });
                    var totaldebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totaldebitall);

                    var selisih = total_all + total_kredit - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (total_kredit + total_all === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                });
                $(document).on("keyup", ".tipe_kg", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.tipe_kgbiasa' + count).val(0)
                    } else {
                        $(this).val(input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.tipe_kgbiasa' + count).val(input2)
                    }
                });

                $(document).on("keyup", ".tipe_rp_satuan", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.tipe_rp_satuanbiasa' + count).val(0)
                    } else {
                        $(this).val(input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.tipe_rp_satuanbiasa' + count).val(input2)
                    }

                    var pcs = $('.tipe_pcs_biasa' + count).val();

                    total = parseFloat(pcs) * parseFloat(input2);

                    var totalRupiah = total.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $('.tipe_ttl_rp' + count).text(totalRupiah);

                    $('.tipe_ttl_rpbiasa' + count).val(total);

                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });
                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;


                    var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    var totalRupiahall = total_all.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total").text(totalRupiahall);
                    $(".total_kredit").text(totalkreditall)
                    $(".total_semua_biasa").val(total_all)

                    // selisih
                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });
                    var totaldebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totaldebitall);

                    var selisih = total_all + total_kredit - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (total_kredit + total_all === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                });

                var count = 2;
                $(document).on("click", ".tbh_baris_pcs", function() {
                    count = count + 1;
                    $.ajax({
                        url: "/tambah_baris_pcs?count=" + count,
                        type: "Get",
                        success: function(data) {
                            $("#tb_baris_pcs").append(data);
                            $(".select").select2();
                        },
                    });
                });

                $(document).on("click", ".remove_baris_pcs", function() {
                    var delete_row = $(this).attr("count");
                    $(".baris" + delete_row).remove();
                    $('.ttl_rpbiasa' + count).val(total);
                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });
                    var totalRupiahall = total_all.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;


                    var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total").text(totalRupiahall)
                    $(".total_kredit").text(totalkreditall)

                    // selisih
                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });
                    var totaldebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totaldebitall);

                    var selisih = Math.round(total_all + total_kredit) - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (Math.round(total_kredit + total_all) === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                });

            });
        </script>

        <script>
            $(document).ready(function() {
                $(document).on("keyup", ".debit", function() {
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

                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var totalDebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totalDebitall);

                    // selisih
                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;
                    var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalKreditall);

                    var selisih = Math.round(total_all + total_kredit) - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    // console.log(Math.round(total_all + total_kredit));
                    // console.log(total_debit);

                    if (Math.round(total_kredit + total_all) === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                });
                $(document).on("keyup", ".kredit", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
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

                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;
                    var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalKreditall);

                    var selisih = perseFloat(total_all + total_kredit) - parseFloat(total_debit);
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    if (perseFloat(total_kredit + total_all) === perseFloat(total_debit)) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);


                });
                var count = 2;
                $(document).on("click", ".tbh_pembayaran", function() {
                    count = count + 1;
                    $.ajax({
                        url: "/tbh_pembayaran?count=" + count,
                        type: "Get",
                        success: function(data) {
                            $("#load_pembayaran").append(data);
                            $(".select").select2();
                        },
                    });
                });

                $(document).on("click", ".delete_pembayaran", function() {
                    var delete_row = $(this).attr("count");
                    $(".baris_bayar" + delete_row).remove();


                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;
                    var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalKreditall);

                    var selisih = total_all + total_kredit - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (total_kredit + total_all === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                });
            });
        </script>
    @endsection
</x-theme.app>
