<x-theme.app title="{{ $title }}" table="Y" sizeCard="8">

    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-12">
                <h6 class="float-start">{{ $title }}</h6>

                <x-theme.button modal="T" href="/peralatan/nota_jurnal/{{ $no_nota }}/3/print" icon="fa-print"
                    addClass="float-end print" teks="Print" />

                    <form action="{{ route('peralatan.save_aktiva') }}" method="post">
                <x-theme.modal size="modal-lg-max" title="Tambah Peralatan" idModal="tambah">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kelompok</th>
                                            <th>Nama Peralatan</th>
                                            <th>Tanggal Perolehan</th>
                                            <th class="text-end">Nilai Perolehan</th>
                                            <th style="text-align: center">Umur</th>
                                            <th>Penyusutan Perbulan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="baris1">
                                            <td>
                                                <select name="id_kelompok[]" id="" required
                                                    class="select2 pilih_kelompok pilih_kelompok1" count='1'>
                                                    <option value="">Pilih Kelompok</option>
                                                    @foreach ($kelompok as $k)
                                                        <option value="{{ $k->id_kelompok }}">{{ $k->nm_kelompok }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" name="nm_aktiva[]" readonly class="form-control "
                                                    value="{{ $jurnal[0]->ket }}"></td>
                                            <td><input value="{{ date('Y-m-d') }}" type="date" name="tgl[]"
                                                    class="form-control"></td>
                                            <td>
                                                <input type="text" readonly
                                                    value="Rp. {{ number_format($jurnal[0]->debit, 0) }}"
                                                    class="text-end form-control nilai_perolehan nilai_perolehan1"
                                                    count='1'>
                                                <input type="hidden" value="{{ $jurnal[0]->debit }}"
                                                    name="h_perolehan[]" class="form-control  nilai_perolehan_biasa1">
                                            </td>

                                            <td>
                                                <p class="umur1 text-center"></p>
                                            </td>
                                            <input type="hidden" class="periode1">
                                            <input type="hidden" class="umurInput1">

                                            <td>
                                                <p class="susut_bulan1 text-center"></p>
                                            </td>

                                        </tr>


                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </x-theme.modal>
                </form>
            </div>

        </div>
        <div class="row tbl1">
            <div class="col-lg-12">
                <table width="100%" cellpadding="10px">
                    <tr>
                        <th style="background-color: white;" width="10%">Tanggal</th>
                        <th style="background-color: white;" width="2%">:</th>
                        <th style="background-color: white;">{{ date('d-m-Y', strtotime($head_jurnal->tgl)) }}</th>
                        <th style="background-color: white;" width="10%">No Nota</th>
                        <th style="background-color: white;" width="2%">:</th>
                        <th style="background-color: white;">{{ $head_jurnal->no_nota }}</th>
                    </tr>
                    <tr>
                        <th style="background-color: white; " width="10%">Proyek</th>
                        <th style="background-color: white; " width="2%">:</th>
                        <th style="background-color: white; ">{{ $head_jurnal->nm_proyek }}</th>
                        <th style="background-color: white; " width="10%">Suplier</th>
                        <th style="background-color: white; " width="2%">:</th>
                        <th style="background-color: white; ">{{ $head_jurnal->nm_suplier ?? '' }}</th>
                    </tr>
                </table>
            </div>
        </div>

    </x-slot>


    <x-slot name="cardBody">
        <form action="{{ route('save_opname_telur_mtd') }}" method="post" class="save_jurnal">
            @csrf
            <section class="row tbl2">


                <div class="col-lg-12">
                    <hr style="border: 1px solid black">
                </div>
                <div class="col-lg-12">
                    <table class="table table-hover table-bordered dborder">
                        <thead>
                            <tr>
                                <th class="dhead" width="5">#</th>
                                <th class="dhead">Akun</th>
                                <th class="dhead">Keterangan</th>
                                <th class="dhead" style="text-align: right">Debit</th>
                                <th class="dhead" style="text-align: right">Kredit</th>
                                <th class="dhead">Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jurnal as $no => $a)
                                <tr>
                                    <td>{{ $no + 1 }}</td>
                                    <td>{{ $a->akun->nm_akun }}</td>
                                    <td>{{ $a->ket }}</td>
                                    <td align="right">{{ number_format($a->debit, 0) }}</td>
                                    <td align="right">{{ number_format($a->kredit, 0) }}</td>
                                    <td>{{ $a->admin }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <x-theme.button idModal="tambah" modal="Y" icon="fa-tools" addClass="float-end"
                        teks="Tambah Peralatan" />
                    <x-theme.button modal="T" href="{{ route('jurnal', ['id_buku' => 10]) }}" icon="fa-arrow-left"
                        addClass="float-end" teks="Kembali" />
                </div>
            </section>

            @section('scripts')
                <script>
                    $(document).on("change", ".pilih_kelompok", function() {
                        var count = $(this).attr("count");
                        var id_kelompok = $('.pilih_kelompok' + count).val();
                        var nilai = $('.nilai_perolehan_biasa' + count).val()
                        $.ajax({
                            type: "GET",
                            url: "{{ route('peralatan.get_data_kelompok') }}",
                            data: {
                                id_kelompok: id_kelompok
                            },
                            dataType: "json",
                            success: function(data) {
                                console.log(data)
                                $('.nilai_persen' + count).text(data['nilai_persen'] * 100 + ' %');
                                $('.inputnilai_persen' + count).val(data['nilai_persen']);
                                $('.umur' + count).text(data['tahun'] + ' ' + data['periode']);
                                $(".periode" + count).val(data['periode']);
                                $(".umurInput" + count).val(data['tahun']);
                                var tarif = $('.inputnilai_persen' + count).val();
                                var umur = data['tahun']
                                var susut_bulan = data['periode'] === 'Bulan' ? parseFloat(nilai) / umur :
                                    parseFloat(nilai) / (umur * 12)

                                var susut_rupiah = susut_bulan.toLocaleString("id-ID", {
                                    style: "currency",
                                    currency: "IDR",
                                });

                                if (nilai === '') {
                                    $('.susut_bulan' + count).text('Rp.0');

                                } else {
                                    $('.susut_bulan' + count).text(susut_bulan);

                                }

                            }
                        });
                    });

                    $(document).on("input change", ".nilai_perolehan", function() {
                        var count = $(this).attr("count");
                        var periode = $('.periode' + count).val()
                        var umur = $('.umurInput' + count).val()
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
                        var susut_bulan = periode === 'Bulan' ? input / umur : input / (umur * 12)

                        var susut_rupiah = susut_bulan.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });

                        $('.susut_bulan' + count).text(susut_rupiah);


                    });
                </script>
            @endsection
    </x-slot>
</x-theme.app>
