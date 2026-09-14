<x-theme.app title="{{ $title }}" table="Y" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><h5 class="mb-0">CV AGRI LARAS</h5><small>{{ $title }}</small></div>
            <div class="d-flex gap-2 report-actions">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#parameterLaporan"><i class="fas fa-calendar-alt me-1"></i> Parameter Laporan</button>
                @if ($result)
                    <a href="{{ route('jurnal-perkiraan.laba-rugi.export', ['bulan_dari'=>$start->month, 'tahun_dari'=>$start->year, 'bulan_sampai'=>$end->month, 'tahun_sampai'=>$end->year]) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
                @endif
                <a href="{{ route('jurnal-perkiraan.index') }}" class="btn btn-light btn-sm">Riwayat Import</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @if ($result)
            <style>.laporan-accurate .budget-column{background:#fff9e8}.laporan-accurate .variance-good{color:#16834b;font-weight:700}.laporan-accurate .variance-bad{color:#d33a42;font-weight:700}</style>
            @php
                $periods = $result['periods'];
                $formatNumber = function ($value) {
                    $number = (float) $value;
                    $formatted = rtrim(rtrim(number_format(abs($number), 2, ',', '.'), '0'), ',');
                    return ($number < 0 ? '-' : '') . ($formatted === '' ? '0' : $formatted);
                };
                $totalPeriods = fn ($values) => array_reduce($values, fn ($carry, $value) => bcadd($carry, $value, 12), '0.000000000000');
            @endphp
            <div class="text-center mb-4">
                <h5 class="mb-1">Laba/Rugi (Multi Periode)</h5>
                <div>Dari Periode {{ $months[$start->month] }} {{ $start->year }} s/d {{ $months[$end->month] }} {{ $end->year }}</div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-2">
                <div style="width:min(100%, 420px)">
                    <label for="cariAkunLabaRugi" class="form-label mb-1">Cari Nama Akun</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input id="cariAkunLabaRugi" type="search" class="form-control" placeholder="Ketik nama akun..." autocomplete="off">
                    </div>
                </div>
                <small id="hasilCariAkun" class="text-muted"></small>
            </div>
            <div class="table-responsive laporan-scroll">
                <table class="table table-sm laporan-accurate align-middle">
                    <thead><tr><th style="min-width:360px">Deskripsi</th>@foreach ($periods as $period)<th class="text-end" style="min-width:145px">{{ $months[$period->month] }} {{ $period->year }} (IDR)</th>@endforeach<th class="text-end period-total-column" style="min-width:155px">Total Aktual</th></tr></thead>
                    <tbody>
                        <tr class="section-row"><td colspan="{{ $periods->count() + 2 }}">PENDAPATAN</td></tr>
                        @include('jurnal_perkiraan.partials.laba_rugi_rows', ['rows' => $result['revenueRows']])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'Jumlah Pendapatan', 'values' => $result['revenue'], 'budget'=>$result['revenueBudget'], 'isIncome'=>true])

                        <tr class="section-row"><td colspan="{{ $periods->count() + 2 }}">BIAYA POKOK PENJUALAN</td></tr>
                        @include('jurnal_perkiraan.partials.laba_rugi_rows', ['rows' => $result['cogsRows']])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'Jumlah Biaya Pokok Penjualan', 'values' => $result['cogs'], 'budget'=>$result['cogsBudget'], 'isIncome'=>false])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'LABA KOTOR', 'values' => $result['gross'], 'budget'=>$result['grossBudget'], 'isIncome'=>true, 'highlight' => true])

                        <tr class="section-row"><td colspan="{{ $periods->count() + 2 }}">BIAYA OPERASIONAL</td></tr>
                        @include('jurnal_perkiraan.partials.laba_rugi_rows', ['rows' => $result['operatingRows']])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'Jumlah Biaya Operasional', 'values' => $result['operating'], 'budget'=>$result['operatingBudget'], 'isIncome'=>false])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'PENDAPATAN OPERASIONAL', 'values' => $result['operatingIncome'], 'budget'=>$result['operatingIncomeBudget'], 'isIncome'=>true, 'highlight' => true])

                        <tr class="section-row"><td colspan="{{ $periods->count() + 2 }}">PENDAPATAN DAN BIAYA NON OPERASIONAL</td></tr>
                        <tr class="subsection-row"><td colspan="{{ $periods->count() + 2 }}">Pendapatan Non Operasional</td></tr>
                        @include('jurnal_perkiraan.partials.laba_rugi_rows', ['rows' => $result['otherIncomeRows']])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'Jumlah Pendapatan Non Operasional', 'values' => $result['otherIncome'], 'budget'=>$result['otherIncomeBudget'], 'isIncome'=>true])
                        <tr class="subsection-row"><td colspan="{{ $periods->count() + 2 }}">Biaya Non Operasional</td></tr>
                        @include('jurnal_perkiraan.partials.laba_rugi_rows', ['rows' => $result['otherExpenseRows']])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'Jumlah Biaya Non Operasional', 'values' => $result['otherExpense'], 'budget'=>$result['otherExpenseBudget'], 'isIncome'=>false])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'Jumlah Pendapatan dan Biaya Non Operasional', 'values' => $result['otherNet'], 'budget'=>$result['otherNetBudget'], 'isIncome'=>true])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'LABA/RUGI SEBELUM PENYUSUTAN', 'values' => $result['beforeDepreciation'], 'budget'=>$result['beforeDepreciationBudget'], 'isIncome'=>true, 'highlight' => true])

                        <tr class="section-row"><td colspan="{{ $periods->count() + 2 }}">BIAYA PENYUSUTAN</td></tr>
                        @include('jurnal_perkiraan.partials.laba_rugi_rows', ['rows' => $result['depreciationRows']])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'Jumlah Biaya Penyusutan', 'values' => $result['depreciationTotal'], 'budget'=>$result['depreciationBudget'], 'isIncome'=>false])
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'LABA/RUGI BERSIH (Sebelum Pajak)', 'values' => $result['beforeTax'], 'budget'=>$result['beforeTaxBudget'], 'isIncome'=>true, 'highlight' => true])

                        @if ($result['taxRows']->isNotEmpty())
                            <tr class="section-row"><td colspan="{{ $periods->count() + 2 }}">PAJAK PENGHASILAN</td></tr>
                            @include('jurnal_perkiraan.partials.laba_rugi_rows', ['rows' => $result['taxRows']])
                            @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'Jumlah Pajak Penghasilan', 'values' => $result['taxTotal'], 'budget'=>$result['taxBudget'], 'isIncome'=>false])
                        @endif
                        @include('jurnal_perkiraan.partials.laba_rugi_total', ['label' => 'LABA/RUGI BERSIH (Setelah Pajak)', 'values' => $result['afterTax'], 'budget'=>$result['afterTaxBudget'], 'isIncome'=>true, 'highlight' => true])
                    </tbody>
                </table>
            </div>
            <p class="text-muted">Nilai hanya berasal dari batch import berstatus aktif.</p>
        @else
            <div class="text-center text-muted py-5"><i class="fas fa-calendar-alt fa-3x mb-3"></i><p>Pilih parameter periode untuk menampilkan laporan.</p></div>
        @endif

        <div class="modal fade" id="parameterLaporan" tabindex="-1">
            <div class="modal-dialog modal-lg"><div class="modal-content">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title text-white">Parameter Laporan</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <form method="get"><div class="modal-body">
                    <ul class="nav nav-tabs mb-4"><li class="nav-item"><span class="nav-link active text-danger">Umum</span></li><li class="nav-item"><span class="nav-link disabled">Kolom</span></li></ul>
                    <h5 class="border-bottom pb-2 mb-4">Tanggal</h5>
                    <div class="row align-items-center mb-3"><label class="col-md-3 col-form-label">Dari Periode</label><div class="col-md-5"><select name="bulan_dari" class="form-select">@foreach ($months as $number => $name)<option value="{{ $number }}" @selected((int) request('bulan_dari', now()->month) === $number)>{{ $name }}</option>@endforeach</select></div><div class="col-md-4"><select name="tahun_dari" class="form-select">@foreach ($years as $year)<option value="{{ $year }}" @selected((int) request('tahun_dari', now()->year) === $year)>{{ $year }}</option>@endforeach</select></div></div>
                    <div class="row align-items-center end-period-fields"><label class="col-md-3 col-form-label">s/d Periode</label><div class="col-md-5"><select name="bulan_sampai" class="form-select">@foreach ($months as $number => $name)<option value="{{ $number }}" @selected((int) request('bulan_sampai', now()->month) === $number)>{{ $name }}</option>@endforeach</select></div><div class="col-md-4"><select name="tahun_sampai" class="form-select">@foreach ($years as $year)<option value="{{ $year }}" @selected((int) request('tahun_sampai', now()->year) === $year)>{{ $year }}</option>@endforeach</select></div></div>
                    <div class="mobile-period-note alert alert-info mt-3 mb-0"><i class="fas fa-mobile-alt me-1"></i> Pada handphone laporan ditampilkan untuk satu bulan.</div>
                </div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Tampilkan Laporan</button></div></form>
            </div></div>
        </div>
    </x-slot>

    @section('scripts')
        <style>
            .laporan-scroll {
                height: calc(100vh - 140px);
                min-height: 620px;
                overflow: auto;
                position: relative;
            }
            .laporan-accurate thead th {
                position: sticky;
                top: 0;
                z-index: 10;
                background: #435ebe !important;
                color: #fff !important;
                box-shadow: 0 2px 0 rgba(0, 0, 0, .2);
                white-space: nowrap;
            }
            .laporan-accurate thead th:first-child {
                left: 0;
                z-index: 20;
                box-shadow: 2px 2px 3px rgba(0, 0, 0, .25);
            }
            .laporan-accurate .account-row > td:first-child,
            .laporan-accurate .subsection-row > td:first-child,
            .laporan-accurate .report-total > td:first-child,
            .laporan-accurate .report-highlight > td:first-child {
                position: sticky;
                left: 0;
                z-index: 5;
                background-color: var(--bs-card-bg, var(--bs-body-bg, #fff));
                box-shadow: 2px 0 3px rgba(0, 0, 0, .16);
            }
            .laporan-accurate tbody a,
            .laporan-accurate tbody a:visited {
                color: #344767 !important;
                text-decoration: none;
            }
            .laporan-accurate tbody a:hover {
                color: #0d6efd !important;
                text-decoration: underline;
            }
            .laporan-accurate .report-amount-link {
                display:block;
                font-variant-numeric:tabular-nums;
            }
            .laporan-accurate .section-row td { background:#eef2f7; font-weight:700; border-top:2px solid #9aa4b2; }
            .laporan-accurate .subsection-row td { font-weight:700; padding-left:24px; }
            .laporan-accurate .report-total td { border-top:1px solid #495057; border-bottom:1px solid #495057; font-weight:700; }
            .laporan-accurate .report-highlight td { background:#f6f8fb; border-top:2px solid #212529; border-bottom:2px solid #212529; font-weight:700; }
            .mobile-period-note { display:none; }
            @media (max-width: 767.98px) {
                .report-actions { width:100%; display:grid !important; grid-template-columns:repeat(2,minmax(0,1fr)); }
                .report-actions .btn { display:flex; align-items:center; justify-content:center; min-height:40px; white-space:nowrap; }
                .laporan-scroll { height:auto; min-height:0; max-height:calc(100vh - 250px); border:1px solid #dfe5f1; border-radius:10px; }
                .laporan-accurate { font-size:12px; margin-bottom:0; }
                .laporan-accurate thead th:first-child,
                .laporan-accurate tbody tr > td:first-child { min-width:190px !important; max-width:210px; }
                .laporan-accurate th, .laporan-accurate td { padding:.55rem .45rem; }
                .laporan-accurate .period-total-column { display:none; }
                .laporan-accurate .account-row > td:first-child { padding-left:12px !important; }
                .end-period-fields { display:none; }
                .mobile-period-note { display:block; }
                #parameterLaporan .modal-dialog { margin:.5rem; }
                #parameterLaporan .modal-body { padding:1rem; }
            }
        </style>
        @if ($result)
            <script>
                document.getElementById('cariAkunLabaRugi')?.addEventListener('input', function () {
                    const keyword = this.value.trim().toLocaleLowerCase('id-ID');
                    const accountRows = document.querySelectorAll('.laporan-accurate tbody .account-row');
                    const contextRows = document.querySelectorAll('.laporan-accurate tbody tr:not(.account-row)');
                    let matched = 0;

                    accountRows.forEach(row => {
                        const visible = !keyword || row.dataset.accountName.includes(keyword);
                        row.classList.toggle('d-none', !visible);
                        if (visible) matched++;
                    });
                    contextRows.forEach(row => row.classList.toggle('d-none', Boolean(keyword)));
                    document.getElementById('hasilCariAkun').textContent = keyword ? `${matched} akun ditemukan` : '';
                });
            </script>
        @endif
        <script>
            (() => {
                const form = document.querySelector('#parameterLaporan form');
                if (!form || !window.matchMedia('(max-width: 767.98px)').matches) return;
                form.addEventListener('submit', () => {
                    form.elements.bulan_sampai.value = form.elements.bulan_dari.value;
                    form.elements.tahun_sampai.value = form.elements.tahun_dari.value;
                });
            })();
        </script>
        @if (! $result)<script>new bootstrap.Modal(document.getElementById('parameterLaporan')).show();</script>@endif
    @endsection
</x-theme.app>
