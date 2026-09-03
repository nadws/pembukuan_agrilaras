<x-theme.app title="Penjualan Telur" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">Penjualan Telur</h5>
                <small class="text-muted">Daftar transaksi penjualan telur</small>
            </div>
            <div>
            <a href="{{ route('transaksi') }}" class="btn btn-outline-primary btn-sm me-1">
                <i class="fas fa-arrow-left me-1"></i> Transaksi
            </a>
            <a href="{{ route('transaksi.penjualan-telur.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Buat Penjualan
            </a>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .egg-page { --egg-primary: #29468f; --egg-border: #dce3f2; }
            .egg-table-wrap { overflow-x: auto; border: 1px solid var(--egg-border); border-radius: 12px; }
            .egg-table { min-width: 900px; margin-bottom: 0; }
            .egg-table thead th { padding: 12px; border-color: #4f69b6; color: #fff; background: var(--egg-primary); font-size: 12px; white-space: nowrap; }
            .egg-table td { padding: 10px 12px; }
            .egg-empty { padding: 46px 20px !important; color: #66738a; text-align: center; }
            .egg-filter { padding: 14px; margin-bottom: 16px; border: 1px solid var(--egg-border); border-radius: 12px; background: #f5f7fc; }
            .egg-filter .form-label { margin-bottom: 5px; color: #536078; font-size: 12px; font-weight: 700; }
            .egg-filter .form-control { min-height: 40px; border-color: var(--egg-border); border-radius: 8px; }
            .egg-type-badge { display: inline-block; padding: 4px 9px; border-radius: 6px; color: #fff !important; background: #29468f !important; font-size: 11px; font-weight: 800; letter-spacing: .3px; }
            .status-badge { display: inline-block; padding: 4px 9px; border-radius: 6px; font-size: 11px; font-weight: 800; white-space: nowrap; }
            .status-paid { color: #176b38; background: #d9f2df; }
            .status-unpaid { color: #a12a35; background: #fbdadd; }
        </style>
        <div class="egg-page">
        <form method="GET" action="{{ route('transaksi.penjualan-telur.index') }}" class="egg-filter">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-6">
                    <label class="form-label" for="tanggal_awal">Dari tanggal</label>
                    <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="{{ $tanggalAwal }}">
                </div>
                <div class="col-lg-3 col-6">
                    <label class="form-label" for="tanggal_akhir">Sampai tanggal</label>
                    <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="{{ $tanggalAkhir }}">
                </div>
                <div class="col-lg-4">
                    <label class="form-label" for="cari_penjualan">Cari nota atau customer</label>
                    <input type="search" id="cari_penjualan" name="cari" class="form-control" value="{{ $cari }}" placeholder="Masukkan nomor nota atau nama customer">
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan</button>
                </div>
            </div>
        </form>

        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <div class="p-2 px-3 border rounded" style="background-color: #f8f9fa;">
                    <small class="text-secondary d-block font-size-12 fw-semibold">Total Penjualan</small>
                    <span class="fw-bold fs-6" style="color: #1e293b;">Rp {{ number_format($totalRp ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-2 px-3 border rounded" style="background-color: #f8f9fa;">
                    <small class="text-secondary d-block font-size-12 fw-semibold">Total Telur (Pcs)</small>
                    <span class="fw-bold fs-6" style="color: #1e293b;">{{ number_format($totalPcs ?? 0, 0, ',', '.') }} Pcs</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-2 px-3 border rounded" style="background-color: #f8f9fa;">
                    <small class="text-secondary d-block font-size-12 fw-semibold">Total Telur (Kg)</small>
                    <span class="fw-bold fs-6" style="color: #1e293b;">{{ number_format($totalKg ?? 0, 2, ',', '.') }} Kg</span>
                </div>
            </div>
        </div>

        <div class="egg-table-wrap">
            <table class="table table-hover align-middle egg-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No Nota</th>
                        <th>Customer</th>
                        <th>Customer 2</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Jumlah Item</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penjualan as $i => $item)
                        <tr>
                            <td>{{ ($penjualan->firstItem() ?? 1) + $i }}</td>
                            <td>{{ tanggal($item->tgl) }}</td>
                            <td class="fw-semibold">{{ $item->no_nota }}</td>
                            <td>{{ $item->nm_customer ?? '-' }}</td>
                            <td>{{ $item->nm_customer2 ?? '-' }}</td>
                            <td><span class="egg-type-badge">{{ strtoupper($item->tipe) }}</span></td>
                            <td><span class="status-badge {{ $item->status === 'unpaid' ? 'status-unpaid' : 'status-paid' }}">{{ $item->status === 'unpaid' ? 'Belum Lunas' : 'Lunas' }}</span></td>
                            <td>{{ $item->jumlah_item }} item</td>
                            <td class="text-end">Rp {{ number_format($item->total_rp, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('transaksi.penjualan-telur.detail', $item->no_nota) }}" class="btn btn-outline-primary btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('transaksi.penjualan-telur.edit', $item->no_nota) }}" class="btn btn-outline-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="{{ route('transaksi.penjualan-telur.destroy', $item->no_nota) }}" onsubmit="return confirm('Hapus penjualan {{ $item->no_nota }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="egg-empty">Belum ada penjualan telur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
            <small class="text-muted">
                Menampilkan {{ $penjualan->firstItem() ?? 0 }}–{{ $penjualan->lastItem() ?? 0 }} dari {{ $penjualan->total() }} transaksi
            </small>
            <div>
                {{ $penjualan->links('pagination::bootstrap-5') }}
            </div>
        </div>
        </div>
    </x-slot>
</x-theme.app>
