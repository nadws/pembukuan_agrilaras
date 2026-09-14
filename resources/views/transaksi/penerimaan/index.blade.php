<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Pilih satu atau beberapa nota pembelian untuk diterima stoknya</small>
            </div>
            <a href="{{ route('transaksi') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Transaksi
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .receive-index {
                --receive-primary: #29468f;
                --receive-border: #dce3f2;
                --receive-soft: #f5f7fc;
            }

            .receive-filter {
                padding: 14px;
                margin-bottom: 16px;
                border: 1px solid var(--receive-border);
                border-radius: 12px;
                background: var(--receive-soft);
            }

            .receive-filter .form-label {
                margin-bottom: 5px;
                color: #536078;
                font-size: 12px;
                font-weight: 700;
            }

            .receive-table-wrap {
                overflow-x: auto;
                border: 1px solid var(--receive-border);
                border-radius: 12px;
            }

            .receive-table {
                min-width: 920px;
                margin-bottom: 0;
            }

            .receive-table thead th {
                padding: 12px;
                color: #fff;
                background: var(--receive-primary);
                font-size: 12px;
                white-space: nowrap;
            }

            .empty-receive {
                padding: 48px 20px !important;
                color: #66738a;
                text-align: center;
            }

            .receive-nav {
                gap: 8px;
                margin-bottom: 14px;
            }

            .receive-nav .nav-link {
                border: 1px solid var(--receive-border);
                color: #536078;
                font-size: 13px;
                font-weight: 700;
            }

            .receive-nav .nav-link.active {
                border-color: var(--receive-primary);
                background: var(--receive-primary);
                color: #fff;
            }

        </style>

        <div class="receive-index">
            <form method="get" action="{{ route('transaksi.penerimaan.index') }}" class="receive-filter">
                <input type="hidden" name="status" value="{{ $statusPenerimaan }}">
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
                        <label class="form-label" for="cari_faktur">Cari nota atau pemasok</label>
                        <input type="search" id="cari_faktur" name="cari" class="form-control"
                            value="{{ request('cari') }}" placeholder="Masukkan nomor nota atau nama pemasok">
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>

            <ul class="nav nav-pills receive-nav">
                <li class="nav-item">
                    <a class="nav-link {{ $statusPenerimaan === 'belum' ? 'active' : '' }}"
                        href="{{ route('transaksi.penerimaan.index', request()->except('page', 'status') + ['status' => 'belum']) }}">
                        Nota belum habis ({{ $jumlahBelumHabis }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $statusPenerimaan === 'selesai' ? 'active' : '' }}"
                        href="{{ route('transaksi.penerimaan.index', request()->except('page', 'status') + ['status' => 'selesai']) }}">
                        Nota sudah habis diambil ({{ $jumlahSudahHabis }})
                    </a>
                </li>
            </ul>

            @if ($statusPenerimaan === 'belum')
                <form method="GET" action="{{ route('transaksi.penerimaan.terima') }}" id="form-terima-batch">
            @endif
                @if ($statusPenerimaan === 'belum')
                    <div class="d-flex justify-content-end mb-2">
                        <button type="submit" class="btn btn-primary btn-sm" id="btn-terima-batch" disabled>
                            <i class="fas fa-boxes me-1"></i> Terima Stok Terpilih
                        </button>
                    </div>
                @endif

                <div class="receive-table-wrap">
                    <table class="table table-hover align-middle receive-table">
                        <thead>
                            <tr>
                                <th width="55">No</th>
                                <th>Tanggal</th>
                                <th>Nomor Nota</th>
                                <th>Jenis</th>
                                <th>Pemasok</th>
                                <th class="text-end">Qty Faktur</th>
                                <th class="text-end">Sudah Diterima</th>
                                <th class="text-end">Sisa</th>
                                <th class="text-end">Total</th>
                                <th width="120" class="text-center">
                                    @if ($statusPenerimaan === 'belum')
                                        <input type="checkbox" class="form-check-input" id="check-semua-faktur">
                                    @else
                                        Aksi
                                    @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($faktur as $nomor => $item)
                                @php
                                    $qtyDiterima = (float) ($penerimaanFaktur[$item->no_faktur] ?? 0);
                                    $qtySisa = max((float) $item->total_qty - $qtyDiterima, 0);
                                @endphp
                                <tr>
                                    <td>{{ $faktur->firstItem() + $nomor }}</td>
                                    <td>{{ tanggal($item->tanggal_faktur) }}</td>
                                    <td>{{ $item->no_faktur }}</td>
                                    <td>{{ $item->jenis_faktur === 'barang_umum' ? 'Barang Umum' : ($item->jenis_faktur === 'vaksin' ? 'Vaksin' : ($item->jenis_faktur === 'vitamin' ? 'Vitamin' : 'Pakan')) }}</td>
                                    <td>{{ $item->supplier->nm_suplier ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($item->total_qty, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($qtyDiterima, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($qtySisa, 2, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($statusPenerimaan === 'belum')
                                            <input type="checkbox" name="faktur[]" value="{{ $item->id }}"
                                                class="form-check-input check-faktur">
                                        @else
                                            <form method="POST" action="{{ route('transaksi.penerimaan.batalkan', $item->id) }}" onsubmit="return confirm('Batalkan penerimaan stok {{ $item->no_faktur }}? Stok yang diterima akan dikembalikan.')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-undo me-1"></i> Batalkan</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="empty-receive">
                                        @if ($statusPenerimaan === 'belum')
                                            <strong class="d-block mb-1">Tidak ada nota yang perlu diterima</strong>
                                            <span>Semua nota pada periode ini sudah selesai diterima stoknya.</span>
                                        @else
                                            <strong class="d-block mb-1">Belum ada nota yang sudah habis diambil</strong>
                                            <span>Nota yang penerimaan stoknya sudah lengkap akan muncul di sini.</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @if ($statusPenerimaan === 'belum')
                </form>
            @endif

            @if ($faktur instanceof \Illuminate\Pagination\LengthAwarePaginator && $faktur->hasPages())
                <div class="mt-3">
                    {{ $faktur->links() }}
                </div>
            @endif
        </div>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                const checkSemua = document.getElementById('check-semua-faktur');
                const tombolBatch = document.getElementById('btn-terima-batch');

                function updateTombolBatch() {
                    if (!tombolBatch) {
                        return;
                    }

                    const checked = document.querySelectorAll('.check-faktur:checked').length;
                    tombolBatch.disabled = checked === 0;
                }

                checkSemua?.addEventListener('change', function() {
                    document.querySelectorAll('.check-faktur').forEach((checkbox) => {
                        checkbox.checked = checkSemua.checked;
                    });
                    updateTombolBatch();
                });

                document.addEventListener('change', function(e) {
                    if (e.target.classList.contains('check-faktur')) {
                        updateTombolBatch();
                    }
                });
            })();
        </script>
    @endsection
</x-theme.app>
