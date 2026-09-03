<div class="row g-2 mb-3">
    <div class="col-md-3"><small class="text-muted d-block">Tanggal</small><strong>{{ tanggal($head_jurnal->tgl) }}</strong></div>
    <div class="col-md-3"><small class="text-muted d-block">Nomor Nota</small><strong>{{ $head_jurnal->kode }}-{{ $head_jurnal->urutan }}</strong></div>
    <div class="col-md-3"><small class="text-muted d-block">Pelanggan</small><strong>{{ $customer }}</strong></div>
    <div class="col-md-3"><small class="text-muted d-block">Driver</small><strong>{{ $head_jurnal->driver ?: '-' }}</strong></div>
</div>
<div class="table-responsive border rounded">
    <table class="table table-hover align-middle mb-0">
        <thead><tr class="table-primary"><th>No</th><th>Nama Produk</th><th class="text-end">Qty</th><th class="text-end">Harga Satuan</th><th class="text-end">Total</th><th>Admin</th></tr></thead>
        <tbody>
            @php($total = 0)
            @foreach ($produk as $no => $item)
                @php($total += $item->total_rp)
                <tr><td>{{ $no + 1 }}</td><td>{{ $item->nm_produk }}</td><td class="text-end">{{ number_format($item->qty, 0, ',', '.') }}</td><td class="text-end">Rp {{ number_format($item->rp_satuan, 0, ',', '.') }}</td><td class="text-end">Rp {{ number_format($item->total_rp, 0, ',', '.') }}</td><td>{{ $item->admin }}</td></tr>
            @endforeach
        </tbody>
        <tfoot><tr><th colspan="4" class="text-end">Jumlah Total</th><th class="text-end">Rp {{ number_format($total, 0, ',', '.') }}</th><th></th></tr></tfoot>
    </table>
</div>
