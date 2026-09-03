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
            .setoran-page {
                --setoran-primary: #29468f;
                --setoran-border: #dce3f2;
                --setoran-soft: #f5f7fc;
            }

            .setoran-filter {
                padding: 14px;
                margin-bottom: 16px;
                border: 1px solid var(--setoran-border);
                border-radius: 12px;
                background: var(--setoran-soft);
                box-shadow: 0 4px 18px rgba(32, 55, 110, .06);
            }

            .setoran-filter .form-label {
                margin-bottom: 5px;
                color: #536078 !important;
                font-size: 12px;
                font-weight: 700;
            }

            .setoran-filter .form-control {
                min-height: 40px;
                border-color: var(--setoran-border);
                border-radius: 8px;
            }

            .setoran-table-wrap {
                overflow-x: auto;
                border: 1px solid var(--setoran-border);
                border-radius: 12px;
                box-shadow: 0 4px 18px rgba(32, 55, 110, .06);
            }

            .setoran-table {
                width: 100%;
                min-width: 1050px;
                margin-bottom: 0;
                font-size: 13px;
                border-collapse: separate;
                border-spacing: 0;
            }

            .setoran-table thead th {
                background: var(--setoran-primary) !important;
                color: #ffffff !important;
                font-weight: 700;
                font-size: 12.5px;
                padding: 12px 14px;
                border-bottom: 0 !important;
                white-space: nowrap;
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

            .aksi-buttons {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                white-space: nowrap;
            }

            .aksi-buttons form { margin: 0; }

            .setoran-empty {
                padding: 48px 20px !important;
                color: #66738a !important;
                text-align: center;
            }

            .setoran-empty i {
                display: block;
                margin-bottom: 10px;
                color: #8ca0ca;
                font-size: 28px;
            }

            .setoran-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-top: 16px;
            }

            .setoran-footer .pagination { justify-content: flex-end; margin-bottom: 0; }
            .setoran-footer .pagination .page-link { border: 0; margin: 0 2px; border-radius: 7px; color: #3455a1; }
            .setoran-footer .pagination .active .page-link { background: #3455a1; color: #fff; }

            @media (max-width: 767.98px) {
                .setoran-filter { padding: 11px; }
                .setoran-filter .btn { width: 100%; }
                .setoran-footer { align-items: flex-start; flex-direction: column; }
                .setoran-footer .pagination { justify-content: flex-start; }
            }
        </style>

        <div class="setoran-page">
            <form method="GET" action="{{ route('transaksi.setoran-kas.index') }}" class="setoran-filter">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-6">
                        <label class="form-label" for="tanggal_awal">Dari tanggal</label>
                        <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="{{ $tanggalAwal }}">
                    </div>
                    <div class="col-lg-3 col-6">
                        <label class="form-label" for="tanggal_akhir">Sampai tanggal</label>
                        <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="{{ $tanggalAkhir }}">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label" for="cari_setoran">Cari setoran atau akun tujuan</label>
                        <input type="search" id="cari_setoran" name="cari" class="form-control" value="{{ $cari }}"
                            placeholder="Nomor setoran, referensi, keterangan, atau akun">
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>

            <div class="setoran-table-wrap">
                <table class="setoran-table table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="55">No</th>
                        <th width="120">Tanggal Setoran</th>
                        <th width="170">No. Transaksi</th>
                        <th width="140">No. Referensi</th>
                        <th>Akun Tujuan</th>
                        <th width="130">Jumlah Item</th>
                        <th width="160" class="text-end">Nominal Total</th>
                        <th width="145" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($setoranKas as $item)
                        <tr>
                            <td>{{ ($setoranKas->firstItem() ?? 1) + $loop->index }}</td>
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
                            <td class="text-center text-nowrap">
                                <div class="aksi-buttons">
                                    <a href="{{ route('transaksi.setoran-kas.show', $item) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('transaksi.setoran-kas.cetak', $item) }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary" title="Cetak Bukti Setoran">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <form action="{{ route('transaksi.setoran-kas.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus setoran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="setoran-empty">
                                <i class="fas fa-search"></i>
                                <strong class="d-block mb-1">Data setoran tidak ditemukan</strong>
                                <span>Coba ubah periode atau kata pencarian.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>

            @if($setoranKas->total() > 0)
                <div class="setoran-footer">
                    <small class="text-muted">
                        Menampilkan {{ $setoranKas->firstItem() ?? 0 }}–{{ $setoranKas->lastItem() ?? 0 }}
                        dari {{ number_format($setoranKas->total(), 0, ',', '.') }} setoran
                    </small>
                    <div>{{ $setoranKas->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
                </div>
            @endif
        </div>
    </x-slot>
</x-theme.app>
