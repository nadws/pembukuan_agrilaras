<div class="row">
    <div class="col-lg-8">
        <form id="history_produk">
            <div class="row">
                <div class="col-lg-5">
                    <label for="">Dari</label>
                    <input type="date" id="tgl1" class="form-control" value="{{ $tgl1 }}">
                </div>
                <div class="col-lg-5">
                    <label for="">Sampai</label>
                    <input type="date" id="tgl2" class="form-control" value="{{ $tgl2 }}">
                    <input type="hidden" id="id_kandang" value="{{ $id_kandang }}">
                    <input type="hidden" id="id_produk" value="{{ $id_produk }}">
                </div>
                <div class="col-lg-2">
                    <label for="">Aksi</label> <br>
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>

                </div>
            </div>
        </form>
    </div>
</div>
<br>
<br>
<h6>Kandang : {{ $kandang->nm_kandang }}</h6>
<table class="table table-bordered" id="tableahisory">
    <thead>
        <tr>
            <th class="dhead">No</th>
            <th class="dhead">Tanggal</th>
            <th class="dhead">Nama Produk</th>
            <th class="dhead text-end">Qty</th>
            <th class="dhead">Satuan</th>
            <th class="dhead">Admin</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($history as $no => $h)
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ tanggal($h->tgl) }}</td>
                <td>{{ $h->nm_produk }}</td>
                <td class="text-end">{{ number_format($h->pcs_kredit, 2) }}</td>
                <td>{{ $h->nm_satuan }}</td>
                <td>{{ $h->admin }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
