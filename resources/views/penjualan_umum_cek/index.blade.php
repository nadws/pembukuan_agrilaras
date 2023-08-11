<x-theme.app title="{{ $title }}" table="Y" sizeCard="11">
    <x-slot name="cardHeader">
        <div class="row">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}</h6> <br><br>
                <p>Piutang Diceklis : Rp. <span class="piutangBayar">0</span></p>
            </div>
            <div class="col-lg-6">
                <x-theme.button modal="T" icon="fa-plus" addClass="float-end btn_bayar" teks="Setor" />
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
            <table class="table" id="tablealdi">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Nota</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th width="20%" class="text-center">Total Produk</th>
                        <th class="text-end">Total Rp</th>
                        <th width="20%" class="text-center">Cek</th>
                        <th class="text-center">Diterima</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penjualan as $no => $d)
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td>{{ $d->kode }}-{{$d->urutan}}</td>
                        <td>{{ tanggal($d->tgl) }}</td>
                        <td>{{ $d->nm_customer }}</td>
                        <td align="center">{{ $d->ttl_produk }}</td>
                        <td align="right">Rp. {{ number_format($d->total, 2) }}</td>

                        <td align="center">
                            @if ($d->cek == 'Y')
                            <i class="fas fa-check text-success"></i>
                            @else
                            <input type="checkbox" name="" no_nota="{{$d->urutan}}" piutang="{{ $d->total }}" id=""
                                class="cek_bayar">
                            @endif
                        </td>
                        <td align="center">{{ $d->admin_cek }}</td>
                        <td>
                            <a class="btn btn-primary btn-sm detail_nota" href="#" no_nota="{{ $d->urutan }}" href="#"
                                data-bs-toggle="modal" data-bs-target="#detail"><i
                                    class="me-2 fas fa-search"></i>Detail</a>
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
        </section>
        @section('js')
        <script>
            edit('detail_nota', 'no_nota', 'penjualan2/detail', 'detail_jurnal')
            
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
                window.location.href = "/terima_invoice_umum_cek?" + queryString;

            });
        </script>
        @endsection
    </x-slot>
</x-theme.app>