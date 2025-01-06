<x-theme.app title="{{ $title }}" cont="container-fluid" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}: {{ tanggal($tgl2Tutup) }}</h6>
            </div>
            <div class="col-lg-6">

            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <form action="{{ route('penutup.saldo') }}" method="post">
            <section class="row">
                @csrf
                <div class="col-lg-6">

                </div>
                <div class="col-lg-6 mb-4">
                    <a data-bs-toggle="modal" data-bs-target="#cencel" href="#"
                        class="btn btn-primary btn-sm float-end ms-2 cencel"><i class="fas fa-history"></i> Cancel
                        Penutup</a>

                    <button type="submit" class="btn btn-primary btn-sm float-end "
                        {{ empty($aktiva) || empty($peralatan) || empty($atk) ? 'Hidden' : '' }}><i
                            class="fas fa-window-close"></i>
                        Penutup</button>


                    <a data-bs-toggle="modal" data-bs-target="#history" href="#"
                        class="btn btn-primary btn-sm float-end me-2 history"><i class="fas fa-history"></i> History</a>


                    <a data-bs-toggle="modal" data-bs-target="#akun" href="#"
                        class="btn btn-primary btn-sm float-end me-2 list_akun"><i class="fas fa-clipboard-list"></i>
                        Akun
                        <span class="badge bg-danger">{{ $total->total }}</span>
                    </a>
                </div>
                {{-- <div class="col-lg-12">
                    <div class="alert alert-danger">
                        <i class="bi bi-file-excel"></i> Saldo <b><em>{{ tanggal($tgl1Tutup) }} ~ {{ tanggal($tgl2Tutup)
                                }}</em></b>
                        Belum Di Tutup.
                    </div>
                </div> --}}
                <style>
                    .dhead {
                        background-color: #435EBE !important;
                        color: white;
                    }

                    .vertical-text-container {
                        position: relative;
                        height: 100%;
                    }

                    .vertical-text {
                        position: absolute;
                        bottom: 0;
                        left: 50%;
                        transform: translateX(-50%) rotate(-90deg);
                        white-space: nowrap;
                    }
                </style>
                <div class="col-lg-5">
                    <table class="table table-bordered" style="font-size: 8px">
                        <thead>
                            <tr>
                                <th width="5%" class="dhead">Tanggal</th>
                                <th class="dhead">Nama Akun</th>
                                <th class="dhead">Debit</th>
                                <th class="dhead">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="100">
                                    <div class="vertical-text-container">
                                        <div class="vertical-text">
                                            AKHIR PERIODE
                                        </div>
                                    </div>

                                </td>
                            </tr>
                            @foreach ($pendapatan as $no => $b)
                                <tr>

                                    <td>{{ ucwords(strtolower($b->nm_akun)) }}</td>
                                    <td align="right">Rp xxx</td>
                                    <td align="right"></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td style="padding-left: 20px;">Ikhtisar Laba Rugi</td>
                                <td align="right"></td>
                                <td align="right">Rp xxx</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <td>Ikhtisar Laba Rugi</td>
                                <td align="right">Rp xxx</td>
                                <td align="right"></td>
                            </tr>
                            @foreach ($biaya as $no => $b)
                                <tr>
                                    <td style="padding-left: 20px">{{ ucwords(strtolower($b->nm_akun)) }}</td>
                                    <td align="right"></td>
                                    <td align="right">Rp xxx</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="4" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="fw-bold"><u>Jika Terjadi Laba</u></td>
                            </tr>
                            <tr>
                                <td>Ikhtisar Laba Rugi</td>
                                <td align="right">Rp xxx</td>
                                <td align="right"></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 20px">Modal</td>
                                <td align="right"></td>
                                <td align="right">Rp xxx</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="fw-bold"><u>Jika Terjadi Rugi</u></td>
                            </tr>
                            <tr>
                                <td>Modal</td>
                                <td align="right">Rp xxx</td>
                                <td align="right"></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 20px">Ikhtisar Laba Rugi</td>
                                <td align="right"></td>
                                <td align="right">Rp xxx</td>
                            </tr>

                            <tr>
                                <td colspan="4" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <td>Modal</td>
                                <td align="right">Rp xxx</td>
                                <td align="right"></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 20px">Prive</td>
                                <td align="right"></td>
                                <td align="right">Rp xxx</td>
                            </tr>

                            <tr>
                                <td style="font-weight: bold">Jumlah Total</td>
                                <td style="font-weight: bold" align="right">Rp xxx</td>
                                <td style="font-weight: bold" align="right">Rp xxx</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-7">
                    @if (empty($aktiva) || empty($peralatan) || empty($atk))
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    {{-- <th class="dhead" width="5">#</th> --}}
                                    {{-- <th class="dhead">Kode Akun</th> --}}
                                    <th class="dhead">Tangal</th>
                                    <th width="50%" class="dhead">Akun</th>
                                    <th width="25%" class="dhead" style="text-align: right">Debit</th>
                                    <th width="25%" class="dhead" style="text-align: right">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <td colspan="4">
                                    <p class="text-center text-danger fw-bold">Jurnal penyesuaian belum dilakukan</p>
                                </td>
                            </tbody>
                        </table>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    {{-- <th class="dhead" width="5">#</th> --}}
                                    {{-- <th class="dhead">Kode Akun</th> --}}
                                    <th class="dhead">Tangal</th>
                                    <th width="50%" class="dhead">Akun</th>
                                    <th width="25%" class="dhead" style="text-align: right">Debit</th>
                                    <th width="25%" class="dhead" style="text-align: right">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- <tr>
                                <td colspan="5" class="fw-bold"></td>
                            </tr> --}}
                                <tr>
                                    <td rowspan="100">
                                        <div class="vertical-text-container">
                                            <div class="vertical-text">
                                                Periode {{ tanggal($tgl2Tutup) }}
                                            </div>
                                        </div>
                                        <input type="hidden" name="tgl" value="{{ $tgl2Tutup }}">
                                    </td>
                                </tr>
                                @php
                                    $total_pendapatan = 0;
                                @endphp
                                @foreach ($pendapatan as $no => $b)
                                    @php
                                        $total_pendapatan += $b->kredit - $b->debit;
                                    @endphp
                                    <tr>

                                        <td>
                                            {{ ucwords(strtolower($b->nm_akun)) }}
                                            <input type="hidden" name="id_akun_pembelian[]"
                                                value="{{ $b->id_akun }}">
                                        </td>
                                        <td align="right">
                                            Rp {{ number_format($b->kredit - $b->debit, 0) }}
                                            <input type="hidden" name="debit_pembelian[]"
                                                value="{{ $b->kredit - $b->debit }}">

                                        </td>
                                        <td align="right">
                                            Rp 0
                                            <input type="hidden" name="kredit_pembelian[]" value="0">
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="padding-left: 20px;">
                                        Ikhtisar Laba Rugi sda
                                        <input type="hidden" name="id_akun_pembelian[]" value="31">
                                    </td>
                                    <td align="right">Rp 0
                                        <input type="hidden" name="debit_pembelian[]" value="0">
                                    </td>
                                    <td align="right">
                                        Rp {{ number_format($total_pendapatan, 0) }}
                                        <input type="hidden" name="kredit_pembelian[]"
                                            value="{{ $total_pendapatan }}">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="fw-bold"></td>
                                </tr>

                                @php
                                    $total_biaya = 0;
                                    $laba = 0;
                                @endphp
                                @foreach ($biaya as $c)
                                    @php
                                        $total_biaya += $c->debit - $c->kredit;
                                        $laba += $c->debit;
                                    @endphp
                                @endforeach
                                <tr>
                                    <td>
                                        Ikhtisar Laba Rugi
                                        <input type="hidden" name="id_akun_biaya[]" value="31">
                                    </td>
                                    <td align="right">
                                        Rp {{ number_format($total_biaya, 0) }}
                                        <input type="hidden" name="debit_biaya[]" value="{{ $total_biaya }}">
                                    </td>
                                    <td align="right">
                                        Rp 0
                                        <input type="hidden" name="kredit_biaya[]" value="0">
                                    </td>
                                </tr>
                                @foreach ($biaya as $no => $b)
                                    <tr>
                                        <td style="padding-left: 20px">
                                            {{ ucwords(strtolower($b->nm_akun)) }}
                                            <input type="hidden" name="id_akun_biaya[]"
                                                value="{{ $b->id_akun }}">
                                        </td>
                                        <td align="right">
                                            Rp 0
                                            <input type="hidden" name="debit_biaya[]" value="0">
                                        </td>
                                        <td align="right">
                                            Rp {{ number_format($b->debit - $b->kredit, 0) }}
                                            <input type="hidden" name="kredit_biaya[]"
                                                value="{{ $b->debit - $b->kredit }}">
                                        </td>
                                    </tr>
                                @endforeach
                                @php
                                    $pen = empty($total_pendapatan) ? '0' : $total_pendapatan;
                                    $biy = empty($total_biaya) ? '0' : $total_biaya;
                                    $biy2 = empty($laba) ? '0' : $laba;
                                @endphp
                                <tr>
                                    <td colspan="4" class="fw-bold"></td>
                                </tr>
                                <input type="hidden" name="laba_independent" value="{{ $pen - $biy2 }}">
                                @if ($pen - $biy > 0)
                                    <tr>
                                        <td>
                                            Ikhtisar Laba Rugi
                                            <input type="hidden" name="id_akun_modal[]" value="31">
                                        </td>
                                        <td align="right">
                                            Rp {{ number_format($pen - $biy, 0) }}
                                            <input type="hidden" name="debit_modal[]" value="{{ $pen - $biy }}">
                                        </td>
                                        <td align="right">
                                            Rp 0
                                            <input type="hidden" name="kredit_modal[]" value="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 20px">
                                            Laba Berjalan
                                            <input type="hidden" name="id_akun_modal[]" value="95">
                                        </td>
                                        <td align="right">
                                            Rp 0
                                            <input type="hidden" name="debit_modal[]" value="0">
                                        </td>
                                        <td align="right">
                                            Rp {{ number_format($pen - $biy, 0) }}
                                            <input type="hidden" name="kredit_modal[]" value="{{ $pen - $biy }}">
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td>
                                            Laba Berjalan
                                            <input type="hidden" name="id_akun_modal[]" value="95">
                                        </td>
                                        <td align="right">
                                            Rp {{ number_format(($pen - $biy) * -1, 0) }}
                                            <input type="hidden" name="debit_modal[]"
                                                value="{{ ($pen - $biy) * -1 }}">
                                        </td>
                                        <td align="right">
                                            Rp 0
                                            <input type="hidden" name="kredit_modal[]" value="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 20px">
                                            Ikhtisar Laba Rugi
                                            <input type="hidden" name="id_akun_modal[]" value="31">
                                        </td>
                                        <td align="right">
                                            Rp 0
                                            <input type="hidden" name="debit_modal[]" value="0">
                                        </td>
                                        <td align="right">
                                            Rp {{ number_format(($pen - $biy) * -1, 0) }}
                                            <input type="hidden" name="kredit_modal[]"
                                                value="{{ ($pen - $biy) * -1 }}">
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="fw-bold"></td>
                                </tr>
                                <tr>
                                    <td>Laba Berjalan</td>
                                    <td align="right"><input type="text" readonly class="form-control modal_1">
                                    </td>
                                    <td align="right">Rp 0</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 20px">Prive</td>
                                    <td align="right">Rp 0</td>
                                    <td align="right">
                                        <input type="text" class="form-control prive">
                                        <input type="hidden" name="prive_biasa" class="form-control prive_biasa"
                                            value="0">
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-weight: bold">Jumlah Total
                                        <input type="hidden" class="form-control total_penutup"
                                            value="{{ $pen - $biy + ($pen - $biy) }}">
                                    </td>
                                    <td align="right" class="total_all fw-bold">Rp
                                        {{ $pen - $biy < 0
                                            ? number_format(($pen - $biy + ($pen - $biy)) * -1, 2, ',', '.')
                                            : number_format($pen - $biy + ($pen - $biy), 2, ',', '.') }}
                                    </td>
                                    <td align="right" class="total_all fw-bold">Rp
                                        {{ $pen - $biy < 0
                                            ? number_format(($pen - $biy + ($pen - $biy)) * -1, 2, ',', '.')
                                            : number_format($pen - $biy + ($pen - $biy), 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                </div>

            </section>
        </form>

        {{-- <form action="" method="get">
            <x-theme.modal title="Filter Buku" idModal="view">
                <div class="row">
                    <div class="col-lg-12">
                        <table width="100%" cellpadding="10px">
                            <tr>
                                <td><label for="">&nbsp;</label> <br>Tanggal</td>
                                <td>
                                    <label for="">Dari</label>
                                    <input type="date" name="tgl1" class="form-control">
                                </td>
                                <td>
                                    <label for="">Sampai</label>
                                    <input type="date" name="tgl2" class="form-control">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </x-theme.modal>
        </form> --}}



        <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <h5 class="text-danger ms-4 mt-4"><i class="fas fa-window-close"></i> Gagal
                            </h5>
                            <p class=" ms-4 mt-4">Silahkan lakukan penyesuaian bulan
                                <b>{{ date(
                                    'F
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    Y',
                                    strtotime($tgl2Tutup),
                                ) }}</b>
                                terlebih dahulu !!
                            </p>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>

                    </div>
                </div>
            </div>
        </div>


        {{-- history --}}
        <form action="" method="get">
            <x-theme.modal title="History Penutup" size="modal-lg" btnSave="" idModal="history">
                <div id="load-history"></div>
            </x-theme.modal>
        </form>

        <form action="{{ route('penutup.edit_akun') }}" method="post">
            @csrf
            <x-theme.modal title="Akun" size="modal-lg" btnSave="" idModal="akun">
                <div id="load-akun"></div>
            </x-theme.modal>

        </form>
        <form action="{{ route('penutup.cancel_penutup') }}" method="post">
            @csrf
            <x-theme.modal title="Cancel Jurnal Penutup" size="modal-sm" btnSave="Y" idModal="cencel">
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="dhead text-center">Bulan & Tahun</th>
                                    <th class="text-center dhead">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cancel as $c)
                                    <tr>
                                        <td class="text-center">{{ date('M-Y', strtotime($c->tgl)) }}</td>
                                        <td class="text-center"><input type="radio" name="tgl1" id=""
                                                value="{{ $c->tgl }}">
                                        </td>
                                    </tr>
                                @endforeach
                                @php
                                    $tgl_akhir_penutup = empty($c->tgl) ? '0' : $c->tgl;
                                @endphp
                            </tbody>

                        </table>
                        <input type="hidden" name="tgl2" value="{{ $tgl_akhir_penutup }}">
                    </div>
                </div>
            </x-theme.modal>

        </form>
        {{-- end history --}}
    </x-slot>
    @section('scripts')
        <script>
            // Menangani event klik pada setiap baris dan mengarahkan pengguna ke URL yang sesuai
            document.querySelectorAll('tbody .tbl').forEach(function(row) {
                row.addEventListener('click', function() {
                    window.location.href = row.getAttribute('data-href');
                });
            });

            $(document).on('click', '.history', function() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('penutup.history') }}",
                    success: function(r) {
                        $("#load-history").html(r);
                    }
                });
            });
            $(document).on('click', '.list_akun', function() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('penutup.akun') }}",
                    success: function(r) {
                        $("#load-akun").html(r);
                        $('#tableScroll').DataTable({
                            "searching": true,
                            scrollY: '400px',
                            scrollX: true,
                            scrollCollapse: true,
                            "autoWidth": true,
                            "paging": false,
                        });
                    }
                });
            });

            $(document).on('click', '.iktisar1', function() {
                var urutan = $(this).attr('urutan');
                if ($(this).is(":checked")) {
                    $('.hasil_iktisar' + urutan).val('Y')
                    $('.iktisarB' + urutan).prop("checked", false);
                } else {
                    $('.hasil_iktisar' + urutan).val('T')
                }
            });
            $(document).on('click', '.iktisar2', function() {
                var urutan = $(this).attr('urutanB');
                if ($(this).is(":checked")) {
                    $('.hasil_iktisar' + urutan).val('H')
                    $('.iktisarA' + urutan).prop("checked", false);
                } else {
                    $('.hasil_iktisar' + urutan).val('T')
                }
            });


            $(document).ready(function() {
                $(document).on("keyup", ".prive", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");


                    if (input === "") {
                        $(this).val("");
                        $('.modal_1').val("");
                        $('.prive_biasa').val(0)
                    } else {
                        $(this).val("Rp " + input);
                        $('.modal_1').val("Rp " + input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.prive_biasa').val(input2)

                    }

                    var prive = $(".prive_biasa").val();
                    var total = $(".total_penutup").val();
                    var total_all = parseFloat(prive) + parseFloat(total);
                    var totalRupiah = total_all.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });
                    var debit = $(".total_all").text(totalRupiah);
                });



            });
        </script>
        <!-- Tambahkan kode JavaScript di bawah ini -->
        <!-- Tambahkan kode JavaScript di bawah ini -->
    @endsection

</x-theme.app>
