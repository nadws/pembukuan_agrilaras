<div class="row g-2 planning-row" data-category="{{ $category }}">
    <div class="col-lg-3"><label>{{ $category === 'pakan' ? 'Type' : 'Obat' }}</label>
        <select name="{{ $category }}[{{ $index }}][id_produk]" class="form-select planning-product planning-select2" @required($category === 'pakan')>
            <option value="">- Pilih {{ $category === 'pakan' ? 'Pakan' : 'Obat' }} -</option>
            @foreach($products->where('kategori', $category) as $product)<option value="{{ $product->id_produk }}" @selected((int)($row['id_produk'] ?? 0) === (int)$product->id_produk)>{{ $product->nm_produk }}</option>@endforeach
        </select>
    </div>
    @if($category === 'pakan')
        <div class="col-lg-2"><label>Stok</label><input readonly class="form-control planning-stock"></div>
        <div class="col-lg-2"><label>%</label><input required type="number" step="any" min="0.000001" max="100" name="pakan[{{ $index }}][persen]" value="{{ $row['persen'] ?? '' }}" class="form-control planning-percent"></div>
        <div class="col-lg-3"><label>Pakan (Gr)</label><input readonly value="{{ $row['gr'] ?? '' }}" class="form-control planning-grams"></div>
    @else
        <div class="col-lg-2"><label>Dosis</label><input type="number" step="any" min="0.000001" name="{{ $category }}[{{ $index }}][dosis]" value="{{ $row['dosis'] ?? '' }}" class="form-control planning-dose"></div>
        <div class="col-lg-1"><label>Satuan</label><input readonly class="form-control planning-unit"></div>
        @if($category !== 'obat_ayam')
            <div class="col-lg-2"><label>Campuran</label><input type="number" step="any" min="0.000001" name="{{ $category }}[{{ $index }}][campuran]" value="{{ $row['campuran'] ?? '' }}" class="form-control planning-mix"></div>
            <div class="col-lg-1"><label>Satuan</label><input readonly class="form-control planning-mix-unit"></div>
        @endif
        @if($category === 'obat_air')
            <div class="col-lg-2"><label>Waktu</label><input type="time" name="obat_air[{{ $index }}][waktu]" value="{{ substr($row['waktu'] ?? '',0,5) }}" class="form-control"></div>
            <div class="col-lg-3"><label>Cara Pemakaian</label><input maxlength="200" name="obat_air[{{ $index }}][cara_pemakaian]" value="{{ $row['cara_pemakaian'] ?? '' }}" class="form-control"></div>
            <div class="col-lg-3"><label>Keterangan</label><input maxlength="200" name="obat_air[{{ $index }}][ket]" value="{{ $row['ket'] ?? '' }}" class="form-control"></div>
        @endif
    @endif
    @if($mode !== 'detail')<div class="col-lg-2"><label>Aksi</label><br><button type="button" class="btn btn-outline-danger btn-sm planning-remove">Hapus baris</button></div>@endif
</div>
