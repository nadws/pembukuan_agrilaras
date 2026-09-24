<x-theme.app title="{{ $title }}" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Laporan Faktur Pajak</h5>
                <small class="text-muted">Daftar faktur pajak penjualan telur per nota &middot; {{ tanggal($tgl1) }} s/d {{ tanggal($tgl2) }}</small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if(!empty($btnExport))
                <a href="{{ route('laporan.faktur-pajak.export', ['tgl1' => $tgl1, 'tgl2' => $tgl2, 'npwp' => $npwpPenjual]) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Coretax (XLSX)</a>
                @endif
                <a href="{{ route('laporan') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Daftar Laporan</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .fp-filter{padding:14px;border:1px solid #dce4f2;border-radius:12px;background:#f7f9fd}
            .fp-kpi{height:100%;padding:14px 16px;border:1px solid #e1e7f2;border-radius:12px;background:#fff;box-shadow:0 5px 15px rgba(35,60,115,.06)}
            .fp-kpi small{display:block;color:#7583a0;font-size:10px;font-weight:700;text-transform:uppercase}
            .fp-kpi strong{display:block;margin-top:5px;color:#18366f;font-size:19px}
            .fp-table{overflow:auto;border:1px solid #dce4f2;border-radius:13px;max-height:560px}
            .fp-table table{margin:0;width:100%;font-size:12px}
            .fp-table thead th{padding:8px 6px;background:#304f9e;color:#fff;font-size:11px;white-space:nowrap;vertical-align:middle;position:sticky;top:0;z-index:2}
            .fp-table td{padding:7px 6px;vertical-align:middle}
            .fp-table tbody tr:nth-child(even){background:#f8fafd}
            .fp-table tfoot td{position:sticky;bottom:0;z-index:2;background:#eef3ff}
            .amount{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
        </style>
        <form method="get" class="fp-filter mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label fw-semibold">Dari tanggal</label><input type="date" name="tgl1" value="{{ $tgl1 }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Sampai tanggal</label><input type="date" name="tgl2" value="{{ $tgl2 }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">NPWP Penjual (16 digit)</label><input type="text" name="npwp" value="{{ $npwpPenjual }}" class="form-control" maxlength="16" inputmode="numeric"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button></div>
            </div>
        </form>
        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="fp-kpi"><small>Jumlah Nota</small><strong>{{ number_format($rows->count(), 0, ',', '.') }}</strong></div></div>
            <div class="col-md-4"><div class="fp-kpi"><small>Total DPP</small><strong>Rp {{ number_format($totalDpp, 0, ',', '.') }}</strong></div></div>
            <div class="col-md-4"><div class="fp-kpi"><small>Total PPN</small><strong>Rp {{ number_format($totalPpn, 0, ',', '.') }}</strong></div></div>
        </div>
        <div class="fp-table table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>No</th><th>Tanggal</th><th>No. Nota</th><th>Customer</th><th>NPWP / NIK</th><th class="amount">DPP (Rp)</th><th class="amount">PPN (Rp)</th></tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td style="white-space:nowrap">{{ tanggal($row->tgl) }}</td>
                            <td class="fw-semibold" style="white-space:nowrap">{{ $row->no_nota }}</td>
                            <td>{{ $row->customer }}</td>
                            <td style="white-space:nowrap">{{ $row->npwp !== '' && $row->npwp !== 'Null' ? $row->npwp : ($row->ktp !== '' ? $row->ktp : '-') }}</td>
                            <td class="amount">{{ number_format($row->dpp, 0, ',', '.') }}</td>
                            <td class="amount">{{ number_format($row->ppn, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada faktur pajak pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="amount">{{ number_format($totalDpp, 0, ',', '.') }}</td><td class="amount">{{ number_format($totalPpn, 0, ',', '.') }}</td></tr>
                </tfoot>
                @endif
            </table>
        </div>
        <small class="text-muted d-block mt-2">Format file mengikuti template upload Coretax pajak keluaran (sheet Faktur, DetailFaktur, REF, Keterangan). DPP Nilai Lain = DPP &divide; 1,12 sehingga DPP Nilai Lain + PPN (12%) = DPP per baris detail.</small>
    </x-slot>
</x-theme.app>
