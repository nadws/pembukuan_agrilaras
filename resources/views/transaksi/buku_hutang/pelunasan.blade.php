<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">{{ $tagihan->nomor_tagihan }} - {{ $komponen === 'pph23' ? 'Kas Negara — PPh 23' : ($faktur->supplier->nm_suplier ?? '-') }}</small>
            </div>
            <a href="{{ route('transaksi.buku-hutang.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Buku Hutang
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .pay-page {
                --pay-primary: #29468f;
                --pay-border: #dce3f2;
                --pay-soft: #f5f7fc;
            }

            .pay-grid {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 420px;
                gap: 16px;
            }

            .pay-panel {
                border: 1px solid var(--pay-border);
                border-radius: 12px;
                background: #fff;
                overflow: hidden;
            }

            .pay-panel-header {
                padding: 14px 16px;
                border-bottom: 1px solid var(--pay-border);
                background: var(--pay-soft);
            }

            .pay-panel-header h6 {
                margin: 0;
                color: #1d3167;
                font-weight: 800;
            }

            .pay-panel-body {
                padding: 16px;
            }

            .summary-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 14px;
            }

            .summary-box {
                padding: 12px;
                border: 1px solid var(--pay-border);
                border-radius: 10px;
                background: var(--pay-soft);
            }

            .summary-box .label {
                color: #637089;
                font-size: 12px;
                font-weight: 700;
            }

            .summary-box .value {
                color: #1d3167;
                font-size: 18px;
                font-weight: 800;
            }

            .detail-table {
                margin-bottom: 0;
            }

            .detail-table thead th {
                color: #fff;
                background: var(--pay-primary);
                font-size: 12px;
                white-space: nowrap;
            }

            .pay-form .form-label {
                color: #536078;
                font-size: 12px;
                font-weight: 700;
            }

            .total-pay-box {
                padding: 14px;
                border: 1px solid #ffd38a;
                border-radius: 10px;
                background: #fff9ed;
            }

            .total-pay-box .label {
                color: #8a5d13;
                font-size: 12px;
                font-weight: 700;
            }

            .total-pay-box .value {
                color: #7a4300;
                font-size: 22px;
                font-weight: 900;
            }

            @media (max-width: 992px) {
                .pay-grid,
                .summary-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="pay-page">
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

            <div class="pay-grid">
                <div class="pay-panel">
                    <div class="pay-panel-header">
                        <h6>Ringkasan Hutang</h6>
                    </div>
                    <div class="pay-panel-body">
                        <div class="summary-grid">
                            <div class="summary-box">
                                <div class="label">Nilai tagihan {{ strtolower($tagihan->nama_komponen) }}</div>
                                <div class="value">Rp {{ number_format($tagihan->nominal_hutang, 0, ',', '.') }}</div>
                            </div>
                            <div class="summary-box">
                                    <div class="label">Hutang diselesaikan</div>
                                <div class="value">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
                            </div>
                            <div class="summary-box">
                                <div class="label">Sisa hutang</div>
                                <div class="value">Rp {{ number_format($sisaHutang, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        @if($komponen === 'barang')<div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle detail-table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-end">Qty</th>
                                        <th>Satuan</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($faktur->detail as $detail)
                                        <tr>
                                            <td>{{ $detail->produk->nm_produk ?? '-' }}</td>
                                            <td class="text-end">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                                            <td>{{ $detail->satuan }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->harga_satuan, 2, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>@else
                            <div class="alert alert-light border mb-3"><strong>{{ $tagihan->nama_komponen }}</strong><div class="text-muted small">{{ $komponen === 'pph23' ? 'Pembayaran pajak ini mengurangi akun Hutang Pajak PPh 23 (210203), bukan hutang pemasok.' : 'Tagihan biaya ini dipisahkan dari tagihan barang.' }} Faktur {{ $faktur->no_faktur }}.</div></div>
                        @endif

                        <h6 class="mb-2">Riwayat Pelunasan {{ $tagihan->nama_komponen }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Akun Pembayaran</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Hutang dilunasi</th>
                                        <th class="text-end">Selisih biaya</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($riwayatPelunasan as $riwayat)
                                        <tr>
                                            <td>{{ tanggal($riwayat->tanggal_bayar) }}</td>
                                            <td>{{ $riwayat->kode_perkiraan }} - {{ $riwayat->nama_akun }}</td>
                                            <td class="text-end">Rp {{ number_format($riwayat->jumlah_bayar, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($riwayat->hutang_dilunasi ?? $riwayat->jumlah_bayar, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($riwayat->selisih_biaya ?? 0, 0, ',', '.') }}</td>
                                            <td>{{ $riwayat->keterangan ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">Belum ada pelunasan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="pay-panel">
                    <div class="pay-panel-header">
                        <h6>Input Pelunasan</h6>
                    </div>
                    <div class="pay-panel-body">
                        <form method="POST" action="{{ route('transaksi.buku-hutang.pelunasan.store', $faktur) }}"
                            class="pay-form">
                            @csrf
                            <input type="hidden" name="komponen_hutang" value="{{ $komponen }}">
                            <div class="mb-3">
                                <label class="form-label" for="tanggal_bayar">Tanggal bayar</label>
                                <input type="date" id="tanggal_bayar" name="tanggal_bayar" class="form-control"
                                    value="{{ old('tanggal_bayar', now()->toDateString()) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="id_akun_kas">Akun pembayaran</label>
                                <select id="id_akun_kas" name="id_akun_kas" class="form-select select-search-akun"
                                    data-placeholder="Cari kas atau bank" required>
                                    <option value="">-- Pilih Kas / Bank --</option>
                                    @foreach ($akunKas as $akun)
                                        <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_kas') == $akun->id_akun_perkiraan)>
                                            {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="jumlah_bayar">Jumlah bayar</label>
                                <input type="number" step="0.01" min="{{ $komponen === 'barang' ? '0.01' : '0' }}" @if($komponen === 'barang') max="{{ $sisaHutang }}" @endif
                                    id="jumlah_bayar" name="jumlah_bayar" class="form-control text-end"
                                    value="{{ old('jumlah_bayar', $sisaHutang) }}" required>
                            </div>
                            @if($komponen !== 'barang')
                                <div class="alert alert-light border small">Pembayaran ini menyelesaikan seluruh sisa hutang {{ $tagihan->nama_komponen }}. Selisih lebih/kurang dicatat ke biaya, tanpa mengubah persediaan.</div>
                                <div class="mb-3">
                                    <label class="form-label" for="id_akun_selisih">Akun biaya selisih</label>
                                    <select id="id_akun_selisih" name="id_akun_selisih" class="form-select select-search-akun">
                                        <option value="">Pilih jika ada selisih pembayaran</option>
                                        @foreach($akunSelisih as $akun)
                                            <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_selisih', $komponen === 'ongkir' ? $akunSelisih->firstWhere('kode_perkiraan', '600001')?->id_akun_perkiraan : null) == $akun->id_akun_perkiraan)>{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('id_akun_selisih')<small class="text-danger">{{ $message }}</small>@enderror
                                    <small class="d-block mt-2" id="preview-selisih"></small>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label" for="keterangan">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control" rows="3"
                                    placeholder="Catatan pembayaran jika ada">{{ old('keterangan') }}</textarea>
                            </div>

                            <div class="total-pay-box mb-3">
                                <div class="label">{{ $komponen === 'barang' ? 'Maksimal pembayaran' : 'Hutang yang akan dilunasi' }}</div>
                                <div class="value">Rp {{ number_format($sisaHutang, 0, ',', '.') }}</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('transaksi.buku-hutang.index') }}" class="btn btn-outline-secondary">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan Pelunasan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                const jumlah = document.getElementById('jumlah_bayar');
                const preview = document.getElementById('preview-selisih');
                if (jumlah && preview) {
                    const update = () => {
                        const selisih = Math.round(((Number(jumlah.value) || 0) - @json((float) $sisaHutang)) * 100) / 100;
                        preview.textContent = selisih === 0 ? 'Tidak ada selisih.' : (selisih > 0 ? 'Tambahan biaya: Rp ' : 'Pengurang biaya: Rp ') + Math.abs(selisih).toLocaleString('id-ID');
                        document.getElementById('id_akun_selisih').required = selisih !== 0;
                    };
                    jumlah.addEventListener('input', update);
                    update();
                }
                if (!window.jQuery || !jQuery.fn.select2) return;

                jQuery('.select-search-akun').select2({
                    width: '100%',
                    placeholder: 'Cari atau pilih akun',
                    allowClear: true,
                    matcher: function(params, data) {
                        if (data.element && data.element.disabled) {
                            return null;
                        }

                        if (!params.term || data.text.toLowerCase().includes(params.term.toLowerCase())) {
                            return data;
                        }

                        return null;
                    }
                });
            })();
        </script>
    @endsection
</x-theme.app>
