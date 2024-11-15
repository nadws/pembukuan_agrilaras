<x-theme.app cont="container-fluid" title="{{ $title }}" cont="container-fluid" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row ">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }} </h6>
            </div>
            <div class="col-lg-6">
                <a href="#" data-bs-toggle="modal" data-bs-target="#view"
                    class="btn btn-sm btn-primary float-end"><i class="fas fa-calendar-week"></i></a>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .dhead {
                background-color: #435EBE !important;
                color: white;
                vertical-align: middle;
            }

            .step-wizard-list {
                background: #fff;
                box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
                color: #333;
                list-style-type: none;
                border-radius: 10px;
                display: flex;
                padding: 20px 10px;
                position: relative;
                z-index: 10;
            }

            .step-wizard-item {
                padding: 0 20px;
                flex-basis: 0;
                -webkit-box-flex: 1;
                -ms-flex-positive: 1;
                flex-grow: 1;
                max-width: 100%;
                display: flex;
                flex-direction: column;
                text-align: center;
                min-width: 170px;
                position: relative;
            }

            .step-wizard-item+.step-wizard-item:after {
                content: "";
                position: absolute;
                left: 0;
                top: 19px;
                background: #21d4fd;
                width: 100%;
                height: 2px;
                transform: translateX(-50%);
                z-index: -10;
            }

            .progress-count {
                height: 40px;
                width: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                font-weight: 600;
                margin: 0 auto;
                position: relative;
                z-index: 10;
                color: transparent;
            }

            .progress-count:after {
                content: "";
                height: 40px;
                width: 40px;
                background: #21d4fd;
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                border-radius: 50%;
                z-index: -10;
            }

            .progress-count:before {
                content: "";
                height: 10px;
                width: 20px;
                border-left: 3px solid #fff;
                border-bottom: 3px solid #fff;
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -60%) rotate(-45deg);
                transform-origin: center center;
            }

            .progress-label {
                font-size: 14px;
                font-weight: 600;
                margin-top: 10px;
            }

            .current-item .progress-count:before,
            .current-item~.step-wizard-item .progress-count:before {
                display: none;
            }

            .current-item~.step-wizard-item .progress-count:after {
                height: 10px;
                width: 10px;
            }

            .current-item~.step-wizard-item .progress-label {
                opacity: 0.5;
            }

            .current-item .progress-count:after {
                background: #fff;
                border: 2px solid #21d4fd;
            }

            .current-item .progress-count {
                color: #21d4fd;
            }
        </style>


        <section class="row">
            <div class="col-lg-6">
                <h6>Stok masuk martadah {{ tanggal($tanggal) }}</h6>
                <table class="table table-bordered">
                    <thead style="font-size: 10px; border-top-left-radius: 50px">
                        <tr>
                            <th class="dhead" rowspan="2" style="text-align: center">Kandang</th>
                            @foreach ($produk as $p)
                                <th colspan="2" style="text-align: center" class="dhead">{{ $p->nm_telur }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($produk as $p)
                                <th style="text-align: center" class="dhead">Pcs</th>
                                <th style="text-align: center" class="dhead">Kg</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody style="border-color: #435EBE; font-size: 10px;">

                        @foreach ($kandang as $k)
                            <tr>
                                <td>{{ $k->nm_kandang }}</td>
                                @foreach ($produk as $p)
                                    @php
                                        $stok = DB::selectOne("SELECT a.pcs , a.kg
                            FROM stok_telur as a
                            where a.tgl = '$tanggal' and a.id_telur = '$p->id_produk_telur' and a.id_gudang = '1'
                            and
                            a.id_kandang = '$k->id_kandang'
                            ");
                                    @endphp
                                    <td align="right">{{ empty($stok->pcs) ? '0' : number_format($stok->pcs, 0) }}</td>
                                    <td align="right">{{ empty($stok->kg) ? '0' : number_format($stok->kg, 1) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="border-color: #435EBE; font-size: 10px">
                        <tr>
                            <th>Total</th>
                            @foreach ($produk as $p)
                                @php
                                    $total_mtd = DB::selectOne("SELECT sum(a.pcs) as pcs , sum(a.kg) as kg
                            FROM stok_telur as a
                            where a.tgl = '$tanggal' and a.id_telur = '$p->id_produk_telur' and a.id_gudang = '1' and
                            a.id_kandang != '0'
                            ");
                                @endphp
                                <th class="text-end">{{ number_format($total_mtd->pcs, 0) }}</th>
                                <th class="text-end">{{ number_format($total_mtd->kg, 1) }}</th>
                            @endforeach
                        </tr>

                    </tfoot>

                </table>


                @if (!empty($cekStokMasuk->check))
                    <a href="{{ route('CheckMartadah', ['cek' => $cekStokMasuk->check, 'tgl' => $tanggal]) }}"
                        class="float-end btn btn-sm  btn-primary">{{ $cekStokMasuk->check == 'T' ? 'Save' : 'Unsave' }}</a>
                @endif


                {{-- dasda --}}
                <button class="float-end btn btn-sm btn-primary me-2 history-mtd"><i class="fas fa-history"></i>
                    History
                </button>

            </div>
            <div class="col-lg-6">
                <h6>Stok Transfer Alpa {{ tanggal($tanggal) }}</h6>
                <table class="table table-bordered table-dashboard ">
                    <thead style="font-size: 10px">
                        <tr>
                            @foreach ($produk as $p)
                                <th colspan="2" style="text-align: center" class="dhead">{{ $p->nm_telur }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($produk as $p)
                                <th style="text-align: center" class="dhead">Pcs</th>
                                <th style="text-align: center" class="dhead">Kg</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody style="border-color: #435EBE; font-size: 10px">
                        @foreach ($produk as $p)
                            @php
                                $stok_transfer = DB::selectOne("SELECT sum(a.pcs) as pcs , sum(a.kg) as kg
                        FROM stok_telur as a
                        where a.tgl = '$tanggal' and a.pcs != '0' and a.id_telur = '$p->id_produk_telur' and a.id_gudang
                        = '2'
                        ");
                            @endphp
                            <td align="right">
                                {{ empty($stok_transfer->pcs) ? '0' : number_format($stok_transfer->pcs, 0) }}
                            </td>
                            <td align="right">
                                {{ empty($stok_transfer->kg) ? '0' : number_format($stok_transfer->kg, 1) }}
                            </td>
                        @endforeach
                    </tbody>

                </table>
                @php

                @endphp

                @if (!empty($cekTransfer))
                    <a href="{{ route('CheckAlpa', ['cek' => $cekTransfer->check, 'tgl' => $tanggal]) }}"
                        class="float-end btn btn-sm  btn-primary">{{ $cekTransfer->check == 'T' ? 'Save' : 'Unsave' }}</a>
                @endif

                <button class="float-end btn btn-sm btn-primary me-2 history-tf-alpa"><i class="fas fa-history"></i>
                    History</button>
            </div>
            <div class="col-lg-12 mt-4">
                <h6>Stok Telur</h6>
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th rowspan="2" class="dhead" style="vertical-align: middle">Gudang</th>
                            @foreach ($produk as $p)
                                <th colspan="3" style="text-align: center" class="dhead">{{ $p->nm_telur }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($produk as $p)
                                <th style="text-align: center" class="dhead">pcs</th>
                                <th style="text-align: center" class="dhead">kg</th>
                                <th style="text-align: center" class="dhead">ikat</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody style="border-color: #435EBE; ">
                        @foreach ($gudang as $g)
                            <tr>
                                <td>
                                    {{ $g->nm_gudang }}
                                    <a href="#" onclick="event.preventDefault();"
                                        class="badge bg-primary float-end ms-2  text-sm {{ $g->id_gudang_telur == '2' ? 'history-tf-alpa' : 'history-mtd' }} "><i
                                            class="fas fa-history"></i></i>
                                    </a>
                                    <a href="{{ route('penyetoran_telur') }}"
                                        {{ $g->id_gudang_telur == '2' ? '' : 'hidden' }}
                                        class="badge bg-success text-sm float-end" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="penyetoran telur">
                                        <i class="fas fa-money-bill-wave-alt"></i>
                                    </a>
                                    <a href="{{ route('piutang_telur') }}"
                                        {{ $g->id_gudang_telur == '2' ? '' : 'hidden' }}
                                        class="badge bg-primary text-sm me-2 float-end" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Piutang telur"><i
                                            class="far fa-credit-card"></i></i>
                                    </a>
                                    <a href="{{ route('tbh_invoice_telur') }}"
                                        {{ $g->id_gudang_telur == '2' ? '' : 'hidden' }}
                                        class="badge bg-primary me-2 text-sm float-end" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Penjualan telur"><i class="fas fa-plus"></i>
                                    </a>
                                    <a href="{{ route('penjualan_agrilaras') }}"
                                        {{ $g->id_gudang_telur == '2' ? '' : 'hidden' }}
                                        class="badge bg-primary me-2 text-sm float-end" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Penjualan telur"><i class="fas fa-list"></i>
                                    </a>
                                </td>
                                @foreach ($produk as $p)
                                    @php
                                        $stok = DB::selectOne("SELECT sum(a.pcs) as pcs , sum(a.kg) as kg, sum(a.pcs_kredit) as
                            pcs_kredit, sum(a.kg_kredit) as kg_kredit
                            FROM stok_telur as a
                            where a.id_gudang ='$g->id_gudang_telur' and a.id_telur = '$p->id_produk_telur' and a.check
                            ='Y' and opname = 'T' group by a.id_telur");
                                        $stok2 = DB::selectOne("SELECT sum(a.pcs) as pcs , sum(a.kg) as kg, sum(a.pcs_kredit) as
                            pcs_kredit, sum(a.kg_kredit) as kg_kredit
                            FROM stok_telur as a
                            where a.id_gudang ='$g->id_gudang_telur' and a.id_telur = '$p->id_produk_telur' and opname =
                            'T' group by a.id_telur");
                                    @endphp
                                    @if ($g->id_gudang_telur == '1')
                                        <td align="right">
                                            {{ number_format($stok->pcs - $stok->pcs_kredit, 0) }}
                                            <br>
                                            {{ number_format($stok2->pcs - $stok2->pcs_kredit, 0) }}
                                        </td>
                                        <td align="right">
                                            {{ number_format($stok->kg - $stok->kg_kredit, 2) }}
                                            <br>
                                            {{ number_format($stok2->kg - $stok2->kg_kredit, 2) }}
                                        </td>
                                        <td align="right">
                                            {{ number_format(($stok->pcs - $stok->pcs_kredit) / 180, 2) }}
                                            <br>
                                            {{ number_format(($stok2->pcs - $stok2->pcs_kredit) / 180, 2) }}
                                        </td>
                                    @else
                                        <td align="right">
                                            {{ number_format($stok->pcs - $stok->pcs_kredit, 0) }}
                                        </td>
                                        <td align="right">
                                            {{ number_format($stok->kg - $stok->kg_kredit, 1) }}
                                        </td>
                                        <td align="right">
                                            {{ number_format(($stok->pcs - $stok->pcs_kredit) / 180, 1) }}
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-lg-8">


                <div class="row">
                    <div class="col-lg-5">
                        <h6>Penjualan martadah</h6> <br>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="dhead" width="5">#</th>
                            <th class="dhead">Tipe Penjualan</th>
                            <th class="dhead" style="text-align: right">Total Rp</th>
                            <th class="dhead" style="text-align: right">Yang Sudah Diterima</th>
                            <th class="dhead" style="text-align: right">Sisa</th>
                            <th class="dhead" style="text-align: center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="border-color: #435EBE; ">
                        <tr>
                            <td>1</td>
                            <td>Penjualan Telur</td>
                            <td align="right">Rp
                                {{ number_format($penjualan_cek_mtd->ttl_rp + $penjualan_blmcek_mtd->ttl_rp, 0) }}
                            </td>
                            <td align="right">Rp {{ number_format($penjualan_cek_mtd->ttl_rp, 0) }}</td>
                            <td align="right">Rp {{ number_format($penjualan_blmcek_mtd->ttl_rp, 0) }}</td>
                            <td align="center">
                                <a href="{{ route('penjualan_martadah_cek', ['lokasi' => 'mtd']) }}"
                                    class="btn btn-primary btn-sm"><i class="fas fa-history"></i>
                                    History
                                    <span
                                        class="badge bg-danger">{{ empty($penjualan_blmcek_mtd->jumlah) ? '0' : $penjualan_blmcek_mtd->jumlah }}</span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Penjualan Umum</td>
                            <td align="right">Rp
                                {{ number_format($penjualan_umum_mtd->ttl_rp + $penjualan_umum_blmcek_mtd->ttl_rp, 0) }}
                            </td>
                            <td align="right">Rp {{ number_format($penjualan_umum_mtd->ttl_rp, 0) }}</td>
                            <td align="right">Rp {{ number_format($penjualan_umum_blmcek_mtd->ttl_rp, 0) }}</td>
                            <td align="center">

                                <a href="{{ route('penyetoran_penjualan_umum') }}"
                                    class="btn btn-primary btn-sm">Setor</a>
                                <a href="{{ route('penjualan_umum_cek') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-history"></i> History
                                    <span
                                        class="badge bg-danger">{{ empty($penjualan_umum_blmcek_mtd->jumlah) ? '0' : $penjualan_umum_blmcek_mtd->jumlah }}</span>
                                </a>
                                <a href="{{ route('penjualan2.add') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i>

                                </a>
                                <a href="{{ route('penjualan2.piutang') }}" class="btn btn-primary btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Piutang Umum">
                                    <i class="fas fa-money-check"></i>

                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Penjualan Ayam</td>
                            <td align="right">Rp
                                {{ number_format($penjualan_ayam_mtd->ttl_rp + $penjualan_ayam_blmcek_mtd->ttl_rp, 0) }}
                            </td>
                            <td align="right">Rp {{ number_format($penjualan_ayam_mtd->ttl_rp, 0) }}</td>
                            <td align="right">Rp {{ number_format($penjualan_ayam_blmcek_mtd->ttl_rp, 0) }}</td>
                            <td align="center">
                                <a href="{{ route('penjualan_ayam.index') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-history"></i> History
                                    <span
                                        class="badge bg-danger">{{ empty($penjualan_ayam_blmcek_mtd->jumlah) ? '0' : $penjualan_ayam_blmcek_mtd->jumlah }}</span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Opname Telur Martadah</td>
                            <td align="right">Rp
                                {{ number_format($opname_cek_mtd->ttl_rp + $opname_blmcek_mtd->ttl_rp, 0) }}
                            </td>
                            <td align="right">Rp {{ number_format($opname_cek_mtd->ttl_rp, 0) }}</td>
                            <td align="right">Rp {{ number_format($opname_blmcek_mtd->ttl_rp, 0) }}</td>
                            <td align="center">
                                <a href="{{ route('bukukan_opname_martadah') }}" class="btn btn-primary btn-sm"><i
                                        class="fas fa-history"></i>
                                    History
                                    <span
                                        class="badge bg-danger">{{ empty($opname_blmcek_mtd->jumlah) ? '0' : $opname_blmcek_mtd->jumlah }}</span>
                                </a>
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>
            <div class="col-lg-4">
                <div id="load_stok_ayam"></div>
            </div>

            <div class="col-lg-12">
                <hr style="border: 1px solid #435EBE">
            </div>

            <div id="load_stok_pakan"></div>
        </section>

        <x-theme.modal btnSave='T' title="History Telur Martdah" size="modal-lg-max" idModal="history_mtd">
            <div class="row">
                <div class="col-lg-12">
                    <div id="h_martadah"></div>
                </div>
            </div>

        </x-theme.modal>
        <x-theme.modal btnSave='T' title="History Transfer Alpa" size="modal-lg-max" idModal="history_alpa">
            <div class="row">
                <div class="col-lg-12">
                    <div id="h_alpa"></div>
                </div>
            </div>
        </x-theme.modal>

        <form action="{{ route('save_opname_pakan') }}" method="post">
            @csrf
            <x-theme.modal title="Opname Pakan" size="modal-lg" idModal="opname_pakan">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="opname_stk_pkn"></div>
                    </div>
                </div>
            </x-theme.modal>
        </form>
        <form action="{{ route('save_opname_pakan') }}" method="post">
            @csrf
            <x-theme.modal title="Opname Vitamin" size="modal-lg" idModal="opname_vitamin">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="opname_stk_vtmn"></div>
                    </div>
                </div>
            </x-theme.modal>
        </form>
        <x-theme.modal title="History Stok" btnSave='T' size="modal-lg" idModal="history_stok">
            <div class="row">
                <div class="col-lg-12">
                    <div id="history_stk"></div>
                </div>
            </div>
        </x-theme.modal>
        <form action="{{ route('save_tambah_pakan') }}" method="post">
            @csrf
            <x-theme.modal title="Tambah Stok Pakan" size="modal-lg" idModal="tbh_pakan">
                <div class="row">
                    <div id="tambah_pakan"></div>
                </div>
            </x-theme.modal>
        </form>
        <form action="" method="get">
            <x-theme.modal title="View Tanggal" idModal="view">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">Tanggal</label>
                        <input type="date" name="tgl" class="form-control" value="{{ $tanggal }}">
                    </div>
                </div>
            </x-theme.modal>
        </form>
    </x-slot>
    @section('js')
        <script>
            $(document).ready(function() {

                $('.step').on('click', function() {
                    $(this).addClass('active').prevAll().addClass('active');
                    $(this).nextAll().removeClass('active');
                });
                $(document).on('click', '.history-mtd', function() {
                    $.ajax({
                        type: "get",
                        url: "/HistoryMtd",
                        success: function(data) {
                            $('#h_martadah').html(data);
                            $('#history_mtd').modal('show');
                        }
                    });
                });
                $(document).on('click', '.history-tf-alpa', function() {
                    $.ajax({
                        type: "get",
                        url: "/HistoryAlpa",
                        success: function(data) {
                            $('#h_alpa').html(data);
                            $('#history_alpa').modal('show');
                        }
                    });
                });

                $(document).on('submit', '#search_history_mtd', function(e) {
                    e.preventDefault();
                    var tgl1 = $('#tgl1').val();
                    var tgl2 = $('#tgl2').val();
                    $.ajax({
                        type: "get",
                        url: "/HistoryMtd?tgl1=" + tgl1 + "&tgl2=" + tgl2,
                        success: function(data) {
                            $('#h_martadah').html(data);
                        }
                    });
                });
                $(document).on('submit', '#search_history_alpa', function(e) {
                    e.preventDefault();
                    var tgl1 = $('#tgl1').val();
                    var tgl2 = $('#tgl2').val();
                    $.ajax({
                        type: "get",
                        url: "/edit_telur_dashboard?id_kandang=" + id_kandang + "&tgl=" + tgl,
                        success: function(data) {
                            $('#edit_martadah').html(data);
                            $('#edit_mtd').modal('show');
                        }
                    });
                });
                load_stok_ayam();

                function load_stok_ayam() {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('stok_ayam') }}",
                        success: function(data) {
                            $("#load_stok_ayam").html(data);
                            $(".select2-pakan").select2({
                                dropdownParent: $(`#penjualan_ayam .modal-content`)
                            });

                        }
                    });
                }
                $(document).on("keyup", ".ekor", function() {
                    var ekor = $('.ekor').val();
                    var h_satuan = $('.h_satuan').val();
                    var ttl_rp = parseFloat(ekor) * parseFloat(h_satuan);
                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = ttl_rp + total_kredit;

                    var totalRupiahall = ttl_rp.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    $('.ttl_rp').val(ttl_rp);
                    $(".total_kredit").text(totalkreditall)
                    $(".total").text(totalRupiahall);

                });
                $(document).on("keyup", ".h_satuan", function() {
                    var ekor = $('.ekor').val();
                    var h_satuan = $('.h_satuan').val();
                    var ttl_rp = parseFloat(ekor) * parseFloat(h_satuan);
                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = ttl_rp + total_kredit;

                    var totalRupiahall = ttl_rp.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    var totalkreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });


                    $('.ttl_rp').val(ttl_rp);
                    $(".total_kredit").text(totalkreditall)
                    $(".total").text(totalRupiahall);

                });
                var count = 2;
                $(document).on("click", ".tbh_pembayaran", function() {
                    count = count + 1;
                    $.ajax({
                        url: "/tbh_pembayaran?count=" + count,
                        type: "Get",
                        success: function(data) {
                            $("#load_pembayaran").append(data);
                            $(".select").select2();
                        },
                    });
                });

                $(document).on("click", ".delete_pembayaran", function() {
                    var delete_row = $(this).attr("count");
                    $(".baris_bayar" + delete_row).remove();


                    var total_all = 0;
                    $(".ttl_rpbiasa").each(function() {
                        total_all += parseFloat($(this).val());
                    });

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = total_all + total_kredit;
                    var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalKreditall);

                    var selisih = total_all + total_kredit - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    if (total_kredit + total_all === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                });
                $(document).on("keyup", ".debit", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.debit_biasa' + count).val(0)
                    } else {
                        $(this).val("Rp " + input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.debit_biasa' + count).val(input2)
                    }

                    var total_all = $('.ttl_rp').val();

                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var totalDebitall = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_debit").text(totalDebitall);

                    // selisih
                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = parseFloat(total_all) + total_kredit;
                    var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalKreditall);

                    var selisih = Math.round(parseFloat(total_all) + total_kredit) - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    // console.log(Math.round(total_all + total_kredit));
                    // console.log(total_debit);

                    if (Math.round(total_kredit + parseFloat(total_all)) === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save-modal").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save-modal").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);

                });
                $(document).on("keyup", ".kredit", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.kredit_biasa' + count).val(0)
                    } else {
                        $(this).val("Rp " + input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.kredit_biasa' + count).val(input2)
                    }

                    var total_all = $('.ttl_rp').val();


                    var total_debit = 0;
                    $(".debit_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var total_kredit = 0;
                    $(".kredit_biasa").each(function() {
                        total_kredit += parseFloat($(this).val());
                    });
                    var total_all_kredit = parseFloat(total_all) + total_kredit;
                    var totalKreditall = total_all_kredit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    $(".total_kredit").text(totalKreditall);

                    var selisih = Math.round(parseFloat(total_all) + total_kredit) - total_debit;
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });


                    if (Math.round(total_kredit + parseFloat(total_all)) === total_debit) {
                        $(".cselisih").css("color", "green");
                        $(".button-save-modal").removeAttr("hidden");
                    } else {
                        $(".cselisih").css("color", "red");
                        $(".button-save-modal").attr("hidden", true);
                    }
                    $(".selisih").text(selisih_total);


                });
                var count = 2;
                $(document).on("click", ".tambah_hrga_pakan", function() {
                    count = count + 1;
                    $.ajax({
                        url: "/tbh_stok_pakan?count=" + count,
                        type: "Get",
                        success: function(data) {
                            $("#tbh_baris_hrga_pakan").append(data);
                            $(".select").select2();
                        },
                    });
                });
                $(document).on("click", ".minus_hrga_pakan", function() {
                    var delete_row = $(this).attr("count");
                    $(".baris" + delete_row).remove();
                });
                $(document).on("click", ".edit_hrga_pakan", function() {
                    var id_harga_pakan = $(this).attr("id_harga_pakan");
                    $.ajax({
                        type: "get",
                        url: "{{ route('get_edit_hrga_pakan') }}",
                        data: {
                            id_harga_pakan: id_harga_pakan
                        },
                        success: function(response) {
                            $("#load_edit_harga_pakan").html(response)
                            $(".select").select2();
                        }
                    });

                });
            });
        </script>
        <script src="{{ asset('js') }}/stok_opname.js"></script>
    @endsection
</x-theme.app>
