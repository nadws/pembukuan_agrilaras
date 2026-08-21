<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Daftar transaksi pembelian dari pemasok</small>
            </div>
            <div>
                <a href="{{ route('transaksi') }}" class="btn btn-outline-primary btn-sm ">
                    <i class="fas fa-arrow-left me-1"></i> Transaksi
                </a>
                <a href="{{ route('transaksi.faktur-pembelian.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Transaksi
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .purchase-invoice-page {
                --invoice-primary: #29468f;
                --invoice-border: #dce3f2;
                --invoice-soft: #f5f7fc;
            }

            .purchase-filter {
                padding: 14px;
                margin-bottom: 16px;
                border: 1px solid var(--invoice-border);
                border-radius: 12px;
                background: var(--invoice-soft);
            }

            .purchase-filter .form-label {
                margin-bottom: 5px;
                color: #536078;
                font-size: 12px;
                font-weight: 700;
            }

            .purchase-filter .form-control {
                min-height: 40px;
                border-color: var(--invoice-border);
                border-radius: 8px;
            }

            .purchase-table-wrap {
                overflow-x: auto;
                border: 1px solid var(--invoice-border);
                border-radius: 12px;
            }

            .purchase-table {
                min-width: 850px;
                margin-bottom: 0;
            }

            .purchase-table thead th {
                padding: 12px;
                border-color: #4f69b6;
                color: #fff;
                background: var(--invoice-primary);
                font-size: 12px;
                white-space: nowrap;
            }

            .empty-invoice {
                padding: 52px 20px !important;
                color: #66738a;
                text-align: center;
            }

            .empty-invoice .empty-icon {
                display: grid;
                width: 58px;
                height: 58px;
                margin: 0 auto 12px;
                border-radius: 16px;
                color: var(--invoice-primary);
                background: #e8edfa;
                font-size: 24px;
                place-items: center;
            }

            .status-badge {
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 700;
                white-space: nowrap;
                text-transform: capitalize;
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

            @media (max-width: 576px) {
                .purchase-filter {
                    padding: 11px;
                }

                .purchase-filter .btn {
                    width: 100%;
                }

                .empty-invoice {
                    padding: 38px 16px !important;
                }
            }
        </style>

        <div class="purchase-invoice-page">
            <form method="get" action="{{ route('transaksi.faktur-pembelian.index') }}" class="purchase-filter">
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
                        <label class="form-label" for="cari_faktur">Cari faktur atau pemasok</label>
                        <input type="search" id="cari_faktur" name="cari" class="form-control"
                            value="{{ request('cari') }}" placeholder="Masukkan nomor faktur atau nama pemasok">
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>

            <div class="purchase-table-wrap">
                <table class="table table-hover align-middle purchase-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Tanggal</th>
                            <th>Nomor Faktur</th>
                            <th>Jenis</th>
                            <th>Pemasok</th>
                            {{-- <th>Jatuh Tempo</th> --}}
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th width="90" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($faktur as $nomor => $item)
                            <tr>
                                <td>{{ $faktur->firstItem() + $nomor }}</td>
                                <td>{{ tanggal($item->tanggal_faktur) }}</td>
                                <td>{{ $item->no_faktur }}</td>
                                <td>
                                    <span
                                        class="status-badge status-{{ $item->jenis_faktur === 'vitamin' ? 'sebagian' : 'lunas' }}">
                                        {{ $item->jenis_faktur === 'vitamin' ? 'Vitamin' : 'Pakan' }}
                                    </span>
                                </td>
                                <td>{{ $item->supplier->nm_suplier ?? '-' }}</td>
                                {{-- <td>{{ tanggal($item->jatuh_tempo) }}</td> --}}
                                <td class="text-end">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status-badge status-{{ $item->status_bayar }}">
                                        {{ str_replace('_', ' ', $item->status_bayar) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-outline-primary btn-sm" title="Lihat detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-invoice">
                                    <span class="empty-icon"><i class="fas fa-file-invoice"></i></span>
                                    <strong class="d-block mb-1">Belum ada faktur pembelian</strong>
                                    <span>Halaman siap digunakan setelah form dan struktur data faktur
                                        ditambahkan.</span>
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
