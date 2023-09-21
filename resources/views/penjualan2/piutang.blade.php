<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="row">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}</h6> <br><br>
            </div>
            <div class="col-lg-6">
                <button class="btn btn-sm icon icon-left btn-primary me-2 float-end btn_bayar"><i
                        class="fas fa-money-bill"></i>
                    Bayar</button>
            </div>
        </div>

    </x-slot>

    <x-slot name="cardBody">
        <section class="row">
            <div class="col-md-12">
                @php
                $ttlAllPiutang = 0;
                foreach ($invoice_umum as $i) {
                $ttlAllPiutang += $i->total_bayar;
                }
                @endphp
                <button type="button" class="btn btn-outline-primary btn-md font-extrabold mb-0"> Semua Piutang
                    : Rp. {{ number_format($ttlAllPiutang, 2) }}
                    <br>
                    Piutang Diceklis : Rp. <span class="piutangBayar">0</span>
                </button>

            </div>
            <div class="col-lg-8"></div>
            <div class="col-lg-4 mb-2">
                <table class="float-end">
                    <td>Pencarian :</td>
                    <td><input type="text" id="pencarian" class="form-control float-end"></td>
                </table>
            </div>
            <table class="table table-bordered" id="tablealdi" width="100%">
                <thead>
                    <th class="dhead">No</th>
                    <th class="dhead">Tanggal</th>
                    <th class="dhead">No Nota</th>
                    <th class="dhead">Produk</th>
                    <th class="dhead">Customer</th>
                    <th class="dhead text-end">Qty</th>
                    <th class="dhead text-end">Total Harga</th>
                    <th class="dhead text-end">Sisa Hutang</th>
                    <th class="dhead text-end">Aksi</th>

                </thead>
                <tbody>
                    @foreach ($invoice_umum as $no => $i)
                    <tr>
                        <td>{{$no+1}}</td>
                        <td>{{tanggal($i->tgl)}}</td>
                        <td>{{$i->kode}}-{{$i->urutan}}</td>
                        <td>{{$i->nm_produk_concat}}</td>
                        <td>
                            @if ($i->lokasi == 'mtd')
                            {{$i->id_customer}}
                            @else
                            {{$i->nm_customer}}
                            @endif

                        </td>
                        <td class="text-end">{{$i->qty}}</td>
                        <td class="text-end">Rp. {{number_format($i->ttl_rp,0)}}</td>
                        <td class="text-end">Rp. {{number_format($i->total_bayar,0)}}</td>
                        <td align="center">
                            <input type="checkbox" no_nota="{{ $i->urutan }}" piutang="{{ $i->total_bayar }}"
                                class="form-check-glow form-check-input form-check-primary cek_bayar" />
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
            <form action="{{ route('hapus_ayam') }}" method="get">
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
                                <button type="button" class="btn btn-outline-danger"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>


        </section>
        @section('js')
        <script>
            $(document).ready(function() {
                pencarian('pencarian', 'tablealdi');
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
                    var queryString = 'no_nota[]=' + dipilih.join('&no_nota[]=');
                    window.location.href = "/penjualan2/bayar_piutang_umum?" + queryString;

                });
            });
        </script>
        @endsection
    </x-slot>
</x-theme.app>