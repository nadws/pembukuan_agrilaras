<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div><h5 class="mb-1">Penjualan Ayam</h5><small class="text-muted">Daftar penjualan ayam Martadah yang akan dibukukan.</small></div>
            <a href="{{ route('produk_telur') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-home me-1"></i> Dashboard</a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .ayam-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}.ayam-summary-card{padding:14px 16px;border:1px solid #dce4f2;border-radius:12px;background:#f7f9fd}.ayam-summary-card small{display:block;margin-bottom:4px;color:#71809a;font-weight:600}.ayam-summary-card strong{color:#18366f;font-size:19px}.ayam-filter{padding:14px;border:1px solid #dce4f2;border-radius:12px;background:#fff}.ayam-filter label{font-size:12px;font-weight:700;color:#52627a}.ayam-table-wrap{overflow:auto;max-height:62vh;border:1px solid #dce4f2;border-radius:12px}.ayam-table{min-width:900px;margin:0;font-size:13px}.ayam-table thead th{position:sticky;top:0;z-index:2;padding:12px 10px;background:#304f9e!important;color:#fff!important;border-color:#4966ad;white-space:nowrap}.ayam-table td{padding:11px 10px;vertical-align:middle;border-color:#e8edf5}.ayam-table tbody tr:hover td{background:#f2f6ff}.ayam-nota{font-weight:700;color:#294a97}.ayam-customer{display:block;margin-top:3px;color:#6f7d91;font-size:12px}.ayam-pagination .pagination{margin:0}@media(max-width:767px){.ayam-summary{grid-template-columns:1fr}.ayam-table-wrap{max-height:70vh}}
        </style>
        @if(session('sukses'))<div class="alert alert-success">{{ session('sukses') }}</div>@endif
        <div class="ayam-summary">
            <div class="ayam-summary-card"><small>Jumlah nota ditemukan</small><strong>{{ number_format($penjualan->total(), 0, ',', '.') }}</strong></div>
            <div class="ayam-summary-card"><small>Total penjualan</small><strong>Rp {{ number_format($ttlRp, 0, ',', '.') }}</strong></div>
            <div class="ayam-summary-card"><small>Total belum dicek</small><strong>Rp {{ number_format($ttlRpBelumDiCek, 0, ',', '.') }}</strong></div>
        </div>
        <form method="GET" action="{{ route('penjualan_ayam.index') }}" class="ayam-filter row g-2 align-items-end mb-3">
            <input type="hidden" name="period" value="costume">
            <div class="col-md-2"><label class="form-label">Dari tanggal</label><input type="date" name="tgl1" value="{{ $tgl1 }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Sampai tanggal</label><input type="date" name="tgl2" value="{{ $tgl2 }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Cari nomor nota atau pelanggan</label><div class="input-group"><span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span><input type="search" name="pencarian" value="{{ $pencarian }}" class="form-control" placeholder="Nomor nota atau nama pelanggan"></div></div>
            <div class="col-md-2"><label class="form-label">Data per halaman</label><select name="per_page" class="form-select">@foreach([20,50,100] as $jumlah)<option value="{{ $jumlah }}" @selected($perPage === $jumlah)>{{ $jumlah }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button></div>
        </form>
        <div class="ayam-table-wrap"><table class="table table-hover table-striped ayam-table">
            <thead><tr><th class="text-center">No</th><th>Tanggal</th><th>Nota dan pelanggan</th><th class="text-end">Qty</th><th class="text-end">Harga satuan</th><th class="text-end">Total</th><th class="text-center">Status</th><th>Diterima oleh</th></tr></thead>
            <tbody>@forelse($penjualan as $no => $d)<tr>
                <td class="text-center text-muted">{{ ($penjualan->firstItem() ?? 1) + $no }}</td><td class="text-nowrap">{{ tanggal($d->tgl) }}</td>
                <td><span class="ayam-nota">{{ $d->no_nota }}</span><span class="ayam-customer">{{ $d->nm_customer ?: ($d->customer ?: '-') }}</span></td>
                <td class="text-end fw-semibold">{{ number_format($d->qty, 0, ',', '.') }} ekor</td><td class="text-end text-nowrap">Rp {{ number_format($d->h_satuan, 0, ',', '.') }}</td><td class="text-end fw-bold text-nowrap">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                <td class="text-center">@if($d->cek === 'Y')<span class="badge bg-success"><i class="fas fa-check me-1"></i> Dibukukan</span>@else<a href="{{ route('penjualan_ayam.cek', ['no_nota' => $d->urutan]) }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> Setor</a>@endif</td><td>{{ $d->admin_cek ?: '-' }}</td>
            </tr>@empty<tr><td colspan="8" class="text-center text-muted py-4">Data penjualan ayam tidak ditemukan.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="ayam-pagination d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"><small class="text-muted">Menampilkan {{ $penjualan->firstItem() ?? 0 }}–{{ $penjualan->lastItem() ?? 0 }} dari {{ number_format($penjualan->total(), 0, ',', '.') }} nota</small>{{ $penjualan->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
    </x-slot>
</x-theme.app>
