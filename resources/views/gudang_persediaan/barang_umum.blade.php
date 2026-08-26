<x-theme.app title="{{ $title }}" table="T" sizeCard="12">
    <x-slot name="slot">
        <style>
            .general-stock{padding:24px;border-radius:16px;background:#fff}.general-stock h4{margin:0;color:#18366f;font-weight:700}
            .stock-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}.summary-card{padding:15px;border:1px solid #dfe6f3;border-radius:12px;background:#f7f9fd}.summary-card small{display:block;color:#6f7d96;font-weight:600}.summary-card strong{display:block;margin-top:5px;color:#18366f;font-size:20px}
            .stock-filter{padding:14px;border:1px solid #dfe6f3;border-radius:12px;background:#f7f9fd}.stock-table-wrap{overflow-x:auto;border:1px solid #dfe6f3;border-radius:12px}.stock-table{min-width:950px;margin:0}.stock-table thead th{padding:11px;background:#304f9e;color:#fff;font-size:12px;white-space:nowrap}.stock-table td{padding:10px;vertical-align:middle}.stock-qty{font-weight:700}.pagination-wrap{min-height:38px}.pagination-wrap nav{display:flex;justify-content:flex-end;max-width:100%;overflow-x:auto}.pagination-wrap .pagination{margin:0;white-space:nowrap}.pagination-wrap .page-link{padding:.42rem .72rem;color:#304f9e;border-color:#dfe6f3}.pagination-wrap .page-item.active .page-link{background:#304f9e;border-color:#304f9e;color:#fff}.pagination-wrap .page-item.disabled .page-link{color:#9aa8bd}
            @media(max-width:900px){.stock-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:560px){.general-stock{padding:16px}.stock-summary{grid-template-columns:1fr}}
        </style>
        <div class="general-stock">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div><h4>Stok Barang Umum</h4><small class="text-muted">Saldo stok awal, pembelian umum, dan hasil penyesuaian stok opname.</small></div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('barang-umum.index') }}" class="btn btn-outline-primary"><i class="fas fa-boxes me-1"></i> Isi Stok Awal</a>
                    <a href="{{ route('gudang-persediaan.barang-umum.opname') }}" class="btn btn-primary"><i class="fas fa-clipboard-check me-1"></i> Mulai Stok Opname</a>
                </div>
            </div>
            <div class="stock-summary">
                <div class="summary-card"><small>Total barang</small><strong>{{ number_format($ringkasan->total_produk ?? 0, 0) }}</strong></div>
                <div class="summary-card"><small>Barang tersedia</small><strong>{{ number_format($ringkasan->tersedia ?? 0, 0) }}</strong></div>
                <div class="summary-card"><small>Kosong/minus</small><strong>{{ number_format($ringkasan->kosong ?? 0, 0) }}</strong></div>
                <div class="summary-card"><small>Nilai persediaan</small><strong style="font-size:17px">Rp {{ number_format(max(0, $ringkasan->nilai_persediaan ?? 0), 0) }}</strong></div>
            </div>
            <form method="GET" action="{{ route('gudang-persediaan.barang-umum') }}" class="stock-filter mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4"><label class="form-label">Gudang</label><select name="gudang" class="form-select"><option value="">Semua gudang</option>@foreach($gudang as $item)<option value="{{ $item->id_gudang }}" @selected((string)request('gudang') === (string)$item->id_gudang)>{{ $item->nm_gudang }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Cari barang</label><input type="search" name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Nama, kode, satuan, atau gudang"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Cari</button></div>
                </div>
            </form>
            <div class="stock-table-wrap">
                <table class="table table-hover stock-table">
                    <thead><tr><th>No</th><th>Kode</th><th>Barang</th><th>Gudang</th><th>Satuan</th><th class="text-end">Stok Saat Ini</th><th class="text-end">Nilai Persediaan</th><th class="text-center">Kontrol Stok</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($barang as $item)
                        <tr><td>{{ $barang->firstItem() + $loop->index }}</td><td>{{ $item->kd_produk ?: '-' }}</td><td class="fw-semibold">{{ $item->nm_produk }}</td><td>{{ $item->nm_gudang ?: '-' }}</td><td>{{ $item->nm_satuan ?: '-' }}</td><td class="text-end stock-qty {{ $item->stok < 0 ? 'text-danger' : '' }}">{{ number_format($item->stok, 0) }}</td><td class="text-end">Rp {{ number_format(max(0, $item->nilai_stok), 0) }}</td><td class="text-center"><span class="badge {{ $item->kontrol_stok === 'Y' ? 'bg-primary' : 'bg-secondary' }}">{{ $item->kontrol_stok === 'Y' ? 'Aktif' : 'Tidak' }}</span></td><td>@if($item->stok > 0)<span class="badge bg-success">Tersedia</span>@elseif($item->stok < 0)<span class="badge bg-danger">Minus</span>@else<span class="badge bg-secondary">Kosong</span>@endif</td></tr>
                    @empty<tr><td colspan="9" class="text-center text-muted py-4">Barang umum tidak ditemukan.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            @if($barang->total() > 0)<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pagination-wrap"><small class="text-muted">Menampilkan {{ $barang->firstItem() }}–{{ $barang->lastItem() }} dari {{ $barang->total() }} barang</small><div>{{ $barang->onEachSide(1)->links('pagination::bootstrap-5') }}</div></div>@endif
        </div>
    </x-slot>
</x-theme.app>
