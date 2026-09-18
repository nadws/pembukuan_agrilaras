<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}</h6>
            </div>
            <div class="col-lg-6">
                <x-theme.button modal="T" icon="fa-plus" addClass="float-end btn_bayar" teks="Bayar" />
                <x-theme.button modal="T" href="/produk_telur" icon="fa-home" addClass="float-end" teks="" />
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <div class="card">
            <div class="card-body px-4 py-4-5">
                <div class="row float-end text-center">
                    <div class="col-lg-12">
                        <button type="button" class="btn btn-outline-primary btn-md font-extrabold mb-0"> Semua Piutang
                            : Rp. {{ number_format($totalPiutang, 2) }}
                            <br>
                            Piutang Diceklis : Rp. <span class="piutangBayar">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $kategori == 'All'? 'active': '' }}" aria-current="page"
                    href="{{ route('piutang_telur',['kategori'=> 'All', 'tgl1'=> $tgl1, 'tgl2'=> $tgl2]) }}">All</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kategori == 'Unpaid'? 'active': '' }}"
                    href="{{ route('piutang_telur',['kategori'=> 'Unpaid', 'tgl1'=> $tgl1, 'tgl2'=> $tgl2]) }}">Unpaid</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kategori == 'Paid'? 'active': '' }}"
                    href="{{ route('piutang_telur',['kategori'=> 'Paid', 'tgl1'=> $tgl1, 'tgl2'=> $tgl2]) }}">Paid</a>
            </li>
        </ul>
        <form method="GET" action="{{ route('piutang_telur') }}" class="row g-2 mb-3 align-items-end" id="filterForm">
            <input type="hidden" name="kategori" value="{{ $kategori }}">
            <div class="col-auto">
                <label class="form-label small fw-bold">Dari Tanggal</label>
                <input type="date" name="tgl1" class="form-control" value="{{ $tgl1 }}">
            </div>
            <div class="col-auto">
                <label class="form-label small fw-bold">Sampai Tanggal</label>
                <input type="date" name="tgl2" class="form-control" value="{{ $tgl2 }}">
            </div>
            <div class="col-auto">
                <label class="form-label small fw-bold">Cari</label>
                <input type="text" id="pencarian" class="form-control" placeholder="Ketik nomor nota..." autofocus>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
            <table class="table table-hover" id="tablealdi">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th></th>
                        <th>Tanggal</th>
                        <th>No Nota</th>
                        <th>Customer</th>
                        <th style="text-align: right">Total Rp</th>
                        <th style="text-align: right">Terbayar</th>
                        <th style="text-align: right">Sisa Hutang</th>
                        <th>Tipe Jual</th>
                        <th>Admin</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice as $no => $i)
                    <tr class="fw-bold induk_detail{{ $i->no_nota }}">
                        <td>{{$no+1}}</td>
                        <td>
                            <a href="#" onclick="event.preventDefault();"
                                class="detail_bayar detail_bayar{{ $i->no_nota }}" no_nota="{{ $i->no_nota }}"><i
                                    class="fas fa-angle-down"></i></a>

                            <a href="#" onclick="event.preventDefault();" class="hide_bayar hide_bayar{{ $i->no_nota }}"
                                no_nota="{{ $i->no_nota }}"><i class="fas fa-angle-up"></i></a>
                        </td>
                        <td>{{tanggal($i->tgl)}}</td>
                        <td>{{$i->no_nota}}</td>
                        <td>{{$i->id_customer == 0 ? $i->customer : $i->nm_customer}}{{$i->urutan_customer}}</td>
                        <td align="right">Rp {{number_format($i->ttl_rp,0)}}</td>
                        <td align="right">Rp {{number_format($i->bayar,0)}}</td>
                        <td align="right">Rp {{number_format($i->paid,0)}}</td>
                        <td>{{$i->tipe}}</td>
                        <td>{{ ucwords($i->admin) }}</td>
                        <td>
                            <span class="badge {{ $i->paid != '0' ? 'bg-warning' : 'bg-success' }}">
                                {{ $i->paid != '0' ? 'Unpaid' : 'Paid' }}
                            </span>
                        </td>
                        <td>
                            @if ($i->paid == 0)
                            <i class="fas fa-check text-success"></i>
                            @else
                            <input type="checkbox" name="" no_nota="{{$i->no_nota}}" piutang="{{ $i->paid }}" id=""
                                class="cek_bayar">
                            @endif

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

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
        {{-- end sub akun --}}
    </x-slot>
    @section('scripts')
    <script>
        $(document).ready(function() {
            var debounceTimer;
            $('#pencarian').on('input', function() {
                var val = $(this).val().toLowerCase();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    $('#tablealdi tbody tr').each(function() {
                        var text = $(this).text().toLowerCase();
                        $(this).toggle(text.indexOf(val) > -1);
                    });
                }, 300);
            });
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
                window.location.href = "/bayar_piutang_telur?" + queryString;

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
            $('#tablealdi tbody tr').on('click', function(e) {
                if ($(e.target).is('input, a, button, .cek_bayar')) return;
                var cb = $(this).find('.cek_bayar');
                if (cb.length && !cb.prop('disabled')) {
                    cb.prop('checked', !cb.prop('checked')).trigger('change');
                }
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
        });
    </script>
    @endsection
</x-theme.app>