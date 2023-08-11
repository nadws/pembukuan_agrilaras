<div class="col-lg-12">
    <table class="table table-bordered" width="100%">
        <tr>
            <th style="text-align: center" colspan="2">
                <h6>Stok Ayam <br>{{ tanggal(date('Y-m-d')) }}</h6>
            </th>
        </tr>
        <tr>
            <th style="text-align: center; height: 60px;">
                <h6>Stok Martadah <br>{{ $stok_ayam->saldo_kandang }}</h6>
            </th>
            <th style="text-align: center; height: 60px;">
                <h6>Stok BJM <br>{{ empty($stok_ayam_bjm->saldo_bjm) ? '0' : $stok_ayam_bjm->saldo_bjm }}</h6>
            </th>
        </tr>
        <tr>
            <th style="text-align: center" colspan="2">
                <a href="#" data-bs-toggle="modal" data-bs-target="#penjualan_ayam"
                    class="btn btn-sm btn-primary">Penjualan Ayam</a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#history_ayam" class="btn btn-sm btn-primary">History
                    Stok
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#history_penjualan_ayam"
                    class="btn btn-sm btn-primary">History
                    Penjualan
                </a>
            </th>
        </tr>

    </table>
</div>

<form action="{{route('save_penjualan_ayam')}}" method="post">
    @csrf
    <x-theme.modal title="Penjualan ayam" size="modal-lg-max_custome" idModal="penjualan_ayam">
        <div class="row">
            <div class="col-lg-4">
                <label for="">Tanggal</label>
                <input type="date" class="form-control" value="{{ date('Y-m-d') }}" name="tgl">
            </div>
            <div class="col-lg-4">
                <label for="">Customer</label>
                <select name="customer" class="select2-pakan" required>
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
                <input type="number" min="0" max="{{ $stok_ayam_bjm->saldo_bjm }}" class="form-control ekor" name="qty"
                    value="0">
            </div>
            <div class="col-lg-4">
                <label for="">Harga Satuan</label>
                <input type="text" class="form-control h_satuan" name="h_satuan" value="0" style="text-align: right">
            </div>
            <div class="col-lg-4">
                <label for="">Total Rp</label>
                <input type="text" class="form-control ttl_rp" name="ttl_rp" readonly style="text-align: right">
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
                        <select name="id_akun[]" id="" class="select2-pakan">
                            <option value="">-Pilih Akun-</option>
                            @foreach ($akun as $a)
                            <option value="{{ $a->id_akun }}">{{ $a->nm_akun }}</option>
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
                        <input type="hidden" name="kredit[]" class="form-control kredit_biasa kredit_biasa1" value="0">
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

<x-theme.modal title="History ayam martadah" idModal="history_ayam" btn-save="T">
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-bordered" id="table" width="100%">
                <thead>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th class="text-end">Stok Masuk</th>
                    <th class="text-end">Stok Keluar</th>
                    <th class="text-end">Saldo</th>
                    <th>Keterangan</th>
                </thead>
                <tbody>
                    @php
                    $saldo = 0;
                    @endphp
                    @foreach ($history_ayam as $no => $h)
                    @php
                    $saldo += $h->debit - $h->kredit;
                    @endphp
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td style="white-space: nowrap">{{ tanggal($h->tgl) }}</td>
                        <td align="right">{{ $h->debit }}</td>
                        <td align="right">{{ $h->kredit }}</td>
                        <td align="right">{{ $saldo }}</td>
                        <td>{{ $h->kredit == 0 ? 'Ayam Masuk' : ($h->no_nota != '' ? 'Penjualan' : 'Transfer') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-theme.modal>
<x-theme.modal title="History Penjualan Ayam" size="modal-lg-max" idModal="history_penjualan_ayam" btn-save="T">
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-bordered" id="table" width="100%">
                <thead>
                    <th class="dhead">No</th>
                    <th class="dhead">Tanggal</th>
                    <th class="dhead">No Nota</th>
                    <th class="dhead">Customer</th>
                    <th class="dhead text-end">Qty</th>
                    <th class="dhead text-end">Harga </th>
                    <th class="dhead text-end">Total Harga</th>
                    <th class="dhead">Aksi</th>
                </thead>
                <tbody>
                    @foreach ($invoice_ayam as $no => $i)
                    <tr>
                        <td>{{$no+1}}</td>
                        <td>{{tanggal($i->tgl)}}</td>
                        <td>{{$i->no_nota}}</td>
                        <td>{{$i->nm_customer}}{{$i->urutan_customer}}</td>
                        <td class="text-end">{{$i->qty}}</td>
                        <td class="text-end">Rp. {{number_format($i->h_satuan,0)}}</td>
                        <td class="text-end">Rp. {{$i->qty * $i->h_satuan}}</td>
                        <td>
                            <a href="" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-theme.modal>