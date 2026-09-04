<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h5 class="mb-0">{{ $title }}</h5>
            </div>
            <div class="col-lg-6 text-lg-end mt-2 mt-lg-0">
                <x-theme.button modal="T" href="/produk_telur" icon="fa-home" addClass="float-lg-end" teks="" />
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .mtd-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}
            .mtd-summary-card{padding:14px 16px;border:1px solid #dce4f2;border-radius:12px;background:#f7f9fd;display:flex;flex-direction:column;gap:2px}
            .mtd-summary-card small{color:#71809a;font-weight:600}
            .mtd-summary-card strong{color:#18366f;font-size:19px}
            .mtd-filter{padding:14px;border:1px solid #dce4f2;border-radius:12px;background:#fff}
            .mtd-filter label{font-size:12px;font-weight:700;color:#52627a}
            .mtd-table-wrap{overflow:auto;max-height:62vh;border:1px solid #dce4f2;border-radius:12px}
            .mtd-table{margin:0;font-size:13px}
            .mtd-table thead th{position:sticky;top:0;z-index:2;padding:12px 10px;background:#304f9e!important;color:#fff!important;border-color:#4966ad;vertical-align:middle;white-space:nowrap}
            .mtd-table tbody td{padding:11px 10px;vertical-align:middle;border-color:#e8edf5}
            .mtd-table tbody tr:hover td{background:#f2f6ff}
            .mtd-table thead th:last-child{position:sticky;right:0;z-index:5;background:#304f9e!important;box-shadow:-5px 0 10px rgba(24,54,111,.14)}
            .mtd-table tbody td:last-child{position:sticky;right:0;z-index:1;background:#fff;box-shadow:-5px 0 10px rgba(24,54,111,.10);white-space:nowrap}
            .mtd-table tbody tr:nth-of-type(odd) td:last-child{background:#f7f8fb}
            .mtd-table tbody tr:hover td:last-child{background:#f2f6ff}
            .mtd-nota{font-weight:700;color:#294a97;white-space:nowrap}
            .mtd-customer{display:block;margin-top:3px;color:#6f7d91;font-size:12px}
            .mtd-qty{display:block;text-align:right;white-space:nowrap;line-height:1.55}
            .mtd-qty strong{color:#243b6b}
            .mtd-pagination .pagination{margin-bottom:0}
            @media(max-width:767px){.mtd-summary{grid-template-columns:1fr}.mtd-table-wrap{max-height:70vh}}
        </style>
        <section>
            <div class="mtd-summary">
                <div class="mtd-summary-card"><small>Jumlah nota ditemukan</small><strong>{{ number_format($invoice->total(), 0, ',', '.') }}</strong></div>
                <div class="mtd-summary-card"><small>Total penjualan</small><strong>Rp {{ number_format($ttlRp, 0, ',', '.') }}</strong></div>
                <div class="mtd-summary-card"><small>Total belum dicek</small><strong>Rp {{ number_format($ttlRpBelumDiCek, 0, ',', '.') }}</strong></div>
            </div>
            <form method="GET" action="{{ route('penjualan_martadah_cek') }}" class="mtd-filter row g-2 align-items-end mb-3">
                <input type="hidden" name="lokasi" value="mtd">
                <input type="hidden" name="period" value="costume">
                <div class="col-md-3">
                    <label class="form-label">Dari tanggal</label>
                    <input type="date" name="tgl1" value="{{ $tgl1 }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai tanggal</label>
                    <input type="date" name="tgl2" value="{{ $tgl2 }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cari nomor nota atau pelanggan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="search" name="pencarian" value="{{ $pencarian }}" class="form-control"
                            placeholder="Masukkan nomor nota atau nama pelanggan">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data per halaman</label>
                    <select name="per_page" class="form-select">
                        @foreach ([20, 50, 100] as $jumlah)
                            <option value="{{ $jumlah }}" @selected($perPage === $jumlah)>{{ $jumlah }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i> Tampilkan</button>
                    @if ($pencarian !== '')
                        <a class="btn btn-outline-secondary" title="Hapus pencarian"
                            href="{{ route('penjualan_martadah_cek', ['lokasi' => 'mtd', 'period' => 'costume', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'per_page' => $perPage]) }}"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </form>
            <div class="mtd-table-wrap">
            <table class="table table-hover table-striped mtd-table" style="min-width: {{ 720 + ($produk->count() * 150) }}px">
                <thead>
                    <tr>
                        <th style="width:45px" class="text-center">No</th>
                        <th style="width:105px">Tanggal</th>
                        <th style="min-width:230px">Nota dan pelanggan</th>
                        <th style="min-width:140px" class="text-end">Total</th>
                        @foreach ($produk as $p)
                            <th class="text-end" style="min-width:140px">{{ ucwords($p->nm_telur) }}</th>
                        @endforeach
                        <th style="min-width:100px" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoice as $no => $i)
                        <tr>
                            <td class="text-center text-muted">{{ ($invoice->firstItem() ?? 1) + $no }}</td>
                            <td class="text-nowrap">{{ tanggal($i->tgl) }}</td>
                            <td>
                                <span class="mtd-nota">{{ $i->no_nota }}</span>
                                <span class="mtd-customer">{{ $i->customer ?: ($i->nm_customer ?: '-') }}@if($i->customer && $i->nm_customer) ({{ $i->nm_customer }})@endif</span>
                            </td>
                            <td class="text-end fw-bold text-nowrap">Rp {{ number_format($i->ttl_rp, 0, ',', '.') }}</td>
                            @foreach ($produk as $p)
                                @php($telurDetail = $detailProduk->get($i->no_nota . '|' . $p->id_produk_telur))
                                <td><span class="mtd-qty"><strong>{{ number_format($telurDetail->pcs ?? 0, 0, ',', '.') }}</strong> Pcs<br><strong>{{ number_format($telurDetail->kg ?? 0, 1, ',', '.') }}</strong> Kg</span></td>
                            @endforeach
                            <td class="text-center">
                                @if ($i->cek == 'Y')
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i> Dicek</span>
                                        <a href="{{ route('terima_invoice_mtd', ['no_nota' => $i->no_nota]) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit setoran"><i class="fas fa-edit"></i> Edit</a>
                                    </div>
                                @else
                                    <a href="{{ route('terima_invoice_mtd', ['no_nota' => $i->no_nota]) }}"
                                        class="btn btn-sm btn-primary"><i class="fas fa-plus"></i>
                                        Setor</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 5 + $produk->count() }}" class="text-center text-muted py-4">Data penjualan tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="mtd-pagination d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <small class="text-muted">Menampilkan {{ $invoice->firstItem() ?? 0 }}–{{ $invoice->lastItem() ?? 0 }} dari {{ number_format($invoice->total()) }} nota</small>
                {{ $invoice->onEachSide(1)->links('pagination::bootstrap-5') }}
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
