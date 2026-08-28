<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Input transaksi pembelian pakan, vitamin/obat/vaksin, atau barang umum dari pemasok</small>
            </div>
            <a href="{{ route('transaksi.faktur-pembelian.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .invoice-form {
                --invoice-primary: #29468f;
                --invoice-border: #dce3f2;
                --invoice-soft: #f5f7fc;
            }

            .invoice-form .form-label {
                margin-bottom: 5px;
                color: #536078;
                font-size: 12px;
                font-weight: 700;
            }

            .invoice-form .form-control,
            .invoice-form .form-select {
                min-height: 40px;
                border-color: var(--invoice-border);
                border-radius: 8px;
            }

            .invoice-form .select2-container {
                width: 100% !important;
            }

            .invoice-form .select2-container--default .select2-selection--single {
                min-height: 40px;
                border-color: var(--invoice-border);
                border-radius: 8px;
            }

            .invoice-form .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 40px;
            }

            .invoice-form .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 40px;
            }

            .header-section {
                padding: 16px;
                margin-bottom: 20px;
                border: 1px solid var(--invoice-border);
                border-radius: 12px;
                background: var(--invoice-soft);
            }

            .jenis-section {
                padding: 16px;
                margin-bottom: 20px;
                border: 1px solid var(--invoice-border);
                border-radius: 12px;
                background: #fff;
            }

            .jenis-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
            }

            .jenis-card {
                display: flex;
                align-items: center;
                gap: 12px;
                min-height: 78px;
                padding: 14px 16px;
                border: 1px solid var(--invoice-border);
                border-radius: 12px;
                background: var(--invoice-soft);
                cursor: pointer;
                transition: 0.15s ease;
            }

            .jenis-card input {
                width: 18px;
                height: 18px;
                margin: 0;
            }

            .jenis-card .jenis-title {
                display: block;
                color: #263b78;
                font-size: 15px;
                font-weight: 800;
            }

            .jenis-card .jenis-akun {
                display: block;
                color: #64728b;
                font-size: 12px;
                font-weight: 600;
            }

            .jenis-card.is-active {
                border-color: var(--invoice-primary);
                box-shadow: 0 0 0 3px rgba(41, 70, 143, 0.12);
                background: #eef3ff;
            }

            .invoice-entry.is-hidden {
                display: none;
            }

            .item-table-wrap {
                overflow-x: auto;
                border: 1px solid var(--invoice-border);
                border-radius: 12px;
                margin-bottom: 16px;
            }

            .item-table {
                min-width: 970px;
                margin-bottom: 0;
            }

            .item-table thead th {
                padding: 10px;
                border-color: #4f69b6;
                color: #fff;
                background: var(--invoice-primary);
                font-size: 12px;
                white-space: nowrap;
            }

            .item-table td {
                vertical-align: middle;
                padding: 8px;
            }

            .baris-item.harga-berbeda td {
                background: #fff0f0;
            }

            .harga-rata {
                display: block;
                min-height: 16px;
                font-size: 11px;
            }

            .harga-berbeda .harga-rata {
                color: #c62828 !important;
                font-weight: 700;
            }

            .item-table .form-control,
            .item-table .form-select {
                min-height: 36px;
                font-size: 13px;
            }

            .subtotal-cell {
                min-width: 130px;
                font-weight: 600;
                text-align: right;
                white-space: nowrap;
            }

            .grand-total-box {
                padding: 14px 18px;
                border: 1px solid var(--invoice-border);
                border-radius: 12px;
                background: var(--invoice-soft);
                text-align: right;
            }

            .grand-total-box .label {
                font-size: 12px;
                color: #536078;
                font-weight: 700;
            }

            .grand-total-box .value {
                font-size: 20px;
                font-weight: 700;
                color: var(--invoice-primary);
            }

            .grand-total-box .summary-row {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                margin-top: 8px;
                color: #536078;
                font-size: 13px;
                font-weight: 700;
            }

            .grand-total-box .summary-row.net {
                padding-top: 8px;
                border-top: 1px solid var(--invoice-border);
                color: var(--invoice-primary);
            }

            .btn-remove-item {
                white-space: nowrap;
            }

            @media (max-width: 900px) {
                .jenis-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 576px) {
                .jenis-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <form method="POST" action="{{ route('transaksi.faktur-pembelian.store') }}" class="invoice-form"
            id="form-faktur">
            @csrf
            @php
                $jenisFakturTerpilih = old('jenis_faktur');
            @endphp

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

            <div class="jenis-section">
                <label class="form-label d-block mb-2">Jenis Faktur</label>
                <div class="jenis-grid">
                    <label class="jenis-card {{ $jenisFakturTerpilih === 'pakan' ? 'is-active' : '' }}"
                        data-jenis-card="pakan">
                        <input type="radio" name="jenis_faktur" value="pakan" @checked($jenisFakturTerpilih === 'pakan') required>
                        <span>
                            <span class="jenis-title">Faktur Pakan</span>
                            <span class="jenis-akun">Persediaan Pakan</span>
                        </span>
                    </label>
                    <label class="jenis-card {{ $jenisFakturTerpilih === 'vitamin' ? 'is-active' : '' }}"
                        data-jenis-card="vitamin">
                        <input type="radio" name="jenis_faktur" value="vitamin" @checked($jenisFakturTerpilih === 'vitamin') required>
                        <span>
                            <span class="jenis-title">Faktur Vitamin &amp; Vaksin</span>
                            <span class="jenis-akun">Vitamin, obat, dan vaksin</span>
                        </span>
                    </label>
                    <label class="jenis-card {{ $jenisFakturTerpilih === 'barang_umum' ? 'is-active' : '' }}" data-jenis-card="barang_umum">
                        <input type="radio" name="jenis_faktur" value="barang_umum" @checked($jenisFakturTerpilih === 'barang_umum') required>
                        <span><span class="jenis-title">Faktur Barang Umum</span><span class="jenis-akun">Persediaan Umum</span></span>
                    </label>
                </div>
            </div>

            <div class="invoice-entry {{ $jenisFakturTerpilih ? '' : 'is-hidden' }}" id="invoice-entry">
                <div class="header-section">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label" for="no_faktur">Nomor Faktur</label>
                            <input type="text" id="no_faktur" name="no_faktur" class="form-control"
                                value="{{ old('no_faktur', $noFakturDefault) }}" required readonly>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label" for="tanggal_faktur">Tanggal Faktur</label>
                            <input type="date" id="tanggal_faktur" name="tanggal_faktur" class="form-control"
                                value="{{ old('tanggal_faktur', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label" for="supplier_id">Suplier</label>
                            <select id="supplier_id" name="supplier_id" class="form-select select-search-supplier"
                                data-placeholder="Cari suplier" required>
                                <option value="">-- Pilih Suplier --</option>
                                @foreach ($suppliers as $s)
                                    <option value="{{ $s->id_suplier }}" @selected(old('supplier_id') == $s->id_suplier)>
                                        {{ $s->nm_suplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label" for="metode_pembayaran">Metode Pembayaran</label>
                            <select id="metode_pembayaran" name="metode_pembayaran" class="form-select" required>
                                <option value="hutang" @selected(old('metode_pembayaran', 'hutang') === 'hutang')>Hutang</option>
                                <option value="tunai" @selected(old('metode_pembayaran') === 'tunai')>Tunai / Bank</option>
                            </select>
                        </div>
                        {{-- <div class="col-lg-3 col-md-6">
                            <label class="form-label" for="jatuh_tempo">Jatuh Tempo</label>
                            <input type="date" id="jatuh_tempo" name="jatuh_tempo" class="form-control"
                                value="{{ old('jatuh_tempo') }}">
                        </div> --}}
                        <div class="col-lg-4 col-md-6" id="akun-pembayaran-wrap">
                            <label class="form-label" for="id_akun_pembayaran">Akun Pembayaran</label>
                            <select id="id_akun_pembayaran" name="id_akun_pembayaran" class="form-select select-search-akun" data-placeholder="Cari kas atau bank">
                                <option value="">-- Pilih Kas / Bank --</option>
                                @foreach($akunPembayaran as $akun)
                                    <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_pembayaran') == $akun->id_akun_perkiraan)>{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-8 col-md-6">
                            <label class="form-label" for="keterangan">Keterangan</label>
                            <input type="text" id="keterangan" name="keterangan" class="form-control"
                                value="{{ old('keterangan') }}" placeholder="Catatan tambahan (opsional)">
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <h6 class="mb-0">Daftar Item Pembelian</h6>
                        <small class="text-muted">Isi produk, jumlah, dan harga pembelian.</small>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-item">
                        <i class="fas fa-plus me-1"></i> Tambah Item
                    </button>
                </div>

                <div class="item-table-wrap">
                    <table class="table item-table" id="tabel-item">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th width="230">Produk</th>
                                <th width="110">Qty</th>
                                <th width="110">Satuan</th>
                                <th width="140">Harga Satuan</th>
                                <th>Subtotal</th>
                                {{-- <th width="130">No. Batch</th>
                                <th width="150">Tgl Expired</th> --}}
                                <th width="60"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-item">
                            {{-- baris item akan disisipkan di sini oleh JavaScript --}}
                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-end mb-3">
                    <div class="col-lg-6 col-md-8">
                        <div class="grand-total-box">
                            <div class="row g-2 align-items-end text-start mb-2">
                                <div class="col-md-7">
                                    <label class="form-label" for="diskon_total">Diskon</label>
                                    <input type="number" step="0.01" min="0" id="diskon_total"
                                        name="diskon_total" class="form-control text-end"
                                        value="{{ old('diskon_total', 0) }}">
                                </div>
                                <div class="col-md-5 text-md-end">
                                    <div class="label" id="payment-total-label">TOTAL HPP / PEMBAYARAN</div>
                                    <div class="value" id="grand-total-display">Rp 0</div>
                                </div>
                            </div>
                            <div class="summary-row">
                                <span>Total sebelum diskon</span>
                                <span id="subtotal-display">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span>Diskon</span>
                                <span id="diskon-display">Rp 0</span>
                            </div>
                            <div class="summary-row net">
                                <span>Nilai masuk HPP/persediaan</span>
                                <span id="net-total-display">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('transaksi.faktur-pembelian.index') }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Faktur
                    </button>
                </div>
            </div>
        </form>

        {{-- Template 1 baris item, disembunyikan, dipakai untuk di-clone via JS --}}
        <template id="template-baris-item">
            <tr class="baris-item">
                <td class="text-center nomor-baris">1</td>
                <td>
                    <input type="hidden" name="item[__INDEX__][sumber_produk]" class="input-sumber-produk" value="perencanaan">
                    <select name="item[__INDEX__][pakan_id]" class="form-select select-produk select-search-produk"
                        data-placeholder="Cari produk" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($produk as $p)
                            <option value="{{ $p->id_produk }}" data-kategori="{{ $p->kategori }}"
                                data-satuan="{{ $p->satuan_dosis }}" data-rata-harga="{{ $hargaRataRata[($p->sumber_produk ?? 'perencanaan') . ':' . $p->id_produk] ?? '' }}">
                                {{ $p->nm_produk }}
                                ({{ $p->kategori }}{{ $p->satuan_dosis ? ' / ' . $p->satuan_dosis : '' }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" name="item[__INDEX__][qty]"
                        class="form-control input-qty" required>
                </td>
                <td>
                    <input type="text" name="item[__INDEX__][satuan]" class="form-control input-satuan"
                        placeholder="Otomatis" readonly>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="item[__INDEX__][harga_satuan]"
                        class="form-control input-harga" placeholder="Dari subtotal" required>
                    <small class="harga-rata text-muted"></small>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="item[__INDEX__][subtotal]"
                        class="form-control input-subtotal" placeholder="Dari harga" required>
                </td>
                {{-- <td>
                    <input type="text" name="item[__INDEX__][no_batch]" class="form-control">
                </td>
                <td>
                    <input type="date" name="item[__INDEX__][tanggal_expired]" class="form-control">
                </td> --}}
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        </template>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                const tbody = document.getElementById('tbody-item');
                const template = document.getElementById('template-baris-item');
                const btnTambah = document.getElementById('btn-tambah-item');
                const grandTotalDisplay = document.getElementById('grand-total-display');
                const subtotalDisplay = document.getElementById('subtotal-display');
                const diskonDisplay = document.getElementById('diskon-display');
                const netTotalDisplay = document.getElementById('net-total-display');
                const diskonTotalInput = document.getElementById('diskon_total');
                const invoiceEntry = document.getElementById('invoice-entry');
                const jenisRadios = document.querySelectorAll('input[name="jenis_faktur"]');
                const jenisCards = document.querySelectorAll('[data-jenis-card]');
                const initialItems = @json(array_values(old('item', [])));
                let indexBaris = 0;

                function jenisTerpilih() {
                    return document.querySelector('input[name="jenis_faktur"]:checked')?.value || '';
                }

                function initSelectSearch(scope = document) {
                    if (!window.jQuery || !jQuery.fn.select2) return;

                    jQuery(scope).find('.select-search-supplier, .select-search-produk, .select-search-akun').each(function() {
                        const select = jQuery(this);

                        if (select.hasClass('select2-hidden-accessible')) {
                            select.select2('destroy');
                        }

                        select.select2({
                            width: '100%',
                            placeholder: select.data('placeholder') || '-- Pilih --',
                            allowClear: true,
                            matcher: function(params, data) {
                                if (data.element && data.element.disabled) {
                                    return null;
                                }

                                if (!params.term || data.text.toLowerCase().includes(params.term
                                        .toLowerCase())) {
                                    return data;
                                }

                                return null;
                            }
                        });

                        select.off('change.fakturPembelian').on('change.fakturPembelian', function() {
                            if (this.classList.contains('select-produk')) {
                                isiSatuan(this);
                                cekHargaRataRata(this);
                            }
                        });
                    });
                }

                function formatRupiah(angka) {
                    return 'Rp ' + Number(angka || 0).toLocaleString('id-ID', {
                        maximumFractionDigits: 2
                    });
                }

                function angkaInput(input) {
                    return parseFloat(input?.value) || 0;
                }

                function isiAngka(input, angka) {
                    if (!input) return;
                    input.value = Number.isFinite(angka) ? Number(angka).toFixed(2) : '';
                }

                function hitungBaris(baris, sumber = 'harga') {
                    const inputQty = baris.querySelector('.input-qty');
                    const inputHarga = baris.querySelector('.input-harga');
                    const inputSubtotal = baris.querySelector('.input-subtotal');
                    const qty = angkaInput(inputQty);
                    const harga = angkaInput(inputHarga);
                    const subtotal = angkaInput(inputSubtotal);

                    if (sumber === 'subtotal') {
                        isiAngka(inputHarga, qty > 0 ? subtotal / qty : 0);
                        return subtotal;
                    }

                    const subtotalBaru = qty * harga;
                    isiAngka(inputSubtotal, subtotalBaru);
                    return subtotalBaru;
                }

                function cekHargaRataRata(selectProduk) {
                    const baris = selectProduk.closest('.baris-item');
                    if (!baris) return;
                    const option = selectProduk.options[selectProduk.selectedIndex];
                    const rata = parseFloat(option?.dataset.rataHarga || '');
                    const harga = angkaInput(baris.querySelector('.input-harga'));
                    const label = baris.querySelector('.harga-rata');
                    if (!Number.isFinite(rata) || rata <= 0) {
                        baris.classList.remove('harga-berbeda');
                        if (label) label.textContent = '';
                        return;
                    }
                    if (label) label.textContent = 'Rata-rata: ' + formatRupiah(rata);
                    baris.classList.toggle('harga-berbeda', harga > 0 && Math.abs(harga - rata) >= 5000);
                }

                function hitungUlangSemua() {
                    let subtotal = 0;

                    tbody.querySelectorAll('.baris-item').forEach((baris, i) => {
                        baris.querySelector('.nomor-baris').textContent = i + 1;
                        subtotal += angkaInput(baris.querySelector('.input-subtotal'));
                    });

                    const diskon = Math.min(angkaInput(diskonTotalInput), subtotal);
                    const totalBersih = Math.max(subtotal - diskon, 0);

                    if (angkaInput(diskonTotalInput) > subtotal) {
                        isiAngka(diskonTotalInput, subtotal);
                    }

                    subtotalDisplay.textContent = formatRupiah(subtotal);
                    diskonDisplay.textContent = formatRupiah(diskon);
                    grandTotalDisplay.textContent = formatRupiah(totalBersih);
                    netTotalDisplay.textContent = formatRupiah(totalBersih);
                }

                function tambahBaris(data = {}) {
                    const clone = template.content.cloneNode(true);
                    const html = clone.querySelector('.baris-item').outerHTML.replaceAll('__INDEX__', indexBaris);

                    tbody.insertAdjacentHTML('beforeend', html);
                    const barisBaru = tbody.querySelector('.baris-item:last-child');
                    const selectProduk = barisBaru.querySelector('.select-produk');
                    barisBaru.querySelector('.input-qty').value = data.qty ?? '';
                    barisBaru.querySelector('.input-harga').value = data.harga_satuan ?? '';
                    barisBaru.querySelector('.input-subtotal').value = data.subtotal ?? '';
                    selectProduk.value = data.pakan_id ?? '';
                    indexBaris++;
                    filterProduk();
                    initSelectSearch(barisBaru);
                    if (window.jQuery && jQuery.fn.select2) {
                        jQuery(selectProduk).val(data.pakan_id ?? null).trigger('change');
                    }
                    cekHargaRataRata(selectProduk);
                    hitungUlangSemua();
                }

                function produkCocok(kategori) {
                    const jenis = jenisTerpilih();
                    if (jenis === 'pakan') return kategori === 'pakan';
                    if (jenis === 'barang_umum') return kategori === 'barang_umum';
                    return ['obat_pakan', 'obat_air', 'obat_ayam', 'vaksin'].includes(kategori);
                }

                function filterProduk() {
                    if (!jenisTerpilih()) return;

                    tbody.querySelectorAll('.select-produk').forEach((select) => {
                        Array.from(select.options).forEach((option) => {
                            if (!option.value) return;

                            option.hidden = !produkCocok(option.dataset.kategori);
                            option.disabled = !produkCocok(option.dataset.kategori);
                        });

                        const selected = select.options[select.selectedIndex];
                        if (selected && selected.value && selected.hidden) {
                            select.value = '';
                            if (window.jQuery && jQuery.fn.select2) {
                                jQuery(select).val(null).trigger('change.select2');
                            }
                        }

                        isiSatuan(select);
                    });

                    if (window.jQuery && jQuery.fn.select2) {
                        jQuery(tbody).find('.select-produk').trigger('change.select2');
                    }
                }

                function isiSatuan(select) {
                    const baris = select.closest('.baris-item');
                    const inputSatuan = baris?.querySelector('.input-satuan');
                    const option = select.options[select.selectedIndex];

                    if (!inputSatuan) return;

                    if (jenisTerpilih() === 'pakan') {
                        inputSatuan.value = 'zak';
                        return;
                    }

                    inputSatuan.value = option?.dataset?.satuan || 'Belum ada satuan';
                }

                function updateJenisFaktur() {
                    const jenis = jenisTerpilih();

                    jenisCards.forEach((card) => {
                        card.classList.toggle('is-active', card.dataset.jenisCard === jenis);
                    });

                    invoiceEntry.classList.toggle('is-hidden', !jenis);
                    tbody.querySelectorAll('.input-sumber-produk').forEach((input) => { input.value = jenis === 'barang_umum' ? 'barang_umum' : 'perencanaan'; });

                    if (jenis && tbody.querySelectorAll('.baris-item').length === 0) {
                        initialItems.length ? initialItems.forEach((item) => tambahBaris(item)) : tambahBaris();
                    }

                    filterProduk();
                }

                tbody.addEventListener('input', function(e) {
                    const baris = e.target.closest('.baris-item');
                    if (!baris) return;

                    if (e.target.classList.contains('input-subtotal')) {
                        baris.dataset.hitungDari = 'subtotal';
                        hitungBaris(baris, 'subtotal');
                        hitungUlangSemua();
                        cekHargaRataRata(baris.querySelector('.select-produk'));
                    }

                    if (e.target.classList.contains('input-harga')) {
                        baris.dataset.hitungDari = 'harga';
                        hitungBaris(baris, 'harga');
                        hitungUlangSemua();
                        cekHargaRataRata(baris.querySelector('.select-produk'));
                    }

                    if (e.target.classList.contains('input-qty')) {
                        hitungBaris(baris, baris.dataset.hitungDari || 'harga');
                        hitungUlangSemua();
                    }
                });

                tbody.addEventListener('change', function(e) {
                    if (e.target.classList.contains('select-produk')) {
                        isiSatuan(e.target);
                        cekHargaRataRata(e.target);
                    }
                });

                tbody.addEventListener('click', function(e) {
                    const tombolHapus = e.target.closest('.btn-remove-item');
                    if (!tombolHapus) return;

                    if (tbody.querySelectorAll('.baris-item').length <= 1) {
                        alert('Minimal harus ada 1 item pembelian.');
                        return;
                    }

                    tombolHapus.closest('.baris-item').remove();
                    hitungUlangSemua();
                });

                btnTambah.addEventListener('click', tambahBaris);
                diskonTotalInput.addEventListener('input', hitungUlangSemua);
                jenisRadios.forEach((radio) => radio.addEventListener('change', updateJenisFaktur));
                const metodePembayaran = document.getElementById('metode_pembayaran');
                const akunPembayaranWrap = document.getElementById('akun-pembayaran-wrap');
                const akunPembayaran = document.getElementById('id_akun_pembayaran');
                function updateMetodePembayaran() {
                    const tunai = metodePembayaran.value === 'tunai';
                    akunPembayaranWrap.style.display = tunai ? 'block' : 'none';
                    akunPembayaran.required = tunai;
                    akunPembayaran.disabled = !tunai;
                    document.getElementById('payment-total-label').textContent = tunai ? 'TOTAL HPP / PEMBAYARAN' : 'TOTAL HPP / HUTANG';
                }
                metodePembayaran.addEventListener('change', updateMetodePembayaran);
                updateMetodePembayaran();
                initSelectSearch();
                updateJenisFaktur();

                document.getElementById('form-faktur').addEventListener('submit', function(e) {
                    if (tbody.querySelectorAll('.baris-item').length === 0) {
                        e.preventDefault();
                        alert('Tambahkan minimal 1 item pembelian.');
                    }
                });
            })();
        </script>
    @endsection
</x-theme.app>
