<x-theme.app title="{{ $title }}" table="T" sizeCard="12">
    <x-slot name="slot">
        <style>
            .opname-page{padding:24px;border-radius:16px;background:#fff}
            .opname-page h4{margin-bottom:4px;color:#18366f;font-weight:700}
            .opname-info,.opname-filter{padding:14px;border:1px solid #dfe6f3;border-radius:12px;background:#f7f9fd}
            .opname-table-wrap{overflow-x:auto;border:1px solid #dfe6f3;border-radius:12px}
            .opname-table{min-width:1000px;margin-bottom:0}
            .opname-table thead th{padding:12px;background:#304f9e;color:#fff;font-size:12px;white-space:nowrap}
            .opname-table td{padding:10px;vertical-align:middle}
            .physical-input{min-width:150px;text-align:right}
            .difference{font-weight:700}
        </style>
        <div class="opname-page">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div><h4>Stok Opname Gudang</h4><small class="text-muted">Bandingkan stok sistem dengan jumlah fisik di gudang.</small></div>
            </div>
            @include('gudang_persediaan.partials.nav')
            @if($errors->any())<div class="alert alert-danger"><strong>Opname belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>@endif
            <div class="opname-info mb-3"><strong>Cara opname:</strong> pilih produk, hitung barang fisik, isi kolom Stok Fisik, periksa selisih, lalu simpan. Produk yang tidak dipilih tidak akan berubah.</div>
            <form method="GET" action="{{ route('gudang-persediaan.opname') }}" class="opname-filter mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3"><label class="form-label">Tanggal opname</label><input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" max="{{ date('Y-m-d') }}"></div>
                    <div class="col-md-3"><label class="form-label">Kategori</label><select id="categoryFilter" class="form-select"><option value="">Semua kategori</option>@foreach($kategori as $itemKategori)<option value="{{ $itemKategori }}">{{ ucwords(str_replace('_',' ',$itemKategori)) }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Cari produk</label><input type="search" id="productSearch" class="form-control" placeholder="Nama atau kode produk"></div>
                    <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-calendar me-1"></i> Ubah Tanggal</button></div>
                </div>
            </form>
            <form method="POST" action="{{ route('gudang-persediaan.opname.store') }}" id="opnameForm">
                @csrf
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <label class="mb-0"><input type="checkbox" id="selectVisible" class="form-check-input me-1"> Pilih semua yang tampil</label>
                    <small class="text-muted"><span id="selectedCount">0</span> produk dipilih</small>
                </div>
                <div class="opname-table-wrap">
                    <table class="table table-hover opname-table">
                        <thead><tr><th class="text-center">Pilih</th><th>Produk</th><th>Kategori</th><th>Satuan</th><th class="text-end">Stok Sistem</th><th>Stok Fisik</th><th class="text-end">Selisih</th></tr></thead>
                        <tbody>
                            @foreach($stok as $item)
                                <tr class="product-row" data-search="{{ strtolower($item->nm_produk.' '.$item->kode_accurate) }}" data-category="{{ $item->kategori }}">
                                    <td class="text-center"><input type="checkbox" name="produk[]" value="{{ $item->id_produk }}" class="form-check-input product-check"></td>
                                    <td><strong>{{ $item->nm_produk }}</strong><br><small class="text-muted">{{ $item->kode_accurate ?: 'Tanpa kode Accurate' }}</small></td>
                                    <td>{{ ucwords(str_replace('_',' ',$item->kategori)) }}</td>
                                    <td>{{ $item->nm_satuan ?: '-' }}</td>
                                    <td class="text-end system-stock" data-value="{{ $item->stok }}">{{ number_format($item->stok,4,',','.') }}</td>
                                    <td><input type="number" name="stok_fisik[{{ $item->id_produk }}]" class="form-control physical-input" value="{{ old('stok_fisik.'.$item->id_produk, $item->stok) }}" min="0" step="0.0001"></td>
                                    <td class="text-end difference">0</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3"><button type="submit" class="btn btn-success" id="saveOpname" disabled onclick="return confirm('Simpan stok opname dan jadikan stok fisik sebagai saldo gudang terbaru?')"><i class="fas fa-save me-1"></i> Simpan Stok Opname</button></div>
            </form>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                const rows=[...document.querySelectorAll('.product-row')], search=document.getElementById('productSearch'), category=document.getElementById('categoryFilter'), selectVisible=document.getElementById('selectVisible');
                function filter(){const q=search.value.toLowerCase().trim(), c=category.value;rows.forEach(row=>{row.hidden=!!((q&&!row.dataset.search.includes(q))||(c&&row.dataset.category!==c));});selectVisible.checked=false;}
                function recalc(row){const system=parseFloat(row.querySelector('.system-stock').dataset.value)||0, physical=parseFloat(row.querySelector('.physical-input').value)||0, diff=physical-system, cell=row.querySelector('.difference');cell.textContent=diff.toLocaleString('id-ID',{maximumFractionDigits:4});cell.className='text-end difference '+(diff<0?'text-danger':diff>0?'text-success':'');}
                function selected(){const count=document.querySelectorAll('.product-check:checked').length;document.getElementById('selectedCount').textContent=count;document.getElementById('saveOpname').disabled=count===0;}
                search.addEventListener('input',filter);category.addEventListener('change',filter);selectVisible.addEventListener('change',function(){rows.filter(row=>!row.hidden).forEach(row=>row.querySelector('.product-check').checked=this.checked);selected();});
                document.querySelectorAll('.physical-input').forEach(input=>{recalc(input.closest('tr'));input.addEventListener('input',()=>recalc(input.closest('tr')));});document.querySelectorAll('.product-check').forEach(check=>check.addEventListener('change',selected));selected();
            });
        </script>
    </x-slot>
</x-theme.app>
