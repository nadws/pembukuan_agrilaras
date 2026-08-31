<x-theme.app title="{{ $title }}" table="Y" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
            <div>
                <h5 class="mb-0">CV AGRI LARAS</h5>
                <small>Laporan Neraca</small>
            </div>
            <div class="d-flex gap-2 no-print">
                <a href="{{ route('jurnal-perkiraan.neraca.cetak', ['tanggal' => $reportDate->toDateString()]) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-print me-1"></i> Cetak
                </a>
                <a href="{{ route('laporan') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Laporan
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        @php
            $formatNumber = function ($value) {
                $number = (float) $value;
                return ($number < 0 ? '(' : '') . 'Rp ' . number_format(abs($number), 0, ',', '.') . ($number < 0 ? ')' : '');
            };
            $balanced = abs((float) $result['difference']) <= 1;
            $firstJournalDate = $result['firstJournalDate'];
        @endphp

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="get" class="balance-filter no-print">
            <div>
                <label for="tanggalNeraca" class="form-label">Posisi per tanggal</label>
                <input id="tanggalNeraca" type="date" name="tanggal" value="{{ $reportDate->toDateString() }}" class="form-control">
            </div>
            <div>
                <label for="cariAkunNeracaBaru" class="form-label">Cari akun</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input id="cariAkunNeracaBaru" type="search" class="form-control" placeholder="Kode atau nama akun" autocomplete="off">
                </div>
            </div>
            <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Tampilkan</button>
        </form>

        <div class="text-center report-heading">
            <h4>CV AGRI LARAS</h4>
            <h5>NERACA</h5>
            <p>Posisi per {{ $reportDate->translatedFormat('d F Y') }}</p>
        </div>

        <div class="row g-3 summary-row no-print">
            <div class="col-md-4"><div class="summary-card"><span>Total Aset</span><strong>{{ $formatNumber($result['totalAssets']) }}</strong></div></div>
            <div class="col-md-4"><div class="summary-card"><span>Total Kewajiban</span><strong>{{ $formatNumber($result['totalLiabilities']) }}</strong></div></div>
            <div class="col-md-4"><div class="summary-card"><span>Total Ekuitas</span><strong>{{ $formatNumber($result['totalEquity']) }}</strong></div></div>
        </div>

        <div class="balance-grid">
            <section class="balance-panel">
                <div class="panel-title">ASET</div>
                <div class="table-responsive">
                    <table class="table balance-table mb-0">
                        <thead><tr><th>Deskripsi</th><th class="text-end">Nilai (IDR)</th></tr></thead>
                        <tbody>
                            <tr class="section-row"><td colspan="2">ASET LANCAR</td></tr>
                            <tr class="subsection-row"><td colspan="2">Kas dan Bank</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['cashRows']])
                            <tr class="subtotal-row"><td>Jumlah Kas dan Bank</td><td class="text-end">{{ $formatNumber($result['cash']) }}</td></tr>

                            <tr class="subsection-row"><td colspan="2">Piutang dan Uang Muka</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['receivableRows']])
                            <tr class="subtotal-row"><td>Jumlah Piutang dan Uang Muka</td><td class="text-end">{{ $formatNumber($result['receivable']) }}</td></tr>

                            <tr class="subsection-row"><td colspan="2">Persediaan</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['inventoryRows']])
                            <tr class="subtotal-row"><td>Jumlah Persediaan</td><td class="text-end">{{ $formatNumber($result['inventory']) }}</td></tr>

                            <tr class="subsection-row"><td colspan="2">Aset Lancar Lainnya</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['otherCurrentRows']])
                            <tr class="subtotal-row"><td>Jumlah Aset Lancar Lainnya</td><td class="text-end">{{ $formatNumber($result['otherCurrent']) }}</td></tr>
                            <tr class="total-row"><td>JUMLAH ASET LANCAR</td><td class="text-end">{{ $formatNumber($result['currentAssets']) }}</td></tr>

                            <tr class="section-row"><td colspan="2">ASET TETAP</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['fixedAssetRows']])
                            <tr class="subtotal-row"><td>Jumlah Harga Perolehan</td><td class="text-end">{{ $formatNumber($result['fixedAssets']) }}</td></tr>
                            <tr class="subsection-row"><td colspan="2">Akumulasi Penyusutan</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['depreciationRows'], 'multiplier' => -1])
                            <tr class="subtotal-row"><td>Jumlah Akumulasi Penyusutan</td><td class="text-end">{{ $formatNumber(bcmul($result['accumulatedDepreciation'], '-1', 12)) }}</td></tr>
                            <tr class="total-row"><td>JUMLAH ASET TETAP NETO</td><td class="text-end">{{ $formatNumber($result['netFixedAssets']) }}</td></tr>
                            <tr class="grand-total"><td>TOTAL ASET</td><td class="text-end">{{ $formatNumber($result['totalAssets']) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="balance-panel">
                <div class="panel-title">KEWAJIBAN DAN EKUITAS</div>
                <div class="table-responsive">
                    <table class="table balance-table mb-0">
                        <thead><tr><th>Deskripsi</th><th class="text-end">Nilai (IDR)</th></tr></thead>
                        <tbody>
                            <tr class="section-row"><td colspan="2">KEWAJIBAN JANGKA PENDEK</td></tr>
                            <tr class="subsection-row"><td colspan="2">Hutang Usaha</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['payableRows']])
                            <tr class="subtotal-row"><td>Jumlah Hutang Usaha</td><td class="text-end">{{ $formatNumber($result['payable']) }}</td></tr>
                            <tr class="subsection-row"><td colspan="2">Kewajiban Lancar Lainnya</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['otherCurrentLiabilityRows']])
                            <tr class="subtotal-row"><td>Jumlah Kewajiban Lancar Lainnya</td><td class="text-end">{{ $formatNumber($result['otherCurrentLiability']) }}</td></tr>
                            <tr class="total-row"><td>JUMLAH KEWAJIBAN JANGKA PENDEK</td><td class="text-end">{{ $formatNumber($result['currentLiabilities']) }}</td></tr>

                            <tr class="section-row"><td colspan="2">KEWAJIBAN JANGKA PANJANG</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['longTermLiabilityRows']])
                            <tr class="total-row"><td>JUMLAH KEWAJIBAN JANGKA PANJANG</td><td class="text-end">{{ $formatNumber($result['longTermLiabilities']) }}</td></tr>
                            <tr class="grand-subtotal"><td>TOTAL KEWAJIBAN</td><td class="text-end">{{ $formatNumber($result['totalLiabilities']) }}</td></tr>

                            <tr class="section-row"><td colspan="2">EKUITAS</td></tr>
                            @include('jurnal_perkiraan.partials.neraca_rows', ['rows' => $result['equityRows']])
                            <tr class="account-row profit-row"><td><span class="account-link"><span>Laba/Rugi Berjalan</span></span></td><td class="text-end">{{ $formatNumber($result['currentProfit']) }}</td></tr>
                            <tr class="total-row"><td>JUMLAH EKUITAS</td><td class="text-end">{{ $formatNumber($result['totalEquity']) }}</td></tr>
                            <tr class="grand-total"><td>TOTAL KEWAJIBAN DAN EKUITAS</td><td class="text-end">{{ $formatNumber($result['liabilitiesAndEquity']) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="balance-status {{ $balanced ? 'is-balanced' : 'is-warning' }}">
            <div>
                <i class="fas {{ $balanced ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                <strong>{{ $balanced ? 'Neraca seimbang' : 'Neraca belum seimbang' }}</strong>
                <span>Selisih Aset dengan Kewajiban + Ekuitas</span>
            </div>
            <strong>{{ $formatNumber($result['difference']) }}</strong>
        </div>
        <p class="text-muted small mt-3 mb-0">Nilai dihitung dari seluruh jurnal perkiraan pada batch berstatus aktif sampai tanggal laporan. Klik nama akun untuk membuka buku besar.</p>
    </x-slot>

    @section('scripts')
        <style>
            .balance-filter { display:grid; grid-template-columns:220px minmax(260px, 460px) auto; align-items:end; gap:12px; margin-bottom:26px; padding:16px; border:1px solid #dce4f3; border-radius:12px; background:#f6f8fc; }
            .balance-filter .form-label { margin-bottom:5px; color:#42526e; font-size:12px; font-weight:700; }
            .report-heading { margin-bottom:22px; color:#17366f; }
            .report-heading h4, .report-heading h5 { margin-bottom:4px; font-weight:750; }
            .report-heading p { margin:0; color:#71809c; }
            .summary-row { margin-bottom:18px; }
            .summary-card { display:flex; min-height:86px; flex-direction:column; justify-content:center; padding:16px 18px; border:1px solid #dfe6f2; border-radius:12px; background:#fff; box-shadow:0 5px 16px rgba(33,55,105,.06); }
            .summary-card span { color:#71809c; font-size:12px; font-weight:700; text-transform:uppercase; }
            .summary-card strong { margin-top:5px; color:#193873; font-size:20px; }
            .balance-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; align-items:start; }
            .balance-panel { overflow:hidden; border:1px solid #dce3ef; border-radius:12px; background:#fff; }
            .panel-title { padding:13px 16px; background:#2f519f; color:#fff; font-weight:750; letter-spacing:.25px; }
            .balance-table { font-size:12px; }
            .balance-table th { padding:10px 12px; background:#eaf0fb; color:#30486f; border-bottom:1px solid #cad5e8; }
            .balance-table td { padding:8px 12px; vertical-align:middle; }
            .balance-table td:last-child { width:175px; white-space:nowrap; font-variant-numeric:tabular-nums; }
            .section-row td { padding-top:14px; background:#eef2f8; color:#203d75; font-weight:800; }
            .subsection-row td { color:#4c5d79; font-weight:750; }
            .account-link { display:flex; gap:8px; color:#52627d !important; text-decoration:none !important; }
            a.account-link:hover { color:#3159b8 !important; }
            .account-code { min-width:68px; color:#8793a8; font-family:monospace; }
            .subtotal-row td { border-top:1px solid #d7deea; color:#354968; font-weight:700; }
            .total-row td { border-top:2px solid #8ea0bd; background:#f7f9fc; color:#213c70; font-weight:800; }
            .grand-subtotal td { border-top:2px solid #526b99; background:#edf2fb; color:#183873; font-weight:800; }
            .grand-total td { border-top:2px solid #213f7a; border-bottom:3px double #213f7a; background:#e7eefb; color:#16366f; font-size:13px; font-weight:850; }
            .balance-status { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-top:18px; padding:14px 18px; border-radius:10px; }
            .balance-status > div { display:flex; align-items:center; gap:9px; }
            .balance-status span { color:inherit; opacity:.75; font-size:12px; }
            .balance-status.is-balanced { border:1px solid #b9e5cf; background:#effaf4; color:#16794b; }
            .balance-status.is-warning { border:1px solid #f0d3a2; background:#fff8eb; color:#9a6010; }
            @media (max-width:991.98px) { .balance-grid { grid-template-columns:1fr; } }
            @media (max-width:767.98px) {
                .balance-filter { grid-template-columns:1fr; }
                .balance-table td:last-child { width:145px; }
                .balance-status, .balance-status > div { align-items:flex-start; flex-direction:column; }
                .balance-status span { display:block; }
            }
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 12mm 10mm 14mm;
                }

                html,
                body {
                    width: 100% !important;
                    min-width: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #fff !important;
                    color: #111 !important;
                    font-family: Arial, Helvetica, sans-serif !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                .no-print,
                body > header,
                body > footer,
                .content-wrapper > footer,
                .card-header {
                    display: none !important;
                }

                .content-wrapper,
                .content-wrapper.container-fluid,
                .page-content,
                .page-content > .row,
                .page-content > .row > [class*="col-"] {
                    width: 100% !important;
                    max-width: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .card {
                    width: 100% !important;
                    margin: 0 !important;
                    border: 0 !important;
                    border-radius: 0 !important;
                    box-shadow: none !important;
                }

                .card-body {
                    padding: 0 !important;
                }

                .report-heading {
                    margin: 0 0 8mm !important;
                    color: #111 !important;
                }

                .report-heading h4 {
                    margin: 0 0 1mm !important;
                    font-size: 15pt !important;
                }

                .report-heading h5 {
                    margin: 0 0 1mm !important;
                    font-size: 13pt !important;
                }

                .report-heading p {
                    color: #333 !important;
                    font-size: 9pt !important;
                }

                .balance-grid {
                    display: block !important;
                }

                .balance-panel {
                    width: 100% !important;
                    overflow: visible !important;
                    border: 1px solid #666 !important;
                    border-radius: 0 !important;
                    background: #fff !important;
                }

                .balance-panel + .balance-panel {
                    break-before: page;
                    page-break-before: always;
                }

                .panel-title {
                    padding: 3mm 3.5mm !important;
                    background: #dce5f5 !important;
                    color: #111 !important;
                    font-size: 11pt !important;
                    letter-spacing: 0 !important;
                    border-bottom: 1px solid #666 !important;
                }

                .table-responsive {
                    overflow: visible !important;
                }

                .balance-table {
                    width: 100% !important;
                    border-collapse: collapse !important;
                    font-size: 8.5pt !important;
                }

                .balance-table thead {
                    display: table-header-group !important;
                }

                .balance-table tr {
                    break-inside: avoid !important;
                    page-break-inside: avoid !important;
                }

                .balance-table th,
                .balance-table td {
                    padding: 1.8mm 2.5mm !important;
                    border-color: #bbb !important;
                    color: #111 !important;
                    background: #fff !important;
                }

                .balance-table th {
                    background: #edf1f7 !important;
                    border-bottom: 1px solid #777 !important;
                    font-size: 8pt !important;
                }

                .balance-table th:last-child,
                .balance-table td:last-child {
                    width: 47mm !important;
                }

                .section-row td {
                    padding-top: 2.5mm !important;
                    background: #e4e9f1 !important;
                    border-top: 1.5px solid #555 !important;
                }

                .subsection-row td {
                    background: #f4f5f7 !important;
                }

                .account-link,
                .account-code,
                a.account-link,
                a.account-link:visited {
                    color: #111 !important;
                    text-decoration: none !important;
                }

                .subtotal-row td {
                    border-top: 1px solid #888 !important;
                    font-weight: 700 !important;
                }

                .total-row td,
                .grand-subtotal td {
                    background: #eef1f5 !important;
                    border-top: 1.5px solid #444 !important;
                    font-weight: 800 !important;
                }

                .grand-total td {
                    background: #dce5f5 !important;
                    border-top: 2px solid #222 !important;
                    border-bottom: 3px double #222 !important;
                    font-size: 9pt !important;
                }

                .balance-status {
                    margin-top: 6mm !important;
                    padding: 3mm !important;
                    border: 1px solid #777 !important;
                    background: #fff !important;
                    color: #111 !important;
                    break-inside: avoid !important;
                }

                .balance-status span {
                    color: #333 !important;
                }

                .text-muted.small {
                    margin-top: 3mm !important;
                    color: #444 !important;
                    font-size: 7.5pt !important;
                }
            }
        </style>
        <script>
            document.getElementById('cariAkunNeracaBaru')?.addEventListener('input', function () {
                const keyword = this.value.trim().toLocaleLowerCase('id-ID');
                document.querySelectorAll('.balance-table .account-row').forEach(row => {
                    row.classList.toggle('d-none', Boolean(keyword) && !row.dataset.accountName?.includes(keyword));
                });
            });
        </script>
    @endsection
</x-theme.app>
