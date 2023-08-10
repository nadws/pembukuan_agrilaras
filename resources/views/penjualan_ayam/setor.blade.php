<x-theme.app title="{{ $title }}" table="Y" sizeCard="11">

    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <h6>Setor Penjualan Ayam</h6>
        </div>

    </x-slot>


    <x-slot name="cardBody">
        <style>
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #000000;
                line-height: 36px;
                /* font-size: 12px; */
                width: 170px;

            }
        </style>
        <style>
            .dhead {
                background-color: #435EBE !important;
                color: white;
            }
        </style>
        <form action="{{ route('penjualan_ayam.save_setor') }}" method="post" class="save_jurnal">
            @csrf

            <section class="row">
                {{-- <div class="col-lg-2 col-6">
                    <label for="">Tanggal</label>
                    <input type="date" class="form-control" name="tgl" value="{{date('Y-m-d')}}">
                </div> --}}
                {{-- <div class="col-lg-2 col-6">
                    <label for="">No Nota</label>
                    <input type="text" class="form-control nota_bk" name="no_nota" value="PT{{$nota}}" readonly>
                </div> --}}
                <div class="col-lg-12">
                    <hr style="border: 1px solid black">
                </div>
                <div class="col-lg-12">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="dhead">No Nota</th>
                                <th class="dhead">Tanggal</th>
                                <th class="dhead">Customer</th>
                                <th class="dhead" style="text-align: right">Total Rp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $total = 0;
                            @endphp
                            @foreach ($nota as $no => $n)
                            @php
                            $hutang = DB::selectOne("SELECT *, sum(a.h_satuan * a.qty) as total, count(*) as ttl_produk FROM
                            `invoice_ayam` as a
                            LEFT JOIN customer as b ON a.id_customer = b.id_customer
                            WHERE a.urutan = '$n'
                            GROUP BY a.urutan;");
                            $total += $hutang->total
                            @endphp
                            <tr>
                                <td>{{$hutang->no_nota}}</td>
                                <td>
                                    {{tanggal($hutang->tgl)}}
                                    <input type="hidden" name="tgl[]" value="{{$hutang->tgl}}">
                                    <input type="hidden" name="urutan[]" value="{{$hutang->urutan}}">
                                    <input type="hidden" name="no_nota[]" value="{{$hutang->no_nota}}">
                                    <input type="hidden" name="pembayaran[]"
                                        class="form-control bayar_biasa bayar_biasa{{$no+1}}" style="text-align: right"
                                        value="{{$hutang->total}}">
                                </td>
                                <td>{{$hutang->nm_customer}}</td>
                                <td align="right">
                                    Rp {{number_format($hutang->total,0)}}

                                </td>
                            </tr>
                            @endforeach


                        </tbody>


                    </table>

                </div>
                <div class="col-lg-4">

                </div>
                <div class="col-lg-8">

                    <hr style="border: 1px solid blue">

                    {{-- <input type="hidden" name="ket" value="{{ implode(',', $no_nota) }}"> --}}
                    <div class="row">
                        <div class="col-lg-6">
                            <h6>Total</h6>
                        </div>
                        <div class="col-lg-6">
                            <h6 class="total float-end">Rp {{number_format($total,2,',','.')}}</h6>
                            <input type="hidden" class="total_semua_biasa" name="total_penjualan" value="{{$total}}">
                        </div>
                        <div class="col-lg-5 mt-2">
                            <label for="">Pilih Akun Pembayaran</label>
                            <select name="id_akun[]" id="" class="select2_add" required>
                                <option value="">-Pilih Akun-</option>
                                @foreach ($akun as $a)
                                <option value="{{$a->id_akun}}">{{$a->nm_akun}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <label for="">Debit</label>
                            <input type="text" class="form-control debit debit1" count="1" style="text-align: right">
                            <input type="hidden" name="debit[]" class="form-control debit_biasa debit_biasa1" value="0">
                        </div>
                        <div class="col-lg-3 mt-2">
                            <label for="">Kredit</label>
                            <input type="text" class="form-control kredit kredit1" count="1" style="text-align: right">
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
                        <div class="col-lg-4">
                            <h6 class="total_kredit float-end">Rp {{number_format($total,0)}} </h6>
                        </div>
                        <div class="col-lg-5">
                            <h6 class="cselisih">Selisih</h6>
                        </div>
                        <div class="col-lg-3">
                        </div>
                        <div class="col-lg-4">
                            <h6 class="selisih float-end cselisih">Rp 0</h6>
                        </div>
                    </div>


                </div>

            </section>
    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary button-save" hidden>Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        <a href="{{ route('terima_invoice_mtd') }}" class="float-end btn btn-outline-primary me-2">Batal</a>
        </form>
    </x-slot>



    @section('scripts')
    <script>
        $(document).ready(function () { 
            $(document).on("keyup", ".debit", function () {
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
                $(".bayar_biasa").each(function () {
                    total_all += parseFloat($(this).val());
                });

                var total_debit = 0;
                $(".debit_biasa").each(function(){
                    total_debit += parseFloat($(this).val());
                });

                var totalDebitall = total_debit.toLocaleString("id-ID", {
                    style: "currency",
                    currency: "IDR",
                });
                $(".total_debit").text(totalDebitall);

                // selisih
                var total_kredit = 0;
                $(".kredit_biasa").each(function(){
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
            $(document).on("keyup", ".kredit", function () {
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
                $(".bayar_biasa").each(function () {
                    total_all += parseFloat($(this).val());
                });

                var total_debit = 0;
                $(".debit_biasa").each(function(){
                    total_debit += parseFloat($(this).val());
                });

                var total_kredit = 0;
                $(".kredit_biasa").each(function(){
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
            var count = 2;
            $(document).on("click", ".tbh_pembayaran", function () {
                count = count + 1;
                $.ajax({
                    url: "/tbh_pembayaran?count=" + count,
                    type: "Get",
                    success: function (data) {
                        $("#load_pembayaran").append(data);
                        $(".select").select2();
                    },
                });
            });

            $(document).on("click", ".delete_pembayaran", function () {
                var delete_row = $(this).attr("count");
                $(".baris_bayar" + delete_row).remove();


                var total_all = 0;
                $(".bayar_biasa").each(function () {
                    total_all += parseFloat($(this).val());
                });

                var total_debit = 0;
                $(".debit_biasa").each(function(){
                    total_debit += parseFloat($(this).val());
                });

                var total_kredit = 0;
                $(".kredit_biasa").each(function(){
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

                var total_debit = 0;
                $(".debit_biasa").each(function(){
                    total_debit += parseFloat($(this).val());
                });

                var totalDebitall = total_debit.toLocaleString("id-ID", {
                    style: "currency",
                    currency: "IDR",
                });
                $(".total_debit").text(totalDebitall);

                var total_kredit = 0;
                $(".kredit_biasa").each(function(){
                    total_kredit += parseFloat($(this).val());
                });
                var total_all_kredit = total_all + total_kredit;
                var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                    style: "currency",
                    currency: "IDR",
                });
                $(".total_kredit").text(totalKreditall);
                
            });
        });
    </script>

    @endsection
</x-theme.app>