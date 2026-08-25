<div class="row g-3">
    <div class="col-md-3"><label class="form-label">Kode Barang</label><input type="number" name="kd_produk" id="{{ $prefix }}_kd_produk" class="form-control" min="1" value="{{ $kode }}" required></div>
    <div class="col-md-9"><label class="form-label">Nama Barang</label><input type="text" name="nm_produk" id="{{ $prefix }}_nm_produk" class="form-control" maxlength="100" placeholder="Contoh: Tali rafia" required></div>
    <div class="col-md-4"><label class="form-label">Satuan</label><select name="satuan_id" id="{{ $prefix }}_satuan_id" class="form-select" required><option value="">Pilih satuan</option>@foreach($satuan as $unit)<option value="{{ $unit->id_satuan }}">{{ $unit->nm_satuan }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Gudang</label><select name="gudang_id" id="{{ $prefix }}_gudang_id" class="form-select" required><option value="">Pilih gudang</option>@foreach($gudang as $warehouse)<option value="{{ $warehouse->id_gudang }}">{{ $warehouse->nm_gudang }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Kontrol Stok</label><select name="kontrol_stok" id="{{ $prefix }}_kontrol_stok" class="form-select" required><option value="Y">Ya, kontrol stok</option><option value="T">Tidak</option></select></div>
</div>
