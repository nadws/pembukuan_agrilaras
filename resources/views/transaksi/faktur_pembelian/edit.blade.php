<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">{{ $faktur->no_faktur }}</small>
            </div>
            <a href="{{ route('transaksi.faktur-pembelian.detail', $faktur) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Detail
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

            .header-section,
            .jenis-section {
                padding: 16px;
                margin-bottom: 20px;
                border: 1px solid var(--invoice-border);
                border-radius: 12px;
                background: var(--invoice-soft);
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
                background: #fff;
                cursor: pointer;
            }

            .jenis-card.is-active {
                border-color: var(--invoice-primary);
                box-shadow: 0 0 0 3px rgba(41, 70, 143, 0.12);
                background: #eef3ff;
            }

            .jenis-title {
                display: block;
                color: #263b78;
                font-size: 15px;
                font-weight: 800;
            }

            .jenis-akun {
                color: #64728b;
                font-size: 12px;
                font-weight: 600;
            }

            .item-table-wrap {
                overflow-x: auto;
                border: 1px solid var(--invoice-border);
                border-radius: 12px;
                margin-bottom: 16px;
            }

            .item-table {
                min-width: 1220px;
                margin-bottom: 0;
            }

            .biaya-table {
                min-width: 0;
                width: 100%;
            }

            .biaya-section {
                max-width: 780px;
            }

            .item-table thead th {
                color: #fff;
                background: var(--invoice-primary);
                font-size: 12px;
                white-space: nowrap;
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
                color: #536078;
                font-size: 12px;
                font-weight: 700;
            }

            .grand-total-box .value {
                color: var(--invoice-primary);
                font-size: 20px;
                font-weight: 700;
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

        </style>

        @php
            $jenisFakturTerpilih = old('jenis_faktur', $faktur->jenis_faktur);
            $itemsLama = collect(old('item'))->values();
            if ($itemsLama->isEmpty()) {
                $diskonLama = (float) ($faktur->diskon_total ?? 0);
                $totalBersihLama = (float) $faktur->detail->sum('subtotal');
                $sisaDiskonLama = $diskonLama;
                $jumlahItemLama = $faktur->detail->count();

                $itemsLama = $faktur->detail->values()->map(
                    function ($detail, $index) use ($diskonLama, $totalBersihLama, &$sisaDiskonLama, $jumlahItemLama, $akunPembayaranDefaultId) {
                        $diskonItem = $index === $jumlahItemLama - 1
                            ? $sisaDiskonLama
                            : ($totalBersihLama > 0 ? round((float) $detail->subtotal / $totalBersihLama * $diskonLama, 2) : 0);
                        $sisaDiskonLama = round($sisaDiskonLama - $diskonItem, 2);
                        $subtotalSebelumDiskon = round((float) $detail->subtotal + $diskonItem, 2);

                        return [
                        'pakan_id' => $detail->pakan_id,
                        'qty' => $detail->qty,
                        'satuan' => $detail->satuan,
                        'harga_satuan' => $detail->qty > 0 ? round($subtotalSebelumDiskon / (float) $detail->qty, 6) : 0,
                        'subtotal' => $subtotalSebelumDiskon,
                        'id_akun_pembayaran' => $detail->id_akun_pembayaran ?? $akunPembayaranDefaultId,
                        ];
                    },
                );
            }
        @endphp

        <form method="POST" action="{{ route('transaksi.faktur-pembelian.update', $faktur) }}" class="invoice-form"
            id="form-faktur">
            @csrf
            @method('PUT')

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

            <input type="hidden" name="jenis_faktur" value="{{ $jenisFakturTerpilih }}">

            <div class="header-section">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="no_faktur">Nomor Faktur</label>
                        <input type="text" id="no_faktur" name="no_faktur" class="form-control"
                            value="{{ old('no_faktur', $faktur->no_faktur) }}" required readonly>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="tanggal_faktur">Tanggal Faktur</label>
                        <input type="date" id="tanggal_faktur" name="tanggal_faktur" class="form-control"
                            value="{{ old('tanggal_faktur', $faktur->tanggal_faktur) }}" required>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="supplier_id">Suplier</label>
                        <select id="supplier_id" name="supplier_id" class="form-select select-search-supplier"
                            data-placeholder="Cari suplier" required>
                            <option value="">-- Pilih Suplier --</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id_suplier }}" @selected(old('supplier_id', $faktur->supplier_id) == $s->id_suplier)>
                                    {{ $s->nm_suplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="keterangan">Keterangan</label>
                        <input type="text" id="keterangan" name="keterangan" class="form-control"
                            value="{{ old('keterangan', $faktur->keterangan) }}">
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <h6 class="mb-0">Daftar Item Pembelian</h6>
                    <small class="text-muted">Isi produk dan nilainya. Item otomatis masuk ke akun hutang.</small>
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-item">
                    <i class="fas fa-plus me-1"></i> Tambah Item
                </button>
            </div>

            <div class="item-table-wrap">
                <table class="table item-table">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th width="260">Produk</th>
                            <th width="120">Qty</th>
                            <th width="120">Satuan</th>
                            <th width="150">Harga Satuan</th>
                            <th>Subtotal</th>
                            <th width="60"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-item"></tbody>
                </table>
            </div>

             @php($biayaLama = is_array($faktur->biaya_lain) ? $faktur->biaya_lain : (json_decode($faktur->biaya_lain ?? '[]', true) ?: []))
             <div class="row justify-content-end mb-3">
                 <div class="col-lg-6 biaya-section mb-3">
                     <div class="d-flex align-items-center justify-content-between mb-2">
                         <div>
                             <h6 class="mb-0">Biaya Tambahan (Ekspedisi, Admin, dll.)</h6>
                             <small class="text-muted">Opsional. Biaya ini akan ditambahkan ke total HPP dan dialokasikan ke akun pembayaran masing-masing.</small>
                         </div>
                     </div>
                     <div class="item-table-wrap">
                         <table class="table biaya-table mb-0">
                             <thead>
                                 <tr>
                                     <th width="250">Jenis Biaya</th>
                                     <th>Nominal Biaya (Rp)</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @foreach(['ongkir' => 'Biaya Ekspedisi / Ongkir', 'admin' => 'Biaya Admin'] as $kode => $label)
                                     @php($biayaItem = collect($biayaLama)->firstWhere('kode', $kode))
                                     <tr>
                                         <td class="fw-semibold align-middle">{{ $label }}</td>
                                         <td>
                                             <input type="number" min="0" step="0.01" name="biaya_lain[{{ $kode }}][nominal]" class="form-control input-biaya-lain" value="{{ old('biaya_lain.'.$kode.'.nominal', $biayaItem['nominal'] ?? 0) }}" placeholder="0">
                                         </td>
                                     </tr>
                                 @endforeach
                                <tr>
                                    <td class="fw-semibold align-middle">Potongan PPh 23 (Manual)</td>
                                    <td>
                                        <input type="number" min="0" step="0.01" name="pph23_manual" class="form-control input-pph23-manual" value="{{ old('pph23_manual', collect($biayaLama)->sum('pph23_nominal')) }}" placeholder="0">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6 col-md-8">
                    <div class="grand-total-box">
                        <div class="row g-2 align-items-end text-start mb-2">
                            <div class="col-md-7">
                                <label class="form-label" for="diskon_total">Diskon</label>
                                <input type="number" step="0.01" min="0" id="diskon_total"
                                    name="diskon_total" class="form-control text-end"
                                    value="{{ old('diskon_total', $faktur->diskon_total ?? 0) }}">
                            </div>
                            <div class="col-md-5 text-md-end">
                                <div class="label" id="payment-total-label">TOTAL HPP</div>
                                <div class="value" id="grand-total-display">Rp 0</div>
                            </div>
                        </div>
                        <div class="summary-row">
                            <span>Total Item Produk</span>
                            <span id="subtotal-display">Rp 0</span>
                        </div>
                        <div class="summary-row">
                            <span>Diskon</span>
                            <span id="diskon-display">Rp 0</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Biaya Tambahan</span>
                            <span id="biaya-lain-display">Rp 0</span>
                        </div>
                        <div class="summary-row"><span>Potongan PPh 23 (kredit hutang pajak)</span><span id="pph23-display">Rp 0</span></div>
                        <div class="summary-row net">
                            <span>Total Hutang Supplier</span>
                            <span id="net-total-display">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('transaksi.faktur-pembelian.detail', $faktur) }}" class="btn btn-outline-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>

        <template id="template-baris-item">
            <tr class="baris-item">
                <td class="text-center nomor-baris">1</td>
                <td>
                    <select name="item[__INDEX__][pakan_id]" class="form-select select-produk select-search-produk"
                        data-placeholder="Cari produk" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($produk as $p)
                            <option value="{{ $p->id_produk }}" data-kategori="{{ $p->kategori }}"
                                data-satuan="{{ $p->satuan_dosis }}">
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
                        readonly>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="item[__INDEX__][harga_satuan]"
                        class="form-control input-harga" placeholder="Dari subtotal" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="item[__INDEX__][subtotal]"
                        class="form-control input-subtotal" placeholder="Dari harga" required>
                </td>
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
                const initialItems = @json($itemsLama->values());
                let indexBaris = 0;

                function jenisTerpilih() {
                    return document.querySelector('input[name="jenis_faktur"]')?.value || '';
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
                                if (data.element && data.element.disabled) return null;
                                if (!params.term || data.text.toLowerCase().includes(params.term.toLowerCase())) return data;
                                return null;
                            }
                        });

                        select.off('change.fakturPembelian').on('change.fakturPembelian', function() {
                            if (this.classList.contains('select-produk')) {
                                isiSatuan(this);
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

                function hitungUlangSemua() {
                     let subtotal = 0;

                     tbody.querySelectorAll('.baris-item').forEach((baris, i) => {
                         baris.querySelector('.nomor-baris').textContent = i + 1;
                         subtotal += angkaInput(baris.querySelector('.input-subtotal'));
                     });

                     let totalBiayaLain = 0;
                     document.querySelectorAll('.input-biaya-lain').forEach((input) => {
                         totalBiayaLain += angkaInput(input);
                     });

                     const diskon = Math.min(angkaInput(diskonTotalInput), subtotal);
                     const totalBersih = Math.max(subtotal - diskon + totalBiayaLain, 0);
                     const pph23ManualInput = document.querySelector('.input-pph23-manual');
                     const pph23 = pph23ManualInput ? angkaInput(pph23ManualInput) : 0;
                     const totalHutangSupplier = Math.max(totalBersih - pph23, 0);

                     if (angkaInput(diskonTotalInput) > subtotal) {
                         isiAngka(diskonTotalInput, subtotal);
                     }

                     subtotalDisplay.textContent = formatRupiah(subtotal);
                     diskonDisplay.textContent = formatRupiah(diskon);
                     const biayaLainDisplay = document.getElementById('biaya-lain-display');
                     if (biayaLainDisplay) {
                         biayaLainDisplay.textContent = formatRupiah(totalBiayaLain);
                     }
                     grandTotalDisplay.textContent = formatRupiah(totalBersih);
                     document.getElementById('pph23-display').textContent = formatRupiah(pph23);
                     netTotalDisplay.textContent = formatRupiah(totalHutangSupplier);
                 }

                function produkCocok(kategori) {
                    const jenis = jenisTerpilih();
                    if (jenis === 'pakan') return kategori === 'pakan';
                    return ['obat_pakan', 'obat_air', 'obat_ayam', 'vaksin'].includes(kategori);
                }

                function filterProduk() {
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
                    inputSatuan.value = jenisTerpilih() === 'pakan' ? 'zak' : (option?.dataset?.satuan || 'Belum ada satuan');
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
                        jQuery(selectProduk).val(data.pakan_id ?? null).trigger('change.select2');
                    }
                    hitungUlangSemua();
                }

                function updateJenisFaktur() {
                    filterProduk();
                }

                tbody.addEventListener('input', function(e) {
                    const baris = e.target.closest('.baris-item');
                    if (!baris) return;

                    if (e.target.classList.contains('input-subtotal')) {
                        baris.dataset.hitungDari = 'subtotal';
                        hitungBaris(baris, 'subtotal');
                        hitungUlangSemua();
                    }

                    if (e.target.classList.contains('input-harga')) {
                        baris.dataset.hitungDari = 'harga';
                        hitungBaris(baris, 'harga');
                        hitungUlangSemua();
                    }

                    if (e.target.classList.contains('input-qty')) {
                        hitungBaris(baris, baris.dataset.hitungDari || 'harga');
                        hitungUlangSemua();
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

                btnTambah.addEventListener('click', () => tambahBaris());
                diskonTotalInput.addEventListener('input', hitungUlangSemua);
                document.querySelectorAll('.input-biaya-lain').forEach((input) => {
                    input.addEventListener('input', hitungUlangSemua);
                });
                document.querySelector('.input-pph23-manual')?.addEventListener('input', hitungUlangSemua);

                initSelectSearch();
                initialItems.length ? initialItems.forEach((item) => tambahBaris(item)) : tambahBaris();
                updateJenisFaktur();
            })();
        </script>
    @endsection
</x-theme.app>
