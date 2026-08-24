<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Jurnal umum manual untuk pembukuan baru</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('akuntansi_baru') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Pembukuan Baru
                </a>
                @if ($kelompok === 'manual')
                    <a href="{{ route('pembukuan-baru.jurnal-umum.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Buat Jurnal
                    </a>
                @elseif ($kelompok === 'biaya')
                    <a href="{{ route('pembukuan-baru.jurnal-umum.biaya.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Buat Biaya
                    </a>
                @elseif ($kelompok === 'pembelian-umum')
                    <a href="{{ route('pembukuan-baru.jurnal-umum.pembelian-umum.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Buat Pembelian Umum
                    </a>
                @elseif ($kelompok === 'aktiva-gantung')
                    <a href="{{ route('pembukuan-baru.jurnal-umum.aktiva-gantung.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Tambah Aktiva Gantung
                    </a>
                @elseif ($kelompok === 'pembalik-aktiva-gantung')
                    <a href="{{ route('pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-exchange-alt me-1"></i> Buat Pembalik Aktiva
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .journal-filter {
                padding: 14px;
                margin-bottom: 14px;
                border: 1px solid #dce3f2;
                border-radius: 12px;
                background: #f5f7fc;
            }

            .journal-filter .form-label {
                margin-bottom: 5px;
                color: #536078;
                font-size: 12px;
                font-weight: 700;
            }

            .journal-table-wrap {
                overflow-x: auto;
                border: 1px solid #dce3f2;
                border-radius: 12px;
            }

            .journal-table {
                min-width: 920px;
                margin-bottom: 0;
            }

            .journal-table thead th {
                padding: 12px;
                color: #fff;
                background: #29468f;
                font-size: 12px;
                white-space: nowrap;
            }

            .journal-nav {
                gap: 8px;
                margin-bottom: 14px;
            }

            .journal-nav .nav-link {
                border: 1px solid #dce3f2;
                color: #536078;
                font-size: 13px;
                font-weight: 700;
            }

            .journal-nav .nav-link.active {
                border-color: #29468f;
                background: #29468f;
                color: #fff;
            }

            .journal-summary {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 14px;
            }

            .journal-summary-box {
                padding: 12px;
                border: 1px solid #dce3f2;
                border-radius: 10px;
                background: #f5f7fc;
            }

            .journal-summary-box .label {
                color: #637089;
                font-size: 12px;
                font-weight: 700;
            }

            .journal-summary-box .value {
                color: #1d3167;
                font-size: 18px;
                font-weight: 900;
            }

            .journal-master-row {
                cursor: pointer;
            }

            .journal-detail-row {
                display: none;
                background: #f8fafc;
            }

            .journal-detail-row.is-open {
                display: table-row;
            }

            .journal-detail-box {
                padding: 12px;
                border: 1px solid #dce3f2;
                border-radius: 10px;
                background: #fff;
            }

            .journal-detail-table {
                margin-bottom: 0;
            }

            .journal-detail-table thead th {
                background: #eef2fb;
                color: #1d3167;
            }

            .empty-journal {
                padding: 44px 20px !important;
                color: #66738a;
                text-align: center;
            }

            @media (max-width: 768px) {
                .journal-summary {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <form method="get" action="{{ route('pembukuan-baru.jurnal-umum.index') }}" class="journal-filter">
            <input type="hidden" name="kelompok" value="{{ $kelompok }}">
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
                    <label class="form-label" for="cari">Cari nomor atau keterangan</label>
                    <input type="search" id="cari" name="cari" class="form-control"
                        value="{{ request('cari') }}" placeholder="Masukkan nomor transaksi atau keterangan">
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>

        <ul class="nav nav-pills journal-nav">
            <li class="nav-item">
                <a class="nav-link {{ $kelompok === 'faktur-pembelian' ? 'active' : '' }}"
                    href="{{ route('pembukuan-baru.jurnal-umum.index', request()->except('page', 'kelompok') + ['kelompok' => 'faktur-pembelian']) }}">
                    Faktur Pembelian
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kelompok === 'pelunasan-hutang' ? 'active' : '' }}"
                    href="{{ route('pembukuan-baru.jurnal-umum.index', request()->except('page', 'kelompok') + ['kelompok' => 'pelunasan-hutang']) }}">
                    Pelunasan Hutang
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kelompok === 'penjualan' ? 'active' : '' }}"
                    href="{{ route('pembukuan-baru.jurnal-umum.index', request()->except('page', 'kelompok') + ['kelompok' => 'penjualan']) }}">
                    Penjualan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kelompok === 'biaya' ? 'active' : '' }}"
                    href="{{ route('pembukuan-baru.jurnal-umum.index', request()->except('page', 'kelompok') + ['kelompok' => 'biaya']) }}">
                    Biaya
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kelompok === 'pembelian-umum' ? 'active' : '' }}"
                    href="{{ route('pembukuan-baru.jurnal-umum.index', request()->except('page', 'kelompok') + ['kelompok' => 'pembelian-umum']) }}">
                    Pembelian Umum
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kelompok === 'aktiva-gantung' ? 'active' : '' }}"
                    href="{{ route('pembukuan-baru.jurnal-umum.index', request()->except('page', 'kelompok') + ['kelompok' => 'aktiva-gantung']) }}">
                    Aktiva Gantung
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kelompok === 'pembalik-aktiva-gantung' ? 'active' : '' }}"
                    href="{{ route('pembukuan-baru.jurnal-umum.index', request()->except('page', 'kelompok') + ['kelompok' => 'pembalik-aktiva-gantung']) }}">
                    Pembalik Aktiva Gantung
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kelompok === 'manual' ? 'active' : '' }}"
                    href="{{ route('pembukuan-baru.jurnal-umum.index', request()->except('page', 'kelompok') + ['kelompok' => 'manual']) }}">
                    Jurnal Umum Manual
                </a>
            </li>
        </ul>

        @if ($kelompok === 'faktur-pembelian')
            <div class="journal-summary">
                <div class="journal-summary-box">
                    <div class="label">Jumlah detail</div>
                    <div class="value">{{ number_format($ringkasanFaktur->jumlah_detail ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total debit</div>
                    <div class="value">Rp {{ number_format($ringkasanFaktur->total_debit ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total kredit</div>
                    <div class="value">Rp {{ number_format($ringkasanFaktur->total_kredit ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="journal-table-wrap">
                <table class="table table-hover align-middle journal-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Tanggal</th>
                            <th>No Transaksi</th>
                            <th>Tipe</th>
                            <th class="text-end">Detail</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th width="90" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jurnalFaktur as $nomor => $item)
                            @php
                                $detailRows = $detailFaktur[$item->nomor_transaksi] ?? collect();
                                $detailId = 'detail-faktur-' . md5($item->nomor_transaksi);
                            @endphp
                            <tr class="journal-master-row" data-detail-target="{{ $detailId }}">
                                <td>{{ $jurnalFaktur->firstItem() + $nomor }}</td>
                                <td>{{ tanggal($item->tanggal) }}</td>
                                <td>{{ $item->nomor_transaksi }}</td>
                                <td>{{ $item->tipe_transaksi }}</td>
                                <td class="text-end">{{ number_format($item->jumlah_detail, 0, ',', '.') }} baris</td>
                                <td class="text-end">Rp {{ number_format($item->total_debit, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_kredit, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-toggle-detail"
                                        data-detail-target="{{ $detailId }}">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <tr class="journal-detail-row" id="{{ $detailId }}">
                                <td colspan="8">
                                    <div class="journal-detail-box">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle journal-detail-table">
                                                <thead>
                                                    <tr>
                                                        <th width="45">No</th>
                                                        <th>Akun</th>
                                                        <th>Keterangan</th>
                                                        <th class="text-end">Debit</th>
                                                        <th class="text-end">Kredit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($detailRows as $detailNo => $detail)
                                                        <tr>
                                                            <td>{{ $detailNo + 1 }}</td>
                                                            <td>{{ $detail->kode_perkiraan }} - {{ $detail->nama_akun }}</td>
                                                            <td>{{ $detail->deskripsi }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->debit, 0, ',', '.') }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->kredit, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-journal">
                                    <strong class="d-block mb-1">Belum ada jurnal faktur pembelian</strong>
                                    <span>Jurnal akan muncul setelah transaksi faktur pembelian disimpan.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jurnalFaktur->hasPages())
                <div class="mt-3">
                    {{ $jurnalFaktur->links() }}
                </div>
            @endif
        @elseif ($kelompok === 'pelunasan-hutang')
            <div class="journal-summary">
                <div class="journal-summary-box">
                    <div class="label">Jumlah detail</div>
                    <div class="value">{{ number_format($ringkasanPelunasan->jumlah_detail ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total debit</div>
                    <div class="value">Rp {{ number_format($ringkasanPelunasan->total_debit ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total kredit</div>
                    <div class="value">Rp {{ number_format($ringkasanPelunasan->total_kredit ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="journal-table-wrap">
                <table class="table table-hover align-middle journal-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Tanggal</th>
                            <th>No Transaksi</th>
                            <th>Tipe</th>
                            <th class="text-end">Detail</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th width="90" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jurnalPelunasan as $nomor => $item)
                            @php
                                $detailRows = $detailPelunasan[$item->nomor_transaksi] ?? collect();
                                $detailId = 'detail-pelunasan-' . md5($item->nomor_transaksi);
                            @endphp
                            <tr class="journal-master-row" data-detail-target="{{ $detailId }}">
                                <td>{{ $jurnalPelunasan->firstItem() + $nomor }}</td>
                                <td>{{ tanggal($item->tanggal) }}</td>
                                <td>{{ $item->nomor_transaksi }}</td>
                                <td>{{ $item->tipe_transaksi }}</td>
                                <td class="text-end">{{ number_format($item->jumlah_detail, 0, ',', '.') }} baris</td>
                                <td class="text-end">Rp {{ number_format($item->total_debit, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_kredit, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-toggle-detail"
                                        data-detail-target="{{ $detailId }}">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <tr class="journal-detail-row" id="{{ $detailId }}">
                                <td colspan="8">
                                    <div class="journal-detail-box">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle journal-detail-table">
                                                <thead>
                                                    <tr>
                                                        <th width="45">No</th>
                                                        <th>Akun</th>
                                                        <th>Keterangan</th>
                                                        <th class="text-end">Debit</th>
                                                        <th class="text-end">Kredit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($detailRows as $detailNo => $detail)
                                                        <tr>
                                                            <td>{{ $detailNo + 1 }}</td>
                                                            <td>{{ $detail->kode_perkiraan }} - {{ $detail->nama_akun }}</td>
                                                            <td>{{ $detail->deskripsi }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->debit, 0, ',', '.') }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->kredit, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-journal">
                                    <strong class="d-block mb-1">Belum ada jurnal pelunasan hutang</strong>
                                    <span>Jurnal akan muncul setelah pelunasan hutang faktur pembelian disimpan.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jurnalPelunasan->hasPages())
                <div class="mt-3">
                    {{ $jurnalPelunasan->links() }}
                </div>
            @endif
        @elseif ($kelompok === 'penjualan')
            <div class="journal-summary">
                <div class="journal-summary-box">
                    <div class="label">Jumlah detail</div>
                    <div class="value">{{ number_format($ringkasanPenjualanTelur->jumlah_detail ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total debit</div>
                    <div class="value">Rp {{ number_format($ringkasanPenjualanTelur->total_debit ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total kredit</div>
                    <div class="value">Rp {{ number_format($ringkasanPenjualanTelur->total_kredit ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="journal-table-wrap">
                <table class="table table-hover align-middle journal-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Tanggal</th>
                            <th>No Transaksi</th>
                            <th>Tipe</th>
                            <th class="text-end">Detail</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th width="90" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jurnalPenjualanTelur as $nomor => $item)
                            @php
                                $detailRows = $detailPenjualanTelur[$item->nomor_transaksi] ?? collect();
                                $detailId = 'detail-penjualan-telur-' . md5($item->nomor_transaksi);
                            @endphp
                            <tr class="journal-master-row" data-detail-target="{{ $detailId }}">
                                <td>{{ $jurnalPenjualanTelur->firstItem() + $nomor }}</td>
                                <td>{{ tanggal($item->tanggal) }}</td>
                                <td>{{ $item->nomor_transaksi }}</td>
                                <td>{{ $item->tipe_transaksi }}</td>
                                <td class="text-end">{{ number_format($item->jumlah_detail, 0, ',', '.') }} baris</td>
                                <td class="text-end">Rp {{ number_format($item->total_debit, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_kredit, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-toggle-detail" data-detail-target="{{ $detailId }}" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="journal-detail-row" id="{{ $detailId }}">
                                <td colspan="8">
                                    <div class="journal-detail-box">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle journal-detail-table">
                                                <thead><tr><th width="45">No</th><th>Akun</th><th>Keterangan</th><th class="text-end">Debit</th><th class="text-end">Kredit</th></tr></thead>
                                                <tbody>
                                                    @foreach ($detailRows as $detailNo => $detail)
                                                        <tr>
                                                            <td>{{ $detailNo + 1 }}</td>
                                                            <td>{{ $detail->kode_perkiraan }} - {{ $detail->nama_akun }}</td>
                                                            <td>{{ $detail->deskripsi }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->debit, 0, ',', '.') }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->kredit, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-journal"><strong class="d-block mb-1">Belum ada jurnal penjualan</strong><span>Jurnal akan muncul setelah penjualan telur atau ayam disimpan.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jurnalPenjualanTelur->hasPages())
                <div class="mt-3">{{ $jurnalPenjualanTelur->links() }}</div>
            @endif
        @elseif ($kelompok === 'biaya')
            <div class="journal-summary">
                <div class="journal-summary-box">
                    <div class="label">Jumlah detail</div>
                    <div class="value">{{ number_format($ringkasanBiaya->jumlah_detail ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total debit</div>
                    <div class="value">Rp {{ number_format($ringkasanBiaya->total_debit ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total kredit</div>
                    <div class="value">Rp {{ number_format($ringkasanBiaya->total_kredit ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="journal-table-wrap">
                <table class="table table-hover align-middle journal-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Tanggal</th>
                            <th>No Transaksi</th>
                            <th>Tipe</th>
                            <th class="text-end">Detail</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th width="140" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jurnalBiaya as $nomor => $item)
                            @php
                                $detailRows = $detailBiaya[$item->nomor_transaksi] ?? collect();
                                $detailId = 'detail-biaya-' . md5($item->nomor_transaksi);
                            @endphp
                            <tr class="journal-master-row" data-detail-target="{{ $detailId }}">
                                <td>{{ $jurnalBiaya->firstItem() + $nomor }}</td>
                                <td>{{ tanggal($item->tanggal) }}</td>
                                <td>{{ $item->nomor_transaksi }}</td>
                                <td>{{ $item->tipe_transaksi }}</td>
                                <td class="text-end">{{ number_format($item->jumlah_detail, 0, ',', '.') }} baris</td>
                                <td class="text-end">Rp {{ number_format($item->total_debit, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_kredit, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-toggle-detail"
                                            data-detail-target="{{ $detailId }}" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('pembukuan-baru.jurnal-umum.biaya.edit', $item->nomor_transaksi) }}"
                                            class="btn btn-outline-warning" title="Edit Biaya">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-delete-biaya"
                                            data-nomor="{{ $item->nomor_transaksi }}"
                                            data-url="{{ route('pembukuan-baru.jurnal-umum.biaya.destroy', $item->nomor_transaksi) }}"
                                            title="Hapus Biaya">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="journal-detail-row" id="{{ $detailId }}">
                                <td colspan="8">
                                    <div class="journal-detail-box">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle journal-detail-table">
                                                <thead>
                                                    <tr>
                                                        <th width="45">No</th>
                                                        <th>Akun</th>
                                                        <th>Keterangan</th>
                                                        <th class="text-end">Debit</th>
                                                        <th class="text-end">Kredit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($detailRows as $detailNo => $detail)
                                                        <tr>
                                                            <td>{{ $detailNo + 1 }}</td>
                                                            <td>{{ $detail->kode_perkiraan }} - {{ $detail->nama_akun }}</td>
                                                            <td>{{ $detail->deskripsi }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->debit, 0, ',', '.') }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->kredit, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-journal">
                                    <strong class="d-block mb-1">Belum ada jurnal biaya</strong>
                                    <span>Jurnal biaya listrik, air, PDAM, dan biaya lain akan muncul di sini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jurnalBiaya->hasPages())
                <div class="mt-3">
                    {{ $jurnalBiaya->links() }}
                </div>
            @endif
        @elseif ($kelompok === 'pembelian-umum')
            <div class="journal-summary">
                <div class="journal-summary-box">
                    <div class="label">Total Pembelian</div>
                    <div class="value">Rp {{ number_format($ringkasanPembelianUmum->total_debit ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Jumlah Transaksi</div>
                    <div class="value">{{ number_format($jurnalPembelianUmum->total(), 0, ',', '.') }} transaksi</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total Baris Detail</div>
                    <div class="value">{{ number_format($ringkasanPembelianUmum->jumlah_detail ?? 0, 0, ',', '.') }} baris</div>
                </div>
            </div>

            <div class="journal-table-wrap">
                <table class="table table-hover align-middle journal-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th width="120">Tanggal</th>
                            <th width="180">Nomor Transaksi</th>
                            <th>Keterangan / Deskripsi</th>
                            <th width="110" class="text-center">Detail</th>
                            <th width="160" class="text-end">Total Pembelian</th>
                            <th width="140" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jurnalPembelianUmum as $nomor => $jurnal)
                            @php
                                $detailRows = $detailPembelianUmum[$jurnal->nomor_transaksi] ?? collect();
                                $detailId = 'detail-pu-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $jurnal->nomor_transaksi);
                            @endphp
                            <tr class="journal-master-row" data-detail-target="{{ $detailId }}">
                                <td>{{ $jurnalPembelianUmum->firstItem() + $nomor }}</td>
                                <td>{{ tanggal($jurnal->tanggal) }}</td>
                                <td>
                                    <strong>{{ $jurnal->nomor_transaksi }}</strong>
                                    <div class="text-muted small">Pembelian Umum</div>
                                </td>
                                <td>
                                    @php
                                        $mainDesc = $detailRows->where('debit', '>', 0)->pluck('deskripsi')->implode(', ');
                                    @endphp
                                    <span>{{ $mainDesc ?: 'Pembelian Umum' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-primary text-primary">
                                        {{ $jurnal->jumlah_detail }} baris
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary">
                                        Rp {{ number_format($jurnal->total_debit, 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-toggle-detail"
                                            data-detail-target="{{ $detailId }}" title="Detail Transaksi">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('pembukuan-baru.jurnal-umum.pembelian-umum.edit', $jurnal->nomor_transaksi) }}"
                                            class="btn btn-outline-warning" title="Edit Transaksi">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-delete-pu"
                                            data-nomor="{{ $jurnal->nomor_transaksi }}"
                                            data-url="{{ route('pembukuan-baru.jurnal-umum.pembelian-umum.destroy', $jurnal->nomor_transaksi) }}"
                                            title="Hapus Transaksi">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="journal-detail-row" id="{{ $detailId }}">
                                <td colspan="7" class="p-0">
                                    <div class="p-3 bg-light border-bottom">
                                        <div class="journal-detail-box">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">
                                                    Rincian Jurnal: {{ $jurnal->nomor_transaksi }}
                                                </strong>
                                                <small class="text-muted">
                                                    {{ $detailRows->count() }} baris jurnal pembukuan
                                                </small>
                                            </div>
                                            <table class="table table-sm table-bordered journal-detail-table">
                                                <thead>
                                                    <tr>
                                                        <th width="130">Kode Akun</th>
                                                        <th width="220">Nama Akun</th>
                                                        <th>Keterangan</th>
                                                        <th width="150" class="text-end">Debit</th>
                                                        <th width="150" class="text-end">Kredit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($detailRows as $detail)
                                                        <tr>
                                                            <td>{{ $detail->kode_perkiraan }}</td>
                                                            <td>{{ $detail->nama_akun }}</td>
                                                            <td>{{ $detail->deskripsi }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->debit, 0, ',', '.') }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->kredit, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-journal">
                                    <strong class="d-block mb-1">Belum ada jurnal pembelian umum</strong>
                                    <span>Transaksi pembelian barang/peralatan/perlengkapan umum akan muncul di sini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jurnalPembelianUmum->hasPages())
                <div class="mt-3">
                    {{ $jurnalPembelianUmum->links() }}
                </div>
            @endif
        @elseif ($kelompok === 'aktiva-gantung')
            <div class="journal-summary">
                <div class="journal-summary-box">
                    <div class="label">Jumlah aset</div>
                    <div class="value">{{ number_format($ringkasanAktivaGantung->jumlah_aset ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Biaya periode</div>
                    <div class="value">Rp {{ number_format($ringkasanAktivaGantung->total_debit ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Jumlah transaksi</div>
                    <div class="value">{{ number_format($ringkasanAktivaGantung->jumlah_detail ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="journal-table-wrap">
                <table class="table table-hover align-middle journal-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Kode</th>
                            <th>Nama Aset</th>
                            <th>Status</th>
                            <th class="text-end">Biaya Periode</th>
                            <th class="text-end">Total Terkumpul</th>
                            <th class="text-end">Transaksi</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($aktivaGantung as $nomor => $item)
                            @php
                                $detailRows = $detailAktivaGantung[$item->id] ?? collect();
                                $detailId = 'detail-aktiva-gantung-' . $item->id;
                            @endphp
                            <tr class="journal-master-row" data-detail-target="{{ $detailId }}">
                                <td>{{ $aktivaGantung->firstItem() + $nomor }}</td>
                                <td>{{ $item->kode }}</td>
                                <td>
                                    <strong>{{ $item->nama_aset }}</strong>
                                    @if ($item->keterangan)
                                        <div class="text-muted small">{{ $item->keterangan }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $item->status === 'gantung' ? 'warning' : 'success' }}">
                                        {{ $item->status === 'gantung' ? 'Gantung' : ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="text-end">Rp {{ number_format($item->total_periode, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_terkumpul, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($item->jumlah_transaksi, 0, ',', '.') }} kali</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-toggle-detail"
                                            data-detail-target="{{ $detailId }}" title="Detail Transaksi">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-edit-aset"
                                            data-id="{{ $item->id }}"
                                            data-nama="{{ $item->nama_aset }}"
                                            data-keterangan="{{ $item->keterangan ?? '' }}"
                                            data-status="{{ $item->status }}"
                                            data-url="{{ route('pembukuan-baru.jurnal-umum.aktiva-gantung.aset.update', $item->id) }}"
                                            title="Edit Master Aset">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-delete-aset"
                                            data-nama="{{ $item->nama_aset }}"
                                            data-transaksi="{{ $item->jumlah_transaksi }}"
                                            data-url="{{ route('pembukuan-baru.jurnal-umum.aktiva-gantung.aset.destroy', $item->id) }}"
                                            title="Hapus Master Aset">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="journal-detail-row" id="{{ $detailId }}">
                                <td colspan="8">
                                    <div class="journal-detail-box">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle journal-detail-table">
                                                <thead>
                                                    <tr>
                                                        <th width="45">No</th>
                                                        <th>Tanggal</th>
                                                        <th>No Transaksi</th>
                                                        <th>Akun Penampung</th>
                                                        <th>Dibayar Dari</th>
                                                        <th>Keterangan</th>
                                                        <th class="text-end">Jumlah</th>
                                                        <th width="100" class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($detailRows as $detailNo => $detail)
                                                        <tr>
                                                            <td>{{ $detailNo + 1 }}</td>
                                                            <td>{{ tanggal($detail->tanggal) }}</td>
                                                            <td>{{ $detail->nomor_transaksi }}</td>
                                                            <td>{{ $detail->kode_akun_aktiva }} - {{ $detail->nama_akun_aktiva }}</td>
                                                            <td>{{ $detail->kode_akun_kas }} - {{ $detail->nama_akun_kas }}</td>
                                                            <td>{{ $detail->keterangan }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                                                            <td class="text-center">
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <a href="{{ route('pembukuan-baru.jurnal-umum.aktiva-gantung.transaksi.edit', $detail->nomor_transaksi) }}"
                                                                        class="btn btn-outline-warning" title="Edit Transaksi">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-outline-danger btn-delete-ag-transaksi"
                                                                        data-nomor="{{ $detail->nomor_transaksi }}"
                                                                        data-url="{{ route('pembukuan-baru.jurnal-umum.aktiva-gantung.transaksi.destroy', $detail->nomor_transaksi) }}"
                                                                        title="Hapus Transaksi">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted">
                                                                Tidak ada transaksi pada periode filter ini.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-journal">
                                    <strong class="d-block mb-1">Belum ada aktiva gantung</strong>
                                    <span>Biaya pembangunan/pengadaan aset yang masih ditampung akan muncul di sini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($aktivaGantung->hasPages())
                <div class="mt-3">
                    {{ $aktivaGantung->links() }}
                </div>
            @endif
        @elseif ($kelompok === 'pembalik-aktiva-gantung')
            <div class="journal-summary">
                <div class="journal-summary-box">
                    <div class="label">Jumlah transaksi</div>
                    <div class="value">{{ number_format($ringkasanPembalik->jumlah_detail ?? 0, 0, ',', '.') }} baris</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total debit</div>
                    <div class="value">Rp {{ number_format($ringkasanPembalik->total_debit ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="journal-summary-box">
                    <div class="label">Total kredit</div>
                    <div class="value">Rp {{ number_format($ringkasanPembalik->total_kredit ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="journal-table-wrap">
                <table class="table table-hover align-middle journal-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Tanggal</th>
                            <th>No Transaksi</th>
                            <th>Tipe</th>
                            <th class="text-end">Detail</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th width="140" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jurnalPembalik as $nomor => $item)
                            @php
                                $detailRows = $detailPembalik[$item->nomor_transaksi] ?? collect();
                                $detailId = 'detail-pembalik-' . md5($item->nomor_transaksi);
                            @endphp
                            <tr class="journal-master-row" data-detail-target="{{ $detailId }}">
                                <td>{{ $jurnalPembalik->firstItem() + $nomor }}</td>
                                <td>{{ tanggal($item->tanggal) }}</td>
                                <td>{{ $item->nomor_transaksi }}</td>
                                <td>{{ $item->tipe_transaksi }}</td>
                                <td class="text-end">{{ number_format($item->jumlah_detail, 0, ',', '.') }} baris</td>
                                <td class="text-end">Rp {{ number_format($item->total_debit, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_kredit, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-toggle-detail"
                                            data-detail-target="{{ $detailId }}" title="Detail Jurnal">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.edit', $item->nomor_transaksi) }}"
                                            class="btn btn-outline-warning" title="Edit Pembalik Aktiva">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-delete-pembalik"
                                            data-nomor="{{ $item->nomor_transaksi }}"
                                            data-url="{{ route('pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.destroy', $item->nomor_transaksi) }}"
                                            title="Hapus Pembalik Aktiva">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="journal-detail-row" id="{{ $detailId }}">
                                <td colspan="8">
                                    <div class="journal-detail-box">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle journal-detail-table">
                                                <thead>
                                                    <tr>
                                                        <th width="45">No</th>
                                                        <th>Akun Perkiraan</th>
                                                        <th>Keterangan</th>
                                                        <th class="text-end">Debit</th>
                                                        <th class="text-end">Kredit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($detailRows as $detailNo => $detail)
                                                        <tr>
                                                            <td>{{ $detailNo + 1 }}</td>
                                                            <td>{{ $detail->kode_perkiraan }} - {{ $detail->nama_akun }}</td>
                                                            <td>{{ $detail->deskripsi }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->debit, 0, ',', '.') }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->kredit, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-journal">
                                    <strong class="d-block mb-1">Belum ada jurnal pembalik aktiva gantung</strong>
                                    <span>Jurnal pemindahan saldo aktiva gantung ke aset tetap akan muncul di sini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jurnalPembalik->hasPages())
                <div class="mt-3">
                    {{ $jurnalPembalik->links() }}
                </div>
            @endif
        @else

            <div class="journal-table-wrap">
                <table class="table table-hover align-middle journal-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Nama</th>
                            <th>Periode</th>
                            <th class="text-end">Detail</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batch as $nomor => $item)
                            <tr>
                                <td>{{ $batch->firstItem() + $nomor }}</td>
                                <td>{{ $item->nama_file }}</td>
                                <td>{{ tanggal($item->periode_awal) }}</td>
                                <td class="text-end">{{ number_format($item->jumlah_detail, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_debit, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_kredit, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->status === 'aktif' ? 'success' : 'danger' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-journal">
                                    <strong class="d-block mb-1">Belum ada jurnal umum</strong>
                                    <span>Jurnal yang dibuat dari halaman ini akan muncul di sini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($batch->hasPages())
                <div class="mt-3">
                    {{ $batch->links() }}
                </div>
            @endif
        @endif

        {{-- Modal Edit Master Aset --}}
        <div class="modal fade" id="modalEditAset" tabindex="-1" aria-labelledby="modalEditAsetLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="formEditAset" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditAsetLabel"><i class="fas fa-edit me-1"></i> Edit Master Aset Gantung</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label" for="edit_nama_aset">Nama Aset</label>
                                <input type="text" class="form-control" id="edit_nama_aset" name="nama_aset" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="edit_keterangan">Catatan / Keterangan</label>
                                <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="edit_status">Status</label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="gantung">Gantung (Masih dalam proses)</option>
                                    <option value="selesai">Selesai (Sudah jadi aktiva tetap)</option>
                                    <option value="dibatalkan">Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Form Hapus Generic --}}
        <form id="formDeleteGeneric" method="POST" action="" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                // Toggle detail row
                document.addEventListener('click', function(e) {
                    const trigger = e.target.closest('[data-detail-target]');
                    if (!trigger) return;

                    if (e.target.closest('.journal-detail-row')) return;

                    const target = document.getElementById(trigger.dataset.detailTarget);
                    if (!target) return;

                    target.classList.toggle('is-open');
                });

                // Delete Biaya
                document.querySelectorAll('.btn-delete-biaya').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const nomor = this.dataset.nomor;
                        const url = this.dataset.url;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Hapus Jurnal Biaya?',
                                html: `Apakah Anda yakin ingin menghapus transaksi biaya <strong>${nomor}</strong>?<br><small class="text-muted">Jurnal umum terkait juga akan dihapus.</small>`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const form = document.getElementById('formDeleteGeneric');
                                    form.action = url;
                                    form.submit();
                                }
                            });
                        } else if (confirm(`Yakin ingin menghapus transaksi biaya ${nomor}?`)) {
                            const form = document.getElementById('formDeleteGeneric');
                            form.action = url;
                            form.submit();
                        }
                    });
                });

                // Edit Master Aset Modal
                document.querySelectorAll('.btn-edit-aset').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const id = this.dataset.id;
                        const nama = this.dataset.nama;
                        const keterangan = this.dataset.keterangan || '';
                        const status = this.dataset.status;
                        const url = this.dataset.url;

                        document.getElementById('formEditAset').action = url;
                        document.getElementById('edit_nama_aset').value = nama;
                        document.getElementById('edit_keterangan').value = keterangan;
                        document.getElementById('edit_status').value = status;

                        const modalEl = document.getElementById('modalEditAset');
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        }
                    });
                });

                // Delete Master Aset
                document.querySelectorAll('.btn-delete-aset').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const nama = this.dataset.nama;
                        const transaksiCount = Number(this.dataset.transaksi || 0);
                        const url = this.dataset.url;

                        let warningText = `Apakah Anda yakin ingin menghapus master aset <strong>${nama}</strong>?`;
                        if (transaksiCount > 0) {
                            warningText += `<br><span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> Perhatian: ${transaksiCount} riwayat transaksi dan jurnal terkait aset ini juga akan dihapus!</span>`;
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Hapus Master Aset?',
                                html: warningText,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus Semua!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const form = document.getElementById('formDeleteGeneric');
                                    form.action = url;
                                    form.submit();
                                }
                            });
                        } else if (confirm(`Yakin ingin menghapus master aset ${nama}?`)) {
                            const form = document.getElementById('formDeleteGeneric');
                            form.action = url;
                            form.submit();
                        }
                    });
                });

                // Delete Aktiva Gantung Transaksi
                document.querySelectorAll('.btn-delete-ag-transaksi').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const nomor = this.dataset.nomor;
                        const url = this.dataset.url;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Hapus Transaksi Aktiva Gantung?',
                                html: `Apakah Anda yakin ingin menghapus transaksi <strong>${nomor}</strong>?<br><small class="text-muted">Jurnal aktiva gantung terkait juga akan dihapus.</small>`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const form = document.getElementById('formDeleteGeneric');
                                    form.action = url;
                                    form.submit();
                                }
                            });
                        } else if (confirm(`Yakin ingin menghapus transaksi ${nomor}?`)) {
                            const form = document.getElementById('formDeleteGeneric');
                            form.action = url;
                            form.submit();
                        }
                    });
                });

                // Delete Pembalik Aktiva Gantung
                document.querySelectorAll('.btn-delete-pembalik').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const nomor = this.dataset.nomor;
                        const url = this.dataset.url;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Hapus Pembalik Aktiva?',
                                html: `Apakah Anda yakin ingin menghapus jurnal pembalik <strong>${nomor}</strong>?<br><small class="text-muted">Jurnal umum pemindahan aset ini akan dihapus.</small>`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const form = document.getElementById('formDeleteGeneric');
                                    form.action = url;
                                    form.submit();
                                }
                            });
                        } else if (confirm(`Yakin ingin menghapus jurnal pembalik ${nomor}?`)) {
                            const form = document.getElementById('formDeleteGeneric');
                            form.action = url;
                            form.submit();
                        }
                    });
                });
                // Delete Pembelian Umum
                document.querySelectorAll('.btn-delete-pu').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const nomor = this.dataset.nomor;
                        const url = this.dataset.url;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Hapus Pembelian Umum?',
                                html: `Apakah Anda yakin ingin menghapus transaksi <strong>${nomor}</strong>?<br><small class="text-muted">Jurnal pembelian umum terkait juga akan dihapus.</small>`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const form = document.getElementById('formDeleteGeneric');
                                    form.action = url;
                                    form.submit();
                                }
                            });
                        } else if (confirm(`Yakin ingin menghapus transaksi ${nomor}?`)) {
                            const form = document.getElementById('formDeleteGeneric');
                            form.action = url;
                            form.submit();
                        }
                    });
                });
            })();
        </script>
    @endsection
</x-theme.app>
