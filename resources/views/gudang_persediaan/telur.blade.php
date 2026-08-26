<x-theme.app title="{{ $title }}" table="T" sizeCard="12">
    <x-slot name="slot">
        <style>
            .egg-page{padding:24px;border-radius:16px;background:#fff}
            .egg-heading{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;margin-bottom:18px}
            .egg-heading h4{margin:0;color:#18366f;font-weight:700}
            .egg-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}
            .egg-summary-card{padding:15px;border:1px solid #dfe6f3;border-radius:12px;background:#f7f9fd}
            .egg-summary-card small{display:block;color:#6f7d96;font-weight:600}
            .egg-summary-card strong{display:block;margin-top:5px;color:#18366f;font-size:20px}
            .egg-warehouse-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
            .egg-warehouse-card{overflow:hidden;border:1px solid #dfe6f3;border-radius:12px;background:#fff}
            .egg-warehouse-header{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:13px 15px;background:#f7f9fd;border-bottom:1px solid #dfe6f3}
            .egg-warehouse-header strong{color:#18366f;font-size:16px}
            .egg-warehouse-total{color:#65748d;font-size:12px;font-weight:700;text-align:right}
            .egg-table{margin:0}
            .egg-table thead th{padding:9px 11px;background:#304f9e;color:#fff;font-size:11px;white-space:nowrap}
            .egg-table td{padding:9px 11px;vertical-align:middle}
            .egg-product-code{display:block;color:#7a879d;font-size:11px}
            @media(max-width:900px){.egg-warehouse-grid{grid-template-columns:1fr}}
            @media(max-width:700px){.egg-summary{grid-template-columns:1fr}.egg-page{padding:16px}}
        </style>

        <div class="egg-page">
            <div class="egg-heading">
                <div>
                    <h4>Stok Telur per Gudang</h4>
                    <small class="text-muted">Saldo aktif telur masuk dikurangi telur keluar pada masing-masing gudang.</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('gudang-persediaan.telur.riwayat') }}" class="btn btn-primary"><i class="fas fa-history me-1"></i> Riwayat Opname</a>
                    <a href="{{ route('gudang_persediaan') }}" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i> Menu Gudang</a>
                </div>
            </div>

            <div class="egg-summary">
                <div class="egg-summary-card"><small>Jumlah gudang</small><strong>{{ number_format($jumlahGudangTelur, 0) }}</strong></div>
                <div class="egg-summary-card"><small>Total stok telur</small><strong>{{ number_format($totalStokTelurPcs, 0) }} pcs</strong></div>
                <div class="egg-summary-card"><small>Total berat telur</small><strong>{{ number_format($totalStokTelurKg, 0) }} kg</strong></div>
            </div>

            <div class="egg-warehouse-grid">
                @forelse($stokTelurPerGudang as $stokGudang)
                    @php
                        $gudang = $stokGudang->first();
                        $totalPcsGudang = $stokGudang->sum(fn ($row) => (float) $row->stok_pcs);
                        $totalKgGudang = $stokGudang->sum(fn ($row) => (float) $row->stok_kg);
                    @endphp
                    <article class="egg-warehouse-card">
                        <div class="egg-warehouse-header">
                            <strong><i class="fas fa-warehouse me-1"></i> {{ $gudang->nm_gudang }}</strong>
                            <div class="d-flex align-items-center gap-2">
                                <div class="egg-warehouse-total">
                                    {{ number_format($totalPcsGudang, 0) }} pcs<br>
                                    {{ number_format($totalKgGudang, 0) }} kg
                                </div>
                                <a href="{{ route('gudang-persediaan.telur.opname', $gudang->id_gudang_telur) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-clipboard-check me-1"></i> Opname
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover egg-table">
                                <thead>
                                    <tr><th>Jenis Telur</th><th class="text-end">PCS</th><th class="text-end">KG</th><th class="text-center">Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($stokGudang as $telur)
                                        <tr>
                                            <td><span class="fw-semibold">{{ $telur->nm_telur }}</span><span class="egg-product-code">{{ $telur->kode_produk ?: '-' }}</span></td>
                                            <td class="text-end fw-semibold {{ $telur->stok_pcs < 0 ? 'text-danger' : '' }}">{{ number_format($telur->stok_pcs, 0) }}</td>
                                            <td class="text-end {{ $telur->stok_kg < 0 ? 'text-danger' : '' }}">{{ number_format($telur->stok_kg, 0) }}</td>
                                            <td class="text-center">
                                                @if($telur->stok_pcs > 0 || $telur->stok_kg > 0)<span class="badge bg-success">Ada</span>
                                                @elseif($telur->stok_pcs < 0 || $telur->stok_kg < 0)<span class="badge bg-danger">Minus</span>
                                                @else<span class="badge bg-secondary">Kosong</span>@endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </article>
                @empty
                    <div class="alert alert-light border text-muted mb-0">Belum ada master gudang telur.</div>
                @endforelse
            </div>
        </div>
    </x-slot>
</x-theme.app>
