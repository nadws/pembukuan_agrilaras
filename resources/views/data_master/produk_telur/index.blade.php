<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0">Master Produk Telur</h5>
                <small class="text-muted">Kelola kode dan nama produk telur yang digunakan pada stok serta penjualan.</small>
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahProdukTelur">
                <i class="fas fa-plus me-1"></i> Tambah Produk
            </button>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .egg-master-wrap{overflow-x:auto;border:1px solid #e0e6f1;border-radius:12px}
            .egg-master-table{margin-bottom:0}
            .egg-master-table thead th{padding:12px;background:#304f9e;color:#fff;font-size:12px;white-space:nowrap}
            .egg-master-table td{padding:12px;vertical-align:middle}
            .egg-master-table tbody tr:hover{background:#f7f9fd}
            .egg-code{display:inline-block;min-width:60px;padding:5px 9px;border-radius:7px;background:#e8eefc;color:#304f9e;font-family:monospace;font-weight:700;text-align:center}
            .egg-product-form .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}
            .egg-product-form .form-control{min-height:42px;border-color:#dce3f2;border-radius:8px}
        </style>

        @if ($errors->any())
            <div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>
        @endif

        <div class="egg-master-wrap">
            <table class="table table-hover egg-master-table" id="table1">
                <thead>
                    <tr>
                        <th style="width:70px">No</th>
                        <th style="width:220px">Kode Produk</th>
                        <th>Nama Produk Telur</th>
                        <th class="text-center" style="width:150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($produk as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="egg-code">{{ $item->kode_produk }}</span></td>
                            <td class="fw-semibold">{{ $item->nm_telur }}</td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-warning edit-egg-product"
                                    data-bs-toggle="modal" data-bs-target="#editProdukTelur"
                                    data-id="{{ $item->id_produk_telur }}"
                                    data-code="{{ $item->kode_produk }}"
                                    data-name="{{ $item->nm_telur }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('produk-telur-master.destroy', $item->id_produk_telur) }}" class="d-inline"
                                    onsubmit="return confirm('Hapus produk telur {{ addslashes($item->nm_telur) }}? Produk yang sudah digunakan tidak dapat dihapus.')">
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

        <form method="POST" action="{{ route('produk-telur-master.store') }}" class="egg-product-form">
            @csrf
            <x-theme.modal title="Tambah Produk Telur" idModal="tambahProdukTelur">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Kode Produk</label>
                        <input type="text" name="kode_produk" class="form-control text-uppercase" maxlength="25" placeholder="Contoh: TU" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Nama Produk Telur</label>
                        <input type="text" name="nm_telur" class="form-control" maxlength="50" placeholder="Contoh: Telur Utuh" required>
                    </div>
                </div>
            </x-theme.modal>
        </form>

        <form method="POST" action="" id="editEggProductForm" class="egg-product-form">
            @csrf
            @method('PUT')
            <x-theme.modal title="Edit Produk Telur" idModal="editProdukTelur">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Kode Produk</label>
                        <input type="text" name="kode_produk" id="edit_egg_code" class="form-control text-uppercase" maxlength="25" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Nama Produk Telur</label>
                        <input type="text" name="nm_telur" id="edit_egg_name" class="form-control" maxlength="50" required>
                    </div>
                </div>
            </x-theme.modal>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.edit-egg-product').forEach(function (button) {
                    button.addEventListener('click', function () {
                        document.getElementById('editEggProductForm').action = @json(url('/data-master/produk-telur')) + '/' + this.dataset.id;
                        document.getElementById('edit_egg_code').value = this.dataset.code || '';
                        document.getElementById('edit_egg_name').value = this.dataset.name || '';
                    });
                });
            });
        </script>
    </x-slot>
</x-theme.app>
