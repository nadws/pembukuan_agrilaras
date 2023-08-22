<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}</h6> <br><br>
                {{-- <p>Piutang Diceklis : Rp. <span class="piutangBayar">0</span></p> --}}
            </div>
            <div class="col-lg-6">
                {{-- <x-theme.button modal="T" icon="fa-plus" addClass="float-end btn_bayar" teks="Setor" /> --}}
                <x-theme.button modal="T" href="/produk_telur" icon="fa-home" addClass="float-end" teks="" />
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            <div class="col-lg-8"></div>
            <div class="col-lg-4 mb-2">
                <table class="float-end">
                    <td>Pencarian :</td>
                    <td><input type="text" id="pencarian" class="form-control float-end"></td>
                </table>
            </div>
            <table class="table table-hover table-striped" id="tablealdi" width="100%">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Tanggal</th>
                        <th width="18%">No Nota<br>Pelanggan</th>
                        <th width="19%" style="text-align: right">Total Rp <br> Semua :
                            ({{ number_format($ttlRp, 0) }}) <br> Belum dicek :
                            ({{ number_format($ttlRpBelumDiCek, 0) }})
                        </th>
                        @foreach ($produk as $p)
                            <th class="text-end">{{ ucwords($p->nm_telur) }}</th>
                        @endforeach
                        <th style="text-align: center">Cek</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice as $no => $i)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ tanggal($i->tgl) }}</td>
                            <td>
                                {{ $i->no_nota }} <br>
                                {{ $i->customer }}
                            </td>
                            <td align="right">Rp {{ number_format($i->ttl_rp, 0) }}</td>
                            @foreach ($produk as $p)
                                @php
                                    $telurDetail = DB::table('invoice_telur')
                                        ->where([['id_produk', $p->id_produk_telur], ['no_nota', $i->no_nota]])
                                        ->first();
                                @endphp
                                <td align="right">{{ number_format($telurDetail->pcs ?? 0, 0) }}
                                    Pcs<br>{{ number_format($telurDetail->kg ?? 0, 1) }} Kg</td>
                            @endforeach
                            <td align="center">
                                @if ($i->cek == 'Y')
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <a href="{{ route('terima_invoice_mtd', ['no_nota' => $i->no_nota]) }}"
                                        class="btn btn-sm btn-primary"><i class="fas fa-plus"></i>
                                        Setor</a>
                                @endif
                            </td>
                            {{-- <td>
                            <a class=" btn btn-primary btn-sm detail_nota" href="#" href="#" data-bs-toggle="modal"
                                no_nota="{{ $i->no_nota }}" data-bs-target="#detail"><i
                                    class="me-2 fas fa-eye"></i>Detail</a>
                        </td> --}}
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
                pencarian('pencarian', 'tablealdi')
                $(document).on("click", ".detail_nota", function() {
                    var no_nota = $(this).attr('no_nota');
                    $.ajax({
                        type: "get",
                        url: "/detail_penjualan_mtd?no_nota=" + no_nota,
                        success: function(data) {
                            $("#detail_invoice").html(data);
                        }
                    });

                });
                $(document).on('click', '.delete_nota', function() {
                    var no_nota = $(this).attr('no_nota');
                    $('.no_nota').val(no_nota);
                });

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

                $('.hide_bayar').hide();
                $(document).on("click", ".detail_bayar", function() {
                    var no_nota = $(this).attr('no_nota');
                    var clickedElement = $(this); // Simpan elemen yang diklik dalam variabel

                    clickedElement.prop('disabled', true); // Menonaktifkan elemen yang diklik

                    $.ajax({
                        type: "get",
                        url: "/get_pembayaranpiutang_telur?no_nota=" + no_nota,
                        success: function(data) {
                            $('.induk_detail' + no_nota).after("<tr>" + data + "</tr>");
                            $(".show_detail" + no_nota).show();
                            $(".detail_bayar" + no_nota).hide();
                            $(".hide_bayar" + no_nota).show();

                            clickedElement.prop('disabled',
                                false
                            ); // Mengaktifkan kembali elemen yang diklik setelah tampilan ditambahkan
                        },
                        error: function() {
                            clickedElement.prop('disabled',
                                false
                            ); // Jika ada kesalahan dalam permintaan AJAX, pastikan elemen yang diklik diaktifkan kembali
                        }
                    });
                });
                $(document).on("click", ".hide_bayar", function() {
                    var no_nota = $(this).attr('no_nota');
                    $(".show_detail" + no_nota).remove();
                    $(".detail_bayar" + no_nota).show();
                    $(".hide_bayar" + no_nota).hide();

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
                    var queryString = 'no_nota[]=' + dipilih.join('&no_nota[]=');
                    window.location.href = "/terima_invoice_mtd?" + queryString;

                });
            });
        </script>
    @endsection
</x-theme.app>
