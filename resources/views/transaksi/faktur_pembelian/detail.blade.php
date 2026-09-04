<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">{{ $faktur->no_faktur }}</small>
            </div>
            <div>
                <a href="{{ route('transaksi.faktur-pembelian.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <a href="{{ route('transaksi.faktur-pembelian.edit', $faktur) }}"
                    class="btn btn-warning btn-sm {{ $sudahAdaPenerimaan ? 'disabled' : '' }}">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .detail-box {
                padding: 14px;
                border: 1px solid #dce3f2;
                border-radius: 10px;
                background: #f5f7fc;
            }

            .detail-box .label {
                color: #64728b;
                font-size: 12px;
                font-weight: 700;
            }

            .detail-box .value {
                color: #263b78;
                font-weight: 800;
            }

            .table-detail {
                min-width: 860px;
            }

            .table-detail thead th {
                color: #fff;
                background: #29468f;
                font-size: 12px;
                white-space: nowrap;
            }
        </style>

        @if ($sudahAdaPenerimaan)
            <div class="alert alert-info">
                Faktur ini sudah memiliki penerimaan stok. Edit dikunci supaya stok dan jurnal tetap sama.
            </div>
        @endif

        <div class="detail-box mb-3">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="label">Nomor Faktur</div>
                    <div class="value">{{ $faktur->no_faktur }}</div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="label">Tanggal</div>
                    <div class="value">{{ tanggal($faktur->tanggal_faktur) }}</div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="label">Jenis</div>
                    <div class="value">{{ in_array($faktur->jenis_faktur, ['vitamin', 'vaksin']) ? 'Vitamin & Vaksin' : ucfirst(str_replace('_', ' ', $faktur->jenis_faktur)) }}</div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="label">Suplier</div>
                    <div class="value">{{ $faktur->supplier->nm_suplier ?? '-' }}</div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="label">Total Qty</div>
                    <div class="value">{{ number_format($faktur->total_qty, 2, ',', '.') }}</div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="label">Total Harga</div>
                    <div class="value">Rp {{ number_format($faktur->total_harga, 0, ',', '.') }}</div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="label">Diskon</div>
                    <div class="value">Rp {{ number_format($faktur->diskon_total ?? 0, 0, ',', '.') }}</div>
                </div>
                @foreach (($faktur->biaya_lain ?? []) as $biaya)
                    <div class="col-lg-3 col-md-6">
                        <div class="label">Biaya {{ $biaya['nama'] ?? ucfirst($biaya['kode'] ?? 'lain') }}</div>
                        <div class="value">Rp {{ number_format($biaya['nominal'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                @endforeach
                @php($totalPph23 = collect($faktur->biaya_lain ?? [])->sum('pph23_nominal'))
                @if($totalPph23 > 0)<div class="col-lg-3 col-md-6"><div class="label">Potongan PPh 23</div><div class="value">Rp {{ number_format($totalPph23,0,',','.') }}</div></div>@endif
                <div class="col-lg-3 col-md-6"><div class="label">Total Hutang Supplier</div><div class="value">Rp {{ number_format($faktur->total_hutang,0,',','.') }}</div></div>
                <div class="col-lg-3 col-md-6">
                    <div class="label">Metode Pembayaran</div>
                    <div class="value">{{ match ($faktur->metode_pembayaran ?? 'hutang') {
                        'tunai' => 'Tunai / Bank',
                        'campuran' => 'Campuran',
                        default => 'Hutang',
                    } }}</div>
                </div>
                <div class="col-lg-6">
                    <div class="label">Keterangan</div>
                    <div class="value">{{ $faktur->keterangan ?: '-' }}</div>
                </div>
            </div>
        </div>

        <h6>Item Pembelian</h6>
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-detail">
                <thead>
                    <tr>
                        <th width="45">No</th>
                        <th>Produk</th>
                        <th class="text-end">Qty Faktur</th>
                        <th class="text-end">Sudah Diterima</th>
                        <th>Satuan</th>
                        <th class="text-end">HPP / Satuan</th>
                        <th class="text-end">Subtotal Bersih</th>
                        <th>Akun Hutang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($faktur->detail as $no => $detail)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>
                                <strong>{{ ($detail->sumber_produk ?? 'perencanaan') === 'barang_umum' ? ($detail->produkUmum->nm_produk ?? '-') : ($detail->produk->nm_produk ?? '-') }}</strong>
                                <div class="text-muted small">{{ ($detail->sumber_produk ?? 'perencanaan') === 'barang_umum' ? 'Barang Umum' : ($detail->produk->kategori ?? '-') }}</div>
                            </td>
                            <td class="text-end">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                            <td class="text-end">
                                {{ number_format((float) ($qtyDiterimaByProduk[$detail->pakan_id] ?? 0), 2, ',', '.') }}
                            </td>
                            <td>{{ $detail->satuan ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            <td>
                                @if ($detail->akunPembayaran)
                                    {{ $detail->akunPembayaran->kode_perkiraan }} - {{ $detail->akunPembayaran->nama }}
                                @elseif (($faktur->metode_pembayaran ?? 'hutang') === 'hutang')
                                    Hutang Lainnya
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if (count($faktur->biaya_lain ?? []))
            <h6>Biaya Lain-lain</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-detail">
                    <thead><tr><th>Akun Hutang</th><th>Jenis Biaya</th><th class="text-end">Hutang Neto</th><th class="text-end">PPh 23</th><th class="text-end">Biaya Bruto (Ke HPP)</th></tr></thead>
                    <tbody>
                        @foreach (($faktur->biaya_lain ?? []) as $biaya)
                            @php($akun = $akunBiaya[$biaya['id_akun'] ?? 0] ?? null)
                            <tr>
                                <td>{{ $akun?->kode_perkiraan ?? '-' }} - {{ $akun?->nama ?? 'Akun tidak ditemukan' }}</td>
                                <td>{{ $biaya['nama'] ?? ucfirst($biaya['kode'] ?? 'Biaya lain') }}</td>
                                <td class="text-end">Rp {{ number_format($biaya['nominal'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($biaya['pph23_nominal'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format(($biaya['nominal'] ?? 0)+($biaya['pph23_nominal'] ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h6>Jurnal</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-detail">
                <thead>
                    <tr>
                        <th>Akun</th>
                        <th>Deskripsi</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jurnal as $row)
                        <tr>
                            <td>{{ $row->kode_perkiraan }} - {{ $row->nama }}</td>
                            <td>{{ $row->deskripsi }}</td>
                            <td class="text-end">Rp {{ number_format($row->debit, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($row->kredit, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada jurnal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-slot>
</x-theme.app>
