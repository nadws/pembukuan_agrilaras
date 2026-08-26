<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Terima stok dari {{ $fakturs->count() }} nota pembelian</small>
            </div>
            <a href="{{ route('transaksi.penerimaan.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .batch-receive {
                --receive-primary: #29468f;
                --receive-border: #dce3f2;
                --receive-soft: #f5f7fc;
            }

            .nota-block {
                margin-bottom: 18px;
                border: 1px solid var(--receive-border);
                border-radius: 12px;
                overflow: hidden;
                background: #fff;
            }

            .nota-head {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 8px;
                padding: 12px 14px;
                background: var(--receive-soft);
            }

            .nota-title {
                color: #263b78;
                font-weight: 800;
            }

            .nota-meta {
                color: #64728b;
                font-size: 12px;
                font-weight: 700;
            }

            .receive-table {
                min-width: 850px;
                margin-bottom: 0;
            }

            .receive-table-wrap {
                overflow-x: auto;
            }

            .receive-table thead th {
                padding: 11px;
                color: #fff;
                background: var(--receive-primary);
                font-size: 12px;
                white-space: nowrap;
            }

            .receive-table td {
                vertical-align: middle;
            }

            .batch-total {
                padding: 14px 18px;
                border: 1px solid var(--receive-border);
                border-radius: 12px;
                background: var(--receive-soft);
                text-align: right;
            }

            .batch-total .label {
                color: #64728b;
                font-size: 12px;
                font-weight: 700;
            }

            .batch-total .value {
                color: var(--receive-primary);
                font-size: 20px;
                font-weight: 800;
            }
        </style>

        <div class="batch-receive">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Ada kesalahan input:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-info">
                Qty pakan tetap diisi dalam zak. Saat masuk stok, 1 zak dihitung sebagai 50.000 gram.
            </div>

            <form method="POST" action="{{ route('transaksi.penerimaan.terima.store') }}">
                @csrf

                <div class="row align-items-end g-3 mb-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="tanggal_terima">Tanggal Terima</label>
                        <input type="date" id="tanggal_terima" name="tanggal_terima" class="form-control"
                            value="{{ old('tanggal_terima', now()->toDateString()) }}" required>
                    </div>
                </div>

                @foreach ($fakturs as $faktur)
                    <input type="hidden" name="faktur[]" value="{{ $faktur->id }}">
                    <div class="nota-block">
                        <div class="nota-head">
                            <div>
                                <div class="nota-title">{{ $faktur->no_faktur }}</div>
                                <div class="nota-meta">
                                    {{ tanggal($faktur->tanggal_faktur) }} |
                                    {{ $faktur->jenis_faktur === 'barang_umum' ? 'Barang Umum' : ($faktur->jenis_faktur === 'vaksin' ? 'Vaksin' : ($faktur->jenis_faktur === 'vitamin' ? 'Vitamin' : 'Pakan')) }} |
                                    {{ $faktur->supplier->nm_suplier ?? '-' }}
                                </div>
                            </div>
                            <div class="nota-meta text-end">
                                Total Rp {{ number_format($faktur->total_harga, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="receive-table-wrap">
                            <table class="table receive-table">
                                <thead>
                                    <tr>
                                        <th width="45">No</th>
                                        <th>Produk</th>
                                        <th class="text-end" width="120">Qty Faktur</th>
                                        <th class="text-end" width="130">Qty Diterima</th>
                                        <th width="110">Satuan</th>
                                        <th class="text-end" width="150">Harga Satuan</th>
                                        <th class="text-end" width="150">Subtotal Terima</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($faktur->detail as $no => $detail)
                                        @php
                                            $qtyDiterima = (float) data_get($qtyDiterimaByNota, $faktur->no_faktur . '.' . $detail->pakan_id, 0);
                                            $qtySisa = max((float) $detail->qty - $qtyDiterima, 0);
                                        @endphp
                                        <tr>
                                            <td>{{ $no + 1 }}</td>
                                            <td>
                                                <strong>{{ ($detail->sumber_produk ?? 'perencanaan') === 'barang_umum' ? ($detail->produkUmum->nm_produk ?? '-') : ($detail->produk->nm_produk ?? '-') }}</strong>
                                                <div class="text-muted small">{{ ($detail->sumber_produk ?? 'perencanaan') === 'barang_umum' ? 'Barang Umum' : ($detail->produk->kategori ?? '-') }}</div>
                                            </td>
                                            <td class="text-end">
                                                {{ number_format($detail->qty, 2, ',', '.') }}
                                                @if ($qtyDiterima > 0)
                                                    <div class="text-muted small">
                                                        sudah {{ number_format($qtyDiterima, 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0.01"
                                                    name="detail[{{ $detail->id }}][qty_diterima]"
                                                    class="form-control text-end qty-terima"
                                                    value="{{ old('detail.' . $detail->id . '.qty_diterima', $qtySisa) }}"
                                                    data-harga="{{ $detail->harga_satuan }}" required>
                                            </td>
                                            <td>{{ $detail->satuan ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                            <td class="text-end subtotal-terima">
                                                Rp {{ number_format($qtySisa * $detail->harga_satuan, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                <div class="row justify-content-end">
                    <div class="col-lg-4 col-md-6">
                        <div class="batch-total">
                            <div class="label">TOTAL STOK DITERIMA</div>
                            <div class="value" id="total-terima">Rp 0</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('transaksi.penerimaan.index') }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-boxes me-1"></i> Terima Stok
                    </button>
                </div>
            </form>
        </div>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                const totalTerima = document.getElementById('total-terima');

                function formatRupiah(angka) {
                    return 'Rp ' + Number(angka || 0).toLocaleString('id-ID', {
                        maximumFractionDigits: 0
                    });
                }

                function hitungTotal() {
                    let total = 0;

                    document.querySelectorAll('.qty-terima').forEach((input) => {
                        const qty = parseFloat(input.value) || 0;
                        const harga = parseFloat(input.dataset.harga) || 0;
                        const subtotal = qty * harga;

                        input.closest('tr').querySelector('.subtotal-terima').textContent = formatRupiah(subtotal);
                        total += subtotal;
                    });

                    totalTerima.textContent = formatRupiah(total);
                }

                document.addEventListener('input', function(e) {
                    if (e.target.classList.contains('qty-terima')) {
                        hitungTotal();
                    }
                });

                hitungTotal();
            })();
        </script>
    @endsection
</x-theme.app>
