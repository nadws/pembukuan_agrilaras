<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0">Master Produk Perencanaan</h5>
                <small class="text-muted">Produk pakan, obat air, obat pakan, dan obat ayam.</small>
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahProdukPerencanaan">
                <i class="fas fa-plus me-1"></i> Tambah Produk
            </button>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .master-product-table-wrap{overflow-x:auto;border:1px solid #e0e6f1;border-radius:12px}
            .master-product-table{min-width:1050px;margin-bottom:0}
            .master-product-table thead th{padding:12px;background:#304f9e;color:#fff;font-size:12px;white-space:nowrap}
            .master-product-table td{padding:11px;vertical-align:middle}
            .master-product-table tbody tr:hover{background:#f7f9fd}
            .product-category{display:inline-block;padding:5px 9px;border-radius:20px;background:#e8eefc;color:#304f9e;font-size:11px;font-weight:700}
            .product-code{font-family:monospace;color:#304f9e;font-weight:700}
            .product-purpose{max-width:310px;white-space:normal}
            .product-form .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}
            .product-form .form-control,.product-form .form-select{min-height:40px;border-color:#dce3f2;border-radius:8px}
            .master-product-filter{padding:14px;margin-bottom:14px;border:1px solid #dce3f2;border-radius:12px;background:#f6f8fc}
            .master-product-filter .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}
            .master-product-filter .form-control,.master-product-filter .form-select{min-height:40px;border-color:#dce3f2;border-radius:8px}
            .category-summary{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
            .category-summary a{display:flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid #dce3f2;border-radius:9px;color:#536078;background:#fff;font-size:12px;font-weight:700}
            .category-summary a:hover,.category-summary a.active{border-color:#304f9e;background:#304f9e;color:#fff}
            .category-count{min-width:23px;padding:2px 6px;border-radius:20px;background:rgba(48,79,158,.12);text-align:center}
            .category-summary a.active .category-count,.category-summary a:hover .category-count{background:rgba(255,255,255,.2)}
            .master-product-footer{display:flex;flex-wrap:wrap;justify-content:space-between;align-items-center;gap:12px;margin-top:14px;color:#68758d;font-size:13px}
            .master-product-empty{padding:48px 20px!important;color:#68758d;text-align:center}
        </style>

        @if ($errors->any())
            <div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>
        @endif

        <form method="GET" action="{{ route('produk-perencanaan.index') }}" class="master-product-filter">
            <div class="row g-2 align-items-end">
                <div class="col-lg-5"><label class="form-label">Cari produk</label><input type="search" name="cari" class="form-control" value="{{ $cari }}" placeholder="Nama produk, kode Accurate, atau kegunaan"></div>
                <div class="col-lg-3 col-md-5"><label class="form-label">Kategori</label><select name="kategori" class="form-select"><option value="">Semua kategori</option>@foreach($kategori as $category)<option value="{{ $category }}" @selected($kategoriTerpilih===$category)>{{ ucwords(str_replace('_',' ',$category)) }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-3"><label class="form-label">Data per halaman</label><select name="per_page" class="form-select">@foreach([10,15,25,50] as $size)<option value="{{ $size }}" @selected($perPage===$size)>{{ $size }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-4 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><i class="fas fa-search me-1"></i> Cari</button><a href="{{ route('produk-perencanaan.index') }}" class="btn btn-outline-secondary" title="Reset filter"><i class="fas fa-undo"></i></a></div>
            </div>
        </form>

        <div class="category-summary">
            <a href="{{ route('produk-perencanaan.index',array_filter(['cari'=>$cari,'per_page'=>$perPage])) }}" class="{{ $kategoriTerpilih===''?'active':'' }}">Semua <span class="category-count">{{ $kategoriRingkasan->sum() }}</span></a>
            @foreach($kategoriRingkasan as $category=>$jumlah)<a href="{{ route('produk-perencanaan.index',array_filter(['kategori'=>$category,'cari'=>$cari,'per_page'=>$perPage])) }}" class="{{ $kategoriTerpilih===$category?'active':'' }}">{{ ucwords(str_replace('_',' ',$category)) }} <span class="category-count">{{ $jumlah }}</span></a>@endforeach
        </div>

        <div class="master-product-table-wrap">
            <table class="table table-hover master-product-table" id="produkPerencanaanTable">
                <thead>
                    <tr>
                        <th style="width:55px">No</th>
                        <th>Nama Produk</th>
                        <th>Kode Accurate</th>
                        <th>Kategori</th>
                        <th>Satuan Dosis</th>
                        <th>Satuan Campuran</th>
                        <th>Kegunaan</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produk as $item)
                        <tr>
                            <td>{{ ($produk->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="fw-semibold">{{ $item->nm_produk }}</td>
                            <td><span class="product-code">{{ $item->kode_accurate ?: '-' }}</span></td>
                            <td><span class="product-category">{{ ucwords(str_replace('_', ' ', $item->kategori)) }}</span></td>
                            <td>{{ $item->nm_satuan_dosis ?: '-' }}</td>
                            <td>{{ $item->nm_satuan_campuran ?: '-' }}</td>
                            <td class="product-purpose">{{ $item->kegunaan ?: '-' }}</td>
                            <td>{{ tanggal($item->tgl) }}</td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-warning edit-product"
                                    data-bs-toggle="modal" data-bs-target="#editProdukPerencanaan"
                                    data-id="{{ $item->id_produk }}"
                                    data-name="{{ $item->nm_produk }}"
                                    data-code="{{ $item->kode_accurate }}"
                                    data-category="{{ $item->kategori }}"
                                    data-date="{{ $item->tgl }}"
                                    data-dose="{{ $item->dosis_satuan }}"
                                    data-mix="{{ $item->campuran_satuan }}"
                                    data-purpose="{{ $item->kegunaan }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('produk-perencanaan.destroy', $item->id_produk) }}" class="d-inline"
                                    onsubmit="return confirm('Hapus produk {{ addslashes($item->nm_produk) }}? Produk yang sudah dipakai transaksi tidak dapat dihapus.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty<tr><td colspan="9" class="master-product-empty"><i class="fas fa-box-open fa-2x d-block mb-2"></i>Tidak ada produk yang sesuai dengan filter.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        @if($produk->total()>0)<div class="master-product-footer"><span>Menampilkan {{ $produk->firstItem() }}–{{ $produk->lastItem() }} dari {{ number_format($produk->total(),0,'.',',') }} produk</span><div>{{ $produk->links('pagination::bootstrap-5') }}</div></div>@endif

        <form method="POST" action="{{ route('produk-perencanaan.store') }}" class="product-form">
            @csrf
            <x-theme.modal title="Tambah Produk Perencanaan" idModal="tambahProdukPerencanaan" size="modal-lg">
                @include('data_master.produk_perencanaan.partials.form', ['prefix' => 'tambah'])
            </x-theme.modal>
        </form>

        <form method="POST" action="" id="editProductForm" class="product-form">
            @csrf
            @method('PUT')
            <x-theme.modal title="Edit Produk Perencanaan" idModal="editProdukPerencanaan" size="modal-lg">
                @include('data_master.produk_perencanaan.partials.form', ['prefix' => 'edit'])
            </x-theme.modal>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.edit-product').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const form = document.getElementById('editProductForm');
                        form.action = @json(url('/data-master/produk-perencanaan')) + '/' + this.dataset.id;
                        document.getElementById('edit_nm_produk').value = this.dataset.name || '';
                        document.getElementById('edit_kode_accurate').value = this.dataset.code || '';
                        document.getElementById('edit_kategori').value = this.dataset.category || '';
                        document.getElementById('edit_tgl').value = this.dataset.date || '';
                        document.getElementById('edit_dosis_satuan').value = this.dataset.dose || '';
                        document.getElementById('edit_campuran_satuan').value = this.dataset.mix || '';
                        document.getElementById('edit_kegunaan').value = this.dataset.purpose || '';
                    });
                });
            });
        </script>
    </x-slot>
</x-theme.app>
