<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="row align-items-center g-2">
            <div class="col-lg-6">
                <h6 class="mb-0">History {{ $kategori === 'pakan' ? 'Pakan' : 'Vitamin & Vaksin' }}</h6>
                <small class="text-muted">Daftar biaya yang belum dibukukan</small>
                {{-- <p>Piutang Diceklis : Rp. <span class="piutangBayar">0</span></p> --}}
            </div>
            <div class="col-lg-6 d-flex justify-content-lg-end gap-2 history-actions">

                <x-theme.button modal="T" icon="fa-plus" addClass="btn_bayar" teks="Bukukan" />
                <x-theme.button modal="T" href="{{ route('history_perencanaan') }}" icon="fa-arrow-left" teks="Kembali" />
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .history-table-wrap {
                overflow-x: auto;
                border: 1px solid #dce3f2;
                border-radius: 10px;
            }

            .history-table-wrap .table {
                min-width: 850px;
                margin-bottom: 0;
            }

            .history-search {
                display: flex;
                max-width: 360px;
                align-items: center;
                gap: 10px;
                margin: 0 0 12px auto;
            }

            .history-search label {
                margin: 0;
                color: #52617a;
                font-weight: 700;
                white-space: nowrap;
            }

            .history-mobile-check {
                display: none;
            }

            @media (max-width: 767.98px) {
                .history-actions {
                    width: 100%;
                    margin-top: 6px;
                }

                .history-actions .btn {
                    display: inline-flex;
                    flex: 1;
                    align-items: center;
                    justify-content: center;
                    margin-right: 0 !important;
                    padding: 9px 12px;
                }

                .history-search {
                    display: block;
                    max-width: none;
                    margin-bottom: 14px;
                }

                .history-search label {
                    display: block;
                    margin-bottom: 6px;
                }

                .history-mobile-check {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin-bottom: 10px;
                    padding: 10px 12px;
                    border: 1px solid #dce3f2;
                    border-radius: 9px;
                    background: #f7f9fd;
                    color: #40516f;
                    font-weight: 700;
                }

                .history-table-wrap {
                    overflow: visible;
                    border: 0;
                    border-radius: 0;
                    background: transparent;
                }

                .history-table-wrap .table {
                    min-width: 0;
                    border-collapse: separate;
                    border-spacing: 0 12px;
                    background: transparent;
                }

                .history-table-wrap thead {
                    display: none;
                }

                .history-table-wrap tbody,
                .history-table-wrap tr,
                .history-table-wrap td {
                    display: block;
                    width: 100%;
                }

                .history-table-wrap tbody tr {
                    overflow: hidden;
                    border: 1px solid #dce3f2;
                    border-radius: 12px;
                    background: #fff !important;
                    box-shadow: 0 5px 16px rgba(35, 60, 115, .06);
                }

                .history-table-wrap tbody td {
                    display: grid;
                    grid-template-columns: minmax(105px, 42%) 1fr;
                    align-items: center;
                    gap: 10px;
                    padding: 9px 12px;
                    border-bottom: 1px solid #edf0f6;
                    text-align: right !important;
                }

                .history-table-wrap tbody td:first-child {
                    display: none;
                }

                .history-table-wrap tbody td:last-child {
                    border-bottom: 0;
                    background: #f7f9fd;
                }

                .history-table-wrap tbody td::before {
                    color: #71809a;
                    content: attr(data-label);
                    font-size: 11px;
                    font-weight: 750;
                    letter-spacing: .2px;
                    text-align: left;
                    text-transform: uppercase;
                }

                .history-table-wrap .cek_bayar {
                    width: 19px;
                    height: 19px;
                    margin-left: auto;
                }
            }
        </style>

        <section>
            <div class="history-search">
                <label for="pencarian">Pencarian</label>
                <input type="search" id="pencarian" class="form-control" placeholder="Cari transaksi...">
            </div>
            <label class="history-mobile-check">
                <input type="checkbox" class="check-all"> Pilih semua transaksi yang tersedia
            </label>
            <div>
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
                        <td data-label="Nomor">{{$no+1}}</td>
                        <td data-label="Tanggal">{{tanggal($s->tgl)}}</td>
                        <td data-label="Kandang">{{$s->nm_kandang}}</td>
                        <td data-label="Produk">{{$s->nm_produk}}</td>
                        <td data-label="Jumlah" class="text-end">{{number_format($s->pcs_kredit,1)}} </td>
                        <td data-label="Satuan">{{$s->nm_satuan}}</td>
                        <td data-label="HPP / Gr" class="text-end">Rp {{number_format($s->hpp_per_gr ?? ($s->pcs_kredit > 0 ? $s->total_rp / $s->pcs_kredit : 0), 2)}}</td>
                        <td data-label="Total" class="text-end">Rp {{number_format($s->total_rp,0)}}</td>
                        <td data-label="Admin">{{$s->admin}}</td>
                        <td data-label="Pilih" class="text-center">
                            <input type="checkbox" name="" no_nota="{{ $s->id_stok_telur }}"
                                piutang="{{ $s->total_rp }}" id=""
                                class="cek_bayar {{$max_tgl == $s->tgl ? 'checkbox' : ''}}" {{$max_tgl==$s->tgl
                            ? '' : 'disabled' }}>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
                    </table>
                </div>
            </div>
            <input type="hidden" class="kategori" value="{{$kategori}}">
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
                $(document).on('input', '#pencarian', function() {
                    var value = $(this).val().toLowerCase();
                    $('#tablealdi tbody tr').each(function() {
                        $(this).toggleClass('d-none', $(this).text().toLowerCase().indexOf(value) === -1);
                    });
                });
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
                    $(".check-all").prop("checked", isChecked);

                    var anyChecked = $('.cek_bayar:checked').length > 0;
                    var totalPiutang = 0;
                    $('.cek_bayar:checked').each(function() {
                        totalPiutang += parseInt($(this).attr('piutang')) || 0;
                    });
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
