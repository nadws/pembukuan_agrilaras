<x-theme.app title="Detail Penjualan Telur" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h5 class="mb-1">Detail Penjualan Telur</h5><small class="text-muted">Nota {{ $nota->no_nota }}</small></div>
            <div>
                <a href="{{ route('transaksi.penjualan-telur.edit', $nota->no_nota) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
                <a href="{{ route('transaksi.penjualan-telur.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .egg-detail { --egg-primary: #29468f; --egg-border: #dce3f2; }
            .egg-detail-info { padding: 14px; margin-bottom: 16px; border: 1px solid var(--egg-border); border-radius: 12px; background: #f5f7fc; }
            .egg-detail-info .label { color: #637089; font-size: 12px; font-weight: 700; }
            .egg-detail-info .value { color: #263b78; font-weight: 700; }
            .egg-detail-table-wrap { overflow-x: auto; border: 1px solid var(--egg-border); border-radius: 12px; }
            .egg-detail-table { min-width: 900px; margin-bottom: 0; }
            .egg-detail-table thead th { padding: 12px; color: #fff; background: var(--egg-primary); font-size: 12px; white-space: nowrap; }
            .egg-type-value { display: inline-block; padding: 4px 9px; border-radius: 6px; color: #fff; background: #29468f; font-size: 11px; font-weight: 800; }
        </style>
        <div class="egg-detail">
        <div class="row g-3 egg-detail-info">
            <div class="col-md-3"><div class="label">Tanggal</div><div class="value">{{ tanggal($nota->tgl) }}</div></div>
            <div class="col-md-4"><div class="label">Customer</div><div class="value">{{ $nota->nm_customer ?? '-' }}</div></div>
            <div class="col-md-2"><div class="label">Tipe Jualan</div><div class="value"><span class="egg-type-value">{{ strtoupper($nota->tipe) }}</span></div></div>
            <div class="col-md-2"><div class="label">Status</div><div class="value">{{ $nota->status === 'unpaid' ? 'Belum Lunas' : 'Lunas' }}</div></div>
            <div class="col-md-2"><div class="label">Admin</div><div class="value">{{ $nota->admin }}</div></div>
        </div>
        <div class="egg-detail-table-wrap">
            <table class="table table-hover align-middle egg-detail-table">
                <thead class="table-light"><tr><th>No</th><th>Produk Telur</th><th class="text-end">Pcs</th><th class="text-end">Kg Kotor</th><th class="text-end">Kg Bersih</th><th class="text-end">Harga Satuan</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td><td>{{ $item->nm_telur ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->pcs, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->kg, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->kg_jual, 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item->rp_satuan, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item->total_rp, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><th colspan="6" class="text-end">Total</th><th class="text-end">Rp {{ number_format($items->sum('total_rp'), 0, ',', '.') }}</th></tr></tfoot>
            </table>
        </div>
        </div>
    </x-slot>
</x-theme.app>
