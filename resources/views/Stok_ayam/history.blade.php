<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="row">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}</h6> <br><br>
            </div>
            <div class="col-lg-6">
                <a href="#" data-bs-toggle="modal" data-bs-target="#penjualan_ayam"
                    class="btn btn-sm btn-primary float-end">Penjualan Ayam</a>
                <x-theme.btn_filter />
            </div>
        </div>

    </x-slot>

    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-bordered" id="table" width="100%">
                <thead>
                    <th class="dhead">No</th>
                    <th class="dhead">Tanggal</th>
                    <th class="dhead">No Nota</th>
                    <th class="dhead">Customer</th>
                    <th class="dhead text-end">Qty</th>
                    <th class="dhead text-end">Harga </th>
                    <th class="dhead text-end">Total Harga</th>
                    <th class="dhead text-end">Ket</th>
                    <th class="dhead">Aksi</th>
                </thead>
                <tbody>
                    @foreach ($invoice_ayam as $no => $i)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ tanggal($i->tgl) }}</td>
                            <td>{{ $i->no_nota }}</td>
                            <td>{{ $i->nm_customer }}{{ $i->urutan_customer }}</td>
                            <td class="text-end">{{ $i->qty }}</td>
                            <td class="text-end">Rp. {{ number_format($i->h_satuan, 0) }}</td>
                            <td class="text-end">Rp. {{ number_format($i->qty * $i->h_satuan, 0) }}</td>
                            <td>{{ $i->total_bayar != '0' ? 'Unpaid' : 'Paid' }}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <a class="btn btn-sm btn-danger delete_nota" no_nota="{{ $i->no_nota }}"
                                    href="#" data-bs-toggle="modal" data-bs-target="#delete"><i
                                        class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <form action="{{ route('hapus_ayam') }}" method="get">
                <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="row">
                                    <h5 class="text-danger ms-4 mt-4"><i class="fas fa-trash"></i> Hapus Data</h5>
                                    <p class=" ms-4 mt-4">Apa anda yakin ingin menghapus ?</p>
                                    <input type="hidden" class="no_nota" name="no_nota">
                                    <input type="hidden" name="tgl1" value="{{ $tgl1 }}">
                                    <input type="hidden" name="tgl2" value="{{ $tgl2 }}">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-danger"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form action="{{ route('save_penjualan_ayam') }}" method="post">
                @csrf
                <x-theme.modal title="Penjualan ayam" size="modal-lg-max_custome" idModal="penjualan_ayam">
                    <div class="row">
                        <div class="col-lg-4">
                            <label for="">Tanggal</label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d') }}" name="tgl">
                        </div>
                        <div class="col-lg-4">
                            <label for="">Customer</label>
                            <select name="customer" class="select_ayam" required>
                                <option value="">Pilih Customer</option>
                                @foreach ($customer as $s)
                                    <option value="{{ $s->id_customer }}">{{ $s->nm_customer }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-12">
                            <hr>
                        </div>
                        <div class="col-lg-4">
                            <label for="">Ekor {{ $stok_ayam_bjm->saldo_bjm }}</label>
                            <input type="number" min="0" max="{{ $stok_ayam_bjm->saldo_bjm }}"
                                class="form-control ekor" name="qty" value="0">
                        </div>
                        <div class="col-lg-4">
                            <label for="">Harga Satuan</label>
                            <input type="text" class="form-control h_satuan" name="h_satuan" value="0"
                                style="text-align: right">
                        </div>
                        <div class="col-lg-4">
                            <label for="">Total Rp</label>
                            <input type="text" class="form-control ttl_rp" name="ttl_rp" readonly
                                style="text-align: right">
                        </div>
                        <div class="col-lg-12">

                        </div>
                        <div class="col-lg-4">

                        </div>
                        <div class="col-lg-8">

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
                                    <select name="id_akun[]" id="" class="select_ayam">
                                        <option value="">-Pilih Akun-</option>
                                        @foreach ($akun as $a)
                                            <option value="{{ $a->id_akun }}">{{ $a->nm_akun }}</option>
                                        @endforeach
                                        <option value="66">Piutang Ayam</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 mt-2">
                                    <label for="">Debit</label>
                                    <input type="text" class="form-control debit debit1" count="1"
                                        style="text-align: right">
                                    <input type="hidden" name="debit[]"
                                        class="form-control debit_biasa debit_biasa1" value="0">
                                </div>
                                <div class="col-lg-3 mt-2">
                                    <label for="">Kredit</label>
                                    <input type="text" class="form-control kredit kredit1" count="1"
                                        style="text-align: right">
                                    <input type="hidden" name="kredit[]"
                                        class="form-control kredit_biasa kredit_biasa1" value="0">
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
                    </div>
                </x-theme.modal>
            </form>

        </section>
        @section('js')
            <script>
                $(document).ready(function() {
                    $(document).on('click', '.delete_nota', function() {
                        var no_nota = $(this).attr('no_nota');
                        $('.no_nota').val(no_nota);
                    });
                    $(document).on("keyup", ".ekor", function() {
                        var ekor = $('.ekor').val();
                        var h_satuan = $('.h_satuan').val();
                        var ttl_rp = parseFloat(ekor) * parseFloat(h_satuan);
                        var total_kredit = 0;
                        $(".kredit_biasa").each(function() {
                            total_kredit += parseFloat($(this).val());
                        });
                        var total_all_kredit = ttl_rp + total_kredit;

                        var totalRupiahall = ttl_rp.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });
                        var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });

                        $('.ttl_rp').val(ttl_rp);
                        $(".total_kredit").text(totalkreditall)
                        $(".total").text(totalRupiahall);

                    });
                    $(document).on("keyup", ".h_satuan", function() {
                        var ekor = $('.ekor').val();
                        var h_satuan = $('.h_satuan').val();
                        var ttl_rp = parseFloat(ekor) * parseFloat(h_satuan);
                        var total_kredit = 0;
                        $(".kredit_biasa").each(function() {
                            total_kredit += parseFloat($(this).val());
                        });
                        var total_all_kredit = ttl_rp + total_kredit;

                        var totalRupiahall = ttl_rp.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });
                        var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });


                        $('.ttl_rp').val(ttl_rp);
                        $(".total_kredit").text(totalkreditall)
                        $(".total").text(totalRupiahall);

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

                        var total_all = $('.ttl_rp').val();

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
                        var total_all_kredit = parseFloat(total_all) + total_kredit;
                        var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });
                        $(".total_kredit").text(totalKreditall);

                        var selisih = Math.round(parseFloat(total_all) + total_kredit) - total_debit;
                        var selisih_total = selisih.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });
                        // console.log(Math.round(total_all + total_kredit));
                        // console.log(total_debit);

                        if (Math.round(total_kredit + parseFloat(total_all)) === total_debit) {
                            $(".cselisih").css("color", "green");
                            $(".button-save-modal").removeAttr("hidden");
                        } else {
                            $(".cselisih").css("color", "red");
                            $(".button-save-modal").attr("hidden", true);
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

                        var total_all = $('.ttl_rp').val();


                        var total_debit = 0;
                        $(".debit_biasa").each(function() {
                            total_debit += parseFloat($(this).val());
                        });

                        var total_kredit = 0;
                        $(".kredit_biasa").each(function() {
                            total_kredit += parseFloat($(this).val());
                        });
                        var total_all_kredit = parseFloat(total_all) + total_kredit;
                        var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });
                        $(".total_kredit").text(totalKreditall);

                        var selisih = Math.round(parseFloat(total_all) + total_kredit) - total_debit;
                        var selisih_total = selisih.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });


                        if (Math.round(total_kredit + parseFloat(total_all)) === total_debit) {
                            $(".cselisih").css("color", "green");
                            $(".button-save-modal").removeAttr("hidden");
                        } else {
                            $(".cselisih").css("color", "red");
                            $(".button-save-modal").attr("hidden", true);
                        }
                        $(".selisih").text(selisih_total);


                    });
                });
            </script>
        @endsection
    </x-slot>
</x-theme.app>
