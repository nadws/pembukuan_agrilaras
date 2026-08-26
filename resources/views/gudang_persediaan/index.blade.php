<x-theme.app title="{{ $title }}" table="T" sizeCard="12">
    <x-slot name="slot">
        <style>
            .warehouse-page {
                padding: 24px;
                border-radius: 16px;
                background: #fff
            }

            .warehouse-heading {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                gap: 14px;
                margin-bottom: 20px
            }

            .warehouse-heading h4 {
                margin: 0;
                color: #18366f;
                font-weight: 700
            }

            .warehouse-summary {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 20px
            }

            .warehouse-card {
                padding: 16px;
                border: 1px solid #dfe6f3;
                border-radius: 12px;
                background: #f7f9fd
            }

            .warehouse-card small {
                display: block;
                color: #6f7d96;
                font-weight: 600
            }

            .warehouse-card strong {
                display: block;
                margin-top: 5px;
                color: #18366f;
                font-size: 20px
            }

            .warehouse-table-wrap {
                overflow-x: auto;
                border: 1px solid #dfe6f3;
                border-radius: 12px
            }

            .warehouse-table {
                min-width: 940px;
                margin-bottom: 0
            }

            .warehouse-table thead th {
                padding: 12px;
                background: #304f9e;
                color: #fff;
                font-size: 12px;
                white-space: nowrap
            }

            .warehouse-table td {
                padding: 11px;
                vertical-align: middle
            }

            .warehouse-category {
                padding: 5px 9px;
                border-radius: 20px;
                background: #e7edfb;
                color: #304f9e;
                font-size: 11px;
                font-weight: 700
            }

            .warehouse-qty {
                font-size: 15px;
                font-weight: 700
            }

            @media(max-width:900px) {
                .warehouse-summary {
                    grid-template-columns: repeat(2, minmax(0, 1fr))
                }
            }

            @media(max-width:560px) {
                .warehouse-page {
                    padding: 16px
                }

                .warehouse-summary {
                    grid-template-columns: 1fr
                }
            }
        </style>
        <div class="warehouse-page">
            <div class="warehouse-heading">
                <div>
                    <h4>Gudang Produk Perencanaan</h4><small class="text-muted">Saldo berjalan pakan dan obat berdasarkan
                        seluruh mutasi gudang.</small>
                </div>
                <a href="{{ route('gudang-persediaan.opname') }}" class="btn btn-primary"><i
                        class="fas fa-clipboard-check me-1"></i> Mulai Stok Opname</a>
            </div>
            @include('gudang_persediaan.partials.nav')
            <div class="warehouse-summary">
                <div class="warehouse-card"><small>Total
                        produk</small><strong>{{ number_format($jumlahProduk, 0, ',', '.') }}</strong></div>
                <div class="warehouse-card"><small>Produk
                        tersedia</small><strong>{{ number_format($produkAdaStok, 0, ',', '.') }}</strong></div>
                <div class="warehouse-card"><small>Stok
                        kosong/minus</small><strong>{{ number_format($produkKosong, 0, ',', '.') }}</strong></div>
                <div class="warehouse-card"><small>Opname terakhir</small><strong
                        style="font-size:16px">{{ $opnameTerakhir ? tanggal($opnameTerakhir) : 'Belum ada' }}</strong>
                </div>
            </div>
            <div class="warehouse-table-wrap">
                <table class="table table-hover warehouse-table" id="table1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Kode Accurate</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th class="text-end">Stok Saat Ini</th>
                            <th class="text-end">Nilai Persediaan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stok as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $item->nm_produk }}</td>
                                <td>{{ $item->kode_accurate ?: '-' }}</td>
                                <td><span
                                        class="warehouse-category">{{ ucwords(str_replace('_', ' ', $item->kategori)) }}</span>
                                </td>
                                <td>{{ $item->nm_satuan ?: '-' }}</td>
                                <td class="text-end warehouse-qty {{ $item->stok < 0 ? 'text-danger' : '' }}">
                                    {{ number_format($item->stok, 0) }}</td>
                                <td class="text-end">Rp {{ number_format(max(0, $item->nilai_stok), 0) }}</td>
                                <td>
                                    @if ($item->stok > 0)
                                        <span class="badge bg-success">Tersedia</span>
                                    @elseif($item->stok < 0)
                                    <span class="badge bg-danger">Minus</span>@else<span
                                            class="badge bg-secondary">Kosong</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </x-slot>
</x-theme.app>
