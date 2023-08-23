<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}: {{ tanggal($tgl1) }} ~ {{ tanggal($tgl2) }}</h6>
            </div>
            <div class="col-lg-6">
                <x-theme.button modal="T" href="{{ route('tbh_invoice_telur') }}" icon="fa-plus" addClass="float-end"
                    teks="Buat Invoice" />
                <x-theme.button modal="T" href="{{ route('export_penjualan_telur', [$tgl1, $tgl2]) }}"
                    icon="fa-print" addClass="float-end" teks="Export" />
                <x-theme.button modal="T" href="{{ route('export_faktur', ['tgl1' => $tgl1, 'tgl2' => $tgl2]) }}"
                    icon="fa-file-excel" addClass="float-end" teks="Export Faktur" />
                <x-theme.btn_filter />
                <x-theme.button modal="T" href="/produk_telur" icon="fa-home" addClass="float-end"
                    teks="" />
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            <div class="col-lg-4">
                <h6>Total Penjualan Alpa : Rp. {{ number_format($total_alpa->ttl_rp, 0) }}</h6>
                <h6>Total Penjualan MTD : Rp. {{ number_format($total_mtd->ttl_rp, 0) }}</h6>
            </div>
            <div class="col-lg-12">
                <br>
                <table class="table table-hover" id="table">
                    <thead>
                        <tr>
                            <th width="5">#</th>
                            <th>Tanggal</th>
                            <th>No Nota</th>
                            <th>Customer</th>
                            <th style="text-align: right">Total Rp</th>
                            <th>Tipe Jual</th>
                            <th>Admin</th>
                            <th>Pengantar</th>
                            <th>Metode</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice as $no => $i)
                            <tr>
                                <td>{{ $no + 1 }}</td>
                                <td>{{ tanggal($i->tgl) }}</td>
                                <td>{{ $i->no_nota }}</td>
                                <td>{{ $i->nm_customer }}{{ $i->urutan_customer }}</td>
                                <td align="right">Rp {{ number_format($i->ttl_rp, 0) }}</td>
                                <td>{{ $i->tipe }}</td>
                                <td>{{ ucwords($i->admin) }}</td>
                                <td>{{ ucwords($i->driver) }}</td>
                                <td>{{ $i->status == 'paid' ? 'Tunai' : 'Piutang' }}</td>
                                <td>{{ $i->lokasi == 'mtd' ? 'Martadah' : 'Alpa' }} </td>
                                <td>
                                    <span
                                        class="badge {{ $i->debit_bayar - $i->kredit_bayar != '0' ? 'bg-warning' : 'bg-success' }}">
                                        {{ $i->debit_bayar - $i->kredit_bayar != '0' ? 'Unpaid' : 'Paid' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <span class="btn btn-sm" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v text-primary"></i>
                                        </span>
                                        <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">

                                            @if ($i->status == 'paid')
                                                @if ($i->setor == 'Y')
                                                @else
                                                    <li>
                                                        <a class="dropdown-item text-primary edit_akun"
                                                            href="{{ route('edit_invoice_telur', ['no_nota' => $i->no_nota]) }}"><i
                                                                class="me-2 fas fa-pen"></i>Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger delete_nota"
                                                            no_nota="{{ $i->no_nota }}" href="#"
                                                            data-bs-toggle="modal" data-bs-target="#delete"><i
                                                                class="me-2 fas fa-trash"></i>Delete
                                                        </a>
                                                    </li>
                                                @endif
                                            @else
                                                @if ($i->debit_bayar - $i->kredit_bayar != '0')
                                                    @if ($i->setor == 'Y')
                                                    @else
                                                        <li>
                                                            <a class="dropdown-item text-primary edit_akun"
                                                                href="{{ route('edit_invoice_telur', ['no_nota' => $i->no_nota]) }}"><i
                                                                    class="me-2 fas fa-pen"></i>Edit
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item text-danger delete_nota"
                                                                no_nota="{{ $i->no_nota }}" href="#"
                                                                data-bs-toggle="modal" data-bs-target="#delete"><i
                                                                    class="me-2 fas fa-trash"></i>Delete
                                                            </a>
                                                        </li>
                                                    @endif
                                                @else
                                                @endif
                                            @endif

                                            <li><a class="dropdown-item  text-info detail_nota" href="#"
                                                    href="#" data-bs-toggle="modal" no_nota="{{ $i->no_nota }}"
                                                    data-bs-target="#detail"><i
                                                        class="me-2 fas fa-search"></i>Detail</a>
                                            </li>

                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- sub akun --}}
        <x-theme.modal title="Edit Akun" idModal="sub-akun" size="modal-lg">
            <div id="load-sub-akun">
            </div>
        </x-theme.modal>

        <x-theme.modal title="Detail Invoice" btnSave='T' size="modal-lg-max" idModal="detail">
            <div class="row">
                <div class="col-lg-12">
                    <div id="detail_invoice"></div>
                </div>
            </div>

        </x-theme.modal>

        <form action="{{ route('delete_invoice_telur') }}" method="get">
            <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row">
                                <h5 class="text-danger ms-4 mt-4"><i class="fas fa-trash"></i> Hapus Data</h5>
                                <p class=" ms-4 mt-4">Apa anda yakin ingin menghapus ?</p>
                                <input type="hidden" class="no_nota" name="no_nota">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        {{-- end sub akun --}}
    </x-slot>
    @section('scripts')
        <script>
            $(document).ready(function() {
                $(document).on("click", ".detail_nota", function() {
                    var no_nota = $(this).attr('no_nota');
                    $.ajax({
                        type: "get",
                        url: "/detail_invoice_telur?no_nota=" + no_nota,
                        success: function(data) {
                            $("#detail_invoice").html(data);
                        }
                    });

                });
                $(document).on('click', '.delete_nota', function() {
                    var no_nota = $(this).attr('no_nota');
                    $('.no_nota').val(no_nota);
                });
            });
        </script>
    @endsection
</x-theme.app>
