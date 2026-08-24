<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Daftar hutang dari faktur pembelian pakan dan vitamin</small>
            </div>
            <a href="{{ route('transaksi') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Transaksi
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .debt-page {
                --debt-primary: #29468f;
                --debt-border: #dce3f2;
                --debt-soft: #f5f7fc;
            }

            .debt-filter,
            .debt-summary {
                border: 1px solid var(--debt-border);
                border-radius: 12px;
                background: var(--debt-soft);
            }

            .debt-filter {
                padding: 14px;
                margin-bottom: 14px;
            }

            .debt-filter .form-label {
                margin-bottom: 5px;
                color: #536078;
                font-size: 12px;
                font-weight: 700;
            }

            .debt-summary {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1px;
                overflow: hidden;
                margin-bottom: 14px;
                background: var(--debt-border);
            }

            .debt-summary-item {
                padding: 14px;
                background: #fff;
            }

            .debt-summary-item .label {
                color: #637089;
                font-size: 12px;
                font-weight: 700;
            }

            .debt-summary-item .value {
                color: #1d3167;
                font-size: 20px;
                font-weight: 800;
            }

            .debt-nav {
                gap: 8px;
                margin-bottom: 14px;
            }

            .debt-nav .nav-link {
                border: 1px solid var(--debt-border);
                color: #536078;
                font-size: 13px;
                font-weight: 700;
            }

            .debt-nav .nav-link.active {
                border-color: var(--debt-primary);
                background: var(--debt-primary);
                color: #fff;
            }

            .debt-nav .badge {
                margin-left: 6px;
                background: rgba(255, 255, 255, .18);
                color: inherit;
            }

            .debt-table-wrap {
                overflow-x: auto;
                border: 1px solid var(--debt-border);
                border-radius: 12px;
            }

            .debt-table {
                min-width: 1060px;
                margin-bottom: 0;
            }

            .debt-table thead th {
                padding: 12px;
                color: #fff;
                background: var(--debt-primary);
                font-size: 12px;
                white-space: nowrap;
            }

            .status-badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 700;
                text-transform: capitalize;
                white-space: nowrap;
            }

            .status-lunas {
                color: #1e7e34;
                background: #d9f2df;
            }

            .status-belum_lunas {
                color: #b02a37;
                background: #fbdadd;
            }

            .status-sebagian {
                color: #9a6700;
                background: #fdf0d5;
            }

            .empty-debt {
                padding: 44px 20px !important;
                color: #66738a;
                text-align: center;
            }

            @media (max-width: 768px) {
                .debt-summary {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="debt-page">
            <form method="get" action="{{ route('transaksi.buku-hutang.index') }}" class="debt-filter">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-6">
                        <label class="form-label" for="tanggal_awal">Dari tanggal</label>
                        <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control"
                            value="{{ $tanggalAwal }}">
                    </div>
                    <div class="col-lg-3 col-6">
                        <label class="form-label" for="tanggal_akhir">Sampai tanggal</label>
                        <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control"
                            value="{{ $tanggalAkhir }}">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label" for="cari_hutang">Cari nota atau pemasok</label>
                        <input type="search" id="cari_hutang" name="cari" class="form-control"
                            value="{{ request('cari') }}" placeholder="Masukkan nomor nota atau nama pemasok">
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>

            <div class="debt-summary">
                <div class="debt-summary-item">
                    <div class="label">Total hutang faktur</div>
                    <div class="value">Rp {{ number_format($totalHutang, 0, ',', '.') }}</div>
                </div>
                <div class="debt-summary-item">
                    <div class="label">Sudah dibayar</div>
                    <div class="value">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
                </div>
                <div class="debt-summary-item">
                    <div class="label">Sisa hutang</div>
                    <div class="value">Rp {{ number_format($totalSisa, 0, ',', '.') }}</div>
                </div>
            </div>

            <ul class="nav nav-pills debt-nav">
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'berjalan' ? 'active' : '' }}"
                        href="{{ route('transaksi.buku-hutang.index', request()->except('page', 'status') + ['status' => 'berjalan']) }}">
                        Hutang berjalan
                        <span class="badge">{{ $jumlahBerjalan }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'lunas' ? 'active' : '' }}"
                        href="{{ route('transaksi.buku-hutang.index', request()->except('page', 'status') + ['status' => 'lunas']) }}">
                        Sudah lunas
                        <span class="badge">{{ $jumlahLunas }}</span>
                    </a>
                </li>
            </ul>

            <div class="debt-table-wrap">
                <table class="table table-hover align-middle debt-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Tanggal</th>
                            <th>Nomor Faktur</th>
                            <th>Jenis</th>
                            <th>Pemasok</th>
                            <th class="text-end">Total Faktur</th>
                            <th class="text-end">Sudah Dibayar</th>
                            <th class="text-end">Sisa Hutang</th>
                            <th>Status</th>
                            <th width="110" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($faktur as $nomor => $item)
                            @php
                                $sisaHutang = max((float) $item->sisa_hutang, 0);
                                $statusBayar = $sisaHutang <= 0 ? 'lunas' : ((float) $item->total_bayar > 0 ? 'sebagian' : 'belum_lunas');
                            @endphp
                            <tr>
                                <td>{{ $faktur->firstItem() + $nomor }}</td>
                                <td>{{ tanggal($item->tanggal_faktur) }}</td>
                                <td>{{ $item->no_faktur }}</td>
                                <td>{{ $item->jenis_faktur === 'vitamin' ? 'Vitamin' : 'Pakan' }}</td>
                                <td>{{ $item->supplier->nm_suplier ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($sisaHutang, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status-badge status-{{ $statusBayar }}">
                                        {{ str_replace('_', ' ', $statusBayar) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($sisaHutang > 0)
                                        <a href="{{ route('transaksi.buku-hutang.pelunasan', $item) }}"
                                            class="btn btn-primary btn-sm">
                                            Bayar
                                        </a>
                                    @else
                                        <span class="text-muted small">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="empty-debt">
                                    <strong class="d-block mb-1">Tidak ada data hutang</strong>
                                    <span>Data faktur sesuai filter akan muncul di sini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($faktur instanceof \Illuminate\Pagination\LengthAwarePaginator && $faktur->hasPages())
                <div class="mt-3">
                    {{ $faktur->links() }}
                </div>
            @endif
        </div>
    </x-slot>
</x-theme.app>
