<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h5 class="mb-1">Penjualan Umum Martadah</h5><small class="text-muted">Data penjualan umum dari lokasi Martadah</small></div>
            <a href="{{ route('penjualan_martadah') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Penjualan Martadah</a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .mtd-general-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px}.mtd-general-summary .summary-item{padding:15px 17px;border:1px solid #dce4f2;border-radius:12px;background:#f7f9fd}.mtd-general-summary small{display:block;color:#74829a;font-size:11px;font-weight:700;letter-spacing:.3px;text-transform:uppercase}.mtd-general-summary strong{display:block;margin-top:6px;color:#18366f;font-size:20px}.mtd-general-filter{padding:14px;margin-bottom:16px;border:1px solid #dce4f2;border-radius:12px;background:#f7f9fd}.mtd-general-filter .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}.mtd-general-filter .form-control,.mtd-general-filter .form-select{min-height:40px;border-color:#dce4f2;border-radius:8px}.mtd-general-table-wrap{overflow-x:auto;border:1px solid #dce4f2;border-radius:12px}.mtd-general-table{min-width:960px;margin-bottom:0}.mtd-general-table thead th{padding:12px 10px;background:#304f9e!important;color:#fff!important;border-color:#4966ad;font-size:12px;white-space:nowrap;vertical-align:middle}.mtd-general-table td{padding:11px 10px;vertical-align:middle}.mtd-general-table tbody tr:hover td{background:#f2f6ff}.mtd-general-nota{color:#18366f;font-weight:700;white-space:nowrap}.mtd-general-customer{display:block;color:#64728a;font-size:12px}.mtd-general-status{display:inline-flex;align-items:center;gap:5px;padding:4px 9px;border-radius:7px;font-size:11px;font-weight:800;white-space:nowrap}.mtd-general-status.checked{color:#176b38;background:#d9f2df}.mtd-general-status.pending{color:#9a5b00;background:#fff0c7}.mtd-general-pagination .pagination{margin-bottom:0}@media(max-width:767px){.mtd-general-summary{grid-template-columns:1fr}.mtd-general-summary strong{font-size:18px}}
        </style>

        <div class="mtd-general-summary">
            <div class="summary-item"><small>Jumlah nota ditemukan</small><strong>{{ number_format($penjualan->total(), 0, ',', '.') }}</strong></div>
            <div class="summary-item"><small>Total penjualan</small><strong>Rp {{ number_format($ttlRp, 0, ',', '.') }}</strong></div>
            <div class="summary-item"><small>Belum diperiksa</small><strong>Rp {{ number_format($ttlRpBelumDiCek, 0, ',', '.') }}</strong></div>
        </div>

        <form method="GET" action="{{ route('penjualan_martadah_umum') }}" class="mtd-general-filter">
            <input type="hidden" name="period" value="costume">
            <div class="row g-2 align-items-end">
                <div class="col-lg-2 col-6"><label class="form-label">Dari tanggal</label><input type="date" name="tgl1" class="form-control" value="{{ $tgl1 }}"></div>
                <div class="col-lg-2 col-6"><label class="form-label">Sampai tanggal</label><input type="date" name="tgl2" class="form-control" value="{{ $tgl2 }}"></div>
                <div class="col-lg-4"><label class="form-label">Cari nota atau pelanggan</label><input type="search" name="pencarian" class="form-control" value="{{ $pencarian }}" placeholder="Masukkan nomor nota atau nama pelanggan"></div>
                <div class="col-lg-2 col-6"><label class="form-label">Tampilkan</label><select name="per_page" class="form-select">@foreach ([20, 50, 100] as $jumlah)<option value="{{ $jumlah }}" @selected($perPage === $jumlah)>{{ $jumlah }} data</option>@endforeach</select></div>
                <div class="col-lg-2 col-6"><button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan</button></div>
            </div>
        </form>

        <div class="mtd-general-table-wrap">
            <table class="table table-striped table-hover mtd-general-table">
                <thead><tr><th class="text-center">No</th><th>Tanggal</th><th>Nota / Pelanggan</th><th class="text-center">Jumlah Item</th><th class="text-end">Total Qty</th><th class="text-end">Total Rp</th><th class="text-center">Status</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($penjualan as $no => $d)
                        <tr>
                            <td class="text-center text-muted">{{ ($penjualan->firstItem() ?? 1) + $no }}</td><td class="text-nowrap">{{ tanggal($d->tgl) }}</td>
                            <td><span class="mtd-general-nota">{{ $d->kode }}-{{ $d->urutan }}</span><span class="mtd-general-customer">{{ $d->nm_customer }}</span></td>
                            <td class="text-center">{{ number_format($d->ttl_produk, 0, ',', '.') }}</td><td class="text-end">{{ number_format($d->total_qty, 0, ',', '.') }}</td><td class="text-end fw-semibold text-nowrap">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                            <td class="text-center">@if ($d->cek === 'Y')<span class="mtd-general-status checked"><i class="fas fa-check"></i> Diperiksa</span>@else<span class="mtd-general-status pending"><i class="fas fa-clock"></i> Belum</span>@endif</td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-outline-primary btn-sm detail-nota" data-url="{{ route('penjualan_umum_mtd.detail', ['urutan' => $d->urutan]) }}" data-bs-toggle="modal" data-bs-target="#detailNota" title="Detail"><i class="fas fa-eye"></i></button>
                                @if ($d->cek !== 'Y')<a class="btn btn-primary btn-sm" href="{{ route('terima_invoice_umum_cek', ['no_nota' => [$d->urutan]]) }}"><i class="fas fa-plus me-1"></i> Periksa</a>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">Belum ada penjualan umum Martadah pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mtd-general-pagination d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"><small class="text-muted">Menampilkan {{ $penjualan->firstItem() ?? 0 }}–{{ $penjualan->lastItem() ?? 0 }} dari {{ number_format($penjualan->total(), 0, ',', '.') }} nota</small>{{ $penjualan->onEachSide(1)->links('pagination::bootstrap-5') }}</div>

        <div class="modal fade" id="detailNota" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Detail Penjualan Umum Martadah</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="detailNotaBody"><div class="text-center text-muted py-5">Memuat detail...</div></div></div></div></div>
        <script>
            document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.detail-nota').forEach(function(button){button.addEventListener('click',function(){const body=document.getElementById('detailNotaBody');body.innerHTML='<div class="text-center text-muted py-5">Memuat detail...</div>';fetch(this.dataset.url,{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(response=>{if(!response.ok)throw new Error();return response.text()}).then(html=>body.innerHTML=html).catch(()=>body.innerHTML='<div class="alert alert-danger mb-0">Detail penjualan tidak dapat dimuat.</div>')})})});
        </script>
    </x-slot>
</x-theme.app>
