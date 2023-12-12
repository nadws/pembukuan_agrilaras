<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">

    </x-slot>
    <x-slot name="cardBody">
        <form action="{{ route('save_aktiva') }}" method="post" class="save_jurnal">
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
                load_menu();

                function load_menu() {
                    $.ajax({
                        method: "GET",
                        url: "/load_aktiva",
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
                        url: "/get_data_kelompok?id_kelompok=" + id_kelompok,
                        dataType: "json",
                        success: function(data) {
                            $('.nilai_persen' + count).text(data['nilai_persen'] * 100 + ' %');
                            $('.inputnilai_persen' + count).val(data['nilai_persen']);
                            $('.umur' + count).text(data['tahun'] + ' Tahun');

                            var tarif = $('.inputnilai_persen' + count).val();
                            var susut_bulan = (parseFloat(nilai) * parseFloat(tarif)) / 12;

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
                    var input = $(this).val();
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
                    var susut_bulan = (parseFloat(input2) * parseFloat(tarif)) / 12;

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
