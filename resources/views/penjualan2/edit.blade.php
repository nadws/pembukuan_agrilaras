<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">

    <x-slot name="cardHeader">
        <div class="col-lg-6">
            <h6 class="float-start mt-1">{{ $title }} Penjualan</h6>
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

            .dhead {
                background-color: #435EBE !important;
                color: white;
            }
        </style>
        <form action="{{ route('penjualan2.update') }}" method="post" class="save_jurnal">
            @csrf
            <section class="row">
                <div class="col-lg-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="5%" class="dhead">Tanggal</th>
                                <th width="9%" class="dhead">No Nota</th>
                                <th width="10%" class="dhead">Pelanggan</th>
                                <th width="15%" class="dhead">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input value="{{ $getPenjualan->tgl }}" type="date" value="{{ date('Y-m-d') }}"
                                        class="form-control" name="tgl">
                                </td>
                                <td>
                                    <input readonly value="PUM-{{ $getPenjualan->urutan }}" type="text" required
                                        class="form-control">
                                    <input value="{{ $getPenjualan->urutan }}" type="hidden" required
                                        class="form-control" name="no_nota">
                                </td>
                              
                                <td>
                                    <select required name="id_customer" class="form-control select2" id="">
                                        <option value="">- Pilih Customer -</option>
                                        @foreach ($customer as $d)
                                            <option
                                                {{ $getPenjualan->id_customer == $d->id_customer ? 'selected' : '' }}
                                                value="{{ $d->id_customer }}">
                                                {{ $d->nm_customer }}</option>
                                        @endforeach
                                    </select>
                                </td>
                               
                                <td>
                                    <input type="text" value="{{ $getPenjualan->ket }}" name="ket" class="form-control">
                                </td>
                            </tr>
                        </tbody>


                    </table>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="20%" class="dhead">Produk</th>
                                <th width="5%" class="dhead">Qty</th>
                                <th width="10%" class="dhead text-end">Harga Satuan</th>
                                <th width="10%" class="dhead text-end">Total Rp</th>
                                <th width="5%" class="text-center dhead">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($getProduk as $no => $p)
                                <tr>
                                    <td>
                                        <select name="id_produk[]" required class="form-control select2 produk-change"
                                            id="">
                                            <option value="">- Pilih Produk -</option>
                                            @foreach ($produk as $d)
                                                <option {{ $d->id_produk == $p->id_produk ? 'selected' : '' }}
                                                    value="{{ $d->id_produk }}">{{ $d->nm_produk }}
                                                    ({{ strtoupper($d->satuan->nm_satuan) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input count="{{$no+1}}" name="qty[]" value="{{ $p->qty }}" type="text"
                                            class="form-control qty qty{{$no+1}}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control dikanan setor-nohide text-end"
                                            value="Rp. {{ number_format($p->rp_satuan, 2) }}" count="{{$no+1}}">
                                        <input type="hidden" class="form-control dikanan setor-hide setor-hide{{$no+1}}"
                                            value="{{ $p->rp_satuan }}" name="rp_satuan[]">
                                    </td>
                                    <td>
                                        <input readonly type="text"
                                            class="form-control dikanan ttlrp-nohide{{$no+1}} text-end"
                                            value="Rp. {{ number_format($p->total_rp, 2) }}" count="{{$no+1}}">
                                        <input type="hidden" class="form-control dikanan ttlrp-hide ttlrp-hide{{$no+1}}"
                                            value="{{ $p->total_rp }}" name="total_rp[]">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tbody id="tbh_baris">
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="9">
                                    <button type="button" class="btn btn-block btn-lg tbh_baris"
                                        style="background-color: #F4F7F9; color: #8FA8BD; font-size: 14px; padding: 13px;">
                                        <i class="fas fa-plus"></i> Tambah Produk Baru
                                    </button>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="col-lg-7 float-end">
                        <table width="100%" class="table">
                            <tbody>
                                <tr>
                                    <th class="dhead">Akun Pembayaran</th>
                                    <th class="dhead text-end">Debit</th>
                                    <th class="dhead text-end">Kredit</th>
                                    <th class="dhead">Aksi</th>
                                </tr>
                                @foreach ($getPembayaran as $no => $p)
                                    <tr>
                                        <td>
                                            <select required name="akun_pembayaran[]" class="form-control select2"
                                                id="">
                                                <option value="">- Pilih Akun -</option>
                                                @foreach ($akun as $d)
                                                    <option {{ $d->id_akun == $p->id_akun ? 'selected' : '' }}
                                                        value="{{ $d->id_akun }}">{{ strtoupper($d->nm_akun) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control dikanan pembayaranDebit-nohide text-end"
                                                value="Rp. {{ number_format($p->debit, 2) }}" count="{{$no+1}}">
                                            <input type="hidden"
                                                class="form-control dikanan debit pembayaranDebit-hide{{$no+1}}"
                                                value="{{ $p->debit }}" name="debit[]">
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control dikanan pembayaranKredit-nohide text-end"
                                                value="Rp. {{ number_format($p->kredit, 2) }}" count="{{$no+1}}">
                                            <input type="hidden"
                                                class="form-control dikanan kredit pembayaranKredit-hide{{$no+1}}"
                                                value="{{ $p->kredit }}" name="kredit[]">
                                        </td>
                                        <td>
                                            <button type="button" class="btn rounded-pill tbh_pembayaran"><i
                                                    class="fas fa-plus text-success"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tbody id="tbh_pembayaran">
                            </tbody>
                        </table>
                        <hr style="border: 1px solid blue">

                        <table class="" width="100%">
                            <tr>
                                <td width="20%" class="fs-5">Total</td>
                                <td width="40%" class="total fs-5" style="text-align: right;">Rp.0</td>
                                <td width="40%" class="total_kredit fs-5" style="text-align: right;">Rp.0</td>
                            </tr>
                            <tr>
                                <td class="cselisih fs-6" colspan="2">Selisih</td>
                                <td style="text-align: right;" class="selisih cselisih fs-6">Rp.0</td>
                            </tr>
                        </table>
                    </div>

                </div>
            </section>
    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary button-save">Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        <a href="{{ route('penjualan2.index') }}" class="float-end btn btn-outline-primary me-2">Batal</a>
        </form>
    </x-slot>



    @section('scripts')
        <script>
            $('.select2').select2()
            var count = 3;
            plusRow(count, 'tbh_baris', 'tbh_add')
            plusRow2(count, 'tbh_pembayaran', 'tbh_pembayaran')

            $(document).on("keyup", ".setor-nohide", function() {
                var count = $(this).attr("count");
                var input = $(this).val();
                var qty = $('.qty' + count).val()

                input = input.replace(/[^\d\,]/g, "");
                input = input.replace(".", ",");
                input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
                if (input === "") {
                    $(this).val("");
                    $('.setor-hide' + count).val(0)
                } else {
                    $(this).val("Rp " + input);
                    input = input.replaceAll(".", "");
                    input2 = input.replace(",", ".");
                    var ttl_rp = parseFloat(input) * parseFloat(qty)
                    $(".ttlrp-nohide" + count).val(ttl_rp.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    }));
                    $(".ttlrp-hide" + count).val(ttl_rp);
                    $('.setor-hide' + count).val(input2)
                }


                var total_rpAtas = 0;
                $(".ttlrp-hide").each(function() {
                    total_rpAtas += parseFloat($(this).val());
                });

                var totalRupiah = total_rpAtas.toLocaleString("id-ID", {
                    style: "currency",
                    currency: "IDR",
                });
                $(".total_kredit").text(totalRupiah);

                var total_debit = 0;
                $(".debit").each(function() {
                    total_debit += parseFloat($(this).val());
                });
                var total_kredit = 0;
                $(".kredit").each(function() {
                    total_kredit += parseFloat($(this).val());
                });

                var selisih = total_rpAtas - total_debit - total_kredit;
                var selisih_total = selisih.toLocaleString("id-ID", {
                    style: "currency",
                    currency: "IDR",
                });
                $(".total").text(selisih_total);

                if (selisih === 0) {
                    $(".cselisih").css("color", "green");
                    $(".button-save").removeAttr("hidden");
                } else {
                    $(".cselisih").css("color", "red");
                    $(".button-save").attr("hidden", true);
                }
                $(".selisih").text(selisih_total);
            });

            function pbyr(classNohide, classHide, classHideLawan) {
                $(document).on('keyup', '.' + classNohide, function() {
                    var count = $(this).attr('count')
                    var input = $(this).val()
                    var total_debit = 0;
                    var total_pbyrDebit = 0;
                    var total_pbyrKredit = 0;

                    $(".ttlrp-hide").each(function() {
                        total_debit += parseFloat($(this).val());
                    });


                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                    } else {
                        $(this).val("Rp " + input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        var totalRupiah = input2.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                        });
                        $('.total').text($(this).val());

                        $("." + classHide + count).val(input);
                    }
                    $(".debit").each(function() {
                        total_pbyrDebit += parseFloat($(this).val());
                    });
                    $(".kredit").each(function() {
                        total_pbyrKredit += parseFloat($(this).val());
                    });
                    var selisih = total_debit - (total_pbyrDebit - total_pbyrKredit);

                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $('.total').text(selisih_total);

                    if (selisih === 0) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);
                })
            }

            pbyr('pembayaranDebit-nohide', 'pembayaranDebit-hide', 'pembayaranKredit-hide')
            pbyr('pembayaranKredit-nohide', 'pembayaranKredit-hide', 'pembayaranDebit-hide')
            // convertRpKoma('setor-nohide', 'setor-hide', '', 'total_kredit')
        </script>
    @endsection
</x-theme.app>
