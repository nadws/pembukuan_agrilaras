<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }} : {{tanggal($tgl1)}} ~ {{tanggal($tgl2)}}</h6>
            </div>
            <div class="col-lg-6">

                @if (!empty($create))
                <x-theme.button modal="T" href="{{ route('pembelian_bk.add') }}" icon="fa-plus" addClass="float-end"
                    teks="Buat Baru" />
                @endif

                @if (!empty($export))
                <x-theme.button modal="T" href="/export_bk?tgl1={{ $tgl1 }}&tgl2={{ $tgl2 }}" icon="fa-file-excel"
                    addClass="float-end float-end btn btn-success me-2" teks="Export" />
                @endif

                <x-theme.btn_filter title="Filter Pembelian Bk" />

                <x-theme.akses :halaman="$halaman" route="pembelian_bk" />
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <form action="{{ route('approve_invoice_bk') }}" method="post">
            @csrf
            @if (!empty($approve))
            <button class="float-end btn btn-primary btn-sm"><i class="fas fa-check"></i> Approve</button>
            <br>
            <br>
            @endif
            <section class="row">
                <div class="col-lg-8"></div>
                <div class="col-lg-4 mb-2">
                    <table class="float-end">
                        <td>Pencarian :</td>
                        <td><input type="text" id="pencarian" class="form-control float-end"></td>
                    </table>


                </div>
                <table class="table table-hover" id="tableSearch" width="100%">
                    <thead>
                        <tr>
                            <th class="dhead" width="5">#</th>
                            <th class="dhead">Tanggal</th>
                            <th class="dhead">No Nota</th>
                            <th class="dhead">Suplier Awal</th>
                            <th class="dhead">Suplier Akhir</th>
                            <th class="dhead" style="text-align: right">Total Harga</th>
                            <th class="dhead" style="text-align: center">Status</th>
                            <th class="dhead" style="text-align: center">Grading</th>
                            @if (!empty($approve))
                            <th class="dhead" style="text-align: center">Approve <br> <input type="checkbox" name=""
                                    id="checkAll" id="">
                            </th>
                            @endif
                            <th class="dhead">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pembelian as $no => $p)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ tanggal($p->tgl) }}</td>
                            <td>{{ $p->no_nota }}</td>
                            <td>{{ ucwords(strtolower($p->nm_suplier)) }}</td>
                            <td>{{ ucwords(strtolower($p->suplier_akhir)) }}</td>
                            <td align="right">Rp. {{ number_format($p->total_harga, 0) }}</td>

                            <td align="center">
                                <span
                                    class="badge {{ $p->lunas == 'D' ? 'bg-warning' : ($p->total_harga + $p->debit - $p->kredit == 0 ? 'bg-success' : 'bg-danger') }}">
                                    {{ $p->lunas == 'D' ? 'Draft' : ($p->total_harga + $p->debit - $p->kredit == 0 ?
                                    'Paid' : 'Unpaid') }}
                                </span>
                            </td>
                            <td align="center">
                                @if (empty($p->nota_grading))
                                <i class="fas fa-times text-danger"></i>
                                @else
                                <a href="#" class="btn btn-sm btn-success grading_nota" no_nota="{{ $p->no_nota }}"
                                    data-bs-toggle="modal" data-bs-target="#viewgrading"><i class="fas fa-eye"></i></a>
                                @endif

                            </td>
                            @if (!empty($approve))
                            <td style="text-align: center">
                                @if ($p->approve == 'Y')
                                <i class="fas fa-check text-success"></i>
                                <input type="hidden" name="ceknota[]" id="" value="Y">
                                @else
                                <input type="checkbox" name="ceknota[]" class="checkbox-item" id=""
                                    value="{{ $p->no_nota }}">
                                @endif

                            </td>
                            @endif
                            <td>
                                <div class="btn-group" role="group">
                                    <span class="btn btn-sm" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v text-primary"></i>
                                    </span>
                                    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                        @php
                                        $emptyKondisi = [$edit, $delete, $print, $grading];
                                        @endphp
                                        <x-theme.dropdown_kosong :emptyKondisi="$emptyKondisi" />

                                        @if ($p->approve == 'Y')
                                        @else
                                        @if (!empty($edit))
                                        <li>
                                            <a class="dropdown-item text-primary edit_akun"
                                                href="{{ route('edit_pembelian_bk', ['nota' => $p->no_nota]) }}">
                                                <i class="me-2 fas fa-pen"></i> Edit
                                            </a>
                                        </li>
                                        @endif
                                        @if (!empty($delete))
                                        <li>
                                            <a class="dropdown-item  text-danger delete_nota"
                                                no_nota="{{ $p->no_nota }}" href="#" data-bs-toggle="modal"
                                                data-bs-target="#delete"><i class="me-2 fas fa-trash"></i>Delete
                                            </a>
                                        </li>
                                        @endif
                                        @endif
                                        @if (!empty($print))
                                        <li>
                                            <a class="dropdown-item  text-info detail_nota" target="_blank"
                                                href="{{ route('print_bk', ['no_nota' => $p->no_nota]) }}"><i
                                                    class="me-2 fas fa-print"></i>Print
                                            </a>
                                        </li>
                                        @endif

                                        @if (!empty($grading))
                                        <li>
                                            <a href="#" class="dropdown-item  text-info grading_notatambah"
                                                no_nota="{{ $p->no_nota }}" data-bs-toggle="modal"
                                                data-bs-target="#grading"><i
                                                    class="me-2 fas fa-balance-scale-right"></i>Grading
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </section>
        </form>

        <form action="{{ route('grading') }}" method="post">
            @csrf
            <x-theme.modal title="Campur BKIN" idModal="grading">
                <div id="grading_nota2"></div>
            </x-theme.modal>
        </form>


        <x-theme.modal title="Campur BKIN" size="modal-lg" idModal="viewgrading" btnSave="T">
            <div id="grading_nota"></div>

        </x-theme.modal>

        <form action="{{ route('delete_bk') }}" method="get">
            <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row">
                                <h5 class="text-danger ms-4 mt-4"><i class="fas fa-trash"></i> Hapus Data
                                </h5>
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





    </x-slot>

    @section('scripts')
    <script>
        $(document).ready(function() {
                pencarian('pencarian', 'tableSearch')

                $(document).on('click', '.delete_nota', function() {
                    var no_nota = $(this).attr('no_nota');
                    $('.no_nota').val(no_nota);
                })
                $(document).on('click', '.grading_nota', function() {
                    var no_nota = $(this).attr('no_nota');
                    $.ajax({
                        type: "get",
                        url: "/get_grading?no_nota=" + no_nota,
                        success: function(data) {
                            $('#grading_nota').html(data);
                            $('.nota_grading').val(no_nota);
                            $('.nota_grading_text').text(no_nota);
                        }
                    });

                });
                $(document).on('click', '.grading_notatambah', function() {
                    var no_nota = $(this).attr('no_nota');

                    $.ajax({
                        type: "get",
                        url: "/get_grading2?no_nota=" + no_nota,
                        success: function(data) {
                            $('#grading_nota2').html(data);
                            $('.nota_grading').val(no_nota);
                            $('.nota_grading_text').text(no_nota);
                        }
                    });


                });

                $(document).on('click', '#checkAll', function() {
                    // Setel properti checked dari kotak centang individu sesuai dengan status "cek semua"
                    $('.checkbox-item').prop('checked', $(this).prop('checked'));
                });

            });
    </script>
    @endsection
</x-theme.app>