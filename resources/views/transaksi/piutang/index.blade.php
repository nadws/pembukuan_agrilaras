<x-theme.app title="Piutang" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h5 class="mb-1">Piutang</h5><small class="text-muted">Daftar piutang penjualan telur, ayam, dan umum</small></div>
            <div class="d-flex gap-2">@if(in_array($jenis,['telur','ayam'],true))<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="{{ $jenis === 'ayam' ? '#importPiutangAyamAccurate' : '#importPiutangAccurate' }}"><i class="fas fa-file-excel me-1"></i> Import Accurate</button>@endif<a href="{{ route('transaksi') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Transaksi</a></div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .receivable-filter,.receivable-table-wrap{border:1px solid #dce3f2;border-radius:12px}.receivable-filter{padding:14px;margin-bottom:14px;background:#f5f7fc}.receivable-filter .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}.receivable-filter .form-control{min-height:40px;border-color:#dce3f2;border-radius:8px}.receivable-nav{gap:8px;margin-bottom:14px}.receivable-nav .nav-link{border:1px solid #dce3f2;color:#536078;font-size:13px;font-weight:700}.receivable-nav .nav-link.active{border-color:#29468f;background:#29468f;color:#fff}.receivable-summary{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px;padding:16px 18px;border:1px solid #d7e1f5;border-radius:12px;background:linear-gradient(135deg,#f3f7ff,#fff)}.receivable-summary-label{color:#68758d;font-size:12px;font-weight:700;text-transform:uppercase}.receivable-summary-value{margin-top:2px;color:#193875;font-size:25px;font-weight:800}.receivable-summary-count{color:#536078;font-size:13px;font-weight:700}.receivable-table-wrap{overflow-x:auto}.receivable-table{min-width:950px;margin-bottom:0}.receivable-table thead th{padding:12px;color:#fff;background:#29468f;font-size:12px;white-space:nowrap}.receivable-total{color:#a12a35;font-weight:800}.receivable-empty{padding:46px 20px!important;color:#66738a;text-align:center}
        </style>
        @if(isset($errors) && $errors->any())<div class="alert alert-danger"><strong>Data belum dapat diimpor.</strong><br>{{ $errors->first() }}</div>@endif
        <form method="GET" action="{{ route('transaksi.piutang.index') }}" class="receivable-filter">
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <div class="row g-2 align-items-end"><div class="col-lg-3 col-6"><label class="form-label">Dari tanggal</label><input type="date" name="tanggal_awal" class="form-control" value="{{ $awal }}"></div><div class="col-lg-3 col-6"><label class="form-label">Sampai tanggal</label><input type="date" name="tanggal_akhir" class="form-control" value="{{ $akhir }}"></div><div class="col-lg-4"><label class="form-label">Cari nota atau customer</label><input type="search" name="cari" class="form-control" value="{{ $cari }}" placeholder="Masukkan nomor nota atau nama customer"></div><div class="col-lg-2"><button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan</button></div></div>
        </form>
        <ul class="nav nav-pills receivable-nav"><li class="nav-item"><a class="nav-link {{ $jenis === 'telur' ? 'active' : '' }}" href="{{ route('transaksi.piutang.index',$tabFilters['telur']) }}"><i class="fas fa-egg me-1"></i> Piutang Telur</a></li><li class="nav-item"><a class="nav-link {{ $jenis === 'ayam' ? 'active' : '' }}" href="{{ route('transaksi.piutang.index',$tabFilters['ayam']) }}"><i class="fas fa-drumstick-bite me-1"></i> Piutang Ayam</a></li><li class="nav-item"><a class="nav-link {{ $jenis === 'umum' ? 'active' : '' }}" href="{{ route('transaksi.piutang.index',$tabFilters['umum']) }}"><i class="fas fa-shopping-basket me-1"></i> Piutang Umum</a></li></ul>
        <div class="receivable-summary"><div><div class="receivable-summary-label">Total Piutang {{ ucfirst($jenis) }}</div><div class="receivable-summary-value">Rp {{ number_format($totalPiutang,0,'.',',') }}</div></div><div class="receivable-summary-count"><i class="fas fa-file-invoice-dollar me-1"></i> {{ number_format($jumlahFaktur,0,'.',',') }} faktur</div></div>
        @if($jenis === 'telur')
            <form method="POST" action="{{ route('transaksi.piutang.import-accurate') }}" enctype="multipart/form-data">@csrf
                <x-theme.modal title="Import Piutang Telur dari Accurate" idModal="importPiutangAccurate" size="modal-lg">
                    <div class="alert alert-info"><strong>Gunakan laporan:</strong> Faktur Penjualan Belum Lunas dari Accurate. Semua faktur pada file akan dimasukkan ke Piutang Telur.</div>
                    <label class="form-label fw-bold">File Excel Accurate</label><input type="file" name="file_accurate" class="form-control" accept=".xlsx,.xls" required>
                    <small class="text-muted d-block mt-2">Nama pelanggan harus sudah tersedia dan aktif di Data Customer. Nomor faktur yang sudah pernah masuk akan dilewati agar tidak menjadi data ganda.</small>
                </x-theme.modal>
            </form>
        @endif
        @if($jenis === 'ayam')
            <form method="POST" action="{{ route('transaksi.piutang.import-accurate-ayam') }}" enctype="multipart/form-data">@csrf
                <x-theme.modal title="Import Piutang Ayam dari Accurate" idModal="importPiutangAyamAccurate" size="modal-lg">
                    <div class="alert alert-info"><strong>Gunakan laporan:</strong> Faktur Penjualan Belum Lunas dari Accurate. Hanya baris dengan Keterangan mengandung AYAM yang akan dimasukkan ke Piutang Ayam.</div>
                    <label class="form-label fw-bold">File Excel Accurate</label><input type="file" name="file_accurate" class="form-control" accept=".xlsx,.xls" required>
                    <small class="text-muted d-block mt-2">Nama pelanggan harus tersedia dan aktif di Data Customer. Nomor faktur yang sudah masuk akan dilewati.</small>
                </x-theme.modal>
            </form>
        @endif
        <form method="GET" action="{{ route('transaksi.piutang.pelunasan') }}" id="formPilihPiutang">
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <div class="d-flex justify-content-between align-items-center mb-3"><span class="text-muted small">Pilih satu atau beberapa nota dari customer yang sama.</span><button type="submit" class="btn btn-success" id="btnPelunasan" disabled><i class="fas fa-arrow-right me-1"></i> Lanjutkan Pelunasan</button></div>
            <div class="receivable-table-wrap"><table class="table table-hover align-middle receivable-table"><thead><tr><th class="text-center">Pilih</th><th>No</th><th>Tanggal</th><th>No Nota</th><th>Customer</th>@if($jenis === 'telur')<th>Tipe</th>@elseif($jenis === 'ayam')<th class="text-end">Qty Ekor</th>@else<th class="text-end">Qty Item</th>@endif<th class="text-end">Total Piutang</th><th>Status</th></tr></thead><tbody>@forelse($piutang as $i => $item)<tr><td class="text-center"><input type="checkbox" class="form-check-input nota-piutang" name="nota[]" value="{{ $item->no_nota }}" data-customer="{{ $item->id_customer }}"></td><td>{{ $i + 1 }}</td><td>{{ tanggal($item->tgl) }}</td><td class="fw-semibold">{{ $item->no_nota }}</td><td>{{ $item->nm_customer ?? '-' }}</td>@if($jenis === 'telur')<td>{{ strtoupper($item->tipe) }}</td>@else<td class="text-end">{{ number_format($item->qty,0,',','.') }}</td>@endif<td class="text-end receivable-total">Rp {{ number_format($item->total_rp,0,',','.') }}</td><td><span class="badge bg-danger">Belum Lunas</span></td></tr>@empty<tr><td colspan="8" class="receivable-empty">Tidak ada piutang {{ $jenis }} pada periode ini.</td></tr>@endforelse</tbody></table></div>
        </form>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const checks = [...document.querySelectorAll('.nota-piutang')];
                const button = document.getElementById('btnPelunasan');
                function syncSelection() {
                    const selected = checks.filter(item => item.checked);
                    const customer = selected.length ? selected[0].dataset.customer : null;
                    checks.forEach(item => { item.disabled = customer !== null && item.dataset.customer !== customer && !item.checked; });
                    button.disabled = selected.length === 0;
                }
                checks.forEach(item => item.addEventListener('change', syncSelection));
                syncSelection();
            });
        </script>
    </x-slot>
</x-theme.app>
