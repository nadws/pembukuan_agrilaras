<x-theme.app title="Riwayat Stok Persediaan">
    <x-slot name="slot">
        <style>
            .stock-history{max-width:1240px;margin:0 auto;padding:4px 4px 34px}.history-panel{overflow:hidden;border:1px solid #e0e7f3;border-radius:16px;background:#fff;box-shadow:0 10px 28px rgba(31,58,112,.07)}.history-head{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:24px 26px;border-bottom:1px solid #edf1f7}.history-head h3{margin:0 0 5px;color:#17356f;font-size:24px;font-weight:750}.history-head p{margin:0;color:#77849a;font-size:13px}.history-filter{margin:20px 24px 0;padding:16px;border:1px solid #dce5f4;border-radius:12px;background:#f7f9fd}.history-filter label{display:block;margin-bottom:7px;color:#50617c;font-size:12px;font-weight:700}.history-filter .form-control,.history-filter .btn{min-height:44px}.stock-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;padding:16px 24px}.summary-item{padding:14px 16px;border:1px solid #e0e7f3;border-radius:11px;background:#fff}.summary-item small{display:block;margin-bottom:5px;color:#7c899d;font-size:11px;font-weight:700;text-transform:uppercase}.summary-item strong{color:#17356f;font-size:18px}.summary-item--end{border-color:#b8c8eb;background:#f4f7ff}.history-table-wrap{margin:0 24px;overflow-x:auto;border:1px solid #dfe6f1;border-radius:12px}.history-table{min-width:940px;margin:0}.history-table thead th{padding:13px 14px;border:0;background:#304f9e;color:#fff;font-size:11px;font-weight:750;text-transform:uppercase;white-space:nowrap}.history-table tbody td{padding:12px 14px;border-color:#e9edf4;color:#42516a;vertical-align:middle}.history-table tbody tr:hover td{background:#f5f8ff}.opening-row td{background:#eef3ff!important;color:#234584!important;font-weight:700}.transaction-number{color:#294b94;font-weight:700}.movement-badge{display:inline-flex;min-width:94px;align-items:center;justify-content:center;gap:5px;padding:5px 9px;border-radius:999px;font-size:11px;font-weight:700}.movement-in{background:#dcf5e6;color:#177544}.movement-out{background:#fff0d8;color:#93600b}.movement-adjust{background:#e9efff;color:#3155a3}.qty-value,.balance-value{color:#17356f!important;font-weight:750;white-space:nowrap}.balance-value{background:#fafbfe}.history-footer{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 24px 22px}.history-footer-info{color:#77849a;font-size:12px}.history-footer .pagination{flex-wrap:wrap;gap:4px;margin:0}.history-footer .page-link{display:flex;min-width:34px;height:34px;align-items:center;justify-content:center;padding:0 9px;border:1px solid #dce4f1;border-radius:7px!important;color:#304f9e;font-size:12px}.history-footer .page-item.active .page-link{border-color:#304f9e;background:#304f9e;color:#fff}.history-empty{padding:52px 16px!important;color:#8692a5!important;text-align:center}@media(max-width:767.98px){.stock-history{padding:0 0 24px}.history-head{align-items:flex-start;padding:19px 17px}.history-head h3{font-size:20px}.history-filter{margin:14px 14px 0;padding:13px}.stock-summary{grid-template-columns:repeat(2,minmax(0,1fr));padding:14px}.history-table-wrap{margin:0 14px}.history-footer{align-items:flex-start;flex-direction:column;padding:15px 14px 19px}}@media(max-width:420px){.stock-summary{grid-template-columns:1fr}}
        </style>

        <style>.history-table tfoot td{padding:14px;border-top:2px solid #304f9e;background:#eef3ff;color:#17356f;font-weight:750;vertical-align:middle}.history-table tfoot .total-period-label{text-align:right;text-transform:uppercase;letter-spacing:.3px}.transaction-number{display:block}.product-code{display:block;max-width:250px;margin-top:3px;overflow:hidden;color:#8a96a9;font-size:10px;text-overflow:ellipsis;white-space:nowrap}</style>

        @php
            $kategoriKembali = in_array($produk->kategori, ['pakan', 'vaksin'], true) ? $produk->kategori : 'vitamin';
            $satuan = $produk->nm_satuan ?: '';
        @endphp
        <section class="stock-history">
            <div class="history-panel">
                <header class="history-head">
                    <div><h3>{{ $produk->nm_produk }}</h3><p>Kartu stok pembelian, pemakaian, dan saldo berjalan.</p></div>
                    <a href="{{ route('laporan.stok-persediaan', ['kategori'=>$kategoriKembali]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                </header>

                <form method="get" class="history-filter">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4"><label for="tgl1">Dari tanggal</label><input id="tgl1" type="date" name="tgl1" value="{{ $tgl1 }}" class="form-control" required></div>
                        <div class="col-md-4"><label for="tgl2">Sampai tanggal</label><input id="tgl2" type="date" name="tgl2" value="{{ $tgl2 }}" class="form-control" required></div>
                        <div class="col-md-4"><button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan Periode</button></div>
                    </div>
                </form>

                <div class="stock-summary">
                    <div class="summary-item"><small>Saldo awal</small><strong>{{ number_format($saldoAwal,2,',','.') }} {{ $satuan }}</strong></div>
                    <div class="summary-item"><small>Total pembelian</small><strong>{{ number_format($totalPembelian,2,',','.') }} {{ $satuan }}</strong></div>
                    <div class="summary-item"><small>Total pemakaian</small><strong>{{ number_format($totalPemakaian,2,',','.') }} {{ $satuan }}</strong></div>
                    <div class="summary-item summary-item--end"><small>Saldo akhir</small><strong>{{ number_format($saldoAkhir,2,',','.') }} {{ $satuan }}</strong></div>
                </div>

                <div class="history-table-wrap">
                    <table class="table table-hover history-table">
                        <thead><tr><th>Tanggal</th><th>No. Transaksi</th><th>Jenis</th><th class="text-end">Pembelian</th><th class="text-end">Pemakaian</th><th class="text-end">Saldo</th><th>Kandang</th><th class="text-end">Ekor Ayam</th><th>Admin</th></tr></thead>
                        <tbody>
                            @if($detail->currentPage() === 1)
                                <tr class="opening-row"><td>{{ \Carbon\Carbon::parse($tgl1)->format('d/m/Y') }}</td><td>-</td><td><span class="movement-badge movement-adjust">Saldo Awal</span></td><td class="text-end">-</td><td class="text-end">-</td><td class="text-end">{{ number_format($saldoAwal,2,',','.') }}</td><td>-</td><td class="text-end">-</td><td>-</td></tr>
                            @endif
                            @forelse($detail as $row)
                                @php $masuk=(float)$row->pcs; $keluar=(float)$row->pcs_kredit; @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row->tgl)->format('d/m/Y') }}</td>
                                    <td><span class="transaction-number">{{ number_format($row->jumlah_transaksi,0,',','.') }} transaksi</span><span class="product-code" title="{{ $row->nomor_transaksi }}">{{ \Illuminate\Support\Str::limit($row->nomor_transaksi ?: '-', 35) }}</span></td>
                                    <td>@if($row->jenis==='penyesuaian')<span class="movement-badge movement-adjust"><i class="fas fa-balance-scale"></i> Penyesuaian</span>@elseif($row->jenis==='pemakaian')<span class="movement-badge movement-out"><i class="fas fa-arrow-up"></i> Pemakaian</span>@else<span class="movement-badge movement-in"><i class="fas fa-arrow-down"></i> Pembelian</span>@endif</td>
                                    <td class="text-end qty-value">{{ $masuk>0 ? number_format($masuk,2,',','.') : '-' }}</td>
                                    <td class="text-end qty-value">{{ $keluar>0 ? number_format($keluar,2,',','.') : '-' }}</td>
                                    <td class="text-end balance-value">{{ number_format((float)$row->saldo,2,',','.') }}</td>
                                    <td>{{ $row->nm_kandang ?: '-' }}</td><td class="text-end qty-value">{{ $row->ekor_ayam === null ? '-' : number_format($row->ekor_ayam,0,',','.') }}</td><td>{{ $row->admin ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="history-empty"><i class="fas fa-history fa-2x d-block mb-2"></i>Tidak ada transaksi pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="total-period-label">Total {{ \Carbon\Carbon::parse($tgl1)->format('d/m/Y') }}–{{ \Carbon\Carbon::parse($tgl2)->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($totalPembelian,2,',','.') }}</td>
                                <td class="text-end">{{ number_format($totalPemakaian,2,',','.') }}</td>
                                <td class="text-end">{{ number_format($saldoAkhir,2,',','.') }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <footer class="history-footer"><span class="history-footer-info">Menampilkan {{ $detail->firstItem() ?? 0 }}–{{ $detail->lastItem() ?? 0 }} dari {{ number_format($detail->total(),0,',','.') }} transaksi</span><div>{{ $detail->onEachSide(1)->links('pagination::bootstrap-5') }}</div></footer>
            </div>
        </section>
    </x-slot>
</x-theme.app>
