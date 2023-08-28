<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <h6 class="float-start">{{ $title }}</h6>
        {{-- @if (!empty($create)) --}}
        <x-theme.button modal="T" href="{{ route('penjualan2.add') }}" icon="fa-plus" addClass="float-end"
            teks="Buat Baru" />
        {{-- @endif --}}
        <x-theme.akses :halaman="$halaman" route="penjualan2.index" />

        <x-theme.btn_filter />
    </x-slot>

    <x-slot name="cardBody">
        <section class="row">
            <table class="table" id="table1">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Nota</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th width="20%" class="text-center">Total Produk</th>
                        <th class="text-end">Total Rp</th>
                        <th width="20%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penjualan as $no => $d)
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td>{{ $d->nota_manual }}</td>
                        <td>{{ tanggal($d->tgl) }}</td>
                        <td>{{ $d->nm_customer }}</td>
                        <td align="center">{{ $d->ttl_produk }}</td>
                        <td align="right">Rp. {{ number_format($d->total, 2) }}</td>
                        <td align="center">
                            <div class="btn-group" role="group">
                                <span class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v text-primary"></i>
                                </span>
                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    @php
                                    $emptyKondisi = [$edit, $delete, $detail];
                                    @endphp
                                    {{-- <x-theme.dropdown_kosong :emptyKondisi="$emptyKondisi" /> --}}

                                    {{-- @if (!empty($detail)) --}}
                                    <li><a class="dropdown-item  text-info detail_nota" href="#"
                                            no_nota="{{ $d->urutan }}" href="#" data-bs-toggle="modal"
                                            data-bs-target="#detail"><i class="me-2 fas fa-search"></i>Detail</a>
                                    </li>
                                    {{-- @endif --}}

                                    {{-- @if (!empty($edit)) --}}
                                    <li>
                                        <a class="dropdown-item text-info edit_akun"
                                            href="{{ route('penjualan2.edit', ['urutan' => $d->urutan]) }}"><i
                                                class="me-2 fas fa-pen"></i>Edit</a>
                                    </li>
                                    {{-- @endif --}}

                                    {{-- @if (!empty($delete)) --}}
                                    <li>
                                        <a class="dropdown-item text-danger delete_nota" no_nota="{{ $d->urutan }}"
                                            href="#" data-bs-toggle="modal" data-bs-target="#delete"><i
                                                class="me-2 fas fa-trash"></i>Delete
                                        </a>
                                    </li>
                                    {{-- @endif --}}
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>

            <x-theme.modal btnSave="" title="Detail Jurnal" size="modal-lg" idModal="detail">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="detail_jurnal"></div>
                    </div>
                </div>
            </x-theme.modal>

            <x-theme.btn_alert_delete route="penjualan2.delete" name="urutan" :tgl1="$tgl1" :tgl2="$tgl2" />
        </section>
        @section('js')
        <script>
            edit('detail_nota', 'no_nota', 'penjualan2/detail', 'detail_jurnal')
        </script>
        @endsection
    </x-slot>
</x-theme.app>