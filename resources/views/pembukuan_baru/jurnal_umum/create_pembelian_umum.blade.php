<x-theme.app title="{{ $title }}" table="Y" sizeCard="11">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Pencatatan pembelian barang/peralatan/perlengkapan umum dari master produk kategori 1</small>
            </div>
            <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembelian-umum']) }}"
                class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Pembelian Umum
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .pu-form {
                border: 1px solid #dce3f2;
                border-radius: 12px;
                background: #fff;
                overflow: hidden;
            }

            .pu-form-header {
                padding: 14px 16px;
                border-bottom: 1px solid #dce3f2;
                background: #f5f7fc;
                color: #1d3167;
                font-weight: 800;
            }

            .pu-form-body {
                padding: 16px;
            }

            .pu-section-title {
                color: #1d3167;
                font-size: 15px;
                font-weight: 800;
            }

            .pu-preview {
                padding: 14px;
                border: 1px solid #c2dbfe;
                border-radius: 10px;
                background: #f0f7ff;
            }

            .pu-preview .label {
                color: #1e429f;
                font-size: 12px;
                font-weight: 700;
            }

            .pu-preview .value {
                color: #1a365d;
                font-size: 22px;
                font-weight: 900;
            }

            .pu-table th {
                background: #2f4f9f;
                color: #fff;
                font-size: 13px;
                white-space: nowrap;
            }

            .pu-table td {
                vertical-align: top;
            }

            .pu-row-number {
                width: 42px;
                padding-top: 13px;
                text-align: center;
                font-weight: 700;
            }

            .pu-row-action {
                width: 54px;
                text-align: center;
            }

            .journal-preview-table th {
                background: #3b5998;
                color: #fff;
                font-size: 12px;
            }

            .journal-preview-table td {
                font-size: 13px;
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
            $oldDetail = old('detail', [
                ['id_produk' => '', 'qty' => 1, 'satuan' => '', 'harga_satuan' => 0, 'id_akun_debit' => '', 'keterangan' => '']
            ]);
        @endphp

        <form method="POST" action="{{ route('pembukuan-baru.jurnal-umum.pembelian-umum.store') }}" class="pu-form">
            @csrf
            <div class="pu-form-header">Input Transaksi Pembelian Umum</div>
            <div class="pu-form-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-control"
                            value="{{ old('tanggal', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="nomor_transaksi">Nomor Transaksi</label>
                        <input type="text" id="nomor_transaksi" name="nomor_transaksi" class="form-control"
                            value="{{ old('nomor_transaksi', $noTransaksi) }}" required>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label" for="id_akun_pembayaran">Metode Pembayaran (Kredit)</label>
                        <select id="id_akun_pembayaran" name="id_akun_pembayaran" class="form-select select-search"
                            data-placeholder="Pilih Kas / Bank / Hutang" required>
                            <option value="">-- Pilih Akun Pembayaran --</option>
                            @foreach ($akunPembayaran as $akun)
                                <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_pembayaran') == $akun->id_akun_perkiraan)>
                                    {{ $akun->kode_perkiraan }} - {{ $akun->nama }} ({{ $akun->tipe_akun }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="keterangan_global">Keterangan / Catatan Transaksi (Opsional)</label>
                        <input type="text" id="keterangan_global" name="keterangan_global" class="form-control"
                            value="{{ old('keterangan_global') }}" placeholder="Contoh: Pembelian perlengkapan dan peralatan kandang Martadah">
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4 mb-1">
                    <div>
                        <div class="pu-section-title">Item Produk Pembelian (Kategori 1)</div>
                        <small class="text-muted">Akun debit otomatis: <strong class="text-primary">110406 - Persediaan Umum</strong></small>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-tambah-baris">
                        <i class="fas fa-plus me-1"></i> Tambah Item
                    </button>
                </div>
                <div class="alert alert-info py-2 mb-2 d-flex align-items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    <span>Setiap item pembelian akan dicatat ke akun <strong>Persediaan Umum (110406)</strong> secara otomatis.</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered pu-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px;">No</th>
                                <th style="min-width: 200px;">Produk (Kategori 1)</th>
                                <th style="min-width: 110px;">Qty</th>
                                <th style="min-width: 90px;">Satuan</th>
                                <th style="min-width: 140px;">Harga Satuan (Rp)</th>
                                <th style="min-width: 140px;">Subtotal (Rp)</th>
                                <th style="min-width: 140px;">Catatan Item</th>
                                <th style="width:44px;"></th>
                            </tr>
                        </thead>
                        <tbody id="pu-body">
                            @foreach ($oldDetail as $index => $item)
                                <tr class="pu-row" data-index="{{ $index }}">
                                    <td class="pu-row-number">{{ $index + 1 }}</td>
                                    <td>
                                        <select name="detail[{{ $index }}][id_produk]"
                                            class="form-select select-search select-produk"
                                            data-placeholder="Pilih Produk Kategori 1" required>
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach ($produkList as $p)
                                                <option value="{{ $p->id_produk }}"
                                                    data-satuan="{{ $p->nm_satuan ?? 'PCS' }}"
                                                    @selected(($item['id_produk'] ?? '') == $p->id_produk)>
                                                    {{ $p->nm_produk }} {{ $p->kd_produk ? "({$p->kd_produk})" : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0.001"
                                            name="detail[{{ $index }}][qty]"
                                            class="form-control input-qty text-end"
                                            value="{{ $item['qty'] ?? 1 }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="detail[{{ $index }}][satuan]"
                                            class="form-control input-satuan bg-light text-center"
                                            value="{{ $item['satuan'] ?? '' }}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0"
                                            name="detail[{{ $index }}][harga_satuan]"
                                            class="form-control input-harga text-end"
                                            value="{{ $item['harga_satuan'] ?? 0 }}" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control input-subtotal bg-light text-end fw-bold"
                                            value="0" readonly>
                                    </td>
                                    <td>
                                        <input type="text" name="detail[{{ $index }}][keterangan]"
                                            class="form-control input-catatan"
                                            value="{{ $item['keterangan'] ?? '' }}"
                                            placeholder="Catatan...">
                                    </td>
                                    <td class="pu-row-action">
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris"
                                            title="Hapus baris">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row g-3 mt-3 align-items-start">
                    <div class="col-lg-6">
                        <div class="card border">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0 text-primary"><i class="fas fa-book me-1"></i> Preview Jurnal Akuntansi</h6>
                            </div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-bordered journal-preview-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Posisi</th>
                                            <th>Akun</th>
                                            <th class="text-end">Debit (Rp)</th>
                                            <th class="text-end">Kredit (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="preview-jurnal-body">
                                        <!-- Dynamic JS Preview -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold bg-light">
                                            <td colspan="2" class="text-end">Total</td>
                                            <td class="text-end" id="preview-total-debit">0</td>
                                            <td class="text-end" id="preview-total-kredit">0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="pu-preview mb-3">
                            <div class="label">TOTAL PEMBELIAN UMUM</div>
                            <div class="value" id="label-total-pembelian">Rp 0</div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembelian-umum']) }}"
                                class="btn btn-light">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Simpan Pembelian Umum
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </x-slot>

    @section('scripts')
        <script>
            $(document).ready(function() {
                const produkData = @json($produkList);
                const produkMap = {};
                produkData.forEach(p => {
                    produkMap[p.id_produk] = p;
                });

                function initSelect2(element) {
                    if (!$.fn.select2) return;
                    $(element).find('.select-search').each(function() {
                        if ($(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2('destroy');
                        }
                        $(this).select2({
                            width: '100%',
                            placeholder: $(this).data('placeholder') || 'Pilih data',
                            allowClear: true
                        });
                    });
                }

                function formatRupiah(val) {
                    return Number(val || 0).toLocaleString('id-ID');
                }

                function hitungBaris($row) {
                    const qty = parseFloat($row.find('.input-qty').val()) || 0;
                    const harga = parseFloat($row.find('.input-harga').val()) || 0;
                    const subtotal = qty * harga;
                    $row.find('.input-subtotal').val(formatRupiah(subtotal));
                    return subtotal;
                }

                function refreshTotals() {
                    let total = 0;
                    const items = [];

                    $('.pu-row').each(function(i) {
                        $(this).find('.pu-row-number').text(i + 1);
                        const subtotal = hitungBaris($(this));
                        total += subtotal;

                        const idProduk = $(this).find('.select-produk').val();
                        const p = produkMap[idProduk];
                        const nmProduk = p ? p.nm_produk : 'Produk';

                        if (subtotal > 0) {
                            items.push({
                                akun: '110406 - Persediaan Umum',
                                nominal: subtotal,
                                namaProduk: nmProduk
                            });
                        }
                    });

                    $('#label-total-pembelian').text('Rp ' + formatRupiah(total));

                    // Update Jurnal Preview
                    const akunKreditText = $('#id_akun_pembayaran option:selected').text().trim() || 'Kas / Bank / Hutang';
                    const $previewBody = $('#preview-jurnal-body');
                    $previewBody.empty();

                    if (items.length === 0) {
                        $previewBody.append('<tr><td colspan="4" class="text-center text-muted py-2">Belum ada item yang valid</td></tr>');
                        $('#preview-total-debit').text('0');
                        $('#preview-total-kredit').text('0');
                        return;
                    }

                    items.forEach(item => {
                        $previewBody.append(`
                            <tr>
                                <td><span class="badge bg-success">DEBIT</span></td>
                                <td>${item.akun}</td>
                                <td class="text-end fw-bold text-success">${formatRupiah(item.nominal)}</td>
                                <td class="text-end">0</td>
                            </tr>
                        `);
                    });

                    $previewBody.append(`
                        <tr>
                            <td><span class="badge bg-danger">KREDIT</span></td>
                            <td>${akunKreditText}</td>
                            <td class="text-end">0</td>
                            <td class="text-end fw-bold text-danger">${formatRupiah(total)}</td>
                        </tr>
                    `);

                    $('#preview-total-debit').text(formatRupiah(total));
                    $('#preview-total-kredit').text(formatRupiah(total));

                    $('.btn-hapus-baris').prop('disabled', $('.pu-row').length <= 1);
                }

                // Event on product change: auto fill satuan
                $(document).on('change', '.select-produk', function() {
                    const $row = $(this).closest('.pu-row');
                    const idProduk = $(this).val();
                    const p = produkMap[idProduk];
                    if (p) {
                        $row.find('.input-satuan').val(p.nm_satuan || 'PCS');
                    } else {
                        $row.find('.input-satuan').val('');
                    }
                    refreshTotals();
                });

                $(document).on('input change', '.input-qty, .input-harga, #id_akun_pembayaran', function() {
                    refreshTotals();
                });

                // Tambah Baris
                $('#btn-tambah-baris').on('click', function() {
                    const index = $('.pu-row').length;
                    let produkOptions = '<option value="">-- Pilih Produk --</option>';
                    produkData.forEach(p => {
                        produkOptions += `<option value="${p.id_produk}" data-satuan="${p.nm_satuan || 'PCS'}">${p.nm_produk} ${p.kd_produk ? '(' + p.kd_produk + ')' : ''}</option>`;
                    });

                    const newRow = `
                        <tr class="pu-row" data-index="${index}">
                            <td class="pu-row-number">${index + 1}</td>
                            <td>
                                <select name="detail[${index}][id_produk]" class="form-select select-search select-produk" data-placeholder="Pilih Produk Kategori 1" required>
                                    ${produkOptions}
                                </select>
                            </td>
                            <td>
                                <input type="number" step="any" min="0.001" name="detail[${index}][qty]" class="form-control input-qty text-end" value="1" required>
                            </td>
                            <td>
                                <input type="text" name="detail[${index}][satuan]" class="form-control input-satuan bg-light text-center" value="" readonly>
                            </td>
                            <td>
                                <input type="number" step="any" min="0" name="detail[${index}][harga_satuan]" class="form-control input-harga text-end" value="0" required>
                            </td>
                            <td>
                                <input type="text" class="form-control input-subtotal bg-light text-end fw-bold" value="0" readonly>
                            </td>
                            <td>
                                <input type="text" name="detail[${index}][keterangan]" class="form-control input-catatan" value="" placeholder="Catatan...">
                            </td>
                            <td class="pu-row-action">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris" title="Hapus baris">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    const $newRow = $(newRow);
                    $('#pu-body').append($newRow);
                    initSelect2($newRow);
                    refreshTotals();
                });

                // Hapus Baris
                $(document).on('click', '.btn-hapus-baris', function() {
                    if ($('.pu-row').length > 1) {
                        $(this).closest('.pu-row').remove();
                        // Re-index names
                        $('.pu-row').each(function(i) {
                            $(this).attr('data-index', i);
                            $(this).find('.pu-row-number').text(i + 1);
                            $(this).find('.select-produk').attr('name', `detail[${i}][id_produk]`);
                            $(this).find('.input-qty').attr('name', `detail[${i}][qty]`);
                            $(this).find('.input-satuan').attr('name', `detail[${i}][satuan]`);
                            $(this).find('.input-harga').attr('name', `detail[${i}][harga_satuan]`);
                            $(this).find('.input-catatan').attr('name', `detail[${i}][keterangan]`);
                        });
                        refreshTotals();
                    }
                });

                // Initialize existing rows
                initSelect2(document);
                $('.pu-row').each(function() {
                    const idProduk = $(this).find('.select-produk').val();
                    const p = produkMap[idProduk];
                    if (p && !$(this).find('.input-satuan').val()) {
                        $(this).find('.input-satuan').val(p.nm_satuan || 'PCS');
                    }
                });
                refreshTotals();
            });
        </script>
    @endsection
</x-theme.app>
