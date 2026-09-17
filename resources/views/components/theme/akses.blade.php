@props([
    'route' => '',
    'halaman' => '',
])

{{-- modal setting akses per role untuk halaman ini --}}
@if (auth()->user()->posisi_id == 1)
    <x-theme.button modal="Y" idModal="akses" icon="fas fa-cog" addClass="float-end" teks="" />
@endif

<form action="{{ route('akses.halaman.save') }}" method="post">
    @csrf
    <input type="hidden" name="route" value="{{ $route }}">
    <input type="hidden" name="permission_id" value="{{ $halaman }}">
    <x-theme.modal title="Akses Setting" idModal="akses" size="modal-lg">
        @php
            $aksesRoles = DB::table('tb_posisi')->orderBy('id_posisi')->get();
            $aksesButtons = DB::table('permission_button')->where('permission_id', $halaman)->orderBy('id_permission_button')->get();
            $aksesGrants = DB::table('permission_role')
                ->whereIn('id_permission_button', $aksesButtons->pluck('id_permission_button'))
                ->get()->groupBy('posisi_id')
                ->map(fn ($rows) => $rows->pluck('id_permission_button')->map(fn ($id) => (int) $id)->flip());
            $aksesByJenis = $aksesButtons->groupBy('jenis');
        @endphp
        <p class="text-muted small">Centang tombol yang boleh dipakai tiap role. Berlaku untuk semua user dalam role tersebut.</p>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Create</th>
                    <th>Read</th>
                    <th>Update</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($aksesRoles as $role)
                    <tr>
                        <td class="text-start fw-bold">
                            {{ ucwords($role->nm_posisi) }}
                            <br>
                            <label class="fw-normal small text-muted"><input type="checkbox" class="form-check-input akses-role-all" data-role-row="{{ $role->id_posisi }}"> semua</label>
                        </td>
                        @foreach (['create', 'read', 'update', 'delete'] as $jenis)
                            <td class="text-start">
                                @foreach ($aksesByJenis->get($jenis, []) as $btn)
                                    <label class="d-block"><input type="checkbox" name="akses[{{ $role->id_posisi }}][]"
                                            value="{{ $btn->id_permission_button }}"
                                            class="form-check-input akses-role-{{ $role->id_posisi }}"
                                            @checked(isset($aksesGrants[$role->id_posisi]) && $aksesGrants[$role->id_posisi]->has((int) $btn->id_permission_button))>
                                        {!! $btn->nm_permission_button !!}</label>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-theme.modal>
</form>
<script>
    document.querySelectorAll('.akses-role-all').forEach(function (master) {
        master.addEventListener('change', function () {
            document.querySelectorAll('.akses-role-' + this.dataset.roleRow).forEach(function (box) {
                box.checked = master.checked;
            });
        });
    });
</script>
{{-- end modal setting --}}
