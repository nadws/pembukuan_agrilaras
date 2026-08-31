<x-theme.app title="Laporan Stok Persediaan">
    <x-slot name="slot">
        <style>
            .inventory-report { max-width:1240px; margin:0 auto; padding:4px 4px 34px; }
            .inventory-panel { overflow:hidden; border:1px solid #e0e7f3; border-radius:16px; background:#fff; box-shadow:0 10px 28px rgba(31,58,112,.07); }
            .inventory-head { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:24px 26px; border-bottom:1px solid #edf1f7; }
            .inventory-head h3 { margin:0 0 5px; color:#17356f; font-size:24px; font-weight:750; }
            .inventory-head p { margin:0; color:#77849a; font-size:13px; }
            .inventory-filter { margin:20px 24px; padding:16px; border:1px solid #dce5f4; border-radius:12px; background:#f7f9fd; }
            .inventory-filter label { display:block; margin-bottom:7px; color:#50617c; font-size:12px; font-weight:700; }
            .inventory-filter .form-control, .inventory-filter .form-select, .inventory-filter .btn, .inventory-filter .input-group-text { min-height:44px; }
            .inventory-table-wrap { margin:0 24px; overflow-x:auto; border:1px solid #dfe6f1; border-radius:12px; }
            .inventory-table { min-width:900px; margin:0; }
            .inventory-table thead th { padding:13px 14px; border:0; background:#304f9e; color:#fff; font-size:11px; font-weight:750; letter-spacing:.35px; text-transform:uppercase; white-space:nowrap; }
            .inventory-table tbody td { padding:12px 14px; border-color:#e9edf4; color:#42516a; vertical-align:middle; }
            .inventory-table tbody tr:hover td { background:#f5f8ff; }
            .product-name { color:#24488f; font-weight:700; text-decoration:none; }
            .product-name:hover { color:#17356f; text-decoration:underline; }
            .product-code { display:block; margin-top:3px; color:#8a96a9; font-size:11px; }
            .category-pill { display:inline-flex; padding:5px 10px; border-radius:999px; background:#eaf0ff; color:#3155a3; font-size:11px; font-weight:700; text-transform:capitalize; }
            .stock-balance { color:#17356f!important; font-weight:750; }
            .history-btn { display:inline-flex; align-items:center; gap:6px; white-space:nowrap; }
            .inventory-footer { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:18px 24px 22px; }
            .inventory-footer-info { color:#77849a; font-size:12px; white-space:nowrap; }
            .inventory-footer nav { display:flex; justify-content:flex-end; }
            .inventory-footer .pagination { flex-wrap:wrap; justify-content:flex-end; gap:4px; margin:0; }
            .inventory-footer .page-link { display:flex; min-width:34px; height:34px; align-items:center; justify-content:center; padding:0 9px; border:1px solid #dce4f1; border-radius:7px!important; color:#304f9e; font-size:12px; box-shadow:none; }
            .inventory-footer .page-item.active .page-link { border-color:#304f9e; background:#304f9e; color:#fff; }
            .inventory-footer .page-item.disabled .page-link { color:#9ba7b8; background:#f6f8fb; }
            .inventory-empty { padding:52px 16px!important; color:#8692a5!important; text-align:center; }
            @media(max-width:767.98px) {
                .inventory-report { padding:0 0 24px; }
                .inventory-head { align-items:flex-start; padding:19px 17px; }
                .inventory-head h3 { font-size:20px; }
                .inventory-filter { margin:14px; padding:13px; }
                .inventory-table-wrap { margin:0 14px; }
                .inventory-footer { align-items:flex-start; flex-direction:column; padding:15px 14px 19px; }
                .inventory-footer nav, .inventory-footer > div { width:100%; }
                .inventory-footer .pagination { justify-content:flex-start; }
            }
        </style>

        <section class="inventory-report">
            <div class="inventory-panel">
                <header class="inventory-head">
                    <div>
                        <h3>Laporan Stok Persediaan</h3>
                        <p>Stok pakan, vitamin, dan vaksin berdasarkan seluruh transaksi masuk dan pemakaian.</p>
                    </div>
                    <a href="{{ route('laporan') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                </header>

                <form method="get" class="inventory-filter">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="kategori">Kategori persediaan</label>
                            <select id="kategori" name="kategori" class="form-select">
                                <option value="">Semua kategori</option>
                                <option value="pakan" @selected($kategori === 'pakan')>Pakan</option>
                                <option value="vitamin" @selected(in_array($kategori, ['vitamin','obat_pakan','obat_air'], true))>Vitamin / Obat</option>
                                <option value="vaksin" @selected($kategori === 'vaksin')>Vaksin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="cari">Cari produk</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input id="cari" type="search" name="cari" value="{{ $cari }}" class="form-control" placeholder="Nama produk atau kode Accurate">
                            </div>
                        </div>
                        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan</button></div>
                    </div>
                </form>

                <div class="inventory-table-wrap">
                    <table class="table table-hover inventory-table">
                        <thead><tr><th>Produk</th><th>Kategori</th><th>Satuan</th><th class="text-end">Total Masuk</th><th class="text-end">Total Terpakai</th><th class="text-end">Stok Akhir</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($summary as $row)
                                <tr>
                                    <td><a class="product-name" href="{{ route('laporan.stok-persediaan.detail', $row->id_produk) }}">{{ $row->nm_produk }}</a><span class="product-code">{{ $row->kode_accurate ?: 'Tanpa kode Accurate' }}</span></td>
                                    <td><span class="category-pill">{{ str_replace('_', ' ', $row->kategori) }}</span></td>
                                    <td>{{ $row->nm_satuan ?: '-' }}</td>
                                    <td class="text-end">{{ number_format((float)$row->total_masuk, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float)$row->total_pakai, 2, ',', '.') }}</td>
                                    <td class="text-end stock-balance">{{ number_format((float)$row->stok_akhir, 2, ',', '.') }}</td>
                                    <td class="text-center"><a class="btn btn-sm btn-outline-primary history-btn" href="{{ route('laporan.stok-persediaan.detail', $row->id_produk) }}"><i class="fas fa-history"></i> Riwayat</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="inventory-empty"><i class="fas fa-box-open fa-2x d-block mb-2"></i>Tidak ada stok yang sesuai dengan filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <footer class="inventory-footer">
                    <span class="inventory-footer-info">Menampilkan {{ $summary->firstItem() ?? 0 }}–{{ $summary->lastItem() ?? 0 }} dari {{ number_format($summary->total(), 0, ',', '.') }} produk</span>
                    <div>{{ $summary->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
                </footer>
            </div>
        </section>
    </x-slot>
</x-theme.app>
