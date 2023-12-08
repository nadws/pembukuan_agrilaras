<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">

    <x-slot name="cardHeader">
        <div class="row justify-content-end">

            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }} </h6>
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
                @if ($id_buku == '12')
                    <div class="col-lg-3">
                        <label for="">Proyek</label>
                        <select name="id_proyek" id="select2" class="proyek proyek_berjalan">

                        </select>
                    </div>
                @endif

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
                    <input type="hidden" id_buku="{{ $id_buku }}">
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
        <div class="alert_saldo" hidden>
            <button type="submit" class="float-end btn btn-primary button-save" hidden>Simpan</button>
        </div>
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
        <x-theme.modal title="Saldo" idModal="tambah">
            <div class="row">

            </div>
        </x-theme.modal>

    </x-slot>





    @section('scripts')
        <script>
            function selectAllText(input) {
                input.focus();
                input.select();
            }
            $(".select2suplier").select2()
            $(document).ready(function() {
                load_menu();

                function load_menu() {
                    var urlParams = new URLSearchParams(window.location.search);
                    var id_akun = urlParams.get('id_akun');
                    var id_buku = urlParams.get('id_buku');
                    if (id_akun) {
                        $.ajax({
                            method: "GET",
                            url: "/load_menu",
                            dataType: "html",
                            data: {
                                id_akun: id_akun,
                                id_buku: id_buku
                            },
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
                    } else {
                        var defaultIdAkun = 'default_value';
                        $.ajax({
                            method: "GET",
                            url: "/load_menu",
                            dataType: "html",
                            data: {
                                id_akun: defaultIdAkun,
                                id_buku: id_buku
                            },
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
                    var id_klasifikasi = $('.id_klasifikasi' + count).val();
                    var saldo = $('.saldo' + count).val();

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

                    if (id_klasifikasi === '1' || id_klasifikasi === '2') {
                        $('.peringatan_debit' + count).attr("hidden", false);

                    } else {
                        $('.peringatan_debit' + count).attr("hidden", true);

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

                function number_format(number) {
                    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }

                $(document).on("keyup", ".kredit_rupiah", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    var id_klasifikasi = $('.id_klasifikasi' + count).val();
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
                    var saldo = $('.saldo' + count).val();

                    if (id_klasifikasi === '3') {
                        $('.peringatan' + count).attr("hidden", false);
                    } else {
                        $('.peringatan' + count).attr("hidden", true);
                        if (parseFloat(saldo) - input2 < 0) {
                            $('.alert_saldo').attr('hidden', true);
                            $('.peringatan_saldo' + count).removeAttr("hidden").text('Saldo saat ini = ' +
                                number_format(saldo));
                        } else {
                            $('.alert_saldo').attr('hidden', false);
                            $('.peringatan_saldo' + count).attr("hidden", true)
                        }
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

                            $(".nilai" + count).val(data['nilai']);
                            $(".saldo" + count).val(data['saldo']);
                            var nilai = data['nilai'];


                            // if (nilai == 1) {
                            //     $('.peringatan_akun' + count).attr("hidden", false);
                            // } else {
                            //     $('.peringatan_akun' + count).attr("hidden", true);
                            // }

                            var total_nilai = 0;
                            $(".nilai").each(function() {
                                total_nilai += parseFloat($(this).val());
                            });


                            if (total_nilai > 0) {
                                $('.button-save').prop('disabled', true);

                            } else {
                                $('.button-save').prop('disabled', false);

                            }

                            if (nilai != 1) {
                                $('.peringatan_akun' + count).attr("hidden", true);

                            } else {
                                $('.peringatan_akun' + count).attr("hidden", false);
                                setTimeout(function() {
                                    $('.peringatan_akun' + count).removeClass("vibrate");
                                }, 1000);
                            }

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
                        url: "/get_post?id_akun=" + id_akun,
                        type: "Get",
                        success: function(data) {
                            $(".post" + count).html(data);
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
