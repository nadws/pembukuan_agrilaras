<x-theme.app title="{{ $title }}" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Laporan Akhir Bulan</h5>
                <small class="text-muted">Ringkasan posisi piutang, penarikan uang, dan uang penjualan</small>
            </div>
            <a href="{{ route('laporan') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Daftar Laporan</a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .month-filter { padding: 16px; border: 1px solid #dce4f2; border-radius: 12px; background: #f7f9fd; }
            .summary-card { height: 100%; padding: 20px 24px; border: 1px solid #e1e7f2; border-radius: 15px; background: #fff; box-shadow: 0 7px 20px rgba(35,60,115,.07); }
            .summary-card small { display: block; color: #7583a0; font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
            .summary-card strong { display: block; margin: 8px 0; color: #18366f; font-size: 25px; }
            .summary-card span { color: #74829a; font-size: 12px; }
            .change-up { color: #c0392b !important; }
            .change-down { color: #16834b !important; }
            .withdrawal-report { overflow: hidden; border: 1px solid #dce4f2; border-radius: 14px; background: #fff; }
            .withdrawal-report table { width: 100%; min-width: 0; margin: 0; table-layout: fixed; }
            .withdrawal-report thead th { padding: 9px 7px; background: #304f9e; color: #fff; font-size: 11px; line-height: 1.25; white-space: normal; }
            .withdrawal-report th:first-child, .withdrawal-report td:first-child { width: 37%; }
            .withdrawal-report th.amount, .withdrawal-report td.amount { width: 21%; }
            .withdrawal-report td { padding: 9px 7px; font-size: 12px; }
            .withdrawal-report .amount { text-align: right; white-space: nowrap; font-size: 11px; font-variant-numeric: tabular-nums; }
            .withdrawal-report .detail-link { color: #3457b2; text-decoration: none; }
            .withdrawal-report .detail-link:hover { text-decoration: underline; }
            .withdrawal-report .amount .detail-link { border-bottom: 1px dotted #3457b2; }
            .withdrawal-report .total-row td { border-top: 2px solid #304f9e; background: #eef3ff; font-weight: 750; }

            .penjualan-report thead th { background: #198754; }
            .penjualan-report th:first-child, .penjualan-report td:first-child { width: 48%; }
            .penjualan-report th.amount, .penjualan-report td.amount { width: 26%; }
            .penjualan-report .total-row td { border-top: 2px solid #198754; background: #f0fdf4; }

            .gear-button { display: inline-flex; width: 32px; height: 32px; align-items: center; justify-content: center; border-radius: 8px; font-size: 13px; }
            .transaction-check { padding: 10px 12px; border: 1px solid #e1e7f2; border-radius: 10px; background: #f9fbff; }
            .transaction-check + .transaction-check { margin-top: 8px; }
            .account-list { max-height: 260px; overflow-y: auto; padding-right: 4px; }
            .account-list .transaction-check { padding: 8px 10px; font-size: 13px; }
            @media(max-width: 991px) {
                .withdrawal-report { overflow-x: auto; }
                .withdrawal-report table { min-width: 600px; table-layout: auto; }
                .withdrawal-report th:first-child, .withdrawal-report td:first-child, .withdrawal-report th.amount, .withdrawal-report td.amount { width: auto; }
            }
        </style>

        {{-- Top Month Filter --}}
        <form method="get" class="month-filter mb-4">
            <div class="row g-2 align-items-end">
                @foreach($selectedTransactionTypes as $type)
                    <input type="hidden" name="tipe[]" value="{{ $type }}">
                @endforeach
                @foreach($selectedAccountIds as $accountId)
                    <input type="hidden" name="akun[]" value="{{ $accountId }}">
                @endforeach
                @if($allTransactionTypes)
                    <input type="hidden" name="semua_tipe" value="1">
                @endif

                @foreach($selectedPenjualanTypes as $type)
                    <input type="hidden" name="tipe_penjualan[]" value="{{ $type }}">
                @endforeach
                @foreach($selectedPenjualanAccountIds as $accountId)
                    <input type="hidden" name="akun_penjualan[]" value="{{ $accountId }}">
                @endforeach
                @if($allPenjualanTypes)
                    <input type="hidden" name="semua_tipe_penjualan" value="1">
                @endif

                <div class="col-sm-4">
                    <label class="form-label fw-semibold" for="tgl1">Dari tanggal</label>
                    <input type="date" id="tgl1" name="tgl1" class="form-control" value="{{ $startDate->toDateString() }}" required>
                </div>
                <div class="col-sm-3">
                    <label class="form-label fw-semibold" for="tgl2">Sampai tanggal</label>
                    <input type="date" id="tgl2" name="tgl2" class="form-control" value="{{ $currentCutoff->toDateString() }}" required>
                </div>
                <div class="col-sm-3">
                    <button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan</button>
                </div>
            </div>
        </form>

        @php 
            $fmt = fn($value) => 'Rp ' . number_format((float)$value, 0, ',', '.'); 
            $difference = $currentTotal - $previousTotal; 
        @endphp

        {{-- Piutang KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="summary-card">
                    <small>Saldo Piutang Awal</small>
                    <strong>{{ $fmt($previousTotal) }}</strong>
                    <span>Posisi s.d. {{ $previousCutoff->translatedFormat('d F Y') }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <small>Saldo Piutang Akhir</small>
                    <strong>{{ $fmt($currentTotal) }}</strong>
                    <span>Posisi s.d. {{ $currentCutoff->translatedFormat('d F Y') }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <small>Perubahan Saldo</small>
                    <strong class="{{ $difference > 0 ? 'change-up' : ($difference < 0 ? 'change-down' : '') }}">{{ $fmt($difference) }}</strong>
                    <span>{{ $difference > 0 ? 'Piutang bertambah' : ($difference < 0 ? 'Piutang berkurang' : 'Tidak ada perubahan') }}</span>
                </div>
            </div>
        </div>

        {{-- Dual Tables: Laporan Penarikan Uang & Laporan Uang Penjualan --}}
        <div class="row g-3 mb-4">
            {{-- 1. Laporan Penarikan Uang --}}
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-2">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0">Laporan Penarikan Uang</h5>
                            <button type="button" class="btn btn-outline-primary gear-button" data-bs-toggle="modal" data-bs-target="#filterTipeTransaksi" title="Setting Akun & Tipe Transaksi Penarikan Uang">
                                <i class="fas fa-cog"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            Periode {{ $startDate->translatedFormat('d F Y') }} – {{ $currentCutoff->translatedFormat('d F Y') }} · {{ $selectedTransactionTypes ? collect($selectedTransactionTypes)->map(fn($type) => $transactionTypeOptions[$type]['label'])->implode(', ') : 'Semua tipe transaksi' }}
                        </small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Total Uang Ditarik</small>
                        <strong class="text-primary fs-5">{{ $fmt($withdrawalTotal) }}</strong>
                    </div>
                </div>
                <div class="withdrawal-report table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nama Akun</th>
                                <th class="amount">Debit</th>
                                <th class="amount">Kredit</th>
                                <th class="amount">Total Uang Ditarik</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($withdrawalRows as $row)
                                @php 
                                    $detailUrl = route('laporan.akhir-bulan.penarikan-detail', [
                                        'akun' => $row->id_akun_perkiraan,
                                        'tgl1' => $startDate->toDateString(),
                                        'tgl2' => $currentCutoff->toDateString(),
                                        'tipe' => $selectedTransactionTypes,
                                        'akun_filter' => $selectedAccountIds,
                                        'semua_tipe' => $allTransactionTypes ? 1 : null,
                                        'tipe_penjualan' => $selectedPenjualanTypes,
                                        'akun_penjualan' => $selectedPenjualanAccountIds,
                                        'semua_tipe_penjualan' => $allPenjualanTypes ? 1 : null,
                                    ]); 
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ $detailUrl }}" class="detail-link"><strong>{{ $row->nama }}</strong></a>
                                        <small class="d-block text-muted">{{ $row->kode_perkiraan }}</small>
                                    </td>
                                    <td class="amount">{{ $fmt($row->debit) }}</td>
                                    <td class="amount">{{ $fmt($row->kredit) }}</td>
                                    <td class="amount fw-semibold">
                                        <a href="{{ $detailUrl }}" class="detail-link" title="Lihat detail transaksi">{{ $fmt($row->total) }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Tidak ada transaksi pada periode ini.</td>
                                </tr>
                            @endforelse
                            @if($withdrawalRows->isNotEmpty())
                                <tr class="total-row">
                                    <td>Total</td>
                                    <td class="amount">{{ $fmt($withdrawalDebit) }}</td>
                                    <td class="amount">{{ $fmt($withdrawalCredit) }}</td>
                                    <td class="amount">{{ $fmt($withdrawalTotal) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. Laporan Uang Penjualan --}}
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-2">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0">Laporan Uang Penjualan</h5>
                            <button type="button" class="btn btn-outline-success gear-button" data-bs-toggle="modal" data-bs-target="#filterTipePenjualan" title="Setting Akun & Tipe Transaksi Uang Penjualan">
                                <i class="fas fa-cog"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            Periode {{ $startDate->translatedFormat('d F Y') }} – {{ $currentCutoff->translatedFormat('d F Y') }} · {{ $selectedPenjualanTypes ? collect($selectedPenjualanTypes)->map(fn($type) => $transactionTypeOptions[$type]['label'])->implode(', ') : 'Semua tipe transaksi' }}
                        </small>
                    </div>
                </div>
                <div class="withdrawal-report penjualan-report table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nama Akun</th>
                                <th class="amount">Debit</th>
                                <th class="amount">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penjualanRows as $row)
                                @php 
                                    $detailPenjualanUrl = route('laporan.akhir-bulan.penjualan-detail', [
                                        'akun' => $row->id_akun_perkiraan,
                                        'tgl1' => $startDate->toDateString(),
                                        'tgl2' => $currentCutoff->toDateString(),
                                        'tipe' => $selectedTransactionTypes,
                                        'akun_filter' => $selectedAccountIds,
                                        'semua_tipe' => $allTransactionTypes ? 1 : null,
                                        'tipe_penjualan' => $selectedPenjualanTypes,
                                        'akun_penjualan' => $selectedPenjualanAccountIds,
                                        'semua_tipe_penjualan' => $allPenjualanTypes ? 1 : null,
                                    ]); 
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ $detailPenjualanUrl }}" class="detail-link"><strong>{{ $row->nama }}</strong></a>
                                        <small class="d-block text-muted">{{ $row->kode_perkiraan }}</small>
                                    </td>
                                    <td class="amount">
                                        <a href="{{ $detailPenjualanUrl }}" class="detail-link" title="Lihat detail transaksi">{{ $fmt($row->debit) }}</a>
                                    </td>
                                    <td class="amount">
                                        <a href="{{ $detailPenjualanUrl }}" class="detail-link" title="Lihat detail transaksi">{{ $fmt($row->kredit) }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Tidak ada transaksi pada periode ini.</td>
                                </tr>
                            @endforelse
                            @if($penjualanRows->isNotEmpty())
                                <tr class="total-row">
                                    <td>Total</td>
                                    <td class="amount">{{ $fmt($penjualanDebit) }}</td>
                                    <td class="amount">{{ $fmt($penjualanCredit) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <p class="small text-muted mb-0">
            Saldo piutang, penarikan uang, dan uang penjualan dihitung dari <code>jurnal_perkiraan</code> pada batch jurnal berstatus aktif.
        </p>

        {{-- Modal Setting: Filter Penarikan Uang --}}
        <div class="modal fade" id="filterTipeTransaksi" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="get">
                        <input type="hidden" name="tgl1" value="{{ $startDate->toDateString() }}">
                        <input type="hidden" name="tgl2" value="{{ $currentCutoff->toDateString() }}">

                        {{-- Preserve Penjualan Filter --}}
                        @foreach($selectedPenjualanTypes as $type)
                            <input type="hidden" name="tipe_penjualan[]" value="{{ $type }}">
                        @endforeach
                        @foreach($selectedPenjualanAccountIds as $accountId)
                            <input type="hidden" name="akun_penjualan[]" value="{{ $accountId }}">
                        @endforeach
                        @if($allPenjualanTypes)
                            <input type="hidden" name="semua_tipe_penjualan" value="1">
                        @endif

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Filter Laporan Penarikan Uang</h5>
                                <small class="text-muted">Pilih tipe transaksi dan akun kas / bank</small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h6 class="text-primary mb-2">Tipe Transaksi</h6>
                            @foreach($transactionTypeOptions as $key => $option)
                                <label class="transaction-check d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0" type="checkbox" name="tipe[]" value="{{ $key }}" @checked(in_array($key, $selectedTransactionTypes, true))>
                                    <span>{{ $option['label'] }}</span>
                                </label>
                            @endforeach

                            <div class="border-top mt-3 pt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-primary mb-0">Akun Kas / Bank (multi-pilih)</h6>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-link p-0 text-decoration-none small btn-check-all" data-target="#accountListPenarikan">Pilih Semua</button>
                                        <span class="text-muted">·</span>
                                        <button type="button" class="btn btn-link p-0 text-decoration-none small text-danger btn-uncheck-all" data-target="#accountListPenarikan">Kosongkan</button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm search-account" data-target="#accountListPenarikan" placeholder="Cari kode atau nama akun...">
                                </div>
                                <div class="account-list" id="accountListPenarikan">
                                    @foreach($availableAccounts as $account)
                                        <label class="transaction-check d-flex align-items-center gap-3 account-item">
                                            <input class="form-check-input mt-0" type="checkbox" name="akun[]" value="{{ $account->id_akun_perkiraan }}" @checked(in_array((int)$account->id_akun_perkiraan, $selectedAccountIds, true))>
                                            <span class="account-label">{{ $account->kode_perkiraan }} - {{ $account->nama }}</span>
                                        </label>
                                    @endforeach
                                    <div class="no-accounts text-center text-muted py-3 d-none"><small>Akun tidak ditemukan</small></div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-3">Centang akun-akun kas/bank yang ingin ditampilkan pada tabel Penarikan Uang.</small>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ route('laporan.akhir-bulan', [
                                'tgl1' => $startDate->toDateString(),
                                'tgl2' => $currentCutoff->toDateString(),
                                'semua_tipe' => 1,
                                'akun' => $selectedAccountIds,
                                'tipe_penjualan' => $selectedPenjualanTypes,
                                'akun_penjualan' => $selectedPenjualanAccountIds,
                                'semua_tipe_penjualan' => $allPenjualanTypes ? 1 : null,
                            ]) }}" class="btn btn-light">Tampilkan Semua Tipe</a>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Terapkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Setting: Filter Uang Penjualan --}}
        <div class="modal fade" id="filterTipePenjualan" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="get">
                        <input type="hidden" name="tgl1" value="{{ $startDate->toDateString() }}">
                        <input type="hidden" name="tgl2" value="{{ $currentCutoff->toDateString() }}">

                        {{-- Preserve Penarikan Filter --}}
                        @foreach($selectedTransactionTypes as $type)
                            <input type="hidden" name="tipe[]" value="{{ $type }}">
                        @endforeach
                        @foreach($selectedAccountIds as $accountId)
                            <input type="hidden" name="akun[]" value="{{ $accountId }}">
                        @endforeach
                        @if($allTransactionTypes)
                            <input type="hidden" name="semua_tipe" value="1">
                        @endif

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Filter Laporan Uang Penjualan</h5>
                                <small class="text-muted">Pilih tipe transaksi dan akun kas / bank / penjualan</small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h6 class="text-success mb-2">Tipe Transaksi</h6>
                            @foreach($transactionTypeOptions as $key => $option)
                                <label class="transaction-check d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0" type="checkbox" name="tipe_penjualan[]" value="{{ $key }}" @checked(in_array($key, $selectedPenjualanTypes, true))>
                                    <span>{{ $option['label'] }}</span>
                                </label>
                            @endforeach

                            <div class="border-top mt-3 pt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-success mb-0">Akun Kas / Bank (multi-pilih)</h6>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-link p-0 text-decoration-none small text-success btn-check-all" data-target="#accountListPenjualan">Pilih Semua</button>
                                        <span class="text-muted">·</span>
                                        <button type="button" class="btn btn-link p-0 text-decoration-none small text-danger btn-uncheck-all" data-target="#accountListPenjualan">Kosongkan</button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm search-account" data-target="#accountListPenjualan" placeholder="Cari kode atau nama akun...">
                                </div>
                                <div class="account-list" id="accountListPenjualan">
                                    @foreach($availableAccounts as $account)
                                        <label class="transaction-check d-flex align-items-center gap-3 account-item">
                                            <input class="form-check-input mt-0" type="checkbox" name="akun_penjualan[]" value="{{ $account->id_akun_perkiraan }}" @checked(in_array((int)$account->id_akun_perkiraan, $selectedPenjualanAccountIds, true))>
                                            <span class="account-label">{{ $account->kode_perkiraan }} - {{ $account->nama }}</span>
                                        </label>
                                    @endforeach
                                    <div class="no-accounts text-center text-muted py-3 d-none"><small>Akun tidak ditemukan</small></div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-3">Centang akun-akun kas/bank yang ingin ditampilkan pada tabel Uang Penjualan.</small>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ route('laporan.akhir-bulan', [
                                'tgl1' => $startDate->toDateString(),
                                'tgl2' => $currentCutoff->toDateString(),
                                'tipe' => $selectedTransactionTypes,
                                'akun' => $selectedAccountIds,
                                'semua_tipe' => $allTransactionTypes ? 1 : null,
                                'semua_tipe_penjualan' => 1,
                                'akun_penjualan' => $selectedPenjualanAccountIds,
                            ]) }}" class="btn btn-light">Tampilkan Semua Tipe</a>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-success"><i class="fas fa-filter me-1"></i> Terapkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    @section('scripts')
        <script>
            $(function() {
                $('.search-account').on('input', function() {
                    var q = $(this).val().toLowerCase().trim();
                    var target = $(this).data('target');
                    var visibleCount = 0;

                    $(target + ' .account-item').each(function() {
                        var text = $(this).find('.account-label').text().toLowerCase();
                        if (text.indexOf(q) > -1) {
                            $(this).removeClass('d-none').addClass('d-flex');
                            visibleCount++;
                        } else {
                            $(this).addClass('d-none').removeClass('d-flex');
                        }
                    });

                    if (visibleCount === 0) {
                        $(target + ' .no-accounts').removeClass('d-none');
                    } else {
                        $(target + ' .no-accounts').addClass('d-none');
                    }
                });

                $('.btn-check-all').on('click', function() {
                    var target = $(this).data('target');
                    $(target + ' .account-item:not(.d-none) input[type="checkbox"]').prop('checked', true);
                });

                $('.btn-uncheck-all').on('click', function() {
                    var target = $(this).data('target');
                    $(target + ' .account-item:not(.d-none) input[type="checkbox"]').prop('checked', false);
                });

                $('.modal').on('hidden.bs.modal', function() {
                    $(this).find('.search-account').val('').trigger('input');
                });
            });
        </script>
    @endsection
</x-theme.app>
