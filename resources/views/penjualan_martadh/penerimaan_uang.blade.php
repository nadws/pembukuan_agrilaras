<x-theme.app title="{{ $title }}" table="Y" sizeCard="11">

    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-2">

            </div>
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
        <form action="{{ route('save_terima_invoice') }}" method="post" class="save_jurnal">
            @csrf
            <input type="hidden" name="no_nota" value="{{ $nota }}">
            <input type="hidden" name="tgl" value="{{ $invoice2->tgl }}">

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
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-5">
                                    <img src="https://agrilaras.putrirembulan.com/assets/img/logo.png" alt="Logo"
                                        width="150px">
                                </div>
                                <div class="col-lg-7">
                                    <table class="float-end">
                                        <tr>
                                            <td style="padding: 5px">Tanggal</td>
                                            <td style="padding: 5px">:</td>
                                            <td style="padding: 5px">{{ Tanggal($invoice2->tgl) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 5px">No. Nota</td>
                                            <td style="padding: 5px">:</td>
                                            <td style="padding: 5px">{{ $invoice2->no_nota }} <span
                                                    class="text-danger">(mohon
                                                    dicopy
                                                    di nota manual)</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 5px">Kpd Yth</td>
                                            <td style="padding: 5px">:</td>
                                            <td style="padding: 5px">Bpk/Ibu {{ $invoice2->customer }}</td>
                                        </tr>
                                        {{-- <tr>
                                            <td style="padding: 5px">Pengirim</td>
                                            <td style="padding: 5px">:</td>
                                            <td style="padding: 5px"></td>
                                        </tr> --}}
                                    </table>
                                </div>
                            </div>
                        </div>
                        <h6 class="text-center">
                            Nota Penjualan Telur Martadah
                        </h6>
                        <div class="card-body">
                            <table class="table  table-bordered" style="white-space: nowrap;">
                                <thead>
                                    <tr>
                                        <th class="dhead" width="10%" rowspan="2">Produk </th>
                                        <th style="text-align: center" class="dhead abu" colspan="3">Penjualan per
                                            pcs</th>
                                        <th style="text-align: center" class="dhead putih" colspan="3">Penjualan per
                                            ikat
                                        </th>
                                        <th style="text-align: center" class="dhead abuGelap" colspan="4">Penjualan
                                            per rak
                                        </th>
                                        <th rowspan="2" class="dhead" width="10%"
                                            style="text-align: center; white-space: nowrap;">Total
                                            Rp
                                        </th>
                                    </tr>
                                    <tr>


                                        <th class="dhead abu" width="7%" style="text-align: center">Pcs</th>
                                        <th class="dhead abu" width="7%" style="text-align: center">Kg</th>
                                        <th class="dhead abu" width="10%" style="text-align: center;">Rp Pcs</th>

                                        <th class="dhead putih" width="7%" style="text-align: center;">Ikat</th>
                                        <th class="dhead putih" width="7%" style="text-align: center;">Kg</th>
                                        <th class="dhead putih" width="10%" style="text-align: center;">Rp Ikat</th>

                                        <th class="dhead abuGelap" width="7%" style="text-align: center;">Pcs</th>
                                        <th class="dhead abuGelap" width="7%" style="text-align: center;">Kg kotor
                                        </th>
                                        <th class="dhead abuGelap" width="7%" style="text-align: center;">Kg bersih
                                            <br>
                                            potong
                                            rak
                                        </th>
                                        {{-- <th class="dhead" width="7%" style="text-align: center;">Rak</th> --}}
                                        <th class="dhead abuGelap" width="10%" style="text-align: center;">Rp Rak
                                        </th>


                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $total_semua = 0;
                                    $ttl_pcs = 0;
                                    $ttl_kg_kotor = 0;
                                    $ttl_kg_bersih = 0;
                                    @endphp
                                    @foreach ($invoice as $i)
                                    <tr>

                                        <td>{{ $i->nm_telur }}</td>
                                        <td align="right">{{ $i->pcs_pcs }}</td>
                                        <td align="right">{{ $i->kg_pcs }}</td>
                                        <td align="right">Rp. {{ number_format($i->rp_pcs, 0) }}</td>
                                        <!-- Jual Ikat -->
                                        <td align="right">{{ $i->ikat }}</td>
                                        <td align="right">{{ $i->kg_ikat }}</td>
                                        <td align="right">Rp. {{ number_format($i->rp_ikat, 0) }}</td>
                                        <!-- Jual Ikat -->
                                        <!-- Jual Kg -->
                                        <td align="right">{{ $i->pcs_kg }}</td>
                                        <td align="right">{{ $i->kg_kg_kotor }}</td>
                                        <td align="right">{{ $i->kg_kg }}</td>
                                        {{-- <td align="right">{{$i->rak_kg}}</td> --}}
                                        <td align="right">Rp. {{ number_format($i->rp_kg, 0) }}</td>
                                        <!-- Jual Kg -->
                                        <td align="right">
                                            @php
                                            $rp_pcs = $i->pcs_pcs * $i->rp_pcs;
                                            $rp_ikat = ($i->kg_ikat - $i->ikat) * $i->rp_ikat;
                                            // $rak_kali = round($i->rak_kg * 0.12,1);
                                            $rak_kotor = round(($i->pcs_kg / 15) * 0.12, 1);
                                            $kg_rak_kotor = $i->kg_kg + $rak_kotor;
                                            $rp_kg = $i->kg_kg * $i->rp_kg;
                                            $total_rp = $rp_pcs + $rp_ikat + $rp_kg;

                                            $ikat_kg_bersih = $i->kg_ikat - $i->ikat;

                                            @endphp
                                            Rp. {{ number_format($total_rp, 0) }}
                                        </td>
                                    </tr>
                                    @php
                                    $total_semua += $total_rp;
                                    $ttl_pcs += $i->pcs_pcs + $i->ikat * 180 + $i->pcs_kg;
                                    $ttl_kg_kotor += $i->kg_pcs + $i->kg_ikat + $i->kg_kg_kotor;
                                    $ttl_kg_bersih += $ikat_kg_bersih + $i->kg_kg;
                                    @endphp
                                    @endforeach


                                </tbody>
                                {{-- <tfoot>
                                    <tr>
                                        <td colspan="9"></td>
                                        <th>Total</th>
                                        <th style="text-align: right">Rp. {{number_format($total_semua,0)}}</th>
                                    </tr>
                                </tfoot> --}}
                            </table>
                            <table width="50%">
                                <tr>

                                    <td>Total Pcs</td>
                                    <td>:</td>
                                    <td>{{ number_format($ttl_pcs, 0) }}</td>
                                    <td></td>
                                </tr>
                                <tr>

                                    <td>Total (Bruto)</td>
                                    <td>:</td>
                                    <td>{{ number_format($ttl_kg_kotor, 1) }}</td>
                                    <td></td>
                                </tr>
                                <tr>

                                    <td>Berat Bersih (Netto)</td>
                                    <td>:</td>
                                    <td>{{ number_format($ttl_kg_bersih, 1) }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><b>JUMLAH TOTAL </b></td>
                                    <td>:</td>
                                    <td><b>Rp.{{ number_format($total_semua, 0) }}</b></td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="col-lg-12">
                    <hr style="border: 1px solid blue">
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
                            <h6 class="total float-end">Rp {{ number_format($total_semua, 2, ',', '.') }}</h6>
                            <input type="hidden" class="total_semua_biasa" name="total_penjualan"
                                value="{{ $total_semua }}">
                        </div>
                        @if (empty($jurnal))
                        <div class="col-lg-5 mt-2">
                            <label for="">Pilih Akun Setor</label>
                            <select name="id_akun[]" id="" class="select2_add" required>
                                <option value="">-Pilih Akun-</option>
                                @foreach ($akun as $a)
                                <option value="{{ $a->id_akun }}">{{ $a->nm_akun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <label for="">Debit</label>
                            <input type="text" class="form-control debit debit1" count="1" style="text-align: right"
                                value="Rp {{ number_format($total_semua, 2, ',', '.') }}">
                            <input type="hidden" name="debit[]" class="form-control debit_biasa debit_biasa1"
                                value="{{ $total_semua }}">
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

                        <div id="load_pembayaran"></div>

                        <div class="row">
                            <div class="col-lg-12">
                                <hr style="border: 1px solid blue">
                            </div>
                            <div class="col-lg-5">
                                <h6>Total Setor</h6>
                            </div>
                            <div class="col-lg-3">
                                <h6 class="total_debit float-end">Rp {{ number_format($total_semua, 0) }}</h6>
                            </div>
                            <div class="col-lg-4">
                                <h6 class="total_kredit float-end">Rp {{ number_format($total_semua, 0) }} </h6>
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
                        @else
                        @php
                        $debit = 0;
                        $kredit = 0;
                        @endphp
                        @foreach ($jurnal as $j)
                        @php
                        $debit += $j->debit;
                        $kredit += $j->kredit;
                        @endphp
                        <div class="col-lg-5 mt-2">
                            <label for="">Pilih Akun Setor</label>
                            <select name="" id="" class="select2_add" required disabled>
                                <option value="">-Pilih Akun-</option>
                                @foreach ($akun as $a)
                                <option value="{{ $a->id_akun }}" {{ $a->id_akun == $j->id_akun ? 'SELECTED' : '' }}>
                                    {{ $a->nm_akun }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="id_akun[]" value="{{ $j->id_akun }}">
                        </div>
                        <div class="col-lg-3 mt-2">
                            <label for="">Debit</label>
                            <input type="text" class="form-control debit debit1" count="1" style="text-align: right"
                                value="Rp {{ number_format($j->debit, 2, ',', '.') }}" readonly>
                            <input type="hidden" name="debit[]" class="form-control debit_biasa debit_biasa1"
                                value="{{ $j->debit }}">
                        </div>
                        <div class="col-lg-3 mt-2">
                            <label for="">Kredit</label>
                            <input type="text" class="form-control kredit kredit1" count="1" style="text-align: right"
                                value="Rp {{ number_format($j->kredit, 2, ',', '.') }}" readonly>
                            <input type="hidden" name="kredit[]" class="form-control kredit_biasa kredit_biasa1"
                                value="{{ $j->kredit }}">
                        </div>
                        {{-- <div class="col-lg-1 mt-2">
                            <label for="">aksi</label> <br>
                            <button type="button" class="btn rounded-pill tbh_pembayaran" count="1">
                                <i class="fas fa-plus text-success"></i>
                            </button>
                        </div> --}}
                        @endforeach
                        {{-- <div id="load_pembayaran"></div> --}}

                        <div class="row">
                            <div class="col-lg-12">
                                <hr style="border: 1px solid blue">
                            </div>
                            <div class="col-lg-5">
                                <h6>Total Setor</h6>
                            </div>
                            <div class="col-lg-3">
                                <h6 class="total_debit float-end">Rp {{ number_format($debit, 0) }}</h6>
                            </div>
                            <div class="col-lg-4">
                                <h6 class="total_kredit float-end">Rp {{ number_format($total_semua, 0) }} </h6>
                            </div>
                            <div class="col-lg-5">
                                <select name="id_akun_sisa" id="" class="select2_add" required>
                                    <option value="">-Pilih Akun-</option>
                                    @foreach ($akun as $a)
                                    <option value="{{ $a->id_akun }}">
                                        {{ $a->nm_akun }}
                                    </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="selisih" value="{{ $debit - $total_semua }}">
                            </div>
                            <div class="col-lg-3">
                            </div>
                            <div class="col-lg-4">
                                <h6
                                    class="selisih float-end cselisih {{ $debit - $total_semua != 0 ? 'text-danger' : 'text-success' }}">
                                    Rp
                                    {{ number_format($debit - $total_semua) }}</h6>
                            </div>

                        </div>

                        @endif


                    </div>

            </section>
    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary button-save">Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        <a href="{{ route('terima_invoice_mtd') }}" class="float-end btn btn-outline-primary me-2">Batal</a>
        </form>
    </x-slot>



    @section('scripts')
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

                    // var total_all = 0;
                    // $(".bayar_biasa").each(function() {
                    //     total_all += parseFloat($(this).val());
                    // });

                    var  total_all = $('.total_semua_biasa').val()
                   

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
                    var total_all_kredit = parseFloat(total_all) + parseFloat(total_kredit);
                    var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalKreditall);

                    var selisih = parseFloat(total_all) + parseFloat(total_kredit) - parseFloat(total_debit);
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    if (parseFloat(total_kredit) + parseFloat(total_all) === parseFloat(total_debit)) {
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

                    // var total_all = 0;
                    // $(".bayar_biasa").each(function() {
                    //     total_all += parseFloat($(this).val());
                    // });
                    var  total_all = $('.total_semua_biasa').val()

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = parseFloat(total_all) + parseFloat(total_kredit);
                    var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalKreditall);

                    var selisih = parseFloat(total_all) + parseFloat(total_kredit) - parseFloat(total_debit);
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (parseFloat(total_kredit) + parseFloat(total_all) === parseFloat(total_debit)) {
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


                    // var total_all = 0;
                    // $(".bayar_biasa").each(function() {
                    //     total_all += parseFloat($(this).val());
                    // });
                    var  total_all = $('.total_semua_biasa').val()

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = parseFloat(total_all) + parseFloat(total_kredit);
                    var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalKreditall);

                    var selisih = parseFloat(total_all) + parseFloat(total_kredit) - parseFloat(total_debit);
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (parseFloat(total_kredit + total_all) === parseFloat(total_debit)) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var totalDebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totalDebitall);

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

                });
            });
    </script>
    @endsection
</x-theme.app>