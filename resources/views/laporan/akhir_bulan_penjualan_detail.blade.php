<x-theme.app title="{{ $title }}" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Detail Uang Penjualan</h5>
                <small>{{ $account->kode_perkiraan }} - {{ $account->nama }}</small>
            </div>
            <a href="{{ route('laporan.akhir-bulan', [
                'bulan' => $start->month,
                'tahun' => $start->year,
                'tipe' => $selectedTransactionTypes,
                'akun' => $selectedAccountIds,
                'semua_tipe' => $allTransactionTypes ? 1 : null,
                'tipe_penjualan' => $selectedPenjualanTypes,
                'akun_penjualan' => $selectedPenjualanAccountIds,
                'semua_tipe_penjualan' => $allPenjualanTypes ? 1 : null,
            ]) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Laporan Akhir Bulan
            </a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .detail-filter{padding:14px;border:1px solid #dce4f2;border-radius:12px;background:#f7f9fd}.detail-kpi{height:100%;padding:16px 18px;border:1px solid #e1e7f2;border-radius:12px;background:#fff;box-shadow:0 5px 15px rgba(35,60,115,.06)}.detail-kpi small{display:block;color:#7583a0;font-size:10px;font-weight:700;text-transform:uppercase}.detail-kpi strong{display:block;margin-top:5px;color:#18366f;font-size:20px}.detail-table{overflow:hidden;border:1px solid #dce4f2;border-radius:13px}.detail-table table{margin:0;min-width:1050px}.detail-table thead th{padding:11px;background:#198754;color:#fff;white-space:nowrap}.detail-table td{padding:10px}.amount{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.detail-pagination nav{display:flex;justify-content:flex-end}.detail-pagination .pagination{margin:0;gap:4px;flex-wrap:wrap}.detail-pagination .page-link{border-radius:7px}@media(max-width:575px){.detail-filter .btn{width:100%}}
        </style>
        @php $fmt=fn($value)=>'Rp '.number_format((float)$value,0,',','.'); @endphp
        <div class="mb-3">
            <h5 class="mb-1">{{ $account->nama }}</h5>
            <div class="text-muted">Periode {{ $start->translatedFormat('d F Y') }} s/d {{ $end->translatedFormat('d F Y') }} · {{ $selectedPenjualanTypes ? collect($selectedPenjualanTypes)->map(fn($type)=>$transactionTypeOptions[$type]['label'])->implode(', ') : 'Semua tipe transaksi' }}</div>
        </div>
        <form method="get" class="detail-filter mb-3">
            <input type="hidden" name="bulan" value="{{ $start->month }}">
            <input type="hidden" name="tahun" value="{{ $start->year }}">

            @foreach($selectedTransactionTypes as $type)<input type="hidden" name="tipe[]" value="{{ $type }}">@endforeach 
            @foreach($selectedAccountIds as $accountId)<input type="hidden" name="akun[]" value="{{ $accountId }}">@endforeach 
            @if($allTransactionTypes)<input type="hidden" name="semua_tipe" value="1">@endif

            @foreach($selectedPenjualanTypes as $type)<input type="hidden" name="tipe_penjualan[]" value="{{ $type }}">@endforeach 
            @foreach($selectedPenjualanAccountIds as $accountId)<input type="hidden" name="akun_penjualan[]" value="{{ $accountId }}">@endforeach 
            @if($allPenjualanTypes)<input type="hidden" name="semua_tipe_penjualan" value="1">@endif

            <div class="row g-2 align-items-end">
                <div class="col-md-7"><label class="form-label fw-semibold">Cari transaksi</label><input name="cari" value="{{ $search }}" class="form-control" placeholder="Nomor transaksi, tipe, atau keterangan"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Per halaman</label><select name="per_page" class="form-select">@foreach([25,50,100] as $size)<option value="{{ $size }}" @selected((int)request('per_page',50)===$size)>{{ $size }}</option>@endforeach</select></div>
                <div class="col-md-3"><button class="btn btn-success w-100"><i class="fas fa-search me-1"></i> Tampilkan</button></div>
            </div>
        </form>
        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="detail-kpi"><small>Total Debit</small><strong>{{ $fmt($debitTotal) }}</strong></div></div>
            <div class="col-md-4"><div class="detail-kpi"><small>Total Kredit</small><strong>{{ $fmt($creditTotal) }}</strong></div></div>
            <div class="col-md-4"><div class="detail-kpi"><small>Debit − Kredit</small><strong class="text-success">{{ $fmt($penjualanTotal) }}</strong></div></div>
        </div>
        <div class="detail-table table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>No</th><th>Tanggal</th><th>No. Transaksi</th><th>Tipe</th><th>Keterangan</th><th class="amount">Debit</th><th class="amount">Kredit</th><th class="amount">Debit − Kredit</th></tr>
                </thead>
                <tbody>
                    @forelse($details as $row)
                        <tr>
                            <td>{{ $details->firstItem()+$loop->index }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                            <td class="fw-semibold">{{ $row->nomor_transaksi }}</td>
                            <td>{{ $row->tipe_transaksi ?: '-' }}</td>
                            <td>{{ $row->deskripsi ?: '-' }}</td>
                            <td class="amount">{{ $fmt($row->debit) }}</td>
                            <td class="amount">{{ $fmt($row->kredit) }}</td>
                            <td class="amount fw-semibold">{{ $fmt((float)$row->debit-(float)$row->kredit) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada transaksi pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <small class="text-muted">Menampilkan {{ $details->firstItem() ?? 0 }}–{{ $details->lastItem() ?? 0 }} dari {{ $details->total() }} transaksi</small>
            <div class="detail-pagination">{{ $details->links() }}</div>
        </div>
    </x-slot>
</x-theme.app>
