<x-theme.app title="{{ $title }}" table="Y" sizeCard="11">

    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1 text-primary">Setor Penjualan Telur Martadah</h5>
                <small class="text-muted">Periksa detail nota lalu tentukan akun penerimaan setoran.</small>
            </div>
            <span class="badge bg-light text-primary px-3 py-2">{{ $nota }}</span>
        </div>

    </x-slot>


    <x-slot name="cardBody">
        <style>
            .martadah-receipt .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #000000;
                line-height: 40px;
            }
            .martadah-receipt .select2-container{width:100%!important}
            .martadah-receipt .select2-selection--single{height:42px!important;border-color:#dce4f2!important;border-radius:8px!important}
            .martadah-receipt .select2-selection__arrow{height:40px!important}
            .dhead {
                background-color: #435EBE !important;
                color: white;
            }
            .martadah-receipt .invoice-card{overflow:hidden;border:1px solid #dce4f2;border-radius:14px;box-shadow:none}
            .martadah-receipt .invoice-header{padding:18px;background:#f7f9fd;border-bottom:1px solid #dce4f2}
            .martadah-receipt .invoice-meta{font-size:13px}
            .martadah-receipt .invoice-meta td{padding:4px 6px!important}
            .martadah-receipt .invoice-title{margin:18px 0 8px;color:#18366f;font-weight:700}
            .martadah-receipt .invoice-table-wrap{overflow-x:auto;border:1px solid #dce4f2;border-radius:10px}
            .martadah-receipt .invoice-table{min-width:1100px;margin-bottom:0!important}
            .martadah-receipt .invoice-table th{vertical-align:middle;font-size:12px;white-space:nowrap}
            .martadah-receipt .invoice-table td{vertical-align:middle;font-size:13px}
            .martadah-receipt .invoice-totals{width:100%;max-width:440px;margin-top:16px;margin-left:auto;background:#f7f9fd;border-radius:10px}
            .martadah-receipt .invoice-totals td{padding:7px 12px}
            .martadah-receipt .payment-panel{margin-top:4px;padding:20px;border:1px solid #dce4f2;border-radius:14px;background:#f8faff}
            .martadah-receipt .payment-panel label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}
            .martadah-receipt .payment-panel .form-control{min-height:42px;border-color:#dce4f2;border-radius:8px}
            .martadah-receipt .payment-panel hr{border-color:#cad5ea!important;opacity:1}
            @media(max-width:767px){.martadah-receipt .invoice-header img{width:75px!important}.martadah-receipt .invoice-meta{float:none!important;margin-top:12px}.martadah-receipt .payment-panel{padding:14px}}
        </style>
        <form action="{{ route('save_terima_invoice') }}" method="post" class="save_jurnal martadah-receipt">
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
                    <div class="card invoice-card">
                        <div class="card-header invoice-header">
                            <div class="row">
                                <div class="col-lg-5">
                                    <img src="https://ternak.ptagafood.com/assets/login/img/agri_laras2.png"
                                        alt="Logo" width="95px">
                                </div>
                                <div class="col-lg-7">
                                    <table class="float-end invoice-meta">
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
                        <h6 class="text-center invoice-title">
                            Nota Penjualan Telur Martadah
                        </h6>
                        <div class="card-body">
                            <div class="invoice-table-wrap">
                            <table class="table table-bordered invoice-table" style="white-space: nowrap;">
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
                                                    // $rak_kali = round($i->rak_kg * 0.12,1);
                                                    $rak_kotor = round(($i->pcs_kg / 15) * 0.12, 1);
                                                    $kg_rak_kotor = $i->kg_kg + $rak_kotor;
                                                    $total_rp = $i->total_rp;

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
                            </div>
                            <table class="invoice-totals">
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
                <div class="col-lg-4 d-none">

                </div>
                <div class="col-lg-12 payment-panel">

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
                        @if ($jurnal->isEmpty())
                            <div class="col-lg-5 mt-2">
                                <label for="">Pilih Akun Setor</label>
                                <select name="id_akun[]" id="" class="select2_add" required>
                                    <option value="">-Pilih Akun-</option>
                                    @foreach ($akun as $a)
                                        <option value="{{ $a->id_akun_perkiraan }}">{{ $a->kode_perkiraan }} - {{ $a->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 mt-2">
                                <label for="">Debit</label>
                                <input type="text" class="form-control debit debit1" count="1"
                                    style="text-align: right"
                                    value="Rp {{ number_format($total_semua, 2, ',', '.') }}">
                                <input type="hidden" name="debit[]" class="form-control debit_biasa debit_biasa1"
                                    value="{{ $total_semua }}">
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
                                            <option value="{{ $a->id_akun_perkiraan }}"
                                                {{ $a->id_akun_perkiraan == $j->id_akun_perkiraan ? 'SELECTED' : '' }}>
                                                {{ $a->kode_perkiraan }} - {{ $a->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="id_akun[]" value="{{ $j->id_akun_perkiraan }}">
                                </div>
                                <div class="col-lg-3 mt-2">
                                    <label for="">Debit</label>
                                    <input type="text" class="form-control debit debit1" count="1"
                                        style="text-align: right"
                                        value="Rp {{ number_format($j->debit, 2, ',', '.') }}" readonly>
                                    <input type="hidden" name="debit[]"
                                        class="form-control debit_biasa debit_biasa1" value="{{ $j->debit }}">
                                </div>
                                <div class="col-lg-3 mt-2">
                                    <label for="">Kredit</label>
                                    <input type="text" class="form-control kredit kredit1" count="1"
                                        style="text-align: right"
                                        value="Rp {{ number_format($j->kredit, 2, ',', '.') }}" readonly>
                                    <input type="hidden" name="kredit[]"
                                        class="form-control kredit_biasa kredit_biasa1" value="{{ $j->kredit }}">
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
                                    <select name="id_akun_sisa" id="" class="select2_add">
                                        <option value="">-Pilih Akun-</option>
                                        @foreach ($akun as $a)
                                            <option value="{{ $a->id_akun_perkiraan }}">
                                                {{ $a->kode_perkiraan }} - {{ $a->nama }}
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
        <a href="{{ route('penjualan_martadah_cek', ['lokasi' => 'mtd']) }}"
            class="float-end btn btn-outline-primary me-2">Batal</a>
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

                    var total_all = $('.total_semua_biasa').val()


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
                    var total_all = $('.total_semua_biasa').val()

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
                        url: "{{ route('tbh_pembayaran_martadah') }}?count=" + count,
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
                    var total_all = $('.total_semua_biasa').val()

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
