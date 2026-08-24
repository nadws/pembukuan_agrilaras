<x-theme.app title="Edit Penjualan Umum" table="Y" sizeCard="12">
    <x-slot name="cardHeader"><div class="d-flex justify-content-between align-items-center"><div><h5 class="mb-1">Edit Penjualan Umum PU-{{ $nota->urutan }}</h5><small class="text-muted">Perbarui nota penjualan</small></div><a href="{{ route('transaksi.penjualan-umum.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a></div></x-slot>
    <x-slot name="cardBody"><style>.pu-form .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}.pu-form .form-control,.pu-form .form-select{min-height:40px;border-color:#dce3f2;border-radius:8px}.pu-box,.pu-total{padding:16px;border:1px solid #dce3f2;border-radius:12px;background:#f5f7fc}.pu-total{text-align:right}.pu-total .value{color:#29468f;font-size:20px;font-weight:700}.pu-table-wrap{overflow-x:auto;border:1px solid #dce3f2;border-radius:12px}.pu-table{min-width:820px;margin-bottom:0}.pu-table thead th{padding:12px;background:#29468f;color:#fff;font-size:12px;white-space:nowrap}.pu-table .select2-container{width:100%!important}.pu-table .select2-selection--single{height:40px!important;border-color:#dce3f2!important;border-radius:8px!important}.pu-table .select2-selection__rendered{line-height:40px!important}.pu-table .select2-selection__arrow{height:40px!important}</style><div class="pu-form"><form method="POST" action="{{ route('transaksi.penjualan-umum.update', $nota->urutan) }}">@csrf @method('PUT')<div class="pu-box row g-3 mb-3"><div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" name="tgl" class="form-control" value="{{ $nota->tgl }}" required></div><div class="col-md-3"><label class="form-label">No Nota</label><input class="form-control" value="PU-{{ $nota->urutan }}" readonly></div><div class="col-md-6"><label class="form-label">Customer</label><select name="id_customer" class="form-select select2" required><option value="">Pilih customer</option>@foreach($customers as $customer)<option value="{{ $customer->id_customer }}" @selected($nota->id_customer == $customer->id_customer)>{{ $customer->nm_customer }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label">Masuk ke akun</label><select name="id_akun_pembayaran" class="form-select select2" required><option value="">Pilih akun pembayaran</option>@foreach($akunPembayaran as $akun)<option value="{{ $akun->id_akun_perkiraan }}" @selected($idAkunPembayaran == $akun->id_akun_perkiraan)>{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>@endforeach</select></div></div><div class="pu-table-wrap"><table class="table pu-table"><thead><tr><th style="width:46%">Produk</th><th style="width:18%">Qty</th><th style="width:22%">Harga Satuan</th><th style="width:14%">Subtotal</th><th></th></tr></thead><tbody id="itemRows">@foreach($items as $item)<tr class="item-row"><td><select name="id_produk[]" class="form-select select2" required><option value="">Pilih produk</option>@foreach($produk as $p)<option value="{{ $p->id_produk }}" @selected($item->id_produk == $p->id_produk)>{{ $p->nm_produk }}{{ $p->nm_satuan ? ' ('.$p->nm_satuan.')' : '' }}</option>@endforeach</select></td><td><input type="number" name="qty[]" class="form-control qty" min="0" step="0.01" value="{{ $item->qty }}" required></td><td><input type="number" name="rp_satuan[]" class="form-control price" min="0" step="0.01" value="{{ $item->rp_satuan }}" required></td><td class="text-end subtotal">Rp 0</td><td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td></tr>@endforeach</tbody></table></div><div class="mt-3"><button type="button" id="addRow" class="btn btn-outline-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Item</button></div><div class="pu-total mt-3"><div class="fw-semibold">Total Penjualan</div><div class="value" id="grandTotal">Rp 0</div></div><div class="text-end mt-3"><button class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button></div></form></div><script>$(function(){const options=`@foreach($produk as $p)<option value="{{ $p->id_produk }}">{{ $p->nm_produk }}{{ $p->nm_satuan ? ' ('.$p->nm_satuan.')' : '' }}</option>@endforeach`;function hitung(){let total=0;$('.item-row').each(function(){let sub=(parseFloat($(this).find('.qty').val())||0)*(parseFloat($(this).find('.price').val())||0);total+=sub;$(this).find('.subtotal').text('Rp '+Math.round(sub).toLocaleString('id-ID'));});$('#grandTotal').text('Rp '+Math.round(total).toLocaleString('id-ID'));}$('#addRow').on('click',function(){$('#itemRows').append(`<tr class="item-row"><td><select name="id_produk[]" class="form-select select2" required><option value="">Pilih produk</option>${options}</select></td><td><input type="number" name="qty[]" class="form-control qty" min="0" step="0.01" value="1" required></td><td><input type="number" name="rp_satuan[]" class="form-control price" min="0" step="0.01" value="0" required></td><td class="text-end subtotal">Rp 0</td><td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td></tr>`);$('.item-row:last .select2').select2({width:'100%'});});$(document).on('click','.remove-row',function(){if($('.item-row').length>1){$(this).closest('tr').remove();hitung();}});$(document).on('input change','.qty,.price',hitung);$('.select2').select2({width:'100%'});hitung();});</script></x-slot>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.select2) {
        $('.pu-form .select2').each(function () {
            const item = $(this);
            if (item.hasClass('select2-hidden-accessible')) item.select2('destroy');
            item.select2({ width: '100%', dropdownParent: $('.pu-form') });
        });
    }
});
</script>
</x-theme.app>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.getElementById('itemRows');
    const add = document.getElementById('addRow');
    if (!rows || !add) return;
    const productOptions = rows.querySelector('.item-row select').innerHTML;
    const money = value => 'Rp ' + Math.round(value).toLocaleString('id-ID');
    function calculate() {
        let total = 0;
        rows.querySelectorAll('.item-row').forEach(row => {
            const subtotal = (parseFloat(row.querySelector('.qty').value) || 0) * (parseFloat(row.querySelector('.price').value) || 0);
            total += subtotal;
            row.querySelector('.subtotal').textContent = money(subtotal);
        });
        document.getElementById('grandTotal').textContent = money(total);
    }
    add.addEventListener('click', function () {
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = '<td><select name="id_produk[]" class="form-select select2" required><option value="">Pilih produk</option>' + productOptions + '</select></td><td><input type="number" name="qty[]" class="form-control qty" min="0" step="0.01" value="1" required></td><td><input type="number" name="rp_satuan[]" class="form-control price" min="0" step="0.01" value="0" required></td><td class="text-end subtotal">Rp 0</td><td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>';
        rows.appendChild(row);
        if (window.jQuery && $.fn.select2) $(row).find('.select2').select2({ width: '100%', dropdownParent: $('.pu-form') });
        calculate();
    });
    rows.addEventListener('input', calculate);
    rows.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-row');
        if (button && rows.querySelectorAll('.item-row').length > 1) { button.closest('.item-row').remove(); calculate(); }
    });
    calculate();
});
</script>
