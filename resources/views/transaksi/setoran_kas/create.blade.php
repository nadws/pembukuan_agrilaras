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
            #tabelJurnal {
                width: 100%;
                font-size: 13px;
                border-collapse: separate;
                border-spacing: 0;
            }
            #tabelJurnal thead th {
                background: #f1f5f9 !important;
                color: #0f172a !important;
                font-weight: 700;
                font-size: 12.5px;
                padding: 10px 12px;
                border-bottom: 2px solid #cbd5e1 !important;
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
        </style>

        <form action="{{ route('transaksi.setoran-kas.store') }}" method="POST" id="formSetoranKas">
            @csrf

            <div class="row mb-3">
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

            <div class="row mb-3">
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

            <div class="mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold">Pilih Jurnal Penjualan yang akan Disetorkan</h6>
                        <small class="text-muted">Centang transaksi yang ingin disetorkan</small>
                    </div>
                    <div>
                        <span class="badge bg-primary fs-6" id="badgeJumlahItem">{{ $jurnalBelumDisetorkan->count() }} Transaksi Tersedia</span>
                    </div>
                </div>

                {{-- Filter Akun Kas Sumber & Search --}}
                <div class="card bg-light border mb-3 shadow-none">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label mb-1 small fw-bold text-dark">Filter Akun Kas Sumber</label>
                                <select id="filterAkunSumber" class="form-select select2 select-search-akun" data-placeholder="-- Semua Akun Kas Sumber --">
                                    <option value="">-- Semua Akun Kas Sumber --</option>
                                    @foreach($jurnalBelumDisetorkan->unique('id_akun_perkiraan') as $sumber)
                                        <option value="{{ $sumber->id_akun_perkiraan }}">
                                            {{ $sumber->kode_perkiraan }} - {{ $sumber->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 small fw-bold text-dark">Cari No. Transaksi / Deskripsi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" id="cariJurnal" class="form-control" placeholder="Ketik untuk mencari...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-bordered align-middle mb-0" id="tabelJurnal">
                        <thead class="sticky-top" style="z-index: 1;">
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input" style="cursor: pointer;" title="Pilih Semua yang Tampil">
                                </th>
                                <th width="110">Tanggal</th>
                                <th width="140">No. Transaksi</th>
                                <th width="230">Akun Kas Sumber</th>
                                <th>Deskripsi</th>
                                <th width="150" class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyJurnal">
                            @forelse($jurnalBelumDisetorkan as $jurnal)
                                <tr class="baris-jurnal" data-akun-id="{{ $jurnal->id_akun_perkiraan }}" data-text="{{ strtolower($jurnal->nomor_transaksi . ' ' . $jurnal->deskripsi . ' ' . $jurnal->nama . ' ' . $jurnal->kode_perkiraan) }}">
                                    <td class="text-center">
                                        <input type="checkbox" name="jurnal_terpilih[]" value="{{ $jurnal->id_jurnal_perkiraan }}" data-nominal="{{ $jurnal->debit }}" class="form-check-input jurnal-checkbox" style="cursor: pointer;">
                                    </td>
                                    <td class="tanggal-text">{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                                    <td class="no-transaksi-text">{{ $jurnal->nomor_transaksi }}</td>
                                    <td>
                                        <span class="akun-sumber-badge">{{ $jurnal->kode_perkiraan }}</span>
                                        <span class="akun-sumber-nama">{{ $jurnal->nama }}</span>
                                    </td>
                                    <td class="deskripsi-text">{{ $jurnal->deskripsi }}</td>
                                    <td class="text-end nominal-text">
                                        Rp {{ number_format($jurnal->debit, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr id="rowEmpty">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Tidak ada jurnal penjualan yang belum disetorkan
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

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card bg-light border">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Nominal Setoran Terpilih</small>
                            <h4 id="totalNominal" class="mb-0 text-primary fw-bold">Rp 0</h4>
                            <small class="text-muted" id="totalTerpilihCount">0 transaksi dipilih</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('transaksi.setoran-kas.index') }}" class="btn btn-outline-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" id="btnSimpan">
                    <i class="fas fa-save me-1"></i> Simpan Setoran
                </button>
            </div>
        </form>

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
                    }

                    function filterTable() {
                        const selectedAkun = String($('#filterAkunSumber').val() || '').trim();
                        const keyword = String($('#cariJurnal').val() || '').toLowerCase().trim();
                        let visibleCount = 0;

                        $('.baris-jurnal').each(function() {
                            const $row = $(this);
                            const rowAkun = String($row.attr('data-akun-id') || '').trim();
                            const rowText = String($row.attr('data-text') || '').toLowerCase();

                            const matchAkun = !selectedAkun || rowAkun === selectedAkun;
                            const matchText = !keyword || rowText.includes(keyword);

                            if (matchAkun && matchText) {
                                $row.show();
                                visibleCount++;
                            } else {
                                $row.hide();
                            }
                        });

                        $('#badgeJumlahItem').text(visibleCount + ' Transaksi Tampil');
                        updateSelectAllState();
                    }

                    function updateSelectAllState() {
                        const $visibleCbs = $('.baris-jurnal:visible .jurnal-checkbox');
                        const $selectAll = $('#selectAll');
                        if ($visibleCbs.length === 0) {
                            $selectAll.prop('checked', false).prop('indeterminate', false);
                            return;
                        }

                        const allChecked = $visibleCbs.length > 0 && $visibleCbs.filter(':checked').length === $visibleCbs.length;
                        const someChecked = $visibleCbs.filter(':checked').length > 0;

                        $selectAll.prop('checked', allChecked);
                        $selectAll.prop('indeterminate', someChecked && !allChecked);
                    }

                    function updateTotal() {
                        let total = 0;
                        let count = 0;
                        $('.jurnal-checkbox:checked').each(function() {
                            const nominal = parseFloat($(this).attr('data-nominal') || $(this).data('nominal')) || 0;
                            total += nominal;
                            count++;
                        });
                        $('#totalNominal').text('Rp ' + Number(total).toLocaleString('id-ID'));
                        $('#totalTerpilihCount').text(count + ' transaksi dipilih');
                    }

                    // Event listeners via jQuery delegation
                    $(document).off('change select2:select select2:clear select2:unselect', '#filterAkunSumber')
                               .on('change select2:select select2:clear select2:unselect', '#filterAkunSumber', function() {
                        filterTable();
                    });

                    $(document).off('input keyup paste', '#cariJurnal')
                               .on('input keyup paste', '#cariJurnal', function() {
                        filterTable();
                    });

                    $(document).off('change', '#selectAll')
                               .on('change', '#selectAll', function() {
                        const isChecked = $(this).is(':checked');
                        $('.baris-jurnal:visible .jurnal-checkbox').prop('checked', isChecked);
                        updateTotal();
                    });

                    $(document).off('change', '.jurnal-checkbox')
                               .on('change', '.jurnal-checkbox', function() {
                        updateSelectAllState();
                        updateTotal();
                    });

                    $('#formSetoranKas').off('submit').on('submit', function(e) {
                        const checkedCount = $('.jurnal-checkbox:checked').length;
                        if (checkedCount === 0) {
                            e.preventDefault();
                            alert('Pilih minimal 1 jurnal transaksi yang akan disetorkan.');
                        }
                    });

                    updateTotal();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', setupSetoranHandler);
                } else {
                    setupSetoranHandler();
                }

                // Fallback run after small timeout to ensure jQuery/Select2 full ready
                setTimeout(setupSetoranHandler, 300);
            })();
        </script>
    </x-slot>
</x-theme.app>
