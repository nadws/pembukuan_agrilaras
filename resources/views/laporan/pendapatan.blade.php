<x-theme.app title="{{ $title }}" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Laporan Pendapatan</h5>
                <small class="text-muted">Gabungan penjualan telur, umum, dan ayam · {{ \Carbon\Carbon::parse($tanggalAwal)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}</small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('laporan.pendapatan.export', request()->only(['tanggal_awal', 'tanggal_akhir', 'kategori', 'lokasi', 'pembayaran'])) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
                <a href="{{ route('laporan') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Daftar Laporan</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .income-filter{padding:14px;border:1px solid #dce4f2;border-radius:12px;background:#f7f9fd}.income-kpi{height:100%;padding:14px 16px;border:1px solid #e1e7f2;border-radius:12px;background:#fff;box-shadow:0 5px 15px rgba(35,60,115,.06)}.income-kpi small{display:block;color:#7583a0;font-size:10px;font-weight:700;text-transform:uppercase}.income-kpi strong{display:block;margin-top:5px;color:#18366f;font-size:19px}.income-table{overflow:auto;border:1px solid #dce4f2;border-radius:13px;max-height:560px}.income-table table{margin:0;width:100%;min-width:0;table-layout:auto;font-size:12px}.income-table thead th{padding:8px 6px;background:#304f9e;color:#fff;font-size:11px;white-space:normal;vertical-align:middle;position:sticky;top:0;z-index:2}.income-table td{padding:7px 6px;vertical-align:middle;word-break:break-word}.income-table tbody tr:nth-child(even){background:#f8fafd}.income-table tfoot td{position:sticky;bottom:0;z-index:2;background:#eef3ff}.income-table .badge{font-size:10px;white-space:nowrap}.income-table small{font-size:11px;line-height:1.4;display:block}.summary-table thead th{background:#198754}.summary-table tfoot td{background:#e9f9f0}.section-title{margin:0;font-size:14px;font-weight:700;color:#18366f}.section-sub{font-size:11px}.amount{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.income-pagination nav{display:flex;justify-content:flex-end}.income-pagination .pagination{margin:0;gap:4px;flex-wrap:wrap}.income-pagination .page-link{display:flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border:1px solid #dce4f1;border-radius:7px!important;color:#304f9e;font-size:13px;font-weight:500;text-decoration:none}.income-pagination .page-item.active .page-link{background-color:#304f9e;border-color:#304f9e;color:#fff;font-weight:700}.income-pagination .page-item.disabled .page-link{color:#9aa8bd;background-color:#f8fafc;border-color:#e2e8f0}@media(max-width:575px){.income-filter .btn{width:100%}}
        </style>
        @php $fmt=fn($value)=>'Rp '.number_format((float)$value,0,'.',','); @endphp
        <form method="get" class="income-filter mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label fw-semibold">Tanggal awal</label><input type="date" name="tanggal_awal" value="{{ $tanggalAwal }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Tanggal akhir</label><input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Tipe</label><select name="kategori" class="form-select"><option value="" @selected($kategori==='')>Semua</option><option value="telur" @selected($kategori==='telur')>Telur</option><option value="umum" @selected($kategori==='umum')>Umum</option><option value="ayam" @selected($kategori==='ayam')>Ayam</option></select></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Lokasi</label><select name="lokasi" class="form-select"><option value="" @selected(($lokasi ?? '')==='')>Semua</option><option value="alpa" @selected(($lokasi ?? '')==='alpa')>BJM</option><option value="mtd" @selected(($lokasi ?? '')==='mtd')>MTD</option></select></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Pembayaran</label><select name="pembayaran" class="form-select"><option value="">Semua</option>@foreach($akunPembayaran as $akun)<option value="{{ $akun->id_akun_perkiraan }}" @selected(($pembayaranId ?? 0)==(int)$akun->id_akun_perkiraan)>{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>@endforeach</select></div>
                <div class="col-md-1"><label class="form-label fw-semibold">Baris</label><select name="per_page" class="form-select">@foreach([25,50,100] as $size)<option value="{{ $size }}" @selected($perPage===$size)>{{ $size }}</option>@endforeach</select></div>
                <div class="col-md-1"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button></div>
            </div>
        </form>
        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="income-kpi"><small>Pendapatan Telur</small><strong>{{ $fmt($totals['telur']) }}</strong></div></div>
            <div class="col-md-3"><div class="income-kpi"><small>Pendapatan Umum</small><strong>{{ $fmt($totals['umum']) }}</strong></div></div>
            <div class="col-md-3"><div class="income-kpi"><small>Pendapatan Ayam</small><strong>{{ $fmt($totals['ayam']) }}</strong></div></div>
            <div class="col-md-3"><div class="income-kpi"><small>Total Pendapatan</small><strong>{{ $fmt($totals['grand']) }}</strong></div></div>
        </div>
        <div class="row g-3">
            <div class="col-lg-6">
            <h6 class="section-title">Detail per Nota</h6>
            <small class="text-muted d-block mb-2 section-sub">Daftar nota telur, umum, dan ayam sesuai filter.</small>
        <div class="income-table table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>No</th><th>Tipe</th><th>Tanggal</th><th>Invoice</th><th>Lokasi</th><th>Customer</th><th class="amount">Total Rupiah</th><th>Bayar Via</th></tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $rows->firstItem()+$loop->index }}</td>
                            <td><span class="badge bg-primary">{{ ucfirst($row['kategori']) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($row['tgl'])->format('d-m-Y') }}</td>
                            <td class="fw-semibold">{{ $row['no_nota'] }}</td>
                            <td><span class="badge bg-secondary">{{ $row['lokasi'] ?? '-' }}</span></td>
                            <td>{{ $row['customer'] }}</td>
                            <td class="amount">{{ $fmt($row['total']) }}</td>
                            <td><small class="text-muted">{{ $row['pembayaran'] ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada pendapatan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->total() > 0)
                <tfoot>
                    <tr class="fw-bold table-light"><td colspan="6" class="text-end">Total</td><td class="amount">{{ $fmt($totals['grand']) }}</td><td></td></tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
            <small class="text-muted">Menampilkan {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }} nota</small>
            <div class="income-pagination">{{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
        </div>
            </div>
            <div class="col-lg-6">
            <h6 class="section-title">Rangkuman per Produk</h6>
            <small class="text-muted d-block mb-2 section-sub">Mengikuti filter di atas — totalnya harus klop dengan total pendapatan.</small>
            <div class="income-table summary-table table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>No</th><th>Produk</th><th>Tipe</th><th class="amount">Pcs</th><th class="amount">Kg</th><th class="amount">Total Rupiah</th></tr>
                    </thead>
                    <tbody>
                        @forelse($summary as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $item['produk'] }}</td>
                                <td><span class="badge bg-primary">{{ ucfirst($item['tipe']) }}</span></td>
                                <td class="amount">{{ number_format($item['pcs'], 0, '.', ',') }}</td>
                                <td class="amount">{{ number_format($item['kg'], 2, '.', ',') }}</td>
                                <td class="amount">{{ $fmt($item['total']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada rangkuman produk pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                    @if($summary->isNotEmpty())
                    <tfoot>
                        <tr class="fw-bold table-light"><td colspan="5" class="text-end">Total Rangkuman</td><td class="amount">{{ $fmt($summary->sum('total')) }}</td></tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            <h6 class="section-title mt-3">Total per Pembayaran</h6>
            <small class="text-muted d-block mb-2 section-sub">Mengikuti filter di atas — totalnya harus klop dengan total pendapatan.</small>
            <div class="income-table summary-table table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>No</th><th>Pembayaran</th><th class="amount">Nota</th><th class="amount">Total Rupiah</th></tr>
                    </thead>
                    <tbody>
                        @forelse($paySummary as $pay)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $pay['pembayaran'] }}</td>
                                <td class="amount">{{ number_format($pay['jumlah'], 0, '.', ',') }}</td>
                                <td class="amount">{{ $fmt($pay['total']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data pembayaran pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                    @if($paySummary->isNotEmpty())
                    <tfoot>
                        <tr class="fw-bold table-light"><td colspan="3" class="text-end">Total Pembayaran</td><td class="amount">{{ $fmt($paySummary->sum('total')) }}</td></tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            </div>
        </div>
    </x-slot>
</x-theme.app>
