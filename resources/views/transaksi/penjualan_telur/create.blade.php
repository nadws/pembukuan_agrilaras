<x-theme.app title="Buat Penjualan Telur" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h5 class="mb-1">Buat Penjualan Telur</h5><small class="text-muted">Input transaksi penjualan telur</small></div>
            <a href="{{ route('transaksi.penjualan-telur.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .egg-create { --egg-primary: #29468f; --egg-border: #dce3f2; }
            .egg-create .form-label { margin-bottom: 5px; color: #536078; font-size: 12px; font-weight: 700; }
            .egg-create .form-control, .egg-create .form-select { min-height: 40px; border-color: var(--egg-border); border-radius: 8px; }
            .egg-create .select2-container { width: 100% !important; }
            .egg-create .select2-container--default .select2-selection--single { min-height: 40px; border-color: var(--egg-border); border-radius: 8px; }
            .egg-create .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
            .egg-create .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
            .egg-create .header-box { padding: 16px; margin-bottom: 20px; border: 1px solid var(--egg-border); border-radius: 12px; background: #f5f7fc; }
            .egg-create .table-wrap { overflow-x: auto; border: 1px solid var(--egg-border); border-radius: 12px; }
            .egg-create table { min-width: 900px; margin-bottom: 0; }
            .egg-create thead th { padding: 12px; color: #fff; background: var(--egg-primary); font-size: 12px; white-space: nowrap; }
            .egg-create td { padding: 8px; }
            .egg-create .total-box { padding: 14px 18px; border: 1px solid var(--egg-border); border-radius: 12px; background: #f5f7fc; text-align: right; }
            .egg-create .total-box .value { color: var(--egg-primary); font-size: 20px; font-weight: 700; }
        </style>
        <div class="egg-create">
        @if ($errors->any())
            <div class="alert alert-danger">Periksa kembali data yang diisi.</div>
        @endif
        <form method="POST" action="{{ route('transaksi.penjualan-telur.store') }}" id="form-penjualan">
            @csrf
            <div class="row g-3 mb-4 header-box">
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tgl" class="form-control" value="{{ old('tgl', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">No Nota</label>
                    <input type="text" class="form-control" value="TP{{ $nota }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Customer</label>
                    <select name="id_customer" class="form-select select2" required>
                        <option value="">Pilih customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id_customer }}" @selected(old('id_customer') == $customer->id_customer)>{{ $customer->nm_customer }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Masuk ke akun</label>
                    <select name="id_akun_pembayaran" class="form-select select2" required>
                        <option value="">Pilih akun pembayaran</option>
                        @foreach ($akunPembayaran as $akun)
                            <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_pembayaran') == $akun->id_akun_perkiraan)>
                                {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Pilih piutang, kas, BCA, atau bank penerima pembayaran.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipe Jualan</label>
                    <select name="tipe_penjualan" class="form-select" required>
                        <option value="kg" @selected(old('tipe_penjualan', 'kg') === 'kg')>Kg</option>
                        <option value="pcs" @selected(old('tipe_penjualan') === 'pcs')>Pcs</option>
                    </select>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-hover align-middle" id="items-table">
                    <thead>
                        <tr>
                            <th style="min-width:220px">Produk Telur</th>
                            <th style="width:120px">Pcs</th>
                            <th style="width:140px">Kg Kotor</th>
                            <th style="width:150px">Kg Bersih</th>
                            <th style="width:160px">Harga Satuan</th>
                            <th style="width:170px">Subtotal</th>
                            <th style="width:55px"></th>
                        </tr>
                    </thead>
                    <tbody id="item-rows"></tbody>
                </table>
            </div>
            <button type="button" class="btn btn-outline-primary mb-3" id="add-row"><i class="fas fa-plus me-1"></i> Tambah Item</button>
            <div class="total-box mt-3"><div class="fw-semibold">Total</div><div class="value" id="grand-total">Rp 0</div></div>
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Penjualan</button>
            </div>
        </form>
        </div>
    </x-slot>
    @section('scripts')
        <script>
            $(function () {
                $('.select2').select2({ width: '100%' });
                const products = @json($produk);
                let rowNumber = 0;
                const money = value => 'Rp ' + Math.round(value || 0).toLocaleString('id-ID');
                const number = value => parseFloat(String(value || 0).replace(',', '.')) || 0;

                function productOptions() {
                    return '<option value="">Pilih produk telur</option>' + products.map(p => `<option value="${p.id_produk_telur}">${p.nm_telur}</option>`).join('');
                }

                function recalculate(row) {
                    const pcs = number(row.find('.pcs').val());
                    const kgKotor = number(row.find('.kg-kotor').val());
                    const kgBersih = row.find('.kg-bersih');
                    const tipe = $('select[name="tipe_penjualan"]').val();
                    if (!kgBersih.is(':focus')) kgBersih.val((kgKotor - (pcs / 180)).toFixed(2));
                    const subtotal = (tipe === 'pcs' ? pcs : number(kgBersih.val())) * number(row.find('.harga').val());
                    row.find('.subtotal').val(subtotal.toFixed(2));
                    row.find('.subtotal-label').text(money(subtotal));
                    let total = 0;
                    $('.subtotal').each(function () { total += number($(this).val()); });
                    $('#grand-total').text(money(total));
                }

                function addRow() {
                    rowNumber++;
                    $('#item-rows').append(`<tr class="item-row">
                        <td><select name="id_produk[]" class="form-select product-select" required>${productOptions()}</select></td>
                        <td><input type="number" name="pcs[]" class="form-control pcs" min="0" step="1" value="0" required></td>
                        <td><input type="number" name="kg[]" class="form-control kg-kotor" min="0" step="0.01" value="0" required></td>
                        <td><input type="number" name="kg_jual[]" class="form-control kg-bersih" min="0" step="0.01" value="0" required></td>
                        <td><input type="number" name="rp_satuan[]" class="form-control harga" min="0" step="0.01" value="0" required></td>
                        <td><input type="hidden" name="total_rp[]" class="subtotal" value="0"><span class="subtotal-label">Rp 0</span></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Hapus item"><i class="fas fa-trash"></i></button></td>
                    </tr>`);
                    $('#item-rows tr:last .product-select').select2({ width: '100%' });
                }

                addRow();
                $('#add-row').on('click', addRow);
                $('select[name="tipe_penjualan"]').on('change', function () { $('.item-row').each(function () { recalculate($(this)); }); });
                $('#item-rows').on('input', '.pcs, .kg-kotor, .kg-bersih, .harga', function () { recalculate($(this).closest('tr')); });
                $('#item-rows').on('click', '.remove-row', function () {
                    if ($('.item-row').length > 1) $(this).closest('tr').remove();
                    $('.item-row').each(function () { recalculate($(this)); });
                });
            });
        </script>
    @endsection
</x-theme.app>
