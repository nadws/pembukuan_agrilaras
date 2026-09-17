<x-theme.app title="{{ $title }}" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Laporan Pendapatan</h5>
                <small class="text-muted">Gabungan penjualan telur, umum, dan ayam · {{ \Carbon\Carbon::parse($tanggalAwal)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}</small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if(!empty($btnExport))
                <a href="{{ route('laporan.pendapatan.export', request()->only(['tanggal_awal', 'tanggal_akhir', 'kategori', 'lokasi', 'pembayaran'])) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
                @endif
                <a href="{{ route('laporan') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Daftar Laporan</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            html,body{overflow-x:hidden!important}.container-fluid{max-width:100vw;overflow-x:hidden}.income-filter{max-width:100%;padding:14px;border:1px solid #dce4f2;border-radius:12px;background:#f7f9fd}.income-kpi{height:100%;padding:14px 16px;border:1px solid #e1e7f2;border-radius:12px;background:#fff;box-shadow:0 5px 15px rgba(35,60,115,.06)}.income-kpi small{display:block;color:#7583a0;font-size:10px;font-weight:700;text-transform:uppercase}.income-kpi strong{display:block;margin-top:5px;color:#18366f;font-size:19px}.income-table{max-width:100%;overflow:auto;border:1px solid #dce4f2;border-radius:13px;max-height:560px}.income-table table{margin:0;width:100%;min-width:0;table-layout:auto;font-size:12px}.income-table thead th{padding:8px 6px;background:#304f9e;color:#fff;font-size:11px;white-space:normal;vertical-align:middle;position:sticky;top:0;z-index:2}.income-table td{padding:7px 6px;vertical-align:middle;word-break:break-word}.income-table:not(.summary-table) tbody td:nth-child(-n+6),.income-table:not(.summary-table) thead th:nth-child(-n+6){white-space:nowrap}.income-table:not(.summary-table) tbody td:nth-child(4),.income-table:not(.summary-table) thead th:nth-child(4){min-width:92px}.income-table .fw-semibold{white-space:nowrap}.income-table tbody tr:nth-child(even){background:#f8fafd}.income-table tfoot td{position:sticky;bottom:0;z-index:2;background:#eef3ff}.income-table .badge{font-size:10px;white-space:nowrap}.income-table small{font-size:11px;line-height:1.4;display:block}.summary-table thead th{background:#198754}.summary-table tfoot td{background:#e9f9f0}.section-title{margin:0;font-size:14px;font-weight:700;color:#18366f}.section-sub{font-size:11px}.amount{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.income-pagination nav{display:flex;justify-content:flex-end}.income-pagination .pagination{margin:0;gap:4px;flex-wrap:wrap}.income-pagination .page-link{display:flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border:1px solid #dce4f1;border-radius:7px!important;color:#304f9e;font-size:13px;font-weight:500;text-decoration:none}.income-pagination .page-item.active .page-link{background-color:#304f9e;border-color:#304f9e;color:#fff;font-weight:700}.income-pagination .page-item.disabled .page-link{color:#9aa8bd;background-color:#f8fafc;border-color:#e2e8f0}.row{max-width:100%;}@media(max-width:575px){.income-filter .btn{width:100%}}
        </style>
        @php $fmt=fn($value)=>'Rp '.number_format((float)$value,0,'.',','); @endphp
        <form method="get" class="income-filter mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label fw-semibold">Tanggal awal</label><input type="date" name="tanggal_awal" value="{{ $tanggalAwal }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Tanggal akhir</label><input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Tipe</label><select name="kategori" class="form-select"><option value="" @selected($kategori==='')>Semua</option><option value="telur" @selected($kategori==='telur')>Telur</option><option value="umum" @selected($kategori==='umum')>Umum</option><option value="ayam" @selected($kategori==='ayam')>Ayam</option></select></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Lokasi</label><select name="lokasi" class="form-select"><option value="" @selected(($lokasi ?? '')==='')>Semua</option><option value="alpa" @selected(($lokasi ?? '')==='alpa')>BJM</option><option value="mtd" @selected(($lokasi ?? '')==='mtd')>MTD</option></select></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Pembayaran</label><select name="pembayaran[]" class="form-select select2" multiple data-placeholder="Semua">@foreach($akunPembayaran as $akun)<option value="{{ $akun->id_akun_perkiraan }}" @selected(in_array((int)$akun->id_akun_perkiraan, $pembayaranIds ?? []))>{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>@endforeach</select></div>
                <div class="col-md-1"><label class="form-label fw-semibold">Baris</label><select name="per_page" class="form-select">@foreach([25,50,100] as $size)<option value="{{ $size }}" @selected($perPage===$size)>{{ $size }}</option>@endforeach</select></div>
                <div class="col-md-1"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button></div>
            </div>
        </form>
        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="income-kpi"><small>Pendapatan Telur</small><strong>{{ $fmt($totals['telur']) }}</strong></div></div>
            <div class="col-md-3"><div class="income-kpi"><small>Pendapatan Umum</small><strong>{{ $fmt($totals['umum']) }}</strong></div></div>
            <div class="col-md-3"><div class="income-kpi"><small>Pendapatan Ayam</small><strong>{{ $fmt($totals['ayam']) }}</strong></div></div>
            <div class="col-md-3"><div class="income-kpi"><small>Total Pendapatan</small><strong>{{ $fmt($totals['grand']) }}</strong></div></div>
        </div>
        <div class="row g-3">
            <div class="col-lg-6">
            <h6 class="section-title">Detail per Nota</h6>
            <small class="text-muted d-block mb-2 section-sub">Daftar nota telur, umum, dan ayam sesuai filter.</small>
        <div class="income-table table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>No</th><th>Tipe</th><th>Tanggal</th><th>Invoice</th><th>Lokasi</th><th>Customer</th><th class="amount">Total Rupiah</th><th>Bayar Via</th></tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $rows->firstItem()+$loop->index }}</td>
                            <td>{{ ucfirst($row['kategori']) }}</td>
                            <td>{{ \Carbon\Carbon::parse($row['tgl'])->format('d-m-Y') }}</td>
                            <td class="fw-semibold">{{ $row['no_nota'] }}</td>
                            <td>{{ $row['lokasi'] ?? '-' }}</td>
                            <td>{{ $row['customer'] }}</td>
                            <td class="amount">{{ $fmt($row['total']) }}</td>
                            <td><small class="text-muted">{{ $row['pembayaran'] ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada pendapatan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->total() > 0)
                <tfoot>
                    <tr class="fw-bold table-light"><td colspan="6" class="text-end">Total</td><td class="amount">{{ $fmt($totals['grand']) }}</td><td></td></tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
            <small class="text-muted">Menampilkan {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }} nota</small>
            <div class="income-pagination">{{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
        </div>
            </div>
            <div class="col-lg-6">
            <h6 class="section-title">Rangkuman Telur</h6>
            <small class="text-muted d-block mb-2 section-sub">Klik Detail untuk rincian per nota — selisih ≥ Rp10.000 dari rata-rata global ditandai merah.</small>
            <div class="income-table summary-table table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>No</th><th>Produk</th><th>Tipe</th><th class="amount">Pcs</th><th class="amount">Kg Jual</th><th class="amount">Qty Setara (Kg)</th><th class="amount">Rata-rata</th><th class="amount">Total Rupiah</th><th></th></tr>
                    </thead>
                    <tbody>
                        @if(!empty($telurSummary))
                            <tr>
                                <td>1</td>
                                <td class="fw-semibold">{{ $telurSummary['produk'] }}</td>
                                <td>{{ ucfirst($telurSummary['tipe']) }}</td>
                                <td class="amount">{{ number_format($telurSummary['pcs'], 0, '.', ',') }}</td>
                                <td class="amount">{{ number_format($telurSummary['kg'], 2, '.', ',') }}</td>
                                <td class="amount">{{ number_format($telurSummary['qty_setara'], 2, '.', ',') }}</td>
                                <td class="amount">{{ $fmt($telurSummary['rata2']) }}<small class="text-muted">/{{ $telurSummary['satuan'] }}</small></td>
                                <td class="amount">{{ $fmt($telurSummary['total']) }}</td>
                                <td><button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalDetailTelur"><i class="fas fa-eye me-1"></i>Detail</button></td>
                            </tr>
                        @else
                            <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada penjualan telur pada periode ini.</td></tr>
                        @endif
                    </tbody>
                    @if(!empty($telurSummary))
                    <tfoot>
                        <tr class="fw-bold table-light"><td colspan="7" class="text-end">Total Telur</td><td class="amount">{{ $fmt($telurSummary['total']) }}</td><td></td></tr>
                    </tfoot>
                    @endif
                </table>
                <small class="text-muted d-block mt-1 section-sub">Qty Setara: penjualan PCS dikonversi 1 butir = 63 gram agar rata-rata Rp/Kg sebanding dengan penjualan KG.</small>
            </div>
            <div class="modal fade" id="modalDetailTelur" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Rincian Penjualan Telur per Nota</h5>
                                @if(!empty($telurSummary) && $telurSummary['rata2'] > 0)
                                <small class="text-muted">Rata-rata global: {{ $fmt($telurSummary['rata2']) }}/kg — baris merah = selisih ≥ Rp10.000 dari rata-rata global.</small>
                                @endif
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @php $global = !empty($telurSummary) ? (float) $telurSummary['rata2'] : 0; $batasSelisih = 10000; @endphp
                            <div class="income-table table-responsive" style="max-height:60vh">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr><th>No</th><th>Nota</th><th>Tanggal</th><th>Lokasi</th><th>Customer</th><th>Jual</th><th class="amount">Pcs</th><th class="amount">Kg</th><th class="amount">Qty Setara</th><th class="amount">Total</th><th class="amount">Rata-rata</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($telurDetail as $d)
                                            @php
                                                $merah = $global > 0 && abs($d['rata2'] - $global) >= $batasSelisih;
                                            @endphp
                                            <tr @if($merah) class="table-danger" @endif>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="fw-semibold">{{ $d['no_nota'] }}</td>
                                                <td>{{ \Carbon\Carbon::parse($d['tgl'])->format('d-m-Y') }}</td>
                                                <td>{{ $d['lokasi'] }}</td>
                                                <td>{{ $d['customer'] }}</td>
                                                <td>{{ $d['tipe_jual'] }}</td>
                                                <td class="amount">{{ number_format($d['pcs'], 0, '.', ',') }}</td>
                                                <td class="amount">{{ number_format($d['kg'], 2, '.', ',') }}</td>
                                                <td class="amount">{{ number_format($d['qty_setara'], 2, '.', ',') }}</td>
                                                <td class="amount">{{ $fmt($d['total']) }}</td>
                                                <td class="amount">{{ $fmt($d['rata2']) }}</td>
                                                <td><button type="button" class="btn btn-sm btn-outline-primary btn-komponen-nota" data-no-nota="{{ $d['no_nota'] }}" data-lokasi="{{ $d['lokasi_raw'] }}" data-bs-toggle="modal" data-bs-target="#modalKomponenNota" title="Lihat komponen invoice"><i class="fas fa-eye"></i></button></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="12" class="text-center text-muted py-4">Tidak ada rincian nota telur.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalKomponenNota" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Komponen Invoice <span id="komponenNotaJudul"></span></h5>
                                <small class="text-muted" id="komponenNotaSub"></small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="income-table table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr><th>No</th><th>Produk</th><th>Jual</th><th class="amount">Pcs</th><th class="amount">Kg Kotor</th><th class="amount">Kg Jual</th><th class="amount">Ikat</th><th class="amount">Rp Satuan</th><th class="amount">Qty Setara (Kg)</th><th class="amount">Rata-rata</th><th class="amount">Total</th></tr>
                                    </thead>
                                    <tbody id="komponenNotaBody">
                                        <tr><td colspan="11" class="text-center text-muted py-4">Memuat...</td></tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold table-light"><td colspan="10" class="text-end">Total</td><td class="amount" id="komponenNotaTotal"></td></tr>
                                    </tfoot>
                                </table>
                                <small class="text-muted d-block mt-1 section-sub">Baris PCS dikonversi 1 butir = 63 gram dulu baru dihitung rata-rata Rp/Kg-nya.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                var rupiah = function (v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID', {maximumFractionDigits: 0}); };
                var angka = function (v, d) { return Number(v || 0).toLocaleString('id-ID', {minimumFractionDigits: d, maximumFractionDigits: d}); };
                document.querySelectorAll('.btn-komponen-nota').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var noNota = btn.getAttribute('data-no-nota');
                        var lokasi = btn.getAttribute('data-lokasi');
                        document.getElementById('komponenNotaJudul').textContent = noNota;
                        document.getElementById('komponenNotaSub').textContent = 'Memuat...';
                        document.getElementById('komponenNotaBody').innerHTML = '<tr><td colspan="11" class="text-center text-muted py-4">Memuat...</td></tr>';
                        document.getElementById('komponenNotaTotal').textContent = '';
                        fetch("{{ route('laporan.pendapatan.detail-nota') }}?no_nota=" + encodeURIComponent(noNota) + "&lokasi=" + encodeURIComponent(lokasi), {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                            .then(function (res) { if (!res.ok) { throw new Error('Gagal memuat (' + res.status + ')'); } return res.json(); })
                            .then(function (data) {
                                document.getElementById('komponenNotaSub').textContent = (data.tgl || '') + ' · ' + (data.lokasi || '') + ' · ' + (data.customer || '');
                                var html = '';
                                (data.lines || []).forEach(function (l, i) {
                                    html += '<tr><td>' + (i + 1) + '</td><td class="fw-semibold">' + l.produk + '</td><td>' + l.tipe + '</td>'
                                        + '<td class="amount">' + angka(l.pcs, 0) + '</td><td class="amount">' + angka(l.kg, 2) + '</td>'
                                        + '<td class="amount">' + angka(l.kg_jual, 2) + '</td><td class="amount">' + angka(l.ikat, 2) + '</td>'
                                        + '<td class="amount">' + rupiah(l.rp_satuan) + '</td>'
                                        + '<td class="amount">' + angka(l.qty_setara, 2) + '</td><td class="amount">' + rupiah(l.rata2) + '</td>'
                                        + '<td class="amount">' + rupiah(l.total) + '</td></tr>';
                                });
                                if (!html) { html = '<tr><td colspan="11" class="text-center text-muted py-4">Tidak ada baris invoice.</td></tr>'; }
                                document.getElementById('komponenNotaBody').innerHTML = html;
                                document.getElementById('komponenNotaTotal').textContent = rupiah(data.total);
                            })
                            .catch(function (err) {
                                document.getElementById('komponenNotaSub').textContent = '';
                                document.getElementById('komponenNotaBody').innerHTML = '<tr><td colspan="11" class="text-center text-danger py-4">' + err.message + '</td></tr>';
                            });
                    });
                });
            });
            </script>
            <h6 class="section-title mt-3">Rangkuman per Produk</h6>
            <small class="text-muted d-block mb-2 section-sub">Produk selain telur — mengikuti filter di atas.</small>
            <div class="income-table summary-table table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>No</th><th>Produk</th><th>Tipe</th><th class="amount">Pcs</th><th class="amount">Kg Jual</th><th class="amount">Qty Setara (Kg)</th><th class="amount">Rata-rata</th><th class="amount">Total Rupiah</th></tr>
                    </thead>
                    <tbody>
                        @forelse($summaryLain as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $item['produk'] }}</td>
                                <td>{{ ucfirst($item['tipe']) }}</td>
                                <td class="amount">{{ number_format($item['pcs'], 0, '.', ',') }}</td>
                                <td class="amount">{{ number_format($item['kg'], 2, '.', ',') }}</td>
                                <td class="amount">{{ $item['qty_setara'] === null ? '-' : number_format($item['qty_setara'], 2, '.', ',') }}</td>
                                <td class="amount">{{ $fmt($item['rata2']) }}<small class="text-muted">/{{ $item['satuan'] }}</small></td>
                                <td class="amount">{{ $fmt($item['total']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada rangkuman produk lain pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                    @if($summaryLain->isNotEmpty())
                    <tfoot>
                        <tr class="fw-bold table-light"><td colspan="7" class="text-end">Total Rangkuman</td><td class="amount">{{ $fmt($summaryLain->sum('total')) }}</td></tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            <h6 class="section-title mt-3">Total per Pembayaran</h6>
            <small class="text-muted d-block mb-2 section-sub">Mengikuti filter di atas — totalnya harus klop dengan total pendapatan.</small>
            <div class="income-table summary-table table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>No</th><th>Pembayaran</th><th class="amount">Total Rupiah</th></tr>
                    </thead>
                    <tbody>
                        @forelse($paySummary as $pay)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $pay['pembayaran'] }}</td>
                                <td class="amount">{{ $fmt($pay['total']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada data pembayaran pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                    @if($paySummary->isNotEmpty())
                    <tfoot>
                        <tr class="fw-bold table-light"><td colspan="2" class="text-end">Total Pembayaran</td><td class="amount">{{ $fmt($paySummary->sum('total')) }}</td></tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            </div>
        </div>
    </x-slot>
</x-theme.app>
