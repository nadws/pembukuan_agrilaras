<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="row align-items-center g-2">
            <div class="col-lg-6">
                <h6 class="mb-0">{{ $title }}</h6>
                <small class="text-muted">Daftar biaya {{ $kategori }} yang belum dibukukan</small>
                {{-- <p>Piutang Diceklis : Rp. <span class="piutangBayar">0</span></p> --}}
            </div>
            <div class="col-lg-6 d-flex justify-content-lg-end gap-2">

                <x-theme.button modal="T" icon="fa-plus" addClass="btn_bayar" teks="Bukukan" />
                <x-theme.button modal="T" href="/produk_telur" icon="fa-home" teks="" />
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .kategori-nav {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 6px;
                max-width: 420px;
                padding: 5px;
                margin-bottom: 18px;
                border: 1px solid #dce3f2;
                border-radius: 12px;
                background: #f2f5fb;
            }

            .kategori-nav .nav-link {
                padding: 10px 16px;
                border-radius: 8px;
                color: #52617a;
                font-weight: 700;
                text-align: center;
                transition: background-color .2s ease, color .2s ease, box-shadow .2s ease;
            }

            .kategori-nav .nav-link:hover {
                color: #29468f;
                background: #e5ebf8;
            }

            .kategori-nav .nav-link.active {
                color: #fff;
                background: #29468f;
                box-shadow: 0 5px 12px rgba(41, 70, 143, .24);
            }

            .history-table-wrap {
                overflow-x: auto;
                border: 1px solid #dce3f2;
                border-radius: 10px;
            }

            .history-table-wrap .table {
                min-width: 850px;
                margin-bottom: 0;
            }

            @media (max-width: 576px) {
                .kategori-nav {
                    max-width: none;
                    width: 100%;
                }

                .kategori-nav .nav-link {
                    padding: 9px 10px;
                    font-size: 12px;
                }
            }
        </style>

        <nav class="kategori-nav" aria-label="Kategori history perencanaan">
            <a class="nav-link {{ $kategori === 'pakan' ? 'active' : '' }}"
                href="{{ route('history_perencanaan_pakan', ['kategori' => 'pakan']) }}">
                Pakan
            </a>
            <a class="nav-link {{ $kategori === 'vitamin' ? 'active' : '' }}"
                href="{{ route('history_perencanaan_pakan', ['kategori' => 'vitamin']) }}">
                Vitamin
            </a>
        </nav>

        <section class="row">
            <div class="col-lg-8"></div>
            <div class="col-lg-4 mb-2">
                <table class="float-end">
                    <td>Pencarian :</td>
                    <td><input type="text" id="pencarian" class="form-control float-end"></td>
                </table>
            </div>
            <div class="col-12">
                <div class="history-table-wrap">
                    <table class="table table-hover table-striped" id="tablealdi" width="100%">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Tanggal </th>
                        <th>Kandang</th>
                        <th>{{ $kategori === 'pakan' ? 'Nama Pakan' : 'Nama Vitamin' }}</th>
                        <th class="text-end">Pcs</th>
                        <th>Satuan</th>
                        <th class="text-end">HPP / Gr</th>
                        <th class="text-end">Total Rp</th>
                        <th>Admin</th>
                        <th style="text-align: center">Cek <br>
                            <input type="checkbox" class="check-all">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stok as $no => $s)
                    <tr>
                        <td>{{$no+1}}</td>
                        <td>{{tanggal($s->tgl)}}</td>
                        <td>{{$s->nm_kandang}}</td>
                        <td>{{$s->nm_produk}}</td>
                        <td class="text-end">{{number_format($s->pcs_kredit,1)}} </td>
                        <td>{{$s->nm_satuan}}</td>
                        <td class="text-end">Rp {{number_format($s->hpp_per_gr ?? ($s->pcs_kredit > 0 ? $s->total_rp / $s->pcs_kredit : 0), 2)}}</td>
                        <td class="text-end">Rp {{number_format($s->total_rp,0)}}</td>
                        <td>{{$s->admin}}</td>
                        <td align="center">
                            <input type="checkbox" name="" no_nota="{{ $s->id_stok_telur }}"
                                piutang="{{ $s->total_rp }}" id=""
                                class="cek_bayar {{$max_tgl == $s->tgl ? 'checkbox' : ''}}" {{$max_tgl==$s->tgl
                            ? '' : 'disabled' }}>

                        </td>
                    </tr>
                    @endforeach
                    <input type="text" style="display: none" class="kategori" value="{{$kategori}}">
                </tbody>
                    </table>
                </div>
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
            <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                var kategori = $('.kategori').val();
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
                    window.location.href = "/pembukuan_biaya_pv?kategori=" + kategori + "&"+ queryString;

                });

                $(".check-all").change(function() {
                    // Periksa apakah tombol "Check All" sekarang dicentang atau tidak
                    var isChecked = $(this).prop("checked");
                    
                    // Setel semua checkbox lainnya sesuai dengan status tombol "Check All"
                    $(".checkbox").prop("checked", isChecked);

                    var anyChecked = $('.cek_bayar:checked').length > 0;
                    $('.btn_bayar').toggle(anyChecked);
                    $(".piutang_cek").toggle(anyChecked);
                    $('.piutangBayar').text(totalPiutang.toLocaleString('en-US'));
                });

                // Ketika salah satu checkbox diubah
                $(".checkbox").change(function() {
                    // Periksa apakah semua checkbox lainnya telah dicentang
                    var allChecked = $(".checkbox").not(":checked").length === 0;
                    
                    // Jika semua checkbox dicentang, centang juga tombol "Check All"
                    $(".check-all").prop("checked", allChecked);
                });
            });
    </script>
    @endsection
</x-theme.app>
