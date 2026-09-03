<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Detail setoran kas/bank tanggal {{ $setorKas->tanggal_setoran->format('d/m/Y') }}</small>
            </div>
            <a href="{{ route('transaksi.setoran-kas.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .tabel-detail {
                width: 100%;
                font-size: 13px;
            }
            .tabel-detail thead th {
                background: #f1f5f9 !important;
                color: #0f172a !important;
                font-weight: 700;
                padding: 10px 12px;
            }
            .tabel-detail tbody td {
                padding: 10px 12px;
                vertical-align: middle;
                color: #0f172a !important;
                background-color: #ffffff !important;
                border-bottom: 1px solid #e2e8f0;
            }
            .tabel-detail tfoot th {
                padding: 10px 12px;
                color: #0f172a !important;
                background: #f8fafc !important;
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
        </style>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border">
                    <div class="card-body">
                        <h6 class="card-title mb-3 fw-bold text-dark">Informasi Setoran</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Tanggal Setoran</small>
                            <strong class="text-dark">{{ $setorKas->tanggal_setoran->format('d F Y') }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Akun Tujuan</small>
                            <strong class="text-dark">{{ $setorKas->akunTujuan->kode_perkiraan ?? '-' }} - {{ $setorKas->akunTujuan->nama ?? '-' }}</strong>
                        </div>
                        @if($setorKas->nomor_referensi)
                            <div class="mb-2">
                                <small class="text-muted d-block">No. Referensi</small>
                                <strong class="text-dark">{{ $setorKas->nomor_referensi }}</strong>
                            </div>
                        @endif
                        @if($setorKas->keterangan)
                            <div class="mb-2">
                                <small class="text-muted d-block">Keterangan</small>
                                <strong class="text-dark">{{ $setorKas->keterangan }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card bg-primary text-white shadow-sm border-0">
                    <div class="card-body py-4">
                        <h6 class="card-title mb-2 text-white-50">Total Nominal Setoran</h6>
                        <h2 class="mb-2 text-white fw-bold">Rp {{ number_format($setorKas->nominal_total, 0, ',', '.') }}</h2>
                        <small class="text-white-50">{{ $setorKas->detail->count() }} transaksi penerimaan yang disetorkan</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <h6 class="fw-bold mb-3 text-dark">Rincian Jurnal Penjualan yang Disetorkan</h6>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle tabel-detail">
                    <thead>
                        <tr>
                            <th width="120">Tanggal</th>
                            <th width="150">No. Transaksi</th>
                            <th width="220">Akun Kas Sumber</th>
                            <th>Deskripsi</th>
                            <th width="160" class="text-end">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($setorKas->detail as $detail)
                            <tr>
                                <td>{{ $detail->jurnalPerkiraan->tanggal ? \Carbon\Carbon::parse($detail->jurnalPerkiraan->tanggal)->format('d/m/Y') : '-' }}</td>
                                <td><strong class="text-dark">{{ $detail->jurnalPerkiraan->nomor_transaksi ?? '-' }}</strong></td>
                                <td>
                                    <span class="akun-badge">{{ $detail->akunSumber->kode_perkiraan ?? '-' }}</span>
                                    <span class="akun-nama">{{ $detail->akunSumber->nama ?? '-' }}</span>
                                </td>
                                <td>{{ $detail->jurnalPerkiraan->deskripsi ?? '-' }}</td>
                                <td class="text-end fw-bold text-dark">
                                    Rp {{ number_format($detail->nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end fw-bold">Total</th>
                            <th class="text-end fw-bold text-primary">
                                Rp {{ number_format($setorKas->detail->sum('nominal'), 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <form action="{{ route('transaksi.setoran-kas.destroy', $setorKas) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus setoran ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Hapus Setoran
                </button>
            </form>
            <a href="{{ route('transaksi.setoran-kas.index') }}" class="btn btn-outline-secondary">
                Kembali
            </a>
        </div>
    </x-slot>
</x-theme.app>
