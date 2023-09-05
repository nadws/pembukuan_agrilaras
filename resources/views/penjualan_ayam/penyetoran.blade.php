<x-theme.app title="{{ $title }}" table="Y" sizeCard="11">
    <x-slot name="cardHeader">
        <div class="row">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}: {{ tanggal($tgl1) }} ~ {{ tanggal($tgl2) }}</h6>
            </div>
            <div class="col-lg-6">
                <x-theme.button modal="T" icon="fa-plus" addClass="float-end btn_bayar" teks="Bukukan" />
                {{--
                <x-theme.button modal="Y" idModal="list" icon="fa-list" addClass="float-end list_perencanaan"
                    teks="List Perencanaan" /> --}}
                <x-theme.button modal="Y" idModal="history" icon="fa-history"
                    addClass="float-end history_perencanaan" teks="History Penyetoran" />
                <x-theme.button modal="T" href="/produk_telur" icon="fa-home" addClass="float-end"
                    teks="" />
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <section class="row float-end">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row float-end text-center">

                        <div class="col-lg-12">
                            @php
                                $ttlAllPiutang = 0;
                                
                                foreach ($invoice as $d) {
                                    $ttlAllPiutang += $d->debit;
                                }
                            @endphp
                            <button type="button" class="btn btn-outline-primary btn-md font-extrabold mb-0"> Semua
                                Penyetoran
                                : Rp. {{ number_format($ttlAllPiutang, 0) }}
                                <br>
                                Setoran Diceklis : Rp. <span class="piutangBayar">0</span>
                            </button>

                        </div>
                    </div>

                </div>
            </div>
            <div class="col-lg-8"></div>
            <div class="col-lg-4 mb-2">
                <table class="float-end">
                    <td>Pencarian :</td>
                    <td><input type="text" id="pencarian" class="form-control float-end"></td>
                </table>
            </div>
            <table class="table table-hover" id="tablealdi">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Tanggal</th>
                        <th>No Nota</th>
                        <th>Pembayaran</th>
                        <th>Keterangan</th>
                        <th style="text-align: right">Total Rp</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice as $no => $a)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ tanggal($a->tgl) }}</td>
                            <td>{{ $a->no_nota }}</td>
                            <td>{{ ucwords(strtolower($a->nm_akun)) }}</td>
                            <td>{{ $a->ket }}</td>
                            <td align="right">Rp {{ number_format($a->debit, 0) }}</td>
                            <td>
                                <input type="checkbox" class="cek_bayar" no_nota="{{ $a->id_jurnal }}"
                                    piutang="{{ $a->debit }}" name="" id="">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- sub akun --}}
        <x-theme.modal title="Edit Akun" idModal="sub-akun" size="modal-lg">
            <div id="load-sub-akun">
            </div>
        </x-theme.modal>

        <x-theme.modal title="List Perencanaan" idModal="list" size="modal-lg" btnSave="T">
            <div id="load-list"></div>
        </x-theme.modal>

        <x-theme.modal title="Detail Invoice" btnSave='T' size="modal-lg-max" idModal="detail">
            <div class="row">
                <div class="col-lg-12">
                    <div id="detail_invoice"></div>
                </div>
            </div>
        </x-theme.modal>

        <x-theme.modal title="History Perencanaan" btnSave='T' size="modal-lg" idModal="history">
            <div class="row">
                <div class="col-lg-12">
                    <div id="get_history"></div>
                </div>
            </div>
        </x-theme.modal>

        <form action="{{ route('save_setoran') }}" method="post">
            @csrf
            <x-theme.modal title="Detail Invoice" size="modal-lg" idModal="perencanaan">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="get_perencanaan"></div>
                    </div>
                </div>

            </x-theme.modal>
        </form>

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
                pencarian('pencarian', 'tablealdi')
                $(".btn_bayar").hide();
                $(".piutang_cek").hide();
                $(document).on('change', '.cek_bayar', function() {
                    var totalPiutang = 0
                    $('.cek_bayar:checked').each(function() {
                        var piutang = $(this).attr('piutang');
                        totalPiutang += parseInt(piutang);
                    });
                    var anyChecked = $('.cek_bayar:checked').length > 0;
                    $('.btn_bayar').toggle(anyChecked);
                    $(".piutang_cek").toggle(anyChecked);
                    $('.piutangBayar').text(totalPiutang.toLocaleString('en-US'));
                });

                $(document).on('click', '.btn_bayar', function() {
                    var dipilih = [];
                    $('.cek_bayar:checked').each(function() {
                        var no_nota = $(this).attr('no_nota');
                        dipilih.push(no_nota);

                    });
                    var params = new URLSearchParams();

                    dipilih.forEach(function(orderNumber) {
                        params.append('no_nota', orderNumber);
                    });
                    var queryString = 'id_jurnal[]=' + dipilih.join('&id_jurnal[]=');
                    window.location.href = "/penjualan_ayam/perencanaan_setor?" + queryString;

                });
                $(document).on('click', '.list_perencanaan', function() {
                    $.ajax({
                        type: "get",
                        url: "/get_list_perencanaan",
                        success: function(data) {
                            $('#load-list').html(data);
                        }
                    });

                });

                $(document).on('click', '.perencanaan', function() {
                    var no_nota = $(this).attr('nota_setor');
                    $.ajax({
                        type: "get",
                        url: "/get_perencanaan?no_nota=" + no_nota,
                        success: function(data) {
                            $('#perencanaan').modal('show');
                            $('#get_perencanaan').html(data);
                            $('.select').select2({
                                dropdownParent: $('#perencanaan .modal-content')
                            });
                        }
                    });

                });

                $(document).on('click', '.history_perencanaan', function() {
                    $.ajax({
                        type: "get",
                        url: "/penjualan_ayam/get_history_perencanaan",
                        success: function(data) {
                            $('#get_history').html(data);
                        }
                    });

                });


                $(document).on('submit', '#history_serach', function(e) {
                    e.preventDefault();
                    var tgl1 = $(".tgl1").val();
                    var tgl2 = $(".tgl2").val();
                    $.ajax({
                        type: "get",
                        url: "/penjualan_ayam/get_history_perencanaan?tgl1=" + tgl1 + "&tgl2=" + tgl2,
                        success: function(data) {
                            $('#get_history').html(data);
                        }
                    });
                });




            });
        </script>
    @endsection
</x-theme.app>
