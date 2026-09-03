<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1" style="color: #0f172a !important; font-weight: 700;">{{ $title }}</h5>
                <span style="color: #64748b !important; font-size: 13px; font-weight: 500;">
                    Nomor Setoran: <strong style="color: #1e40af !important;">{{ $setorKas->nomor_setoran ?? '-' }}</strong> &bull; 
                    Tanggal: <strong style="color: #0f172a !important;">{{ $setorKas->tanggal_setoran->format('d/m/Y') }}</strong>
                </span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('transaksi.setoran-kas.cetak', $setorKas) }}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="fas fa-print me-1"></i> Cetak
                </a>
                <a href="{{ route('transaksi.setoran-kas.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .info-card-wrap {
                background: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            }
            .info-card-header {
                color: #0f172a !important;
                font-size: 15px;
                font-weight: 700;
                margin-bottom: 14px;
                padding-bottom: 8px;
                border-bottom: 1px solid #f1f5f9;
            }
            .info-field-label {
                color: #64748b !important;
                font-size: 12px;
                font-weight: 600;
                margin-bottom: 2px;
                display: block;
            }
            .info-field-val {
                color: #0f172a !important;
                font-size: 14px;
                font-weight: 700;
            }
            .summary-banner {
                background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important;
                border-radius: 12px;
                padding: 24px;
                color: #ffffff !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            }
            .summary-banner-label {
                color: #dbeafe !important;
                font-size: 13px;
                font-weight: 600;
                margin-bottom: 6px;
            }
            .summary-banner-val {
                color: #ffffff !important;
                font-size: 32px;
                font-weight: 800;
                margin-bottom: 6px;
                letter-spacing: -0.5px;
            }
            .summary-banner-sub {
                color: #bfdbfe !important;
                font-size: 13px;
                font-weight: 500;
            }

            .tabel-detail {
                width: 100%;
                font-size: 13px;
                border-collapse: separate;
                border-spacing: 0;
            }
            .tabel-detail thead th {
                background: #f1f5f9 !important;
                color: #0f172a !important;
                font-weight: 700;
                font-size: 12.5px;
                padding: 10px 14px;
                border-bottom: 2px solid #cbd5e1 !important;
            }
            .tabel-detail tbody td {
                padding: 10px 14px;
                vertical-align: middle;
                color: #0f172a !important;
                background-color: #ffffff !important;
                border-bottom: 1px solid #e2e8f0;
            }
            .tabel-detail tbody tr:hover td {
                background-color: #f8fafc !important;
            }
            .tabel-detail tfoot th {
                padding: 10px 14px;
                color: #0f172a !important;
                background: #f8fafc !important;
                border-top: 2px solid #cbd5e1 !important;
                font-weight: 700;
            }
            .akun-badge {
                display: inline-block;
                background-color: #e0e7ff !important;
                color: #1e40af !important;
                font-weight: 700;
                font-size: 11.5px;
                padding: 2px 7px;
                border-radius: 4px;
                margin-bottom: 3px;
                border: 1px solid #c7d2fe;
            }
            .akun-nama {
                display: block;
                color: #0f172a !important;
                font-weight: 600;
                font-size: 13px;
            }
            .deskripsi-text {
                color: #334155 !important;
                font-size: 13px;
            }
            .no-transaksi-text {
                color: #0f172a !important;
                font-weight: 700;
            }
            .nominal-text {
                color: #0f172a !important;
                font-weight: 700;
                font-size: 13.5px;
            }
            .tanggal-text {
                color: #0f172a !important;
                font-weight: 500;
            }
            .section-heading {
                color: #0f172a !important;
                font-weight: 700;
                font-size: 15px;
                margin-bottom: 12px;
            }
        </style>

        {{-- Info Cards Header --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="info-card-wrap h-100">
                    <div class="info-card-header">
                        <i class="fas fa-info-circle me-1 text-primary"></i> Informasi Setoran
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="info-field-label">Tanggal Setoran</span>
                            <span class="info-field-val">{{ $setorKas->tanggal_setoran->format('d F Y') }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="info-field-label">Nomor Setoran</span>
                            <span class="info-field-val text-primary">{{ $setorKas->nomor_setoran ?? '-' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="info-field-label">Akun Tujuan (Bank/Kas)</span>
                            <span class="akun-badge">{{ $setorKas->akunTujuan->kode_perkiraan ?? '-' }}</span>
                            <span class="info-field-val d-block">{{ $setorKas->akunTujuan->nama ?? '-' }}</span>
                        </div>
                        @if($setorKas->nomor_referensi)
                            <div class="col-sm-6">
                                <span class="info-field-label">No. Referensi</span>
                                <span class="info-field-val">{{ $setorKas->nomor_referensi }}</span>
                            </div>
                        @endif
                        @if($setorKas->keterangan)
                            <div class="col-12">
                                <span class="info-field-label">Keterangan</span>
                                <span class="info-field-val" style="font-weight: 500; color: #334155 !important;">{{ $setorKas->keterangan }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="summary-banner h-100 d-flex flex-column justify-content-center">
                    <div class="summary-banner-label">Total Nominal Setoran (Aktual Disetor)</div>
                    <div class="summary-banner-val">Rp {{ number_format($setorKas->nominal_total, 0, ',', '.') }}</div>
                    <div class="summary-banner-sub">
                        <i class="fas fa-check-circle me-1"></i> {{ $setorKas->detail->count() }} transaksi penerimaan yang disetorkan
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel 1: Rincian Jurnal Penjualan yang Disetorkan --}}
        <div class="mb-4">
            <h6 class="section-heading">
                <i class="fas fa-list me-1 text-primary"></i> Rincian Transaksi yang Disetorkan
            </h6>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle tabel-detail mb-0">
                    <thead>
                        <tr>
                            <th width="120">Tanggal</th>
                            <th width="150">No. Transaksi</th>
                            <th width="240">Akun Kas Sumber</th>
                            <th>Deskripsi</th>
                            <th width="170" class="text-end">Nominal Disetor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setorKas->detail as $detail)
                            <tr>
                                <td class="tanggal-text">
                                    {{ $detail->jurnalPerkiraan && $detail->jurnalPerkiraan->tanggal ? \Carbon\Carbon::parse($detail->jurnalPerkiraan->tanggal)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="no-transaksi-text">
                                    {{ $detail->jurnalPerkiraan->nomor_transaksi ?? '-' }}
                                </td>
                                <td>
                                    <span class="akun-badge">{{ $detail->akunSumber->kode_perkiraan ?? '-' }}</span>
                                    <span class="akun-nama">{{ $detail->akunSumber->nama ?? '-' }}</span>
                                </td>
                                <td class="deskripsi-text">
                                    {{ $detail->jurnalPerkiraan->deskripsi ?? '-' }}
                                </td>
                                <td class="text-end nominal-text">
                                    Rp {{ number_format($detail->nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Tidak ada rincian transaksi</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end" style="color: #0f172a !important;">Total Nominal Transaksi:</th>
                            <th class="text-end" style="color: #1e40af !important; font-size: 14.5px; font-weight: 800;">
                                Rp {{ number_format($setorKas->detail->sum('nominal'), 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Tabel 2: Jurnal Pembukuan yang Terbentuk --}}
        @if(isset($jurnalHasil) && $jurnalHasil->count() > 0)
            <div class="mb-4">
                <h6 class="section-heading">
                    <i class="fas fa-book me-1 text-primary"></i> Jurnal Perkiraan Pembukuan
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle tabel-detail mb-0">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th width="240">Akun Perkiraan</th>
                                <th>Keterangan Jurnal</th>
                                <th width="160" class="text-end">Debit</th>
                                <th width="160" class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jurnalHasil as $idx => $jh)
                                <tr>
                                    <td class="text-center" style="color: #64748b !important; font-weight: 600;">{{ $idx + 1 }}</td>
                                    <td>
                                        <span class="akun-badge">{{ $jh->kode_perkiraan }}</span>
                                        <span class="akun-nama">{{ $jh->nama_akun }}</span>
                                    </td>
                                    <td class="deskripsi-text">{{ $jh->deskripsi }}</td>
                                    <td class="text-end" style="font-weight: 700; color: {{ $jh->debit > 0 ? '#1e40af' : '#64748b' }} !important;">
                                        {{ $jh->debit > 0 ? 'Rp ' . number_format($jh->debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end" style="font-weight: 700; color: {{ $jh->kredit > 0 ? '#0f172a' : '#64748b' }} !important;">
                                        {{ $jh->kredit > 0 ? 'Rp ' . number_format($jh->kredit, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end" style="color: #0f172a !important;">Total Debit & Kredit:</th>
                                <th class="text-end" style="color: #1e40af !important; font-size: 14.5px; font-weight: 800;">
                                    Rp {{ number_format($jurnalHasil->sum('debit'), 0, ',', '.') }}
                                </th>
                                <th class="text-end" style="color: #1e40af !important; font-size: 14.5px; font-weight: 800;">
                                    Rp {{ number_format($jurnalHasil->sum('kredit'), 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="d-flex gap-2 justify-content-end pt-2">
            <form action="{{ route('transaksi.setoran-kas.destroy', $setorKas) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus setoran ini? Seluruh jurnal perkiraan terkait juga akan dihapus.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash me-1"></i> Hapus Setoran
                </button>
            </form>
            <a href="{{ route('transaksi.setoran-kas.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </x-slot>
</x-theme.app>
