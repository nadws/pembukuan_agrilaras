<div class="row">
    <div class="col-lg-4">
        <label for="">Tanggal</label>
        <input type="date" class="form-control" value="{{ $invoice->tgl }}" name="tgl">
        <input type="hidden" class="form-control" value="{{ $no_nota }}" name="no_nota">
        <input type="hidden" class="form-control" value="{{ $invoice->urutan_customer }}" name="urutan_customer">
        <input type="hidden" class="form-control" value="{{ $invoice->urutan }}" name="urutan">
    </div>
    <div class="col-lg-4">
        <label for="">Customer</label>
        <select name="customer" class="select2-edit" required>
            <option value="">Pilih Customer</option>
            @foreach ($customer as $s)
                <option value="{{ $s->id_customer }}" {{ $invoice->id_customer == $s->id_customer ? 'selected' : '' }}>
                    {{ $s->nm_customer }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-12">
        <hr>
    </div>
    <div class="col-lg-4">
        <label for="">Ekor {{ $stok_ayam_bjm->saldo_bjm + $invoice->qty }}</label>
        <input type="number" min="0" max="{{ $stok_ayam_bjm->saldo_bjm + $invoice->qty }}"
            class="form-control ekor2" name="qty" value="{{ $invoice->qty }}">
    </div>
    <div class="col-lg-4">
        <label for="">Harga Satuan</label>
        <input type="text" class="form-control h_satuan2" name="h_satuan" value="{{ $invoice->h_satuan }}"
            style="text-align: right">
    </div>
    <div class="col-lg-4">
        <label for="">Total Rp</label>
        <input type="text" class="form-control ttl_rp2" name="ttl_rp" readonly style="text-align: right"
            value="{{ $invoice->qty * $invoice->h_satuan }}">
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
                <h6 class="total2 float-end">Rp {{ number_format($invoice->qty * $invoice->h_satuan, 0) }} </h6>
                <input type="hidden" class="total_semua_biasa2" name="total_penjualan">
            </div>
            <div class="col-lg-5 mt-2">
                <label for="">Pilih Akun Pembayaran</label>

            </div>
            <div class="col-lg-3 mt-2">
                <label for="">Debit</label>

            </div>
            <div class="col-lg-3 mt-2">
                <label for="">Kredit</label>

            </div>
            <div class="col-lg-1 mt-2">
                <label for="">aksi</label>
            </div>
            @php
                $debit = 0;
            @endphp
            @foreach ($jurnal as $n => $j)
                <div class="col-lg-5 mt-2">
                    <select name="id_akun[]" id="" class="select2-edit">
                        <option value="">-Pilih Akun-</option>
                        @foreach ($akun as $a)
                            <option value="{{ $a->id_akun }}" {{ $a->id_akun == $j->id_akun ? 'SELECTED' : '' }}>
                                {{ $a->nm_akun }}</option>
                        @endforeach
                        <option value="66" {{ $j->id_akun == '66' ? 'SELECTED' : '' }}>Piutang Ayam</option>
                    </select>
                </div>
                <div class="col-lg-3 mt-2">

                    <input type="text" class="form-control debit debit{{ $n }}"
                        count="{{ $n }}" style="text-align: right"
                        value="Rp {{ number_format($j->debit, 0, '.', ',') }}">
                    <input type="hidden" name="debit[]"
                        class="form-control  debit_biasa debit_biasa{{ $n }}" value="{{ $j->debit }}">
                </div>
                <div class="col-lg-3 mt-2">

                    <input type="text" class="form-control kredit kredit1" count="{{ $n }}"
                        style="text-align: right">
                    <input type="hidden" name="kredit[]"
                        class="form-control kredit_biasa kredit_biasa{{ $n }}" value="0">
                </div>
                <div class="col-lg-1 mt-2">
                    <button type="button" class="btn rounded-pill tbh_pembayaran" count="{{ $n }}">
                        <i class="fas fa-plus text-success"></i>
                    </button>
                </div>
                @php
                    $debit += $j->debit;
                @endphp
            @endforeach
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
                <h6 class="total_debit float-end">Rp {{ number_format($debit, 0) }}</h6>
            </div>
            <div class="col-lg-3">
                <h6 class="total_kredit2 float-end">Rp {{ number_format($invoice->qty * $invoice->h_satuan, 0) }}</h6>
            </div>
            <div class="col-lg-1"></div>
            <div class="col-lg-5">
                <h6 class="cselisih">Selisih</h6>
            </div>
            <div class="col-lg-3">
            </div>
            <div class="col-lg-3">
                <h6 class="selisih float-end cselisih">Rp {{ number_format($invoice->qty * $invoice->h_satuan, 0) }}
                </h6>
            </div>
            <div class="col-lg-1"></div>
        </div>


    </div>
</div>
