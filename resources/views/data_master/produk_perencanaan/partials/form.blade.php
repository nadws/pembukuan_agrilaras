<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Nama Produk</label>
        <input type="text" name="nm_produk" id="{{ $prefix }}_nm_produk" class="form-control" maxlength="200" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Kode Accurate</label>
        <input type="text" name="kode_accurate" id="{{ $prefix }}_kode_accurate" class="form-control" maxlength="50" placeholder="Opsional">
    </div>
    <div class="col-md-4">
        <label class="form-label">Kategori</label>
        <select name="kategori" id="{{ $prefix }}_kategori" class="form-select" required>
            <option value="">Pilih kategori</option>
            @foreach ($kategori as $namaKategori)
                <option value="{{ $namaKategori }}">{{ ucwords(str_replace('_', ' ', $namaKategori)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Satuan Dosis</label>
        <select name="dosis_satuan" id="{{ $prefix }}_dosis_satuan" class="form-select">
            <option value="">Tidak ada</option>
            @foreach ($satuan as $itemSatuan)
                <option value="{{ $itemSatuan->id_satuan }}">{{ $itemSatuan->nm_satuan }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Satuan Campuran</label>
        <select name="campuran_satuan" id="{{ $prefix }}_campuran_satuan" class="form-select">
            <option value="">Tidak ada</option>
            @foreach ($satuan as $itemSatuan)
                <option value="{{ $itemSatuan->id_satuan }}">{{ $itemSatuan->nm_satuan }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Tanggal Dibuat</label>
        <input type="date" name="tgl" id="{{ $prefix }}_tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
    </div>
    <div class="col-md-8">
        <label class="form-label">Kegunaan</label>
        <textarea name="kegunaan" id="{{ $prefix }}_kegunaan" class="form-control" rows="3" placeholder="Jelaskan kegunaan produk (opsional)"></textarea>
    </div>
</div>
