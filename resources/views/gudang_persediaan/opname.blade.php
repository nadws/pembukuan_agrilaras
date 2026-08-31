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
                    <div class="col-md-3"><label class="form-label">Cari produk</label><input type="search" id="productSearch" class="form-control" placeholder="Nama atau kode produk"></div>
                    <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-calendar me-1"></i> Ubah Tanggal</button></div>
                    <div class="col-md-1"><button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addItemModal"><i class="fas fa-plus me-1"></i> Add Item</button></div>
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
                                @php($stokSistem = round((float) $item->stok, 4))
                                <tr class="product-row" data-search="{{ strtolower($item->nm_produk.' '.$item->kode_accurate) }}" data-category="{{ $item->kategori }}">
                                    <td class="text-center"><input type="checkbox" name="produk[]" value="{{ $item->id_produk }}" class="form-check-input product-check"></td>
                                    <td><strong>{{ $item->nm_produk }}</strong><br><small class="text-muted">{{ $item->kode_accurate ?: 'Tanpa kode Accurate' }}</small></td>
                                    <td>{{ ucwords(str_replace('_',' ',$item->kategori)) }}</td>
                                    <td>{{ $item->nm_satuan ?: '-' }}</td>
                                    <td class="text-end system-stock" data-value="{{ $stokSistem }}">{{ number_format($stokSistem,4,',','.') }}</td>
                                    <td><input type="number" name="stok_fisik[{{ $item->id_produk }}]" class="form-control physical-input" value="{{ old('stok_fisik.'.$item->id_produk, max(0, $stokSistem)) }}" min="0" step="0.0001"></td>
                                    <td class="text-end difference">0</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3"><button type="submit" class="btn btn-success" id="saveOpname" disabled onclick="return confirm('Simpan stok opname dan jadikan stok fisik sebagai saldo gudang terbaru?')"><i class="fas fa-save me-1"></i> Simpan Stok Opname</button></div>
            </form>

            <!-- Modal Add Item -->
            <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addItemModalLabel">Tambah Produk Stok Kosong</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <input type="search" id="searchItemModal" class="form-control" placeholder="Cari nama atau kode produk...">
                            </div>
                            <div style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover table-sm">
                                    <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                        <tr>
                                            <th>Aksi</th>
                                            <th>Produk</th>
                                            <th>Kategori</th>
                                            <th>Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="emptyStockList">
                                        @forelse($stokKosong as $item)
                                        <tr class="empty-stock-row" data-search="{{ strtolower($item->nm_produk.' '.$item->kode_accurate) }}" data-id="{{ $item->id_produk }}" data-nama="{{ $item->nm_produk }}" data-kategori="{{ $item->kategori }}" data-satuan="{{ $item->nm_satuan ?: '-' }}">
                                            <td><button type="button" class="btn btn-sm btn-primary add-item-btn"><i class="fas fa-plus"></i> Tambah</button></td>
                                            <td><strong>{{ $item->nm_produk }}</strong><br><small class="text-muted">{{ $item->kode_accurate ?: 'Tanpa kode' }}</small></td>
                                            <td>{{ ucwords(str_replace('_',' ',$item->kategori)) }}</td>
                                            <td>{{ $item->nm_satuan ?: '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center text-muted">Semua produk sudah memiliki stok</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                const rows=[...document.querySelectorAll('.product-row')], search=document.getElementById('productSearch'), category=document.getElementById('categoryFilter'), selectVisible=document.getElementById('selectVisible');
                function filter(){const q=search.value.toLowerCase().trim(), c=category.value;rows.forEach(row=>{row.hidden=!!((q&&!row.dataset.search.includes(q))||(c&&row.dataset.category!==c));});selectVisible.checked=false;}
                function recalc(row){const system=parseFloat(row.querySelector('.system-stock').dataset.value)||0, physical=parseFloat(row.querySelector('.physical-input').value)||0, diff=physical-system, cell=row.querySelector('.difference');cell.textContent=diff.toLocaleString('id-ID',{maximumFractionDigits:4});cell.className='text-end difference '+(diff<0?'text-danger':diff>0?'text-success':'');}
                function selected(){const count=document.querySelectorAll('.product-check:checked').length;document.getElementById('selectedCount').textContent=count;document.getElementById('saveOpname').disabled=count===0;}
                search.addEventListener('input',filter);category.addEventListener('change',filter);selectVisible.addEventListener('change',function(){rows.filter(row=>!row.hidden).forEach(row=>row.querySelector('.product-check').checked=this.checked);selected();});
                document.querySelectorAll('.physical-input').forEach(input=>{recalc(input.closest('tr'));input.addEventListener('input',()=>{const row=input.closest('tr');row.querySelector('.product-check').checked=true;recalc(row);selected();});});document.querySelectorAll('.product-check').forEach(check=>check.addEventListener('change',selected));selected();
                
                // Modal search
                document.getElementById('searchItemModal').addEventListener('input', function(){
                    const q = this.value.toLowerCase().trim();
                    document.querySelectorAll('.empty-stock-row').forEach(row => {
                        row.hidden = q && !row.dataset.search.includes(q);
                    });
                });

                // Add item to table
                document.querySelectorAll('.add-item-btn').forEach(btn => {
                    btn.addEventListener('click', function(){
                        const row = this.closest('.empty-stock-row');
                        const id = row.dataset.id;
                        const nama = row.dataset.nama;
                        const kategori = row.dataset.kategori;
                        const satuan = row.dataset.satuan;
                        const kode = row.querySelector('small').textContent;
                        
                        // Check if already exists
                        if(document.querySelector(`input[name="produk[]"][value="${id}"]`)) {
                            alert('Produk sudah ada di tabel opname');
                            return;
                        }
                        
                        // Add to main table
                        const tbody = document.querySelector('.opname-table tbody');
                        const newRow = document.createElement('tr');
                        newRow.className = 'product-row';
                        newRow.dataset.search = row.dataset.search;
                        newRow.dataset.category = kategori;
                        newRow.innerHTML = `
                            <td class="text-center"><input type="checkbox" name="produk[]" value="${id}" class="form-check-input product-check" checked></td>
                            <td><strong>${nama}</strong><br><small class="text-muted">${kode}</small></td>
                            <td>${kategori.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')}</td>
                            <td>${satuan}</td>
                            <td class="text-end system-stock" data-value="0">0,0000</td>
                            <td><input type="number" name="stok_fisik[${id}]" class="form-control physical-input" value="0" min="0" step="0.0001"></td>
                            <td class="text-end difference">0</td>
                        `;
                        tbody.appendChild(newRow);
                        
                        // Setup events for new row
                        const input = newRow.querySelector('.physical-input');
                        const check = newRow.querySelector('.product-check');
                        input.addEventListener('input', ()=>{check.checked=true;recalc(newRow);selected();});
                        check.addEventListener('change', selected);
                        recalc(newRow);
                        selected();
                        
                        // Remove from modal
                        row.remove();
                        
                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
                        
                        // Show success message
                        alert('Produk berhasil ditambahkan ke tabel opname');
                    });
                });
            });
        </script>
    </x-slot>
</x-theme.app>
