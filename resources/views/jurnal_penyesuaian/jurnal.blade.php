<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}</h6>
            </div>
            <div class="col-lg-6">
                @if (auth()->user()->posisi_id == 1)
                    <x-theme.button modal="Y" idModal="cancel" href="#" icon="fa-undo-alt" addClass="float-end"
                        teks="Cancel Jurnal" />
                @endif
                @if (!empty($export))
                    <a href="{{ route('export_jurnal', ['tgl1' => $tgl1, 'tgl2' => $tgl2, 'id_buku' => '4']) }}"
                        class="float-end btn   btn-success me-2"><i class="fas fa-file-excel"></i> Export</a>
                @endif
                @if (!empty($create))
                    <x-theme.button modal="T" href="{{ route('penyesuaian.aktiva') }}" icon="fa-plus"
                        addClass="float-end" teks="Buat Baru" />
                @endif

                <x-theme.btn_filter />
                <x-theme.akses :halaman="$halaman" route="jurnal_aktiva" />

            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-hover" id="table1">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Tanggal</th>
                        <th>No Nota</th>
                        <th>No Cfm</th>
                        <th>Akun</th>
                        <th>Sub Akun</th>
                        <th>Keterangan</th>
                        <th style="text-align: right">Debit</th>
                        <th style="text-align: right">Kredit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jurnal as $no => $a)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td class="nowrap">{{ date('d-m-Y', strtotime($a->tgl)) }}</td>
                            <td>{{ $a->no_nota }}</td>
                            <td>{{ $a->no_urut }}</td>
                            <td>{{ ucwords(strtolower($a->nm_akun)) }}</td>
                            <td>{{ ucwords(strtolower($a->nm_post ?? '')) }}</td>
                            @if (strlen($a->ket) > 60)
                                <td>
                                    <span class="teksLimit{{ $a->id_jurnal }}">
                                        {{ Str::limit($a->ket, 60, '...') }}
                                        <a href="#" class="readMore" id="{{ $a->id_jurnal }}">read
                                            more</a>
                                    </span>
                                    <span class="teksFull{{ $a->id_jurnal }}" style="display:none">{{ $a->ket }}
                                        <a href="#" class="less" id="{{ $a->id_jurnal }}">less</a></span>
                                </td>
                            @else
                                <td>
                                    {{ $a->ket }}
                                </td>
                            @endif
                            <td align="right">{{ number_format($a->debit, 2) }}</td>
                            <td align="right">{{ number_format($a->kredit, 2) }}</td>
                            <td>
                                @if ($a->penutup == 'Y')
                                @else
                                    <div class="btn-group" role="group">
                                        <span class="btn btn-sm" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v text-primary"></i>
                                        </span>
                                        <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                            @if (!empty($detail))
                                                <li><a class="dropdown-item  text-info detail_nota" href="#"
                                                        no_nota="{{ $a->no_nota }}" href="#"
                                                        data-bs-toggle="modal" data-bs-target="#detail"><i
                                                            class="me-2 fas fa-search"></i>Detail</a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>


        <form action="{{ route('import_jurnal') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Import Jurnal" idModal="import">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">File Excel (Format: @file.xlsx)</label>
                        <input type="file" name="file" id="" class="form-control">
                    </div>
                </div>

            </x-theme.modal>
        </form>

        <form action="{{ route('jurnal-delete') }}" method="get">
            <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row">
                                <h5 class="text-danger ms-4 mt-4"><i class="fas fa-trash"></i> Hapus Data</h5>
                                <p class=" ms-4 mt-4">Apa anda yakin ingin menghapus ?</p>
                                <input type="hidden" class="no_nota" name="no_nota">
                                <input type="hidden" name="tgl1" value="{{ $tgl1 }}">
                                <input type="hidden" name="tgl2" value="{{ $tgl2 }}">
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

        <x-theme.modal title="Detail Jurnal" size="modal-lg-max" idModal="detail">
            <div class="row">
                <div class="col-lg-12">
                    <div id="detail_jurnal"></div>
                </div>
            </div>

        </x-theme.modal>

        <form action="{{ route('penyesuaian.save_cancel_penyesuaian') }}" method="post">
            @csrf
            <x-theme.modal title="Cancel Jurnal Penyesuaian" size="modal-sm" idModal="cancel">
                <div class="row">
                    <div class="col-lg-12">
                        <select name="id_akun_penyesuaian" id="id_akun_penyesuaian" class="form-control ">
                            <option value="">Pilih </option>
                            <option value="51">Aktiva</option>
                            <option value="58">Peralatan</option>
                            <option value="91">Atk</option>
                        </select>
                        <br>
                    </div>
                    <div class="col-lg-12">
                        <div id="load_cancel"></div>
                    </div>
                </div>

            </x-theme.modal>
        </form>




    </x-slot>
    @section('scripts')
        <script>
            $(document).ready(function() {
                function readMore() {
                    $(document).on('click', '.readMore', function(e) {
                        e.preventDefault()
                        var id = $(this).attr('id')
                        $(".teksLimit" + id).css('display', 'none')
                        $(".teksFull" + id).css('display', 'block')
                    })
                    $(document).on('click', '.less', function(e) {
                        e.preventDefault()
                        var id = $(this).attr('id')
                        $(".teksLimit" + id).css('display', 'block')
                        $(".teksFull" + id).css('display', 'none')
                    })
                }
                readMore()
                $('.delete_nota').click(function() {
                    var no_nota = $(this).attr('no_nota');
                    $('.no_nota').val(no_nota);

                });
                $('.selectView').select2({
                    dropdownParent: $('#view .modal-content')
                });


                $(document).on("click", ".detail_nota", function() {
                    var no_nota = $(this).attr('no_nota');
                    $.ajax({
                        type: "get",
                        url: "/detail_jurnal?no_nota=" + no_nota,
                        success: function(data) {
                            $("#detail_jurnal").html(data);
                        }
                    });

                });

                $('.costume_muncul').hide();
                $('.tgl').prop('disabled', true);

                $(document).on("change", ".filter_tgl", function() {
                    var period = $(this).val();

                    if (period === 'costume') {
                        $('.costume_muncul').show();
                        $('.tgl').prop('disabled', false);
                    } else {
                        $('.costume_muncul').hide();
                        $('.tgl').prop('disabled', true);
                    }
                });
                $(document).on("change", "#id_akun_penyesuaian", function() {

                    var id_akun = $(this).val();
                    $.ajax({
                        type: "get",
                        url: "{{ route('penyesuaian.load_data_cancel') }}",
                        data: {
                            id_akun: id_akun
                        },
                        success: function(r) {
                            $('#load_cancel').html(r);
                        }
                    });
                });

            });
        </script>
    @endsection
</x-theme.app>
