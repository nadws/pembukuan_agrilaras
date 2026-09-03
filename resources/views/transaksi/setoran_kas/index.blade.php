<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1" style="color: #0f172a !important; font-weight: 700;">{{ $title }}</h5>
                <small class="text-muted">Daftar setoran kas/bank dari penjualan telur</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('transaksi') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Transaksi
                </a>
                <a href="{{ route('transaksi.setoran-kas.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Buat Setoran Baru
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .setoran-table {
                width: 100%;
                font-size: 13px;
                border-collapse: separate;
                border-spacing: 0;
            }

            .setoran-table thead th {
                background: #f1f5f9 !important;
                color: #0f172a !important;
                font-weight: 700;
                font-size: 12.5px;
                padding: 10px 14px;
                border-bottom: 2px solid #cbd5e1 !important;
            }

            .setoran-table tbody td {
                padding: 10px 14px;
                vertical-align: middle;
                border-bottom: 1px solid #e2e8f0;
                color: #0f172a !important;
                background-color: #ffffff !important;
            }

            .setoran-table tbody tr:hover td {
                background-color: #f8fafc !important;
            }

            .akun-tujuan-badge {
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

            .akun-tujuan-nama {
                display: block;
                color: #0f172a !important;
                font-weight: 600;
                font-size: 13px;
                line-height: 1.3;
            }

            .filter-card label {
                color: #0f172a !important;
                font-weight: 600;
            }
        </style>

        <form method="GET" class="mb-3 filter-card">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label mb-1">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" class="form-control" value="{{ $tanggalAwal }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" class="form-control" value="{{ $tanggalAkhir }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="setoran-table table table-hover">
                <thead>
                    <tr>
                        <th width="120">Tanggal Setoran</th>
                        <th width="170">No. Transaksi</th>
                        <th width="140">No. Referensi</th>
                        <th>Akun Tujuan</th>
                        <th width="130">Jumlah Item</th>
                        <th width="160" class="text-end">Nominal Total</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($setoranKas as $item)
                        <tr>
                            <td style="color: #0f172a !important; font-weight: 500;">
                                {{ $item->tanggal_setoran->format('d/m/Y') }}
                            </td>
                            <td style="color: #0f172a !important; font-weight: 700;">
                                {{ $item->nomor_setoran ?? 'SK-' . $item->id }}
                            </td>
                            <td style="color: #334155 !important;">
                                {{ $item->nomor_referensi ?? '-' }}
                            </td>
                            <td>
                                <span class="akun-tujuan-badge">{{ $item->akunTujuan->kode_perkiraan ?? '-' }}</span>
                                <span class="akun-tujuan-nama">{{ $item->akunTujuan->nama ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="akun-tujuan-badge">{{ $item->detail->count() }}
                                    transaksi</span>
                            </td>
                            <td class="text-end"
                                style="color: #0f172a !important; font-weight: 700; font-size: 13.5px;">
                                Rp {{ number_format($item->nominal_total, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('transaksi.setoran-kas.show', $item) }}" class="btn btn-sm btn-info"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('transaksi.setoran-kas.destroy', $item) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus setoran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Tidak ada data setoran kas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $setoranKas->links() }}
        </div>
    </x-slot>
</x-theme.app>
