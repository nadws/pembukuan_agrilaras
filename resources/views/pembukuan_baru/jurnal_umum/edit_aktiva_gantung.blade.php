<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Edit biaya pembangunan/pengadaan aset yang ditampung di aktiva gantung</small>
            </div>
            <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'aktiva-gantung']) }}"
                class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Aktiva Gantung
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .asset-form {
                border: 1px solid #dce3f2;
                border-radius: 12px;
                background: #fff;
                overflow: hidden;
            }

            .asset-form-header {
                padding: 14px 16px;
                border-bottom: 1px solid #dce3f2;
                background: #f5f7fc;
                color: #1d3167;
                font-weight: 800;
            }

            .asset-form-body {
                padding: 16px;
            }

            .asset-form .form-label {
                color: #536078;
                font-size: 12px;
                font-weight: 700;
            }

            .journal-preview {
                padding: 14px;
                border: 1px solid #ffd38a;
                border-radius: 10px;
                background: #fff9ed;
            }

            .journal-preview .value {
                color: #7a4300;
                font-size: 22px;
                font-weight: 900;
            }

            .asset-table-wrap {
                overflow-x: auto;
                border: 1px solid #dce3f2;
                border-radius: 10px;
            }

            .asset-table {
                min-width: 720px;
                margin-bottom: 0;
            }

            .asset-table thead th {
                background: #29468f;
                color: #fff;
                font-size: 12px;
                white-space: nowrap;
            }

            .asset-table td {
                vertical-align: top;
            }

            .asset-row-number {
                width: 42px;
                padding-top: 13px;
                text-align: center;
                font-weight: 700;
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
            $oldDetail = old('detail', !empty($detail) ? $detail : [['keterangan' => '', 'jumlah' => '']]);
        @endphp

        <form method="POST" action="{{ route('pembukuan-baru.jurnal-umum.aktiva-gantung.transaksi.update', $nomor_transaksi) }}"
            class="asset-form">
            @csrf
            @method('PUT')
            <div class="asset-form-header">Edit Biaya Aktiva Gantung ({{ $nomor_transaksi }})</div>
            <div class="asset-form-body">
                <div class="row g-3">
                    <div class="col-lg-12">
                        <label class="form-label" for="aktiva_gantung_id">Pilih Aset Gantung</label>
                        <select id="aktiva_gantung_id" name="aktiva_gantung_id" class="form-select select-search"
                            data-placeholder="Cari aset gantung" required>
                            <option value="">-- Pilih Aset --</option>
                            @foreach ($asetGantung as $aset)
                                <option value="{{ $aset->id }}" @selected(old('aktiva_gantung_id', $aktiva_gantung_id) == $aset->id)>
                                    {{ $aset->kode }} - {{ $aset->nama_aset }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-control"
                            value="{{ old('tanggal', $tanggal) }}" required>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="nomor_transaksi">Nomor Transaksi</label>
                        <input type="text" id="nomor_transaksi" name="nomor_transaksi" class="form-control"
                            value="{{ old('nomor_transaksi', $nomor_transaksi) }}" required>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label" for="akun_aktiva_gantung_display">Akun Penampung Aktiva Gantung</label>
                        <input type="text" id="akun_aktiva_gantung_display" class="form-control"
                            value="{{ $akunAktivaGantung?->kode_perkiraan }} - {{ $akunAktivaGantung?->nama }}"
                            readonly>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label" for="id_akun_kas">Dibayar Dari</label>
                        <select id="id_akun_kas" name="id_akun_kas" class="form-select select-search"
                            data-placeholder="Cari kas atau bank" required>
                            <option value="">-- Pilih Kas / Bank --</option>
                            @foreach ($akunKas as $akun)
                                <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_kas', $id_akun_kas) == $akun->id_akun_perkiraan)>
                                    {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4 mb-2">
                    <div>
                        <h6 class="mb-1">Daftar Material / Biaya</h6>
                        <small class="text-muted">Contoh: paku, semen, bata, ongkos tukang, atau item lain</small>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-tambah-detail">
                        <i class="fas fa-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="asset-table-wrap">
                    <table class="table table-bordered asset-table">
                        <thead>
                            <tr>
                                <th width="45">No</th>
                                <th>Item / Keterangan</th>
                                <th width="220">Jumlah</th>
                                <th width="60"></th>
                            </tr>
                        </thead>
                        <tbody id="detail-body">
                            @foreach ($oldDetail as $index => $item)
                                <tr class="detail-row">
                                    <td class="asset-row-number">{{ $index + 1 }}</td>
                                    <td>
                                        <input type="text" name="detail[{{ $index }}][keterangan]"
                                            class="form-control" value="{{ $item['keterangan'] ?? '' }}"
                                            placeholder="Contoh: Paku" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01"
                                            name="detail[{{ $index }}][jumlah]"
                                            class="form-control text-end input-jumlah"
                                            value="{{ $item['jumlah'] ?? '' }}" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-detail">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="journal-preview mt-3">
                    <div class="fw-bold text-muted small">Jurnal otomatis</div>
                    <div class="mt-1">Setiap item didebit ke akun aktiva gantung, totalnya dikredit ke akun pembayaran.</div>
                    <div class="value mt-1" id="preview-jumlah">Rp 0</div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'aktiva-gantung']) }}"
                        class="btn btn-outline-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        <template id="template-detail-row">
            <tr class="detail-row">
                <td class="asset-row-number"></td>
                <td>
                    <input type="text" class="form-control" placeholder="Contoh: Ongkos tukang" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" class="form-control text-end input-jumlah" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-detail">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        </template>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                const detailBody = document.getElementById('detail-body');
                const detailTemplate = document.getElementById('template-detail-row');
                const btnTambahDetail = document.getElementById('btn-tambah-detail');
                const preview = document.getElementById('preview-jumlah');

                function initSelect() {
                    if (!window.jQuery || !jQuery.fn.select2) return;

                    jQuery('.select-search').select2({
                        width: '100%',
                        placeholder: function() {
                            return jQuery(this).data('placeholder') || '-- Pilih --';
                        },
                        allowClear: true
                    });
                }

                function updatePreview() {
                    const total = Array.from(detailBody.querySelectorAll('.input-jumlah')).reduce(function(sum, input) {
                        return sum + Number(input.value || 0);
                    }, 0);

                    preview.textContent = 'Rp ' + total.toLocaleString('id-ID', {
                        maximumFractionDigits: 2
                    });
                }

                function refreshDetailRows() {
                    detailBody.querySelectorAll('.detail-row').forEach(function(row, index) {
                        row.querySelector('.asset-row-number').textContent = index + 1;
                        row.querySelector('input[type="text"]').setAttribute('name', `detail[${index}][keterangan]`);
                        row.querySelector('.input-jumlah').setAttribute('name', `detail[${index}][jumlah]`);
                    });

                    detailBody.querySelectorAll('.btn-hapus-detail').forEach(function(button) {
                        button.disabled = detailBody.querySelectorAll('.detail-row').length <= 1;
                    });

                    updatePreview();
                }

                function tambahDetailRow() {
                    detailBody.appendChild(detailTemplate.content.cloneNode(true));
                    refreshDetailRows();
                }

                btnTambahDetail.addEventListener('click', tambahDetailRow);
                detailBody.addEventListener('input', function(event) {
                    if (event.target.classList.contains('input-jumlah')) {
                        updatePreview();
                    }
                });
                detailBody.addEventListener('click', function(event) {
                    const button = event.target.closest('.btn-hapus-detail');

                    if (!button || detailBody.querySelectorAll('.detail-row').length <= 1) {
                        return;
                    }

                    button.closest('.detail-row').remove();
                    refreshDetailRows();
                });

                initSelect();
                refreshDetailRows();
            })();
        </script>
    @endsection
</x-theme.app>
