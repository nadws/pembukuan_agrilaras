<x-theme.app title="{{ $title }}" table="T" cont="container-fluid">
    <x-slot name="slot">
        <style>
            .dash-page {
                color: #26364f;
                max-width: 1440px;
                margin: 0 auto;
            }

            .dash-toolbar {
                align-items: center;
                background: #fff;
                border: 1px solid #e2e8f3;
                border-radius: 8px;
                display: flex;
                gap: 16px;
                justify-content: space-between;
                padding: 16px;
            }

            .dash-title {
                font-size: 22px;
                font-weight: 800;
                margin: 0;
            }

            .dash-subtitle {
                color: #687893;
                font-size: 13px;
                margin-top: 4px;
            }

            .filter-form {
                align-items: center;
                display: grid;
                gap: 8px;
                grid-template-columns: 150px 150px 80px;
                min-width: 388px;
            }

            .summary-card,
            .panel {
                background: #fff;
                border: 1px solid #e2e8f3;
                border-radius: 8px;
                box-shadow: 0 8px 22px rgba(38, 54, 79, .05);
            }

            .summary-card {
                display: flex;
                gap: 14px;
                min-height: 118px;
                padding: 16px;
            }

            .summary-icon {
                align-items: center;
                border-radius: 8px;
                display: flex;
                flex: 0 0 42px;
                height: 42px;
                justify-content: center;
            }

            .icon-debit {
                background: #e8f7f1;
                color: #21795c;
            }

            .icon-kredit {
                background: #fff0ee;
                color: #a8483c;
            }

            .icon-balance {
                background: #edf2ff;
                color: #435ebe;
            }

            .icon-akun {
                background: #f4f0ff;
                color: #6d55b8;
            }

            .icon-stock {
                background: #fff7e8;
                color: #b56b13;
            }

            .summary-label {
                color: #687893;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .03em;
                text-transform: uppercase;
            }

            .summary-value {
                color: #26364f;
                font-size: 24px;
                font-weight: 800;
                line-height: 1.15;
                margin-top: 7px;
            }

            .summary-value.good {
                color: #21795c;
            }

            .summary-value.warn {
                color: #a8483c;
            }

            .summary-foot {
                color: #687893;
                font-size: 12px;
                margin-top: 7px;
            }

            .panel-head {
                align-items: center;
                border-bottom: 1px solid #edf1f7;
                display: flex;
                gap: 12px;
                justify-content: space-between;
                padding: 13px 16px;
            }

            .panel-title {
                font-size: 15px;
                font-weight: 800;
                margin: 0;
            }

            .panel-body {
                padding: 16px;
            }

            .legend {
                align-items: center;
                color: #687893;
                display: flex;
                font-size: 12px;
                gap: 12px;
            }

            .legend-dot {
                border-radius: 99px;
                display: inline-block;
                height: 8px;
                margin-right: 5px;
                width: 8px;
            }

            .dot-debit {
                background: #35a77b;
            }

            .dot-kredit {
                background: #de6f62;
            }

            .trend-list {
                display: grid;
                gap: 10px;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            }

            .trend-row {
                background: #f8fafd;
                border: 1px solid #e7edf6;
                border-radius: 8px;
                display: block;
                padding: 12px;
            }

            .trend-month {
                font-weight: 800;
                margin-bottom: 10px;
            }

            .trend-bars {
                display: grid;
                gap: 8px;
            }

            .trend-metric {
                align-items: center;
                display: flex;
                gap: 10px;
                justify-content: space-between;
            }

            .trend-name {
                color: #687893;
                font-size: 12px;
            }

            .trend-value {
                color: #26364f;
                font-size: 13px;
                font-weight: 800;
                white-space: nowrap;
            }

            .trend-diff {
                border-top: 1px solid #e7edf6;
                color: #687893;
                font-size: 12px;
                margin-top: 10px;
                padding-top: 9px;
            }

            .table-dashboard {
                margin: 0;
            }

            .table-dashboard thead th {
                background: #f8fafd;
                border-bottom: 1px solid #e7edf6;
                color: #687893;
                font-size: 11px;
                letter-spacing: .03em;
                padding: 10px 12px;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .table-dashboard tbody td {
                border-color: #edf1f7;
                padding: 12px;
                vertical-align: middle;
            }

            .account-code {
                color: #687893;
                font-size: 12px;
                margin-bottom: 2px;
            }

            .account-name {
                color: #26364f;
                font-weight: 800;
            }

            .muted {
                color: #687893;
                font-size: 12px;
            }

            .rp-debit {
                color: #21795c;
                font-weight: 700;
                white-space: nowrap;
            }

            .rp-kredit {
                color: #a8483c;
                font-weight: 700;
                white-space: nowrap;
            }

            .type-pill {
                background: #f3f6fb;
                border-radius: 999px;
                color: #50627d;
                display: inline-block;
                font-size: 12px;
                padding: 4px 9px;
            }

            .stock-total {
                align-items: center;
                background: #fffaf0;
                border: 1px solid #fde8bd;
                border-radius: 8px;
                display: flex;
                gap: 14px;
                justify-content: space-between;
                padding: 12px 14px;
            }

            .stock-total strong {
                color: #8a510e;
                font-size: 18px;
            }

            .recognized-stock-table th,
            .recognized-stock-table td {
                border-color: #dfe6f3 !important;
                font-size: 13px;
                min-width: 78px;
                padding: 12px 10px !important;
                text-align: right;
                white-space: nowrap;
            }

            .recognized-stock-table th:first-child,
            .recognized-stock-table td:first-child {
                left: 0;
                min-width: 190px;
                position: sticky;
                text-align: left;
                z-index: 3;
            }

            .recognized-stock-table thead th {
                background: #435ebe !important;
                color: #fff !important;
                font-size: 14px !important;
                font-weight: 800 !important;
                text-align: center !important;
            }

            .recognized-stock-table tbody td:first-child {
                background: #fff !important;
                color: #26364f;
            }

            .recognized-stock-table tbody td {
                color: #33445f;
                font-size: 14px;
            }

            .recognized-stock-table tbody td.fw-bold {
                color: #26364f;
                font-weight: 800 !important;
            }

            .empty-state {
                color: #687893;
                padding: 26px;
                text-align: center;
            }

            @media (max-width: 992px) {
                .dash-toolbar {
                    align-items: stretch;
                    display: grid;
                }

                .filter-form {
                    grid-template-columns: 1fr 1fr 80px;
                    min-width: 0;
                }
            }

            @media (max-width: 576px) {
                .filter-form {
                    grid-template-columns: 1fr;
                }

            }
        </style>

        @php
            $totalDebit = (float) ($summary->total_debit ?? 0);
            $totalKredit = (float) ($summary->total_kredit ?? 0);
            $selisih = $totalDebit - $totalKredit;
            $formatRp = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
            $formatRpShort = function ($value) {
                $value = (float) $value;

                if (abs($value) >= 1000000000) {
                    return 'Rp ' . number_format($value / 1000000000, 1, ',', '.') . ' M';
                }

                if (abs($value) >= 1000000) {
                    return 'Rp ' . number_format($value / 1000000, 1, ',', '.') . ' jt';
                }

                return 'Rp ' . number_format($value, 0, ',', '.');
            };
            $formatNumber = fn($value) => number_format((float) $value, 0, ',', '.');
        @endphp

        <div class="dash-page">
            <div class="dash-toolbar mb-3">
                <div>
                    <h1 class="dash-title">Dashboard Jurnal Perkiraan</h1>
                    <div class="dash-subtitle">
                        @if ($tgl1 && $tgl2)
                            Periode {{ tanggal($tgl1) }} sampai {{ tanggal($tgl2) }}
                        @else
                            Ringkasan seluruh jurnal perkiraan aktif
                        @endif
                        @if ($latestBatch)
                            <span class="type-pill ms-2">Import terakhir: {{ $latestBatch->nama_file }}</span>
                        @endif
                    </div>
                </div>

                <form class="filter-form" method="GET" action="{{ route('dashboard') }}">
                    <input type="date" class="form-control" name="tgl1" value="{{ $tgl1 }}">
                    <input type="date" class="form-control" name="tgl2" value="{{ $tgl2 }}">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </form>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6 col-xl">
                    <div class="summary-card">
                        <div class="summary-icon icon-debit"><i class="fas fa-arrow-down"></i></div>
                        <div>
                            <div class="summary-label">Total Debit</div>
                            <div class="summary-value good">{{ $formatRpShort($totalDebit) }}</div>
                            <div class="summary-foot">{{ $formatNumber($summary->jumlah_detail ?? 0) }} baris jurnal</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl">
                    <div class="summary-card">
                        <div class="summary-icon icon-kredit"><i class="fas fa-arrow-up"></i></div>
                        <div>
                            <div class="summary-label">Total Kredit</div>
                            <div class="summary-value warn">{{ $formatRpShort($totalKredit) }}</div>
                            <div class="summary-foot">{{ $formatNumber($summary->jumlah_transaksi ?? 0) }} transaksi</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl">
                    <div class="summary-card">
                        <div class="summary-icon icon-balance"><i class="fas fa-balance-scale"></i></div>
                        <div>
                            <div class="summary-label">Selisih Debit Kredit</div>
                            <div class="summary-value {{ abs($selisih) > 1 ? 'warn' : 'good' }}">{{ $formatRpShort($selisih) }}</div>
                            <div class="summary-foot">{{ abs($selisih) > 1 ? 'Perlu dicek ulang' : 'Debit dan kredit balance' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl">
                    <div class="summary-card">
                        <div class="summary-icon icon-akun"><i class="fas fa-book"></i></div>
                        <div>
                            <div class="summary-label">Akun dan Tipe</div>
                            <div class="summary-value">{{ $formatNumber($summary->jumlah_akun ?? 0) }} akun</div>
                            <div class="summary-foot">{{ $formatNumber($summary->jumlah_tipe ?? 0) }} tipe transaksi</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl">
                    <div class="summary-card">
                        <div class="summary-icon icon-stock"><i class="fas fa-egg"></i></div>
                        <div>
                            <div class="summary-label">Stok Telur</div>
                            <div class="summary-value">{{ $formatNumber($recognizedStockTotal->pcs ?? 0) }} pcs</div>
                            <div class="summary-foot">{{ number_format((float) ($recognizedStockTotal->kg ?? 0), 1, ',', '.') }} kg diakui Martadah</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <div class="panel">
                        <div class="panel-head">
                            <div>
                                <h2 class="panel-title">Stok Diakui Martadah</h2>
                                <div class="muted">Stok = pcs masuk - pcs keluar, hanya data opname = T</div>
                            </div>
                            <span class="type-pill">Gudang Martadah</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-dashboard recognized-stock-table">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="text-center align-middle">Gudang</th>
                                        @foreach ($recognizedStockRows as $row)
                                            <th colspan="3" class="text-center">{{ $row->nm_telur }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach ($recognizedStockRows as $row)
                                            <th>Pcs</th>
                                            <th>Kg</th>
                                            <th>Ikat</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="account-name">Martadah</div>
                                            <div class="muted">stok yang diakui</div>
                                        </td>
                                        @foreach ($recognizedStockRows as $row)
                                            <td class="fw-bold">{{ $formatNumber($row->pcs) }}</td>
                                            <td>{{ number_format((float) $row->kg, 0, ',', '.') }}</td>
                                            <td>{{ number_format((float) $row->pcs / 180, 0, ',', '.') }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="panel">
                        <div class="panel-head">
                            <h2 class="panel-title">Pergerakan Per Bulan</h2>
                            <div class="legend">
                                <span><span class="legend-dot dot-debit"></span>Debit</span>
                                <span><span class="legend-dot dot-kredit"></span>Kredit</span>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="trend-list">
                                @forelse ($monthlyTrend as $row)
                                    @php
                                        $diff = (float) $row->debit - (float) $row->kredit;
                                    @endphp
                                    <div class="trend-row">
                                        <div class="trend-month">{{ $row->bulan }}</div>
                                        <div class="trend-bars">
                                            <div class="trend-metric">
                                                <span class="trend-name"><span class="legend-dot dot-debit"></span>Debit</span>
                                                <span class="trend-value">{{ $formatRpShort($row->debit) }}</span>
                                            </div>
                                            <div class="trend-metric">
                                                <span class="trend-name"><span class="legend-dot dot-kredit"></span>Kredit</span>
                                                <span class="trend-value">{{ $formatRpShort($row->kredit) }}</span>
                                            </div>
                                        </div>
                                        <div class="trend-diff">
                                            Selisih: <strong class="{{ abs($diff) > 1 ? 'rp-kredit' : 'rp-debit' }}">{{ $formatRpShort($diff) }}</strong>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state">Belum ada data jurnal perkiraan pada periode ini.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="panel h-100">
                        <div class="panel-head">
                            <h2 class="panel-title">Tipe Transaksi Terbesar</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-dashboard">
                                <thead>
                                    <tr>
                                        <th>Tipe</th>
                                        <th class="text-end">Transaksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($byType as $row)
                                        <tr>
                                            <td>
                                                <div class="account-name">{{ $row->tipe_transaksi }}</div>
                                                <div class="muted">{{ $formatRpShort((float) $row->debit + (float) $row->kredit) }} aktivitas</div>
                                            </td>
                                            <td class="text-end fw-bold">{{ $formatNumber($row->jumlah_transaksi) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="empty-state">Belum ada data.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="panel">
                        <div class="panel-head">
                            <h2 class="panel-title">Akun Dengan Aktivitas Terbesar</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-dashboard">
                                <thead>
                                    <tr>
                                        <th>Akun</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($topAccounts as $row)
                                        <tr>
                                            <td>
                                                <div class="account-code">{{ $row->kode_perkiraan ?: '-' }}</div>
                                                <div class="account-name">{{ $row->nama ?: 'Tanpa akun' }}</div>
                                                <div class="muted">Aktivitas {{ $formatRpShort($row->aktivitas) }}</div>
                                            </td>
                                            <td class="text-end rp-debit">{{ $formatRpShort($row->debit) }}</td>
                                            <td class="text-end rp-kredit">{{ $formatRpShort($row->kredit) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="empty-state">Belum ada data akun.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="panel">
                        <div class="panel-head">
                            <h2 class="panel-title">Jurnal Terbaru</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-dashboard">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Transaksi</th>
                                        <th class="text-end">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentJournals as $row)
                                        <tr>
                                            <td class="fw-bold">{{ tanggal($row->tanggal) }}</td>
                                            <td>
                                                <div class="account-name">{{ $row->nomor_transaksi }}</div>
                                                <div class="muted">{{ $row->kode_perkiraan }} - {{ $row->nama_akun }}</div>
                                                <span class="type-pill">{{ $row->tipe_transaksi ?: 'Tanpa tipe' }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if ((float) $row->debit > 0)
                                                    <div class="rp-debit">{{ $formatRpShort($row->debit) }}</div>
                                                    <div class="muted">Debit</div>
                                                @else
                                                    <div class="rp-kredit">{{ $formatRpShort($row->kredit) }}</div>
                                                    <div class="muted">Kredit</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="empty-state">Belum ada jurnal terbaru.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-theme.app>
