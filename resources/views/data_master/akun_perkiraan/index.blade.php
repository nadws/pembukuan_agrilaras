<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0">{{ $title }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('master.akun-perkiraan.export') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Export
                </a>
                <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="fas fa-upload me-1"></i> Import
                </button>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fas fa-plus me-1"></i> Tambah Akun
                </button>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        @if (session('sukses'))
            <div class="alert alert-success">{{ session('sukses') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="get" class="row g-2 mb-3">
            <div class="col-lg-4">
                <label class="form-label">Tipe Akun</label>
                <select name="tipe_akun" class="form-select">
                    <option value="">Semua tipe akun</option>
                    @foreach ($tipeAkun as $tipe)
                        <option value="{{ $tipe }}" @selected(request('tipe_akun') === $tipe)>{{ $tipe }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="col-lg-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Terapkan Filter</button>
                <a href="{{ route('master.akun-perkiraan.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Akun</th>
                        <th>Tipe</th>
                        <th>Akun Induk</th>
                        <th>Cabang Saldo</th>
                        <th>Status</th>
                        <th width="230">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($akun as $item)
                        <tr>
                            <td><span class="font-monospace">{{ $item->kode_perkiraan }}</span></td>
                            <td>
                                @if ($item->id_akun_induk)<span class="text-muted me-1">&rdsh;</span>@endif
                                {{ $item->nama }}
                                @if ($item->catatan)
                                    <i class="fas fa-info-circle text-muted ms-1" title="{{ $item->catatan }}"></i>
                                @endif
                            </td>
                            <td><span class="badge bg-light-primary text-primary">{{ $item->tipe_akun }}</span></td>
                            <td>{{ $item->akunInduk ? $item->akunInduk->kode_perkiraan . ' - ' . $item->akunInduk->nama : '-' }}</td>
                            <td>{{ $item->cabang_saldo ?: '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $item->aktif ? 'success' : 'danger' }}">
                                    {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info text-white edit-akun"
                                    data-id="{{ $item->id_akun_perkiraan }}"
                                    data-tipe="{{ $item->tipe_akun }}"
                                    data-kode="{{ $item->kode_perkiraan }}"
                                    data-nama="{{ $item->nama }}"
                                    data-induk="{{ $item->id_akun_induk }}"
                                    data-cabang="{{ $item->cabang_saldo }}"
                                    data-catatan="{{ $item->catatan }}"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit">
                                    <i class="fas fa-pen me-1"></i> Edit
                                </button>
                                <form method="post" action="{{ route('master.akun-perkiraan.toggle', $item) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-{{ $item->aktif ? 'warning' : 'success' }}"
                                        title="{{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        onclick="return confirm('Ubah status akun ini?')">
                                        <i class="fas fa-{{ $item->aktif ? 'ban' : 'check' }} me-1"></i>
                                        {{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Data akun belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <form action="{{ route('master.akun-perkiraan.store') }}" method="post">
            @csrf
            <x-theme.modal title="Tambah Akun Perkiraan" idModal="modalTambah" size="modal-lg">
                @include('data_master.akun_perkiraan.partials.form', ['prefix' => 'tambah'])
            </x-theme.modal>
        </form>

        <form id="formEdit" method="post">
            @csrf @method('PUT')
            <x-theme.modal title="Edit Akun Perkiraan" idModal="modalEdit" size="modal-lg">
                @include('data_master.akun_perkiraan.partials.form', ['prefix' => 'edit'])
            </x-theme.modal>
        </form>

        <form action="{{ route('master.akun-perkiraan.import.preview') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Import Akun Perkiraan" idModal="modalImport" size="modal-lg">
                <div class="alert alert-info">
                    Gunakan format Accurate. Data belum disimpan sebelum preview dikonfirmasi.
                </div>
                <div class="mb-3">
                    <a href="{{ route('master.akun-perkiraan.template') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i> Download Template
                    </a>
                </div>
                <label class="form-label">File Excel</label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
            </x-theme.modal>
        </form>

        @if ($preview)
            <div class="modal fade" id="modalPreview" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Preview Import Akun</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead><tr><th>Baris</th><th>Kode</th><th>Nama</th><th>Tipe</th><th>Induk</th><th>Status</th><th>Keterangan</th></tr></thead>
                                    <tbody>
                                        @foreach ($preview['rows'] as $row)
                                            <tr>
                                                <td>{{ $row['baris'] }}</td>
                                                <td>{{ $row['kode_perkiraan'] }}</td>
                                                <td>{{ $row['nama'] }}</td>
                                                <td>{{ $row['tipe_akun'] }}</td>
                                                <td>{{ $row['kode_akun_induk'] ?: '-' }}</td>
                                                <td><span class="badge bg-{{ $row['status'] === 'gagal' ? 'danger' : ($row['status'] === 'baru' ? 'success' : 'warning') }}">{{ $row['status'] }}</span></td>
                                                <td>{{ implode(' ', $row['errors']) ?: '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <form action="{{ route('master.akun-perkiraan.import.confirm') }}" method="post">
                                @csrf
                                <input type="hidden" name="token" value="{{ $preview['token'] }}">
                                <button class="btn btn-success" @disabled(collect($preview['rows'])->contains(fn ($row) => $row['status'] === 'gagal'))>
                                    Konfirmasi Import
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-slot>

    @section('scripts')
        <script>
            $(function () {
                $('.edit-akun').on('click', function () {
                    const button = $(this);
                    $('#formEdit').attr('action', `{{ url('/master/akun-perkiraan') }}/${button.data('id')}`);
                    $('#edit_tipe_akun').val(button.data('tipe'));
                    $('#edit_kode_perkiraan').val(button.data('kode'));
                    $('#edit_nama').val(button.data('nama'));
                    $('#edit_id_akun_induk').val(button.data('induk')).trigger('change');
                    $('#edit_cabang_saldo').val(button.data('cabang'));
                    $('#edit_catatan').val(button.data('catatan'));
                });

                @if ($preview)
                    new bootstrap.Modal(document.getElementById('modalPreview')).show();
                @endif
            });
        </script>
    @endsection
</x-theme.app>
