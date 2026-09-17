<x-theme.app title="Piutang" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h5 class="mb-1">Piutang</h5><small class="text-muted">Daftar piutang penjualan telur, ayam, dan umum</small></div>
            <div class="d-flex gap-2">@if(in_array($jenis,['telur','ayam'],true) && !empty($btnImport))<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="{{ $jenis === 'ayam' ? '#importPiutangAyamAccurate' : '#importPiutangAccurate' }}"><i class="fas fa-file-excel me-1"></i> Import Accurate</button>@endif @if(!empty($btnRiwayat))<button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalRiwayat"><i class="fas fa-history me-1"></i> Riwayat Pelunasan</button>@endif<a href="{{ route('transaksi') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Transaksi</a></div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .receivable-filter,.receivable-table-wrap{border:1px solid #dce3f2;border-radius:12px}.receivable-filter{padding:14px;margin-bottom:14px;background:#f5f7fc}.receivable-filter .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}.receivable-filter .form-control{min-height:40px;border-color:#dce3f2;border-radius:8px}.receivable-nav{gap:8px;margin-bottom:14px}.receivable-nav .nav-link{border:1px solid #dce3f2;color:#536078;font-size:13px;font-weight:700}.receivable-nav .nav-link.active{border-color:#29468f;background:#29468f;color:#fff}.receivable-summaries{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:14px}.receivable-summary{padding:16px 18px;border:1px solid #d7e1f5;border-radius:12px;background:linear-gradient(135deg,#f3f7ff,#fff)}.receivable-summary.paid{background:linear-gradient(135deg,#f0fbf5,#fff)}.receivable-summary.remaining{background:linear-gradient(135deg,#fff5f5,#fff)}.receivable-summary-label{color:#68758d;font-size:11px;font-weight:700;text-transform:uppercase}.receivable-summary-value{margin-top:3px;color:#193875;font-size:22px;font-weight:800}.receivable-summary.paid .receivable-summary-value{color:#198754}.receivable-summary.remaining .receivable-summary-value{color:#a12a35}.receivable-summary-count{margin-top:5px;color:#536078;font-size:12px;font-weight:700}            .receivable-table-wrap{overflow-x:auto}.receivable-table{min-width:1150px;margin-bottom:0}.receivable-table th:last-child,.receivable-table td:last-child{position:sticky;right:0;z-index:2;background:#fff;box-shadow:-2px 0 6px rgba(25,56,117,.08)}.receivable-table thead th:last-child{background:#29468f}.receivable-table thead th{padding:12px;color:#fff;background:#29468f;font-size:12px;white-space:nowrap}.receivable-total{color:#193875;font-weight:700}.receivable-paid{color:#198754;font-weight:700}.receivable-remaining{color:#a12a35;font-weight:800}.receivable-empty{padding:46px 20px!important;color:#66738a;text-align:center}@media(max-width:767.98px){.receivable-summaries{grid-template-columns:1fr}.receivable-summary{padding:13px 15px}.receivable-summary-value{font-size:20px}}
        </style>
        @if(isset($errors) && $errors->any())<div class="alert alert-danger"><strong>Data belum dapat diimpor.</strong><br>{{ $errors->first() }}</div>@endif
        <form method="GET" action="{{ route('transaksi.piutang.index') }}" class="receivable-filter">
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <div class="row g-2 align-items-end"><div class="col-lg-8"><label class="form-label">Cari nota atau customer</label><input type="search" name="cari" class="form-control" value="{{ $cari }}" placeholder="Masukkan nomor nota atau nama customer"></div><div class="col-lg-2"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Cari</button></div></div>
        </form>
        <ul class="nav nav-pills receivable-nav"><li class="nav-item"><a class="nav-link {{ $jenis === 'telur' ? 'active' : '' }}" href="{{ route('transaksi.piutang.index',$tabFilters['telur']) }}"><i class="fas fa-egg me-1"></i> Piutang Telur</a></li><li class="nav-item"><a class="nav-link {{ $jenis === 'ayam' ? 'active' : '' }}" href="{{ route('transaksi.piutang.index',$tabFilters['ayam']) }}"><i class="fas fa-drumstick-bite me-1"></i> Piutang Ayam</a></li><li class="nav-item"><a class="nav-link {{ $jenis === 'umum' ? 'active' : '' }}" href="{{ route('transaksi.piutang.index',$tabFilters['umum']) }}"><i class="fas fa-shopping-basket me-1"></i> Piutang Umum</a></li></ul>
        <div class="receivable-summaries">
            <div class="receivable-summary"><div class="receivable-summary-label">Total Piutang</div><div class="receivable-summary-value">Rp {{ number_format($totalNilaiPiutang,0,'.',',') }}</div><div class="receivable-summary-count"><i class="fas fa-file-invoice-dollar me-1"></i> {{ number_format($jumlahFaktur,0,'.',',') }} nota {{ $jenis }}</div></div>
            <div class="receivable-summary paid"><div class="receivable-summary-label">Sudah Dibayar</div><div class="receivable-summary-value">Rp {{ number_format($totalDibayar,0,'.',',') }}</div><div class="receivable-summary-count">Akumulasi cicilan nota aktif</div></div>
            <div class="receivable-summary remaining"><div class="receivable-summary-label">Sisa Piutang</div><div class="receivable-summary-value">Rp {{ number_format($totalPiutang,0,'.',',') }}</div><div class="receivable-summary-count">Jumlah yang masih harus dibayar</div></div>
        </div>
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
            <div class="d-flex justify-content-between align-items-center mb-3"><span class="text-muted small">Pilih satu atau beberapa nota dari customer yang sama.</span>@if(!empty($btnPelunasan))<button type="submit" class="btn btn-success" id="btnPelunasan" disabled><i class="fas fa-arrow-right me-1"></i> Lanjutkan Pelunasan</button>@endif</div>
            <div class="receivable-table-wrap"><table class="table table-hover align-middle receivable-table"><thead><tr><th>No</th><th>Tanggal</th><th>No Nota</th><th>Customer</th>@if($jenis === 'telur')<th>Tipe</th>@elseif($jenis === 'ayam')<th class="text-end">Qty Ekor</th>@else<th class="text-end">Qty Item</th>@endif<th class="text-end">Total Piutang</th><th class="text-end">Sudah Dibayar</th><th class="text-end">Sisa Piutang</th><th>Status</th><th class="text-center">Pilih</th></tr></thead><tbody>@forelse($piutang as $i => $item)<tr><td>{{ $i + 1 }}</td><td>{{ tanggal($item->tgl) }}</td><td class="fw-semibold">{{ $item->no_nota }}</td><td>{{ $item->nm_customer ?? '-' }}</td>@if($jenis === 'telur')<td>{{ strtoupper($item->tipe) }}</td>@else<td class="text-end">{{ number_format($item->qty,0,'.',',') }}</td>@endif<td class="text-end receivable-total">Rp {{ number_format($item->nilai_piutang,0,'.',',') }}</td><td class="text-end receivable-paid">Rp {{ number_format($item->jumlah_dibayar,0,'.',',') }}</td><td class="text-end receivable-remaining">Rp {{ number_format($item->sisa_piutang,0,'.',',') }}</td><td>@if($item->jumlah_dibayar > 0) Dicicil @else Belum Dibayar @endif</td><td class="text-center"><input type="checkbox" class="form-check-input nota-piutang" name="nota[]" value="{{ $item->no_nota }}" data-customer="{{ $item->id_customer }}"></td></tr>@empty<tr><td colspan="10" class="receivable-empty">Tidak ada piutang {{ $jenis }} pada periode ini.</td></tr>@endforelse</tbody></table></div>
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
                const cariRiwayat = document.getElementById('cariRiwayat');
                if (cariRiwayat) {
                    const barisRiwayat = [...document.querySelectorAll('#tabelRiwayat tbody .riwayat-master-row')];
                    cariRiwayat.addEventListener('input', function () {
                        const q = this.value.toLowerCase().trim();
                        barisRiwayat.forEach(r => {
                            const detail = r.nextElementSibling;
                            const match = q === '' || (r.textContent + ' ' + (detail?.textContent ?? '')).toLowerCase().includes(q);
                            r.classList.toggle('d-none', !match);
                            detail?.classList.add('d-none');
                            r.querySelector('.btn-detail-riwayat')?.setAttribute('aria-expanded', 'false');
                        });
                    });
                }
                document.querySelectorAll('.btn-detail-riwayat').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const detail = document.getElementById(this.dataset.target);
                        if (!detail) return;
                        detail.classList.toggle('d-none');
                        this.classList.toggle('active');
                        this.setAttribute('aria-expanded', detail.classList.contains('d-none') ? 'false' : 'true');
                    });
                });
            });
        </script>
        <x-theme.modal title="Riwayat Pelunasan" idModal="modalRiwayat" size="modal-xl" btnSave="N">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <small class="text-muted">Nota {{ $jenis }} yang sudah dilunasi/dicicil pada periode filter ({{ $awal }} s/d {{ $akhir }}) — klik edit untuk koreksi.</small>
                <span class="badge bg-success">Rp {{ number_format($totalRiwayat,0,'.',',') }}</span>
            </div>
            <div class="input-group mb-2"><span class="input-group-text"><i class="fas fa-search"></i></span><input type="search" id="cariRiwayat" class="form-control" placeholder="Cari nota, customer, atau akun..."></div>
            <div class="receivable-table-wrap">
                <table class="table table-hover align-middle receivable-table" id="tabelRiwayat">
                    <thead><tr><th>No</th><th>Tgl Bayar</th><th>Voucher Pelunasan</th><th>Customer</th><th>Akun Pembayaran</th><th class="text-end">Jumlah Bayar</th><th class="text-end">Nilai Dilunasi</th><th>Selisih</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($riwayat as $i => $row)
                            @php($detailId = 'riwayat-jurnal-'.$row->id)
                            <tr class="riwayat-master-row">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ tanggal($row->tanggal_bayar) }}</td>
                                <td class="fw-semibold">{{ $row->jurnal_detail->first()->nomor_transaksi ?? $row->daftar_nota }}</td>
                                <td>{{ $row->nm_customer ?? '-' }}</td>
                                <td>{{ trim(($row->kode_perkiraan ?? '').' - '.($row->nama_akun ?? ''), ' -') ?: '-' }}</td>
                                <td class="text-end receivable-paid">Rp {{ number_format($row->jumlah_bayar,0,'.',',') }}</td>
                                <td class="text-end">Rp {{ number_format($row->nilai_piutang_dilunasi,0,'.',',') }}</td>
                                <td>{{ $row->selisih_pembayaran > 0 ? 'Rp '.number_format($row->selisih_pembayaran,0,'.',',') : '-' }}</td>
                                <td><div class="d-flex gap-1 flex-nowrap"><button type="button" class="btn btn-outline-info btn-sm btn-detail-riwayat" data-target="{{ $detailId }}" title="Lihat nota dan jurnal" aria-expanded="false"><i class="fas fa-eye"></i></button><a href="{{ route('transaksi.piutang.pelunasan.voucher.edit', $row->id) }}" class="btn btn-outline-primary btn-sm" title="Edit pelunasan"><i class="fas fa-edit"></i></a></div></td>
                            </tr>
                            <tr id="{{ $detailId }}" class="d-none bg-light riwayat-detail-row">
                                <td colspan="9">
                                    <div class="p-2">
                                        <div class="small fw-bold text-primary mb-2">Nota dalam pelunasan ini</div>
                                        <div class="table-responsive mb-3"><table class="table table-sm table-bordered mb-0">
                                            <thead><tr><th>No Nota</th><th class="text-end">Bayar</th><th class="text-end">Dilunasi</th><th>Selisih</th></tr></thead>
                                            <tbody>@foreach($row->nota_rows as $notaRow)<tr>
                                                <td>{{ $notaRow->no_nota }}</td>
                                                <td class="text-end">Rp {{ number_format($notaRow->jumlah_bayar,0,'.',',') }}</td>
                                                <td class="text-end">Rp {{ number_format($notaRow->nilai_piutang_dilunasi,0,'.',',') }}</td>
                                                <td>{{ $notaRow->jenis_selisih === 'tidak' ? '-' : ucfirst($notaRow->jenis_selisih).' Rp '.number_format($notaRow->selisih_pembayaran,0,'.',',') }}</td>
                                            </tr>@endforeach</tbody>
                                        </table></div>
                                        <div class="small fw-bold text-primary mb-1">Jurnal {{ $row->jurnal_detail->first()->nomor_transaksi ?? '-' }}</div>
                                        <div class="table-responsive"><table class="table table-sm table-bordered mb-0">
                                            <thead><tr><th>Akun</th><th>Keterangan</th><th class="text-end">Debit</th><th class="text-end">Kredit</th></tr></thead>
                                            <tbody>@forelse($row->jurnal_detail as $jurnal)<tr>
                                                <td>{{ $jurnal->kode_perkiraan }} - {{ $jurnal->nama_akun }}</td><td>{{ $jurnal->deskripsi }}</td>
                                                <td class="text-end">Rp {{ number_format($jurnal->debit,0,'.',',') }}</td><td class="text-end">Rp {{ number_format($jurnal->kredit,0,'.',',') }}</td>
                                            </tr>@empty<tr><td colspan="4" class="text-muted text-center">Jurnal tidak tersedia.</td></tr>@endforelse</tbody>
                                        </table></div>
                                    </div>
                                </td>
                            </tr>
                        @empty<tr><td colspan="9" class="receivable-empty">Belum ada pelunasan {{ $jenis }} pada periode ini.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </x-theme.modal>
    </x-slot>
</x-theme.app>
