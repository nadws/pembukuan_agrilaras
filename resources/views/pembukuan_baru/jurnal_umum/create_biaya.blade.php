<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Untuk bayar listrik, PDAM, internet, kebersihan, dan biaya lain</small>
            </div>
            <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'biaya']) }}"
                class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Jurnal Biaya
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .cost-form {
                border: 1px solid #dce3f2;
                border-radius: 12px;
                background: #fff;
                overflow: hidden;
            }

            .cost-form-header {
                padding: 14px 16px;
                border-bottom: 1px solid #dce3f2;
                background: #f5f7fc;
                color: #1d3167;
                font-weight: 800;
            }

            .cost-form-body {
                padding: 16px;
            }

            .cost-section-title {
                color: #1d3167;
                font-size: 15px;
                font-weight: 800;
            }

            .cost-preview {
                padding: 14px;
                border: 1px solid #ffd38a;
                border-radius: 10px;
                background: #fff9ed;
            }

            .cost-preview .label {
                color: #8a5d13;
                font-size: 12px;
                font-weight: 700;
            }

            .cost-preview .value {
                color: #7a4300;
                font-size: 22px;
                font-weight: 900;
            }

            .cost-table th {
                background: #2f4f9f;
                color: #fff;
                font-size: 13px;
                white-space: nowrap;
            }

            .cost-table td {
                vertical-align: top;
            }

            .cost-row-number {
                width: 42px;
                padding-top: 13px;
                text-align: center;
                font-weight: 700;
            }

            .cost-row-action {
                width: 54px;
                text-align: center;
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

        @php
            $oldDetail = old('detail', [['id_akun_biaya' => '', 'keterangan' => '', 'jumlah' => '']]);
        @endphp

        <form method="POST" action="{{ route('pembukuan-baru.jurnal-umum.biaya.store') }}" class="cost-form">
            @csrf
            <div class="cost-form-header">Input Pembayaran Biaya</div>
            <div class="cost-form-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-control"
                            value="{{ old('tanggal', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="nomor_transaksi">Nomor</label>
                        <input type="text" id="nomor_transaksi" name="nomor_transaksi" class="form-control"
                            value="{{ old('nomor_transaksi', $noTransaksi) }}" required>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label" for="id_akun_kas">Dibayar Dari</label>
                        <select id="id_akun_kas" name="id_akun_kas" class="form-select select-search"
                            data-placeholder="Cari kas atau bank" required>
                            <option value="">-- Pilih Kas / Bank --</option>
                            @foreach ($akunKas as $akun)
                                <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_kas') == $akun->id_akun_perkiraan)>
                                    {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4 mb-2">
                    <div>
                        <div class="cost-section-title">Daftar Biaya</div>
                        <small class="text-muted">Contoh: listrik, PDAM/air, internet, biaya lain</small>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-tambah-biaya">
                        <i class="fas fa-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered cost-table mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th style="min-width: 260px;">Akun Biaya</th>
                                <th style="min-width: 260px;">Keterangan</th>
                                <th style="min-width: 160px;">Jumlah</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="biaya-body">
                            @foreach ($oldDetail as $index => $item)
                                <tr class="biaya-row">
                                    <td class="cost-row-number">{{ $index + 1 }}</td>
                                    <td>
                                        <select name="detail[{{ $index }}][id_akun_biaya]"
                                            class="form-select select-search akun-biaya"
                                            data-placeholder="Cari akun biaya" required>
                                            <option value="">-- Pilih Akun Biaya --</option>
                                            @foreach ($akunBiaya as $akun)
                                                <option value="{{ $akun->id_akun_perkiraan }}" @selected(($item['id_akun_biaya'] ?? '') == $akun->id_akun_perkiraan)>
                                                    {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="detail[{{ $index }}][keterangan]"
                                            class="form-control" value="{{ $item['keterangan'] ?? '' }}"
                                            placeholder="Contoh: Bayar listrik kandang" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01"
                                            name="detail[{{ $index }}][jumlah]"
                                            class="form-control text-end input-jumlah"
                                            value="{{ $item['jumlah'] ?? '' }}" required>
                                    </td>
                                    <td class="cost-row-action">
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-biaya"
                                            title="Hapus baris">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-lg-7">
                        <div class="alert alert-light border mb-0">
                            Jurnal otomatis: semua baris biaya masuk debit, totalnya masuk kredit ke akun pembayaran.
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="cost-preview h-100">
                            <div class="label">Total Pembayaran</div>
                            <div class="value mt-1" id="preview-jumlah">Rp 0</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'biaya']) }}"
                        class="btn btn-outline-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Biaya
                    </button>
                </div>
            </div>
        </form>

        <template id="template-biaya-row">
            <tr class="biaya-row">
                <td class="cost-row-number"></td>
                <td>
                    <select class="form-select select-search akun-biaya" data-placeholder="Cari akun biaya" required>
                        <option value="">-- Pilih Akun Biaya --</option>
                        @foreach ($akunBiaya as $akun)
                            <option value="{{ $akun->id_akun_perkiraan }}">
                                {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control" placeholder="Contoh: Bayar PDAM / air" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" class="form-control text-end input-jumlah" required>
                </td>
                <td class="cost-row-action">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-biaya" title="Hapus baris">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        </template>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                const body = document.getElementById('biaya-body');
                const template = document.getElementById('template-biaya-row');
                const addButton = document.getElementById('btn-tambah-biaya');
                const preview = document.getElementById('preview-jumlah');

                function initSelect(context) {
                    if (!window.jQuery || !jQuery.fn.select2) {
                        return;
                    }

                    jQuery(context).find('.select-search').each(function() {
                        if (jQuery(this).data('select2')) {
                            return;
                        }

                        jQuery(this).select2({
                            width: '100%',
                            placeholder: function() {
                                return jQuery(this).data('placeholder') || '-- Pilih --';
                            },
                            allowClear: true
                        });
                    });
                }

                function refreshRows() {
                    body.querySelectorAll('.biaya-row').forEach(function(row, index) {
                        row.querySelector('.cost-row-number').textContent = index + 1;
                        row.querySelector('.akun-biaya').setAttribute('name', `detail[${index}][id_akun_biaya]`);
                        row.querySelector('input[type="text"]').setAttribute('name', `detail[${index}][keterangan]`);
                        row.querySelector('.input-jumlah').setAttribute('name', `detail[${index}][jumlah]`);
                    });

                    body.querySelectorAll('.btn-hapus-biaya').forEach(function(button) {
                        button.disabled = body.querySelectorAll('.biaya-row').length <= 1;
                    });

                    updatePreview();
                }

                function updatePreview() {
                    const total = Array.from(body.querySelectorAll('.input-jumlah')).reduce(function(sum, input) {
                        return sum + Number(input.value || 0);
                    }, 0);

                    preview.textContent = 'Rp ' + total.toLocaleString('id-ID', {
                        maximumFractionDigits: 2
                    });
                }

                function addRow() {
                    const fragment = template.content.cloneNode(true);
                    body.appendChild(fragment);
                    const row = body.querySelector('.biaya-row:last-child');
                    refreshRows();
                    initSelect(row);
                }

                addButton.addEventListener('click', addRow);
                body.addEventListener('input', function(event) {
                    if (event.target.classList.contains('input-jumlah')) {
                        updatePreview();
                    }
                });
                body.addEventListener('click', function(event) {
                    const button = event.target.closest('.btn-hapus-biaya');

                    if (!button || body.querySelectorAll('.biaya-row').length <= 1) {
                        return;
                    }

                    const row = button.closest('.biaya-row');
                    if (window.jQuery && jQuery(row).find('.select-search').data('select2')) {
                        jQuery(row).find('.select-search').select2('destroy');
                    }
                    row.remove();
                    refreshRows();
                });

                initSelect(document);
                refreshRows();
            })();
        </script>
    @endsection
</x-theme.app>
