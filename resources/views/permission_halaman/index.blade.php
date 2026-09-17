<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Langkah 1: kelola role. Langkah 2: centang akses tiap role.</small>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahRoleModal">
                <i class="fas fa-plus me-1"></i> Tambah Role
            </button>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .role-matrix-wrap { overflow-x: auto; border: 1px solid #dce3f2; border-radius: 12px; }
            .role-matrix { min-width: 760px; margin-bottom: 0; font-size: 13px; }
            .role-matrix thead th { background: #29468f; color: #fff; white-space: nowrap; }
            .role-matrix .perm-row td { background: #eef2fb; font-weight: 800; color: #1d3167; }
            .jenis-badge { display: inline-block; min-width: 62px; padding: 1px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; text-align: center; }
            .jenis-create { background: #e7f6ec; color: #17803d; }
            .jenis-read { background: #e8f0fe; color: #2b5cb8; }
            .jenis-update { background: #fff4dd; color: #9a6b0f; }
            .jenis-delete { background: #fde8e8; color: #b42323; }
            .role-page-list { max-height: 560px; overflow-y: auto; border: 1px solid #c9d4e8; border-radius: 12px; background: #fff; }
            .role-page-item { display: flex; flex-direction: column; gap: 2px; border: 0; border-bottom: 1px solid #dbe2f0; border-radius: 0; background: #fff; padding: 11px 14px; }
            .role-page-item:last-child { border-bottom: 0; }
            .role-page-item .role-page-name { font-weight: 700; color: #1d3167; }
            .role-page-item .role-page-meta { font-size: 11px; color: #7b879c; }
            .role-page-item.active { background: #e8eefc; box-shadow: inset 3px 0 0 #29468f; }
            .role-page-item.active .role-page-name { color: #1d3167; }
            .role-page-item.active .role-page-meta { color: #5b6b8c; }
            .role-parent-toggle { background: #dde6f5; border-bottom: 1px solid #c9d4e8; }
            .role-parent-toggle.active { background: #cdd9ef; }
            .role-parent-toggle .role-caret { font-size: 11px; color: #7b879c; transition: transform .15s ease; }
            .role-parent-toggle.kids-open .role-caret { transform: rotate(90deg); }
            .role-page-child { padding-left: 28px; background: #f1f5fb; box-shadow: inset 2px 0 0 #b9c7e4; }
        </style>

        @if (session('sukses'))
            <div class="alert alert-success">{{ session('sukses') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <h6 class="fw-bold mb-2">Langkah 1 — Daftar Role</h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr><th width="5">#</th><th>Nama Role</th><th class="text-center">User</th><th class="text-center">Hak Akses</th><th width="170" class="text-center">Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach ($roles as $no => $role)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td class="fw-bold">{{ $role->nm_posisi }}</td>
                            <td class="text-center">{{ $role->jumlah_user }} user</td>
                            <td class="text-center">{{ $role->jumlah_akses }} tombol</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id_posisi }}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form action="{{ route('akses.role.destroy', $role->id_posisi) }}" method="POST" onsubmit="return confirm('Hapus role {{ $role->nm_posisi }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @foreach ($roles as $role)
            <form action="{{ route('akses.role.update', $role->id_posisi) }}" method="POST">
                @csrf @method('PUT')
                <x-theme.modal title="Edit Role" idModal="editRoleModal{{ $role->id_posisi }}">
                    <label class="form-label fw-bold">Nama role</label>
                    <input type="text" name="nm_posisi" class="form-control" value="{{ $role->nm_posisi }}" required maxlength="100">
                </x-theme.modal>
            </form>
        @endforeach

        <form action="{{ route('akses.role.store') }}" method="POST">
            @csrf
            <x-theme.modal title="Tambah Role" idModal="tambahRoleModal">
                <label class="form-label fw-bold">Nama role</label>
                <input type="text" name="nm_posisi" class="form-control" placeholder="mis. Kasir, Gudang" required maxlength="100">
            </x-theme.modal>
        </form>

        <h6 class="fw-bold mb-2">Langkah 2 — Matriks Akses per Role</h6>
        <p class="text-muted small">Klik nama halaman untuk membuka rinciannya. Centang tombol yang boleh dipakai tiap role, lalu simpan. Berlaku untuk semua user dalam role tersebut.</p>
        <form action="{{ route('akses.matrix.save') }}" method="POST">
            @csrf
            <div class="role-access">
                @php
                    $halamanInduk = $permissionHalaman->whereNull('id_induk')->values();
                    $indukPertama = $halamanInduk->first();
                    $anakPertama = $indukPertama ? $permissionHalaman->where('id_induk', $indukPertama->id_permission)->values() : collect();
                    $panelDefault = $anakPertama->isNotEmpty() ? $anakPertama->first()->id_permission : ($indukPertama->id_permission ?? null);
                @endphp
                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="list-group role-page-list">
                            @foreach ($halamanInduk as $perm)
                                @php $anakHalaman = $permissionHalaman->where('id_induk', $perm->id_permission); @endphp
                                @if ($anakHalaman->isNotEmpty())
                                    <div class="role-parent">
                                        <button type="button" class="list-group-item list-group-item-action role-parent-toggle {{ $perm->id_permission == $indukPertama->id_permission ? 'kids-open' : '' }}" data-kids="kids-{{ $perm->id_permission }}" data-page="perm-panel-{{ $perm->id_permission }}">
                                            <span class="role-page-name"><i class="fas fa-chevron-right role-caret me-1"></i>{{ $perm->nm_permission }}</span>
                                            <span class="role-page-meta">{{ $anakHalaman->count() }} sub-menu</span>
                                        </button>
                                        <div id="kids-{{ $perm->id_permission }}" style="{{ $perm->id_permission == $indukPertama->id_permission ? '' : 'display:none' }}">
                                            @foreach ($anakHalaman as $anak)
                                                @php
                                                    $tombolAnak = $permissionButton->where('permission_id', $anak->id_permission);
                                                    $totalDapat = 0;
                                                    foreach ($roles as $role) {
                                                        $totalDapat += isset($grants[$role->id_posisi]) ? $tombolAnak->filter(fn ($b) => $grants[$role->id_posisi]->has((int) $b->id_permission_button))->count() : 0;
                                                    }
                                                @endphp
                                                <button type="button" class="list-group-item list-group-item-action role-page-item role-page-child {{ $anak->id_permission == $panelDefault ? 'active' : '' }}" data-page="perm-panel-{{ $anak->id_permission }}">
                                                    <span class="role-page-name">{{ $anak->nm_permission }}</span>
                                                    <span class="role-page-meta">{{ $tombolAnak->count() }} tombol &middot; {{ $totalDapat }} dicentang</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $tombolHalaman = $permissionButton->where('permission_id', $perm->id_permission);
                                        $totalDapat = 0;
                                        foreach ($roles as $role) {
                                            $totalDapat += isset($grants[$role->id_posisi]) ? $tombolHalaman->filter(fn ($b) => $grants[$role->id_posisi]->has((int) $b->id_permission_button))->count() : 0;
                                        }
                                    @endphp
                                    <button type="button" class="list-group-item list-group-item-action role-page-item {{ $perm->id_permission == $panelDefault ? 'active' : '' }}" data-page="perm-panel-{{ $perm->id_permission }}">
                                        <span class="role-page-name">{{ $perm->nm_permission }}</span>
                                        <span class="role-page-meta">{{ $tombolHalaman->count() }} tombol &middot; {{ $totalDapat }} dicentang</span>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-8">
                        @foreach ($permissionHalaman as $perm)
                            @php $tombolHalaman = $permissionButton->where('permission_id', $perm->id_permission); @endphp
                            <div class="perm-panel" id="perm-panel-{{ $perm->id_permission }}" style="{{ $perm->id_permission == $panelDefault ? '' : 'display:none' }}">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <h6 class="mb-0">{{ $perm->nm_permission }}</h6>
                                    <div class="d-flex flex-wrap gap-3 small">
                                        @foreach ($roles as $role)
                                            <label class="mb-0"><input type="checkbox" class="form-check-input check-role" data-role="{{ $role->id_posisi }}"> semua {{ $role->nm_posisi }}</label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="role-matrix-wrap">
                                    <table class="table table-bordered role-matrix mb-0">
                                        <thead>
                                            <tr>
                                                <th>Tombol</th>
                                                @foreach ($roles as $role)
                                                    <th class="text-center">{{ $role->nm_posisi }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tombolHalaman as $btn)
                                                <tr>
                                                    <td>
                                                        <span class="jenis-badge jenis-{{ $btn->jenis }}">{{ ucfirst($btn->jenis) }}</span>
                                                        {!! strip_tags($btn->nm_permission_button) !!}
                                                    </td>
                                                    @foreach ($roles as $role)
                                                        <td class="text-center">
                                                            <input type="checkbox" class="form-check-input check-role-{{ $role->id_posisi }}"
                                                                name="akses[{{ $role->id_posisi }}][]" value="{{ $btn->id_permission_button }}"
                                                                @checked(isset($grants[$role->id_posisi]) && $grants[$role->id_posisi]->has((int) $btn->id_permission_button))>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Matriks Akses</button>
            </div>
        </form>
    </x-slot>

    @section('scripts')
        <script>
            document.querySelectorAll('.check-role').forEach(function (master) {
                master.addEventListener('change', function () {
                    document.querySelectorAll('.check-role-' + this.dataset.role).forEach(function (box) {
                        box.checked = master.checked;
                    });
                });
            });
            function pilihPanel(pageId, activeEl) {
                document.querySelectorAll('.role-page-item, .role-parent-toggle').forEach(function (el) { el.classList.remove('active'); });
                if (activeEl) activeEl.classList.add('active');
                document.querySelectorAll('.perm-panel').forEach(function (panel) {
                    panel.style.display = panel.id === pageId ? '' : 'none';
                });
            }
            document.querySelectorAll('.role-page-item').forEach(function (item) {
                item.addEventListener('click', function () { pilihPanel(item.dataset.page, item); });
            });
            document.querySelectorAll('.role-parent-toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var kids = document.getElementById(this.dataset.kids);
                    var buka = kids.style.display === 'none';
                    kids.style.display = buka ? '' : 'none';
                    btn.classList.toggle('kids-open', buka);
                    pilihPanel(btn.dataset.page, btn);
                });
            });
        </script>
    @endsection
</x-theme.app>
