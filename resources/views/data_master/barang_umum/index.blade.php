<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div><h5 class="mb-0">Master Barang Umum</h5><small class="text-muted">Barang yang digunakan pada pembelian umum dan persediaan umum.</small></div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahBarangUmum"><i class="fas fa-plus me-1"></i> Tambah Barang</button>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .general-item-wrap{overflow-x:auto;border:1px solid #e0e6f1;border-radius:12px}.general-item-table{min-width:950px;margin-bottom:0}.general-item-table thead th{padding:12px;background:#304f9e;color:#fff;font-size:12px;white-space:nowrap}.general-item-table td{padding:11px;vertical-align:middle}.general-item-table tbody tr:hover{background:#f7f9fd}.item-code{display:inline-block;min-width:55px;padding:5px 9px;border-radius:7px;background:#e8eefc;color:#304f9e;font-family:monospace;font-weight:700;text-align:center}.stock-badge{display:inline-block;padding:5px 9px;border-radius:20px;font-size:11px;font-weight:700}.stock-yes{background:#e2f7ea;color:#18723a}.stock-no{background:#f1f3f6;color:#697386}.general-item-form .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}.general-item-form .form-control,.general-item-form .form-select{min-height:42px;border-color:#dce3f2;border-radius:8px}
        </style>

        @if (isset($errors) && $errors->any())<div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>@endif

        <div class="general-item-wrap">
            <table class="table table-hover general-item-table" id="table1">
                <thead><tr><th style="width:60px">No</th><th>Kode</th><th>Nama Barang</th><th>Satuan</th><th>Gudang</th><th>Kontrol Stok</th><th>Tanggal Update</th><th>Admin</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                    @foreach ($barang as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td><td><span class="item-code">{{ $item->kd_produk }}</span></td><td class="fw-semibold">{{ $item->nm_produk }}</td><td>{{ $item->nm_satuan ?: '-' }}</td><td>{{ $item->nm_gudang ?: '-' }}</td>
                            <td><span class="stock-badge {{ $item->kontrol_stok === 'Y' ? 'stock-yes' : 'stock-no' }}">{{ $item->kontrol_stok === 'Y' ? 'Dikontrol' : 'Tidak' }}</span></td><td>{{ tanggal($item->tgl) }}</td><td>{{ $item->admin }}</td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-warning edit-general-item" data-bs-toggle="modal" data-bs-target="#editBarangUmum" data-id="{{ $item->id_produk }}" data-code="{{ $item->kd_produk }}" data-name="{{ $item->nm_produk }}" data-unit="{{ $item->satuan_id }}" data-warehouse="{{ $item->gudang_id }}" data-stock="{{ $item->kontrol_stok }}" title="Edit"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="{{ route('barang-umum.destroy', $item->id_produk) }}" class="d-inline" onsubmit="return confirm('Hapus barang {{ addslashes($item->nm_produk) }}? Barang yang sudah dipakai tidak dapat dihapus.')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('barang-umum.store') }}" class="general-item-form">@csrf
            <x-theme.modal title="Tambah Barang Umum" idModal="tambahBarangUmum" size="modal-lg">@include('data_master.barang_umum.partials.form', ['prefix' => 'tambah', 'kode' => $kodeBerikutnya])</x-theme.modal>
        </form>
        <form method="POST" action="" id="editGeneralItemForm" class="general-item-form">@csrf @method('PUT')
            <x-theme.modal title="Edit Barang Umum" idModal="editBarangUmum" size="modal-lg">@include('data_master.barang_umum.partials.form', ['prefix' => 'edit', 'kode' => ''])</x-theme.modal>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.edit-general-item').forEach(function (button) {
                    button.addEventListener('click', function () {
                        document.getElementById('editGeneralItemForm').action = @json(url('/data-master/barang-umum')) + '/' + this.dataset.id;
                        document.getElementById('edit_kd_produk').value = this.dataset.code || '';
                        document.getElementById('edit_nm_produk').value = this.dataset.name || '';
                        document.getElementById('edit_satuan_id').value = this.dataset.unit || '';
                        document.getElementById('edit_gudang_id').value = this.dataset.warehouse || '';
                        document.getElementById('edit_kontrol_stok').value = this.dataset.stock || 'Y';
                    });
                });
            });
        </script>
    </x-slot>
</x-theme.app>
