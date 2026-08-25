<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
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
        </style>

        @if ($errors->any())
            <div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>
        @endif

        <div class="master-product-table-wrap">
            <table class="table table-hover master-product-table" id="table1">
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
                    @foreach ($produk as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
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
                    @endforeach
                </tbody>
            </table>
        </div>

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
