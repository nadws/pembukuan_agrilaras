<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">{{ !empty($isEdit) ? 'Perbarui informasi dan detail debit kredit jurnal' : 'Masukkan informasi dan detail debit kredit jurnal' }}</small>
            </div>
            <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'manual']) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Jurnal Umum
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .stepper {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
                margin-bottom: 18px;
            }

            .stepper button {
                border: 1px solid #dce3f2;
                border-radius: 10px;
                background: #f5f7fc;
                color: #536078;
                padding: 10px 12px;
                font-weight: 800;
                text-align: left;
            }

            .stepper button.active {
                border-color: #29468f;
                background: #29468f;
                color: #fff;
            }

            .step-panel {
                display: none;
            }

            .step-panel.active {
                display: block;
            }

            /* Form jurnal dibuat satu halaman; stepper lama disembunyikan. */
            .stepper { display: none; }
            .step-panel { display: block !important; }
            [data-step-panel="3"] { display: none !important; }
            #btn-prev, #btn-next { display: none !important; }
            #btn-submit { display: inline-block !important; }

            .journal-box {
                border: 1px solid #dce3f2;
                border-radius: 12px;
                background: #fff;
                overflow: hidden;
            }

            .journal-box-header {
                padding: 13px 15px;
                border-bottom: 1px solid #dce3f2;
                background: #f5f7fc;
                color: #1d3167;
                font-weight: 800;
            }

            .journal-box-body {
                padding: 15px;
            }

            .detail-table-wrap {
                overflow-x: auto;
                border: 1px solid #dce3f2;
                border-radius: 12px;
            }

            .detail-table {
                min-width: 920px;
                margin-bottom: 0;
            }

            .detail-table thead th {
                color: #fff;
                background: #29468f;
                font-size: 12px;
                white-space: nowrap;
            }

            .total-strip {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
                margin-top: 14px;
            }

            .total-box {
                padding: 12px;
                border: 1px solid #dce3f2;
                border-radius: 10px;
                background: #f5f7fc;
            }

            .total-box .label {
                color: #637089;
                font-size: 12px;
                font-weight: 700;
            }

            .total-box .value {
                color: #1d3167;
                font-size: 18px;
                font-weight: 900;
            }

            .review-table th {
                width: 190px;
                color: #536078;
            }

            @media (max-width: 768px) {
                .stepper,
                .total-strip {
                    grid-template-columns: 1fr;
                }
            }
        </style>

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

        <form method="POST" action="{{ !empty($isEdit) ? route('pembukuan-baru.jurnal-umum.manual.update', $batchManual->id_impor_jurnal_perkiraan) : route('pembukuan-baru.jurnal-umum.store') }}" id="form-jurnal-step">
            @csrf
            @if (!empty($isEdit))
                @method('PUT')
            @endif
            <div class="stepper">
                <button type="button" class="step-button active" data-step-target="1">1. Info Transaksi</button>
                <button type="button" class="step-button" data-step-target="2">2. Detail Jurnal</button>
                <button type="button" class="step-button" data-step-target="3">3. Review</button>
            </div>

            <div class="step-panel active" data-step-panel="1">
                <div class="journal-box">
                    <div class="journal-box-header">Info Transaksi</div>
                    <div class="journal-box-body">
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label" for="tanggal">Tanggal</label>
                                <input type="date" id="tanggal" name="tanggal" class="form-control"
                                    value="{{ old('tanggal', $jurnalTanggal ?? now()->toDateString()) }}" required>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label" for="nomor_transaksi">Nomor Transaksi</label>
                                <input type="text" id="nomor_transaksi" name="nomor_transaksi" class="form-control"
                                    value="{{ old('nomor_transaksi', $noTransaksi) }}" required>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label" for="keterangan">Keterangan Umum</label>
                                <input type="text" id="keterangan" name="keterangan" class="form-control"
                                    value="{{ old('keterangan') }}" placeholder="Contoh: Koreksi biaya operasional">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step-panel" data-step-panel="2">
                <div class="journal-box">
                    <div class="journal-box-header d-flex justify-content-between align-items-center">
                        <span>Detail Debit Kredit</span>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-baris">
                            <i class="fas fa-plus me-1"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="journal-box-body">
                        <div class="detail-table-wrap">
                            <table class="table detail-table align-middle">
                                <thead>
                                    <tr>
                                        <th width="45">No</th>
                                        <th width="300">Akun</th>
                                        <th>Keterangan</th>
                                        <th width="150" class="text-end">Debit</th>
                                        <th width="150" class="text-end">Kredit</th>
                                        <th width="60"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-detail"></tbody>
                            </table>
                        </div>

                        <div class="total-strip">
                            <div class="total-box">
                                <div class="label">Total Debit</div>
                                <div class="value" id="total-debit">Rp 0</div>
                            </div>
                            <div class="total-box">
                                <div class="label">Total Kredit</div>
                                <div class="value" id="total-kredit">Rp 0</div>
                            </div>
                            <div class="total-box">
                                <div class="label">Selisih</div>
                                <div class="value" id="total-selisih">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step-panel" data-step-panel="3">
                <div class="journal-box">
                    <div class="journal-box-header">Review Sebelum Simpan</div>
                    <div class="journal-box-body">
                        <table class="table table-bordered review-table">
                            <tr>
                                <th>Tanggal</th>
                                <td id="review-tanggal">-</td>
                            </tr>
                            <tr>
                                <th>Nomor Transaksi</th>
                                <td id="review-nomor">-</td>
                            </tr>
                            <tr>
                                <th>Keterangan</th>
                                <td id="review-keterangan">-</td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td><span id="review-debit">Rp 0</span> / <span id="review-kredit">Rp 0</span></td>
                            </tr>
                        </table>
                        <div class="alert alert-warning mb-0" id="review-warning" hidden>
                            Total debit dan kredit belum sama. Jurnal belum bisa disimpan.
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary" id="btn-prev" disabled>Sebelumnya</button>
                <button type="button" class="btn btn-primary" id="btn-next">Lanjut</button>
                <button type="submit" class="btn btn-success" id="btn-submit" hidden>
                    <i class="fas fa-save me-1"></i> {{ !empty($isEdit) ? 'Simpan Perubahan' : 'Simpan Jurnal' }}
                </button>
            </div>
        </form>

        <template id="template-detail-row">
            <tr class="detail-row">
                <td class="text-center row-number">1</td>
                <td>
                    <select name="detail[__INDEX__][id_akun_perkiraan]" class="form-select select-akun"
                        data-placeholder="Cari akun" required>
                        <option value="">-- Pilih Akun --</option>
                        @foreach ($akun as $a)
                            <option value="{{ $a->id_akun_perkiraan }}">
                                {{ $a->kode_perkiraan }} - {{ $a->nama }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" name="detail[__INDEX__][deskripsi]" class="form-control input-deskripsi"
                        placeholder="Keterangan baris">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="detail[__INDEX__][debit]"
                        class="form-control text-end input-debit" value="0">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="detail[__INDEX__][kredit]"
                        class="form-control text-end input-kredit" value="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        </template>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                const tbody = document.getElementById('tbody-detail');
                const template = document.getElementById('template-detail-row');
                const panels = document.querySelectorAll('[data-step-panel]');
                const buttons = document.querySelectorAll('[data-step-target]');
                const btnPrev = document.getElementById('btn-prev');
                const btnNext = document.getElementById('btn-next');
                const btnSubmit = document.getElementById('btn-submit');
                const btnTambah = document.getElementById('btn-tambah-baris');
                const form = document.getElementById('form-jurnal-step');
                const initialDetails = @json(old('detail', $detailAwal ?? []));
                let step = 1;
                let index = 0;
                let totals = {debit: 0, kredit: 0, selisih: 0};

                function formatRupiah(value) {
                    return 'Rp ' + Number(value || 0).toLocaleString('id-ID', {maximumFractionDigits: 2});
                }

                function initSelect(scope = document) {
                    if (!window.jQuery || !jQuery.fn.select2) return;
                    jQuery(scope).find('.select-akun').each(function() {
                        const select = jQuery(this);
                        if (select.hasClass('select2-hidden-accessible')) {
                            select.select2('destroy');
                        }
                        select.select2({
                            width: '100%',
                            placeholder: select.data('placeholder') || 'Cari akun',
                            allowClear: true
                        });
                    });
                }

                function addRow(data = {}) {
                    const html = template.innerHTML.replaceAll('__INDEX__', index);
                    tbody.insertAdjacentHTML('beforeend', html);
                    const row = tbody.querySelector('.detail-row:last-child');
                    row.querySelector('.select-akun').value = data.id_akun_perkiraan || '';
                    row.querySelector('.input-deskripsi').value = data.deskripsi || '';
                    row.querySelector('.input-debit').value = data.debit || 0;
                    row.querySelector('.input-kredit').value = data.kredit || 0;
                    index++;
                    initSelect(row);
                    refreshRows();
                }

                function refreshRows() {
                    totals = {debit: 0, kredit: 0, selisih: 0};
                    tbody.querySelectorAll('.detail-row').forEach((row, i) => {
                        row.querySelector('.row-number').textContent = i + 1;
                        totals.debit += parseFloat(row.querySelector('.input-debit').value) || 0;
                        totals.kredit += parseFloat(row.querySelector('.input-kredit').value) || 0;
                    });
                    totals.selisih = totals.debit - totals.kredit;
                    document.getElementById('total-debit').textContent = formatRupiah(totals.debit);
                    document.getElementById('total-kredit').textContent = formatRupiah(totals.kredit);
                    document.getElementById('total-selisih').textContent = formatRupiah(totals.selisih);
                    updateReview();
                }

                function updateReview() {
                    document.getElementById('review-tanggal').textContent = document.getElementById('tanggal').value || '-';
                    document.getElementById('review-nomor').textContent = document.getElementById('nomor_transaksi').value || '-';
                    document.getElementById('review-keterangan').textContent = document.getElementById('keterangan').value || '-';
                    document.getElementById('review-debit').textContent = formatRupiah(totals.debit);
                    document.getElementById('review-kredit').textContent = formatRupiah(totals.kredit);
                    document.getElementById('review-warning').hidden = Math.abs(totals.selisih) < 0.01;
                    btnSubmit.disabled = Math.abs(totals.selisih) >= 0.01 || totals.debit <= 0;
                }

                function showStep(nextStep) {
                    step = Math.min(Math.max(nextStep, 1), 3);
                    panels.forEach((panel) => panel.classList.toggle('active', panel.dataset.stepPanel == step));
                    buttons.forEach((button) => button.classList.toggle('active', button.dataset.stepTarget == step));
                    btnPrev.disabled = step === 1;
                    btnNext.hidden = step === 3;
                    btnSubmit.hidden = false;
                    refreshRows();
                }

                btnTambah.addEventListener('click', addRow);
                btnPrev.addEventListener('click', () => showStep(step - 1));
                btnNext.addEventListener('click', () => showStep(step + 1));
                buttons.forEach((button) => button.addEventListener('click', () => showStep(parseInt(button.dataset.stepTarget))));

                tbody.addEventListener('input', function(e) {
                    if (e.target.classList.contains('input-debit') && parseFloat(e.target.value || 0) > 0) {
                        e.target.closest('.detail-row').querySelector('.input-kredit').value = 0;
                    }
                    if (e.target.classList.contains('input-kredit') && parseFloat(e.target.value || 0) > 0) {
                        e.target.closest('.detail-row').querySelector('.input-debit').value = 0;
                    }
                    refreshRows();
                });

                tbody.addEventListener('click', function(e) {
                    const remove = e.target.closest('.btn-remove-row');
                    if (!remove) return;
                    if (tbody.querySelectorAll('.detail-row').length <= 2) {
                        alert('Minimal harus ada 2 baris jurnal.');
                        return;
                    }
                    remove.closest('.detail-row').remove();
                    refreshRows();
                });

                form.addEventListener('submit', function(e) {
                    refreshRows();
                    if (Math.abs(totals.selisih) >= 0.01 || totals.debit <= 0) {
                        e.preventDefault();
                        showStep(3);
                    }
                });

                if (initialDetails.length) {
                    initialDetails.forEach((detail) => addRow(detail));
                } else {
                    addRow();
                    addRow();
                }
                initSelect();
                showStep(1);
            })();
        </script>
    @endsection
</x-theme.app>
