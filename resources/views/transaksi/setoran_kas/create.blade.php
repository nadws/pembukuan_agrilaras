<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Pilih jurnal penerimaan/kas penjualan yang akan disetorkan ke bank/kas tujuan</small>
            </div>
            <a href="{{ route('transaksi.setoran-kas.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .setoran-create-page {
                --setoran-primary: #29468f;
                --setoran-border: #dce3f2;
                --setoran-soft: #f5f7fc;
                --setoran-text: #172554;
            }

            .setoran-form-panel,
            .journal-panel {
                border: 1px solid var(--setoran-border);
                border-radius: 12px;
                background: #ffffff;
                box-shadow: 0 4px 18px rgba(32, 55, 110, .06);
            }

            .setoran-form-panel {
                padding: 18px;
                margin-bottom: 18px;
            }

            .panel-heading {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 15px;
                padding-bottom: 12px;
                border-bottom: 1px solid #e9eef7;
            }

            .panel-heading-icon {
                display: grid;
                width: 36px;
                height: 36px;
                flex: 0 0 36px;
                border-radius: 9px;
                color: #3155a5;
                background: #e8edfa;
                place-items: center;
            }

            .panel-heading h6 {
                margin: 0 0 2px;
                color: var(--setoran-text) !important;
                font-weight: 700;
            }

            .panel-heading small { color: #70809d !important; }

            .setoran-create-page .form-label {
                color: #536078 !important;
                font-size: 12px;
                font-weight: 700;
            }

            .setoran-create-page .form-control,
            .setoran-create-page .form-select,
            .setoran-create-page .select2-container .select2-selection {
                border-color: var(--setoran-border) !important;
                border-radius: 8px !important;
            }

            .setoran-create-page .form-control,
            .setoran-create-page .form-select {
                min-height: 42px;
            }

            .journal-panel { overflow: hidden; }

            .journal-panel-header {
                padding: 16px 18px 12px;
            }

            .source-filter-card {
                margin: 0 18px 16px;
                border: 1px solid var(--setoran-border) !important;
                border-radius: 10px;
                background: var(--setoran-soft) !important;
            }

            .journal-table-wrap {
                max-height: 500px;
                overflow: auto;
                border-top: 1px solid var(--setoran-border);
            }

            #tabelJurnal {
                width: 100%;
                min-width: 1030px;
                font-size: 13px;
                border-collapse: separate;
                border-spacing: 0;
            }
            #tabelJurnal thead th {
                background: var(--setoran-primary) !important;
                color: #ffffff !important;
                font-weight: 700;
                font-size: 12.5px;
                padding: 12px;
                border: 0 !important;
                white-space: nowrap;
            }
            #tabelJurnal tbody td {
                padding: 10px 12px;
                vertical-align: middle;
                border-bottom: 1px solid #e2e8f0;
                color: #0f172a !important;
                background-color: #ffffff !important;
            }
            #tabelJurnal tbody tr:hover td {
                background-color: #f8fafc !important;
            }
            .akun-sumber-badge {
                display: inline-block;
                background-color: #e0e7ff !important;
                color: #1e40af !important;
                font-weight: 700;
                font-size: 11.5px;
                padding: 2px 7px;
                border-radius: 4px;
                margin-bottom: 3px;
                border: 1px solid #c7d2fe;
            }
            .akun-sumber-nama {
                display: block;
                color: #0f172a !important;
                font-weight: 600;
                font-size: 13px;
                line-height: 1.3;
            }
            .deskripsi-text {
                color: #334155 !important;
                font-size: 13px;
            }
            .no-transaksi-text {
                color: #0f172a !important;
                font-weight: 700;
            }
            .nominal-text {
                color: #0f172a !important;
                font-weight: 700;
                font-size: 13.5px;
            }
            .tanggal-text {
                color: #0f172a !important;
                font-weight: 500;
            }
            .nominal-aktual-input {
                min-width: 120px;
                min-height: 34px !important;
                text-align: right;
            }
            .selisih-positif { color: #16a34a !important; font-weight: 700; }
            .selisih-negatif { color: #dc2626 !important; font-weight: 700; }
            .selisih-nol     { color: #64748b !important; font-weight: 500; }

            .summary-card {
                height: 100%;
                border: 1px solid var(--setoran-border) !important;
                border-radius: 12px !important;
                background: #ffffff !important;
                box-shadow: 0 4px 16px rgba(32, 55, 110, .05);
            }

            .summary-card .card-body { padding: 17px 18px; }
            .summary-card small:first-child { color: #70809d !important; font-weight: 600; }
            .summary-card h4 { margin-top: 5px; font-size: 21px; }
            #cardSelisih.border-success { border-color: #86d5a3 !important; }
            #cardSelisih.border-danger { border-color: #f3a6ae !important; }
            .journal-panel > .text-danger { padding: 0 18px 14px; }

            .form-actions {
                padding-top: 3px;
            }

            @media (max-width: 767.98px) {
                .setoran-form-panel { padding: 14px; }
                .journal-panel-header { padding: 14px; }
                .source-filter-card { margin: 0 12px 12px; }
                .source-filter-card .card-body { padding: 12px !important; }
                .source-filter-card .btn { padding: 4px 8px !important; }
                .form-actions .btn { flex: 1 1 auto; }
            }
        </style>

        <form action="{{ route('transaksi.setoran-kas.store') }}" method="POST" id="formSetoranKas" class="setoran-create-page">
            @csrf

            <section class="setoran-form-panel">
                <div class="panel-heading">
                    <span class="panel-heading-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    <div>
                        <h6>Informasi Setoran</h6>
                        <small>Lengkapi tanggal, akun tujuan, dan informasi referensi setoran.</small>
                    </div>
                </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Setoran</label>
                    <input type="date" name="tanggal_setoran" class="form-control" value="{{ old('tanggal_setoran', now()->toDateString()) }}" required>
                    @error('tanggal_setoran')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Akun Tujuan (Bank/Kas)</label>
                    <select name="akun_tujuan_id" id="akun_tujuan_id" class="form-select select2 select-search-akun" data-placeholder="-- Cari atau Pilih Akun Tujuan --" required>
                        <option value="">-- Pilih Akun Tujuan --</option>
                        @foreach($akunBank as $akun)
                            <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('akun_tujuan_id') == $akun->id_akun_perkiraan)>
                                {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('akun_tujuan_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">No. Referensi (Opsional)</label>
                    <input type="text" name="nomor_referensi" class="form-control" value="{{ old('nomor_referensi') }}" placeholder="No. slip, bukti transfer, dll">
                    @error('nomor_referensi')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="Keterangan setoran">
                    @error('keterangan')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            </section>

            <div class="journal-panel mb-3">
                <div class="journal-panel-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h6 class="mb-0 fw-bold">Pilih Jurnal Penjualan yang akan Disetorkan</h6>
                        <small class="text-muted">Centang transaksi yang ingin disetorkan</small>
                    </div>
                    <div>
                        <span class="badge bg-primary fs-6" id="badgeJumlahItem">0 Transaksi Tampil</span>
                    </div>
                </div>

                {{-- Filter Akun Kas Sumber & Search --}}
                <div class="card source-filter-card shadow-none">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                    <label class="form-label mb-0 small fw-bold" style="color: #0f172a !important;">Filter Akun Kas Sumber</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#modalPengaturanAkunSumber">
                                        <i class="fas fa-cog me-1"></i> Atur Akun
                                    </button>
                                </div>
                                <select id="filterAkunSumber" class="form-select select2 select-search-akun" data-placeholder="-- Pilih Akun Kas Sumber --">
                                    <option value="">-- Pilih Akun Kas Sumber --</option>
                                    @foreach($jurnalBelumDisetorkan->unique('id_akun_perkiraan') as $sumber)
                                        <option value="{{ $sumber->id_akun_perkiraan }}">
                                            {{ $sumber->kode_perkiraan }} - {{ $sumber->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 small fw-bold" style="color: #0f172a !important;">Cari No. Transaksi / Customer</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" id="cariJurnal" class="form-control" placeholder="Cari nomor transaksi, customer, atau asal...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="journal-table-wrap">
                    <table class="table table-sm table-hover table-bordered align-middle mb-0" id="tabelJurnal">
                        <thead class="sticky-top" style="z-index: 1;">
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input" style="cursor: pointer;" title="Pilih Semua yang Tampil">
                                </th>
                                <th width="110">Tanggal</th>
                                <th width="140">No. Transaksi</th>
                                <th width="190">Customer</th>
                                <th width="150">Asal</th>
                                <th width="150" class="text-end">Nominal</th>
                                <th width="160" class="text-end">Nominal Aktual</th>
                                <th width="140" class="text-end">Selisih</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyJurnal">
                            @if($jurnalBelumDisetorkan->isNotEmpty())
                                <tr id="rowFilterMessage">
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Pilih akun kas sumber untuk menampilkan jurnal yang tersedia
                                    </td>
                                </tr>
                            @endif
                            @forelse($jurnalBelumDisetorkan as $jurnal)
                                <tr class="baris-jurnal" style="display: none;" data-akun-id="{{ $jurnal->id_akun_perkiraan }}" data-text="{{ strtolower($jurnal->nomor_transaksi . ' ' . $jurnal->asal . ' ' . $jurnal->nama_customer) }}">
                                    <td class="text-center">
                                        <input type="checkbox" name="jurnal_terpilih[]" value="{{ $jurnal->id_jurnal_perkiraan }}" data-nominal="{{ round($jurnal->debit) }}" class="form-check-input jurnal-checkbox" style="cursor: pointer;">
                                    </td>
                                    <td class="tanggal-text">{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                                    <td class="no-transaksi-text">{{ $jurnal->nomor_transaksi }}</td>
                                    <td class="fw-semibold">{{ $jurnal->nama_customer }}</td>
                                    <td class="deskripsi-text fw-semibold">{{ $jurnal->asal }}</td>
                                    <td class="text-end nominal-text">
                                        Rp {{ number_format($jurnal->debit, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        <input type="number"
                                               name="nominal_aktual[{{ $jurnal->id_jurnal_perkiraan }}]"
                                               class="form-control form-control-sm nominal-aktual-input text-end"
                                               value="{{ round($jurnal->debit) }}"
                                               min="0" step="1"
                                               data-nominal="{{ round($jurnal->debit) }}"
                                               data-id="{{ $jurnal->id_jurnal_perkiraan }}">
                                    </td>
                                    <td class="text-end selisih-cell selisih-nol">Rp 0</td>
                                </tr>
                            @empty
                                <tr id="rowEmpty">
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Tidak ada jurnal penjualan atau setoran kas yang belum disetorkan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @error('jurnal_terpilih')
                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                @enderror
            </div>

            <div class="row mb-3 g-3">
                <div class="col-md-4">
                    <div class="card summary-card">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Nominal (Jurnal)</small>
                            <h4 id="totalNominal" class="mb-0 text-primary fw-bold">Rp 0</h4>
                            <small class="text-muted" id="totalTerpilihCount">0 transaksi dipilih</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card summary-card">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Nominal Aktual (Disetorkan)</small>
                            <h4 id="totalAktual" class="mb-0 text-success fw-bold">Rp 0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card summary-card" id="cardSelisih">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Selisih (Aktual − Nominal)</small>
                            <h4 id="totalSelisih" class="mb-0 fw-bold selisih-nol">Rp 0</h4>
                            <small id="keteranganSelisih" class="text-muted">Tidak ada selisih</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions d-flex flex-wrap gap-2 justify-content-end">
                <a href="{{ route('transaksi.setoran-kas.index') }}" class="btn btn-outline-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" id="btnSimpan">
                    <i class="fas fa-save me-1"></i> Simpan Setoran
                </button>
            </div>
        </form>

        <div class="modal fade" id="modalPengaturanAkunSumber" tabindex="-1" aria-labelledby="modalPengaturanAkunSumberLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('transaksi.setoran-kas.source-account-settings') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="modalPengaturanAkunSumberLabel">Pengaturan Akun Kas Sumber</h5>
                                <small class="text-muted">Pilih akun yang boleh muncul dan diproses pada setoran kas.</small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label fw-bold">Akun kas/bank yang digunakan</label>
                            <select name="akun_sumber[]" id="pengaturanAkunSumber" class="form-select" multiple required data-placeholder="Cari dan pilih akun kas sumber">
                                @foreach($akunSumberTersedia as $akun)
                                    <option value="{{ $akun->id_akun_perkiraan }}" @selected(in_array((int) $akun->id_akun_perkiraan, array_map('intval', old('akun_sumber', $akunSumberTerpilih))))>
                                        {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2">Minimal satu akun harus dipilih. Akun yang tidak dipilih tidak akan muncul dalam daftar jurnal.</small>
                            @error('akun_sumber', 'sourceAccountSetting')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            @error('akun_sumber.*', 'sourceAccountSetting')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            (function() {
                function setupSetoranHandler() {
                    const $ = window.jQuery;
                    if (!$) return;

                    // Initialize Select2 safely
                    if ($.fn.select2) {
                        $('#akun_tujuan_id, #filterAkunSumber').each(function() {
                            const $el = $(this);
                            if (!$el.hasClass('select2-hidden-accessible')) {
                                $el.select2({
                                    width: '100%',
                                    allowClear: true,
                                    placeholder: $el.data('placeholder') || '-- Pilih --'
                                });
                            }
                        });

                        const $sourceSetting = $('#pengaturanAkunSumber');
                        if (!$sourceSetting.hasClass('select2-hidden-accessible')) {
                            $sourceSetting.select2({
                                width: '100%',
                                placeholder: $sourceSetting.data('placeholder'),
                                dropdownParent: $('#modalPengaturanAkunSumber')
                            });
                        }
                    }

                    function filterTable() {
                        const selectedAkun = String($('#filterAkunSumber').val() || '').trim();
                        const keyword = String($('#cariJurnal').val() || '').toLowerCase().trim();
                        let visibleCount = 0;

                        // Otomatis uncheck transaksi dari akun kas sumber lain agar tidak tergabung
                        $('.baris-jurnal').each(function() {
                            const $row = $(this);
                            const rowAkun = String($row.attr('data-akun-id') || '').trim();
                            if (rowAkun !== selectedAkun) {
                                $row.find('.jurnal-checkbox').prop('checked', false);
                            }
                        });

                        if (!selectedAkun) {
                            $('.baris-jurnal').hide();
                            $('#rowFilterMessage td').text('Pilih akun kas sumber untuk menampilkan jurnal yang tersedia');
                            $('#rowFilterMessage').show();
                            $('#badgeJumlahItem').text('0 Transaksi Tampil');
                            updateSelectAllState();
                            updateTotal();
                            return;
                        }

                        $('.baris-jurnal').each(function() {
                            const $row = $(this);
                            const rowAkun = String($row.attr('data-akun-id') || '').trim();
                            const rowText = String($row.attr('data-text') || '').toLowerCase();

                            const matchAkun = rowAkun === selectedAkun;
                            const matchText = !keyword || rowText.includes(keyword);

                            if (matchAkun && matchText) {
                                $row.show();
                                visibleCount++;
                            } else {
                                $row.hide();
                            }
                        });

                        $('#rowFilterMessage td').text(keyword
                            ? 'Tidak ada jurnal yang sesuai dengan pencarian'
                            : 'Tidak ada jurnal yang tersedia untuk akun ini');
                        $('#rowFilterMessage').toggle(visibleCount === 0);
                        $('#badgeJumlahItem').text(visibleCount + ' Transaksi Tampil');
                        updateSelectAllState();
                        updateTotal();
                    }

                    function updateSelectAllState() {
                        const $visibleCbs = $('.baris-jurnal:visible .jurnal-checkbox');
                        const $selectAll = $('#selectAll');
                        if ($visibleCbs.length === 0) {
                            $selectAll.prop('checked', false).prop('indeterminate', false);
                            return;
                        }
                        const allChecked = $visibleCbs.filter(':checked').length === $visibleCbs.length;
                        const someChecked = $visibleCbs.filter(':checked').length > 0;
                        $selectAll.prop('checked', allChecked);
                        $selectAll.prop('indeterminate', someChecked && !allChecked);
                    }

                    // Update selisih cell for a single row
                    function updateRowSelisih($input) {
                        const $row = $input.closest('tr');
                        const nominal = parseFloat($input.attr('data-nominal')) || 0;
                        const aktual = parseFloat($input.val()) || 0;
                        const selisih = aktual - nominal;
                        const $cell = $row.find('.selisih-cell');

                        $cell.removeClass('selisih-positif selisih-negatif selisih-nol');
                        if (selisih > 0) {
                            $cell.addClass('selisih-positif').text('+Rp ' + selisih.toLocaleString('id-ID'));
                        } else if (selisih < 0) {
                            $cell.addClass('selisih-negatif').text('−Rp ' + Math.abs(selisih).toLocaleString('id-ID'));
                        } else {
                            $cell.addClass('selisih-nol').text('Rp 0');
                        }
                    }

                    function updateTotal() {
                        let totalNominal = 0;
                        let totalAktual  = 0;
                        let count = 0;

                        $('.jurnal-checkbox:checked').each(function() {
                            const $row = $(this).closest('tr');
                            const nominal = parseFloat($(this).attr('data-nominal')) || 0;
                            const aktual  = parseFloat($row.find('.nominal-aktual-input').val()) || 0;
                            totalNominal += nominal;
                            totalAktual  += aktual;
                            count++;
                        });

                        const selisih = totalAktual - totalNominal;

                        $('#totalNominal').text('Rp ' + totalNominal.toLocaleString('id-ID'));
                        $('#totalTerpilihCount').text(count + ' transaksi dipilih');
                        $('#totalAktual').text('Rp ' + totalAktual.toLocaleString('id-ID'));

                        const $ts = $('#totalSelisih');
                        const $ks = $('#keteranganSelisih');
                        const $card = $('#cardSelisih');
                        $ts.removeClass('selisih-positif selisih-negatif selisih-nol text-success text-danger');
                        $card.removeClass('border-success border-danger');

                        if (selisih > 0) {
                            $ts.addClass('selisih-positif').text('+Rp ' + selisih.toLocaleString('id-ID'));
                            $ks.text('Lebih → masuk Pendapatan Selisih Lebih Bayar');
                            $card.addClass('border-success');
                        } else if (selisih < 0) {
                            $ts.addClass('selisih-negatif').text('−Rp ' + Math.abs(selisih).toLocaleString('id-ID'));
                            $ks.text('Kurang → masuk Kerugian Selisih Kurang Bayar');
                            $card.addClass('border-danger');
                        } else {
                            $ts.addClass('selisih-nol').text('Rp 0');
                            $ks.text('Tidak ada selisih');
                        }
                    }

                    // Event: filter dropdown
                    $(document).off('change select2:select select2:clear select2:unselect', '#filterAkunSumber')
                               .on('change select2:select select2:clear select2:unselect', '#filterAkunSumber', function() {
                        filterTable();
                    });

                    // Event: search input
                    $(document).off('input keyup paste', '#cariJurnal')
                               .on('input keyup paste', '#cariJurnal', function() {
                        filterTable();
                    });

                    // Event: select all
                    $(document).off('change', '#selectAll')
                               .on('change', '#selectAll', function() {
                        const isChecked = $(this).is(':checked');
                        $('.baris-jurnal:visible .jurnal-checkbox').prop('checked', isChecked);
                        updateTotal();
                    });

                    // Event: individual checkbox
                    $(document).off('change', '.jurnal-checkbox')
                               .on('change', '.jurnal-checkbox', function() {
                        if ($(this).is(':checked')) {
                            const currentAkunId = String($(this).closest('.baris-jurnal').attr('data-akun-id') || '').trim();
                            // Pastikan tidak ada transaksi dari akun kas sumber lain yang ikut tercentang
                            $('.jurnal-checkbox:checked').each(function() {
                                const rowAkunId = String($(this).closest('.baris-jurnal').attr('data-akun-id') || '').trim();
                                if (rowAkunId !== currentAkunId) {
                                    $(this).prop('checked', false);
                                }
                            });
                        }
                        updateSelectAllState();
                        updateTotal();
                    });

                    // Event: nominal aktual input changed
                    $(document).off('input change', '.nominal-aktual-input')
                               .on('input change', '.nominal-aktual-input', function() {
                        updateRowSelisih($(this));
                        updateTotal();
                    });

                    // Form submit validation
                    $('#formSetoranKas').off('submit').on('submit', function(e) {
                        const checkedCount = $('.jurnal-checkbox:checked').length;
                        if (checkedCount === 0) {
                            e.preventDefault();
                            alert('Pilih minimal 1 jurnal transaksi yang akan disetorkan.');
                        }
                    });

                    // Init all selisih cells and totals
                    $('.nominal-aktual-input').each(function() {
                        updateRowSelisih($(this));
                    });
                    filterTable();
                    updateTotal();

                    @if(isset($errors) && $errors->sourceAccountSetting->any())
                        const settingModalElement = document.getElementById('modalPengaturanAkunSumber');
                        if (settingModalElement && window.bootstrap) {
                            window.bootstrap.Modal.getOrCreateInstance(settingModalElement).show();
                        }
                    @endif
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', setupSetoranHandler);
                } else {
                    setupSetoranHandler();
                }

                setTimeout(setupSetoranHandler, 300);
            })();
        </script>
    </x-slot>
</x-theme.app>
