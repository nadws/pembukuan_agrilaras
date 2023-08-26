{{-- @if ($detail->jenis != 'selesai')

@else
<span>selesai</span>
@endif --}}

@if (!empty(request()->get('no_nota')))
    @php
        $cekStok = \App\Models\Stok::where('no_nota', request()->get('no_nota'))->get();
    @endphp
    <section class="row">
        <div class="col-lg-2">
            <label for="">Tanggal</label>
            <input type="hidden" name="jenis" value="{{ $detail->jenis }}">
            <input type="date" class="form-control" name="tgl" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-lg-2">
            <label for="">No Nota</label>
            <input type="text" style="display: none" class="form-control" readonly name="urutan"
                value="{{ $detail->urutan }}">
            <input type="text" class="form-control" readonly name="no_nota" value="SM-{{ $detail->urutan }}">
        </div>

        <div class="col-lg-3">
            <label for="">Pilih Gudang</label>
            <select required name="gudang_id" class="form-control select2" id="">
                <option value="">- Pilih Gudang -</option>
                @foreach ($gudang as $d)
                    <option {{ $d->id_gudang == $detail->gudang_id ? 'selected' : '' }} value="{{ $d->id_gudang }}">
                        {{ $d->nm_gudang }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4">
            <label for="">Keterangan</label>
            <input type="text" class="form-control" value="{{ $detail->ket }}" name="ket"
                placeholder="Keterangan">
        </div>
        <div class="col-lg-12">
            <hr style="border: 1px solid black">
        </div>
        <div class="col-lg-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="20%">Produk (Satuan)</th>
                        <th width="4%">Stok Sebelumnya</th>
                        <th width="6%">Stok Masuk</th>
                        <th width="10%">Harga Satuan</th>
                        @if ($detail->jenis != 'selesai')
                            <th width="5%">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cekStok as $index => $cs)
                        <tr class="baris{{ $index + 1 }}">
                            <td style="vertical-align: top;">
                                <input type="hidden" name="id_produk[]" value="{{ $cs->id_produk }}">
                                <select disabled required class="form-control select2 produk-change"
                                    count="{{ $index + 1 }}" id="">
                                    <option value="">- Pilih Produk -</option>
                                    @foreach ($produk as $d)
                                        <option {{ $d->id_produk == $cs->id_produk ? 'selected' : '' }}
                                            value="{{ $d->id_produk }}">{{ $d->nm_produk }}
                                            ({{ strtoupper($d->satuan->nm_satuan) }})
                                        </option>
                                    @endforeach
                                    <option value="tambahProduk">+ Produk</option>
                                </select>
                            </td>
                            <td style="vertical-align: top;" align="center">
                                <input class="form-control stok-sebelumnya{{ $index + 1 }}"
                                    style="text-align:right;" type="text" readonly name="jml_sebelumnya[]"
                                    value="{{ $cs->jml_sebelumnya ?? 0 }}">
                            </td>
                            <td style="vertical-align: top;" align="center">
                                <input required name="debit[]" style="text-align:right;" value="{{ $cs->debit ?? 0 }}"
                                    type="text" class="form-control debit-keyup">
                            </td>
                            <td style="vertical-align: top;" align="center">
                                <input type="text" class="form-control dikanan rp-nohide text-end"
                                    value="Rp {{ number_format($cs->rp_satuan, 0) }}" count="{{ $index + 1 }}">
                                <input type="hidden" class="form-control dikanan rp-hide rp-hide1"
                                    value="{{ $cs->rp_satuan }}" name="rp_satuan[]">
                            </td>
                            @if ($detail->jenis != 'selesai')
                                <td style="vertical-align: top;">
                                    <button type="button" class="btn rounded-pill remove_baris"
                                        count="{{ $index + 1 }}"><i class="fas fa-trash text-danger"></i>
                                    </button>
                                </td>
                            @endif

                        </tr>
                    @endforeach
                </tbody>
                <tbody id="tbh_baris">

                </tbody>


                @if ($detail->jenis != 'selesai')
                    <tfoot>
                        <tr>
                            <th colspan="7">
                                <button type="button" class="btn btn-block btn-lg tbh_baris"
                                    style="background-color: #F4F7F9; color: #8FA8BD; font-size: 14px; padding: 13px;">
                                    <i class="fas fa-plus"></i> Tambah Baris Baru
                                </button>
                            </th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>
@else

    <section class="row">
        <div class="col-lg-2">
            <label for="">Tanggal</label>
            <input type="date" class="form-control" name="tgl" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-lg-2">
            <label for="">No Nota</label>
            <input type="text" style="display: none" class="form-control" readonly name="urutan"
                value="{{ $no_nota }}">
            <input type="text" class="form-control" readonly name="no_nota" value="SM-{{ $no_nota }}">
        </div>

        <div class="col-lg-3">
            <label for="">Pilih Gudang</label>
            <select required name="gudang_id" class="form-control select2" id="">
                <option value="">- Pilih Gudang -</option>
                @foreach ($gudang as $d)
                    <option  value="{{ $d->id_gudang }}">
                        {{ $d->nm_gudang }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4">
            <label for="">Keterangan</label>
            <input type="text" class="form-control" value="" name="ket" placeholder="Keterangan">
        </div>
        <div class="col-lg-12">
            <hr style="border: 1px solid black">
        </div>
        <div class="col-lg-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="20%">Produk (Satuan)</th>
                        <th width="4%">Stok Sebelumnya</th>
                        <th width="6%">Stok Masuk</th>
                        <th width="10%">Harga Beli</th>
                        <th width="5%">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                    <tr class="baris1">
                        <td style="vertical-align: top;">
                            <select required name="id_produk[]" class="form-control select2 produk-change"
                                count="1" id="">
                                <option value="">- Pilih Produk -</option>
                                @foreach ($produk as $d)
                                    <option value="{{ $d->id_produk }}">{{ $d->nm_produk }}
                                        ({{ strtoupper($d->satuan->nm_satuan) }})
                                    </option>
                                @endforeach
                                <option value="tambahProduk">+ Produk</option>
                            </select>
                        </td>
                        <td style="vertical-align: top;" align="center">
                            <input class="form-control stok-sebelumnya1" style="text-align:right;" type="text"
                                readonly name="jml_sebelumnya[]" value="{{ $d->ttlDebit - $d->ttlKredit }}">
                        </td>
                        <td style="vertical-align: top;" align="center">
                            <input required name="debit[]" style="text-align:right;" value="{{ $d->debit ?? 0 }}"
                                type="text" class="form-control debit-keyup">
                        </td>
                        <td style="vertical-align: top;" align="center">
                            <input type="text" class="form-control dikanan rp-nohide text-end"
                                value="Rp {{ number_format($d->rp_satuan, 0) }}" count="1">
                            <input type="hidden" class="form-control dikanan rp-hide rp-hide1"
                                value="{{ $d->rp_satuan }}" name="rp_satuan[]">
                        </td>
                        <td style="vertical-align: top;">
                            <button type="button" class="btn rounded-pill remove_baris" count="1"><i
                                    class="fas fa-trash text-danger"></i>
                            </button>
                        </td>

                    </tr>
                    <tr class="baris2">
                        <td style="vertical-align: top;">
                            <select required name="id_produk[]" class="form-control select2 produk-change"
                                count="2" id="">
                                <option value="">- Pilih Produk -</option>
                                @foreach ($produk as $d)
                                    <option value="{{ $d->id_produk }}">{{ $d->nm_produk }}
                                        ({{ strtoupper($d->satuan->nm_satuan) }})
                                    </option>
                                @endforeach
                                <option value="tambahProduk">+ Produk</option>
                            </select>
                        </td>
                        <td style="vertical-align: top;" align="center">
                            <input class="form-control stok-sebelumnya2" style="text-align:right;" type="text"
                                readonly name="jml_sebelumnya[]" value="{{ $d->ttlDebit - $d->ttlKredit }}">
                        </td>
                        <td style="vertical-align: top;" align="center">
                            <input required name="debit[]" style="text-align:right;" value="{{ $d->debit ?? 0 }}"
                                type="text" class="form-control debit-keyup">
                        </td>
                        <td style="vertical-align: top;" align="center">
                            <input type="text" class="form-control dikanan rp-nohide text-end"
                                value="Rp {{ number_format($d->rp_satuan, 0) }}" count="2">
                            <input type="hidden" class="form-control dikanan rp-hide rp-hide2"
                                value="{{ $d->rp_satuan }}" name="rp_satuan[]">
                        </td>
                        <td style="vertical-align: top;">
                            <button type="button" class="btn rounded-pill remove_baris" count="2"><i
                                    class="fas fa-trash text-danger"></i>
                            </button>
                        </td>

                    </tr>
                </tbody>
                <tbody id="tbh_baris">
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="7">
                            <button type="button" class="btn btn-block btn-lg tbh_baris"
                                style="background-color: #F4F7F9; color: #8FA8BD; font-size: 14px; padding: 13px;">
                                <i class="fas fa-plus"></i> Tambah Baris Baru
                            </button>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
@endif
