<x-theme.app cont="container-fluid" title="{{ $title }}" table="Y" sizeCard="8">
    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }} : {{ tanggal($tgl1) }}
                    ~ {{ tanggal($tgl2) }}</h6>
            </div>

            <div class="col-lg-6">

                {{-- <a href="{{ route('controlflow') }}" class="btn btn-primary float-end"><i
                        class="fas fa-home"></i></a> --}}
                @if (!empty($import))
                    <x-theme.button modal="Y" idModal="import" icon="fa-upload" variant="success"
                        addClass="float-end" teks="Import" />
                @endif
                @if (!empty($tambah))
                    <x-theme.button modal="T"
                        href="{{ $id_buku != '13' ? route('jurnal.add', ['id_buku' => $id_buku]) : route('add_balik_aktiva', ['id_buku' => $id_buku]) }}"
                        icon="fa-plus" addClass="float-end" teks="Buat Baru" />
                @endif
                <x-theme.akses :halaman="$halaman" route="jurnal" />


                @if (!empty($export))
                    <x-theme.button modal="T"
                        href="/export_jurnal?tgl1={{ $tgl1 }}&tgl2={{ $tgl2 }}&id_proyek={{ $id_proyek }}&id_buku={{ $id_buku }}"
                        icon="fa-file-excel" addClass="float-end float-end btn btn-success me-2" teks="Export" />
                @endif
                <x-theme.button modal="Y" idModal="view" icon="fa-calendar-week" addClass="float-end"
                    teks="View" />

            </div>
            <div class="col-lg-12">
                <hr style="border: 2px solid #435EBE">
            </div>
            @include('jurnal.nav')
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-hover table-striped" id="table1">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Kode Akun</th>
                        <th>Akun</th>
                        <th style="text-align: right">Debit</th>
                        <th style="text-align: right">Kredit</th>
                        <th style="text-align: right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sldo = 0;
                    @endphp
                    @foreach ($buku as $no => $a)
                        @php
                            $sldo += $a->debit + $a->debit_saldo - ($a->kredit + $a->kredit_saldo);
                        @endphp
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ $a->kode_akun }}</td>
                            <td><a
                                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $a->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ ucwords(strtolower($a->nm_akun)) }}</a>
                            </td>
                            <td style="text-align: right">{{ number_format($a->debit + $a->debit_saldo, 2) }}</td>
                            <td style="text-align: right">{{ number_format($a->kredit + $a->kredit_saldo, 2) }}</td>
                            <td style="text-align: right">
                                {{ number_format($a->debit + $a->debit_saldo - ($a->kredit + $a->kredit_saldo), 2) }}
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
                                <input type="hidden" name="id_proyek" value="{{ $id_proyek }}">
                                <input type="hidden" name="id_buku" value="{{ $id_buku }}">
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

        <form action="" method="get">
            <x-theme.modal title="Filter Tanggal" idModal="view">
                <input type="hidden" name="id_buku" value="{{ $id_buku }}">
                <div class="row">
                    <div class="col-lg-3">Filter</div>
                    <div class="col-lg-1">:</div>
                    <div class="col-lg-8">
                        <select name="period" id="" class="form-control filter_tgl">
                            <option value="daily">Hari ini</option>
                            <option value="mounthly">Bulan </option>
                            <option value="years">Tahun</option>
                            <option value="costume">Custom</option>
                        </select>
                    </div>
                    <div class="col-lg-4 mt-2"></div>
                    <div class="col-lg-4 costume_muncul mt-2">
                        <label for="">Dari</label>
                        <input type="date" name="tgl1" class="form-control tgl">
                    </div>
                    <div class="col-lg-4 costume_muncul mt-2">
                        <label for="">Sampai</label>
                        <input type="date" name="tgl2" class="form-control tgl">
                    </div>
                    <div class="col-lg-4 bulan_muncul mt-2">
                        <label for="">Bulan</label>
                        <select name="bulan" id="bulan" class="selectView bulan">
                            @php
                                $listBulan = DB::table('bulan')->get();
                            @endphp
                            @foreach ($listBulan as $l)
                                <option value="{{ $l->bulan }}"
                                    {{ (int) date('m') == $l->bulan ? 'selected' : '' }}>
                                    {{ $l->nm_bulan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 bulan_muncul mt-2">
                        <label for="">Tahun</label>
                        <select name="tahun" id="" class="selectView bulan">
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025" selected>2025</option>
                        </select>
                    </div>
                    <div class="col-lg-8 tahun_muncul mt-2">
                        <label for="">Tahun</label>
                        <select name="tahunfilter" id="" class="selectView tahun">
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025" selected>2025</option>
                        </select>
                    </div>
                </div>

            </x-theme.modal>
        </form>




    </x-slot>
    @section('js')
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

                $(document).on('click', '.delete_nota', function() {
                    var no_nota = $(this).attr('no_nota');
                    $('.no_nota').val(no_nota);
                })
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
            });
        </script>
    @endsection
</x-theme.app>
