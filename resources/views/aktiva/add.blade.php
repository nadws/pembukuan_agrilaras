<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">

    </x-slot>
    <x-slot name="cardBody">
        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif
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

                var count = 1;
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
                function tampilRupiah(nilai) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency', currency: 'IDR', maximumFractionDigits: 0
                    }).format(Number(nilai) || 0);
                }

                function hitungPenyusutan(count) {
                    var nilaiBuku = Number($('.nilai_sisa_biasa' + count).val()) || 0;
                    var umurTahun = Number($('.umur_tahun' + count).val()) || 0;
                    var umurBulan = Number($('.umur_bulan' + count).val()) || 0;
                    var umurAktiva = (umurTahun * 12) + umurBulan;
                    var penyusutan = umurAktiva > 0 ? nilaiBuku / umurAktiva : 0;
                    $('.susut_bulan' + count).text(tampilRupiah(penyusutan));
                }

                $(document).on("input", ".nilai-uang", function() {
                    var count = $(this).attr("count");
                    var angka = String($(this).val()).replace(/\D/g, '');
                    $($(this).data('target')).val(angka || 0);
                    $(this).val(angka ? 'Rp ' + Number(angka).toLocaleString('id-ID') : '');

                    if ($(this).hasClass('nilai_perolehan') && !$('.nilai_sisa' + count).val()) {
                        $('.nilai_sisa_biasa' + count).val(angka || 0);
                        $('.nilai_sisa' + count).val(angka ? 'Rp ' + Number(angka).toLocaleString('id-ID') : '');
                    }
                    hitungPenyusutan(count);
                });

                $(document).on('input', '.umur_tahun, .umur_bulan', function() {
                    hitungPenyusutan($(this).attr('count'));
                });
                aksiBtn("form");
            });
        </script>
    @endsection
</x-theme.app>
