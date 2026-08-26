<x-theme.app title="{{ $title }}" table="T" cont="container-fluid">
    <x-slot name="slot">
        @php
            $rupiah = fn ($nilai) => 'Rp ' . number_format((float) $nilai, 0);
            $angka = fn ($nilai) => number_format((float) $nilai, 0);
            $pakan = $stokPerencanaan->where('kategori', 'pakan');
            $vitamin = $stokPerencanaan->where('kategori', '!=', 'pakan');
            $telurPcs = $stokTelur->sum('pcs');
            $telurKg = $stokTelur->sum('kg');
        @endphp
        <style>
            .main-dashboard{max-width:1550px;margin:auto;color:#203354}.dash-head,.dash-card,.dash-panel{background:#fff;border:1px solid #e0e7f2;border-radius:14px;box-shadow:0 8px 24px rgba(36,65,130,.05)}
            .dash-head{padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:18px}.dash-title{font-size:24px;font-weight:800;margin:0}.dash-note{color:#71809a;font-size:13px;margin-top:4px}
            .dash-filter{display:grid;grid-template-columns:155px 155px 110px;gap:8px;align-items:end}.dash-filter label{display:block;font-size:11px;font-weight:700;color:#66758e;margin-bottom:4px}
            .dash-card{height:100%;padding:17px;position:relative;overflow:hidden}.dash-card:after{content:"";position:absolute;width:74px;height:74px;border-radius:50%;right:-25px;top:-25px;background:var(--soft)}
            .dash-card-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#71809a}.dash-card-value{font-size:23px;font-weight:900;margin-top:7px;color:#203354}.dash-card-foot{font-size:12px;color:#71809a;margin-top:7px}.dash-card-icon{width:38px;height:38px;border-radius:10px;display:grid;place-items:center;background:var(--soft);color:var(--color);margin-bottom:12px}
            .dash-panel{height:100%;overflow:hidden}.dash-panel-head{padding:15px 17px;border-bottom:1px solid #edf1f7;display:flex;justify-content:space-between;gap:12px;align-items:center}.dash-panel-title{font-size:15px;font-weight:800;margin:0}.dash-panel-body{padding:16px}.dash-link{font-size:12px;font-weight:700;text-decoration:none}
            .dashboard-table{margin:0}.dashboard-table thead th{background:#f6f8fc;color:#687791;font-size:11px;text-transform:uppercase;white-space:nowrap;padding:10px 12px}.dashboard-table td{padding:11px 12px;border-color:#edf1f7;vertical-align:middle}.item-name{font-weight:700;color:#253858}.item-meta{font-size:11px;color:#7a879d}.stock-number{font-weight:800;white-space:nowrap}.stock-zero{color:#d14b5a}.stock-ok{color:#287a5b}
            .profit-positive{color:#23785a!important}.profit-negative{color:#c84f5f!important}.section-caption{font-size:12px;color:#71809a}.stock-tabs{display:flex;gap:7px;flex-wrap:wrap}.stock-chip{padding:5px 9px;border-radius:999px;background:#eef3ff;color:#3957a4;font-size:11px;font-weight:700}
            .profit-row{display:flex;justify-content:space-between;gap:15px;padding:9px 0;border-bottom:1px dashed #e4eaf3}.profit-row:last-child{border:0}.profit-row.total{font-weight:900;color:#203354;border-top:2px solid #dfe6f2;border-bottom:0;margin-top:4px}.expense-bar{height:7px;border-radius:10px;background:#edf1f7;overflow:hidden;margin-top:6px}.expense-bar span{height:100%;display:block;background:#536fc3;border-radius:10px}
            @media(max-width:992px){.dash-head{align-items:stretch;flex-direction:column}.dash-filter{grid-template-columns:1fr 1fr 100px}}@media(max-width:576px){.dash-filter{grid-template-columns:1fr}.dash-card-value{font-size:20px}}
        </style>

        <div class="main-dashboard">
            <div class="dash-head mb-3">
                <div><h1 class="dash-title">Dashboard Agrilaras</h1><div class="dash-note">Ringkasan laba-rugi dan posisi stok operasional • Jurnal terakhir {{ $latestJournal ? tanggal($latestJournal) : '-' }}</div></div>
                <form class="dash-filter" method="GET" action="{{ route('dashboard') }}">
                    <div><label>Dari tanggal</label><input type="date" class="form-control" name="tgl1" value="{{ $tgl1 }}"></div>
                    <div><label>Sampai tanggal</label><input type="date" class="form-control" name="tgl2" value="{{ $tgl2 }}"></div>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i> Filter</button>
                </form>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3"><div class="dash-card" style="--soft:#e9f7f0;--color:#23785a"><div class="dash-card-icon"><i class="fas fa-coins"></i></div><div class="dash-card-label">Pendapatan</div><div class="dash-card-value">{{ $rupiah($labaRugi['pendapatan']) }}</div><div class="dash-card-foot">Periode {{ tanggal($tgl1) }} – {{ tanggal($tgl2) }}</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="dash-card" style="--soft:#fff3e5;--color:#b66a18"><div class="dash-card-icon"><i class="fas fa-boxes"></i></div><div class="dash-card-label">Harga Pokok Penjualan</div><div class="dash-card-value">{{ $rupiah($labaRugi['hpp']) }}</div><div class="dash-card-foot">{{ $labaRugi['pendapatan'] != 0 ? number_format($labaRugi['hpp'] / $labaRugi['pendapatan'] * 100, 1) : 0 }}% dari pendapatan</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="dash-card" style="--soft:#fff0f1;--color:#c84f5f"><div class="dash-card-icon"><i class="fas fa-file-invoice-dollar"></i></div><div class="dash-card-label">Total Beban</div><div class="dash-card-value">{{ $rupiah($labaRugi['total_beban']) }}</div><div class="dash-card-foot">Operasional dan beban lainnya</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="dash-card" style="--soft:#edf1ff;--color:#4561b1"><div class="dash-card-icon"><i class="fas fa-chart-line"></i></div><div class="dash-card-label">Laba Bersih</div><div class="dash-card-value {{ $labaRugi['laba_bersih'] >= 0 ? 'profit-positive' : 'profit-negative' }}">{{ $rupiah($labaRugi['laba_bersih']) }}</div><div class="dash-card-foot">Margin {{ $labaRugi['pendapatan'] != 0 ? number_format($labaRugi['laba_bersih'] / $labaRugi['pendapatan'] * 100, 1) : 0 }}%</div></div></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3"><div class="dash-card" style="--soft:#fff7e5;--color:#b97b17"><div class="dash-card-icon"><i class="fas fa-egg"></i></div><div class="dash-card-label">Stok Telur</div><div class="dash-card-value">{{ $angka($telurPcs) }} pcs</div><div class="dash-card-foot">{{ $angka($telurKg) }} kg • {{ $stokTelur->count() }} gudang</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="dash-card" style="--soft:#eaf7ef;--color:#287a5b"><div class="dash-card-icon"><i class="fas fa-seedling"></i></div><div class="dash-card-label">Stok Pakan</div><div class="dash-card-value">{{ $pakan->where('stok', '>', 0)->count() }} produk tersedia</div><div class="dash-card-foot">{{ $pakan->count() }} jenis • nilai {{ $rupiah($pakan->sum('nilai_stok')) }}</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="dash-card" style="--soft:#f1edff;--color:#6d55b8"><div class="dash-card-icon"><i class="fas fa-capsules"></i></div><div class="dash-card-label">Vitamin, Obat & Vaksin</div><div class="dash-card-value">{{ $vitamin->where('stok', '>', 0)->count() }} produk tersedia</div><div class="dash-card-foot">{{ $vitamin->count() }} jenis • nilai {{ $rupiah($vitamin->sum('nilai_stok')) }}</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="dash-card" style="--soft:#eaf3ff;--color:#3671ad"><div class="dash-card-icon"><i class="fas fa-box-open"></i></div><div class="dash-card-label">Stok Barang Umum</div><div class="dash-card-value">{{ $stokUmum->where('stok', '>', 0)->count() }} barang tersedia</div><div class="dash-card-foot">{{ $stokUmum->count() }} barang • nilai {{ $rupiah($stokUmum->sum('nilai_stok')) }}</div></div></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-8"><div class="dash-panel"><div class="dash-panel-head"><div><h2 class="dash-panel-title">Tren Pendapatan, Beban, dan Laba</h2><div class="section-caption">Berdasarkan jurnal perkiraan aktif pada periode filter</div></div><a class="dash-link" href="{{ route('jurnal-perkiraan.laba-rugi') }}">Laporan lengkap <i class="fas fa-arrow-right ms-1"></i></a></div><div class="dash-panel-body"><div id="profitChart" style="min-height:310px"></div></div></div></div>
                <div class="col-xl-4"><div class="dash-panel"><div class="dash-panel-head"><h2 class="dash-panel-title">Ringkasan Laba-Rugi</h2></div><div class="dash-panel-body">
                    <div class="profit-row"><span>Pendapatan</span><strong>{{ $rupiah($labaRugi['pendapatan']) }}</strong></div><div class="profit-row"><span>HPP</span><strong>({{ $rupiah($labaRugi['hpp']) }})</strong></div><div class="profit-row"><span>Laba Kotor</span><strong>{{ $rupiah($labaRugi['laba_kotor']) }}</strong></div><div class="profit-row"><span>Beban Operasional</span><strong>({{ $rupiah($labaRugi['beban_operasional']) }})</strong></div><div class="profit-row"><span>Pendapatan Lain</span><strong>{{ $rupiah($labaRugi['pendapatan_lain']) }}</strong></div><div class="profit-row"><span>Beban Lain</span><strong>({{ $rupiah($labaRugi['beban_lain']) }})</strong></div><div class="profit-row total"><span>Laba Bersih</span><strong class="{{ $labaRugi['laba_bersih'] >= 0 ? 'profit-positive' : 'profit-negative' }}">{{ $rupiah($labaRugi['laba_bersih']) }}</strong></div>
                </div></div></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-4"><div class="dash-panel"><div class="dash-panel-head"><div><h2 class="dash-panel-title">Stok Telur per Gudang</h2><div class="section-caption">Posisi stok terkini</div></div><a class="dash-link" href="{{ route('gudang-persediaan.telur') }}">Buka stok</a></div><div class="table-responsive"><table class="table dashboard-table"><thead><tr><th>Gudang</th><th class="text-end">Pcs</th><th class="text-end">Kg</th></tr></thead><tbody>@forelse($stokTelur as $row)<tr><td class="item-name">{{ $row->nm_gudang }}</td><td class="text-end stock-number {{ $row->pcs <= 0 ? 'stock-zero' : 'stock-ok' }}">{{ $angka($row->pcs) }}</td><td class="text-end">{{ $angka($row->kg) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">Belum ada gudang telur.</td></tr>@endforelse</tbody></table></div></div></div>
                <div class="col-xl-8"><div class="dash-panel"><div class="dash-panel-head"><div><h2 class="dash-panel-title">Stok Pakan, Vitamin, Obat dan Vaksin</h2><div class="stock-tabs"><span class="stock-chip">Pakan {{ $pakan->count() }} produk</span><span class="stock-chip">Vitamin/Obat/Vaksin {{ $vitamin->count() }} produk</span></div></div><a class="dash-link" href="{{ route('gudang-persediaan.index') }}">Buka gudang</a></div><div class="table-responsive"><table class="table dashboard-table"><thead><tr><th>Produk</th><th>Kategori</th><th class="text-end">Stok</th><th class="text-end">Nilai</th></tr></thead><tbody>@forelse($stokPerencanaan->take(10) as $row)<tr><td><div class="item-name">{{ $row->nm_produk }}</div><div class="item-meta">{{ $row->nm_satuan ?: 'Tanpa satuan' }}</div></td><td><span class="stock-chip">{{ ucwords(str_replace('_', ' ', $row->kategori)) }}</span></td><td class="text-end stock-number {{ $row->stok <= 0 ? 'stock-zero' : 'stock-ok' }}">{{ $angka($row->stok) }} {{ $row->nm_satuan }}</td><td class="text-end">{{ $rupiah($row->nilai_stok) }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Belum ada produk pakan, vitamin, obat, atau vaksin.</td></tr>@endforelse</tbody></table></div></div></div>
            </div>

            <div class="row g-3">
                <div class="col-xl-7"><div class="dash-panel"><div class="dash-panel-head"><div><h2 class="dash-panel-title">Stok Barang Umum</h2><div class="section-caption">Saldo dari pembelian umum dan penyesuaian stok</div></div><a class="dash-link" href="{{ route('gudang-persediaan.barang-umum') }}">Lihat semua</a></div><div class="table-responsive"><table class="table dashboard-table"><thead><tr><th>Barang</th><th class="text-end">Stok</th><th class="text-end">Nilai Persediaan</th></tr></thead><tbody>@forelse($stokUmum->take(10) as $row)<tr><td><div class="item-name">{{ $row->nm_produk }}</div><div class="item-meta">{{ $row->kd_produk ?: '-' }}</div></td><td class="text-end stock-number {{ $row->stok <= 0 ? 'stock-zero' : 'stock-ok' }}">{{ $angka($row->stok) }} {{ $row->nm_satuan }}</td><td class="text-end">{{ $rupiah($row->nilai_stok) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">Belum ada barang umum.</td></tr>@endforelse</tbody></table></div></div></div>
                <div class="col-xl-5"><div class="dash-panel"><div class="dash-panel-head"><div><h2 class="dash-panel-title">Beban Terbesar</h2><div class="section-caption">Akun beban pada periode filter</div></div></div><div class="dash-panel-body">@php $maxBeban = max(1, (float)($topBeban->max('nilai') ?? 1)); @endphp @forelse($topBeban as $row)<div class="mb-3"><div class="d-flex justify-content-between gap-2"><div><div class="item-name">{{ $row->nama }}</div><div class="item-meta">{{ $row->kode_perkiraan }}</div></div><strong>{{ $rupiah($row->nilai) }}</strong></div><div class="expense-bar"><span style="width:{{ min(100, max(0, (float)$row->nilai / $maxBeban * 100)) }}%"></span></div></div>@empty<div class="text-center text-muted py-4">Belum ada beban pada periode ini.</div>@endforelse</div></div></div>
            </div>
        </div>

        <script src="{{ asset('theme/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const el = document.getElementById('profitChart');
                if (!el || typeof ApexCharts === 'undefined') return;
                new ApexCharts(el, {
                    chart:{type:'bar',height:310,toolbar:{show:false}},
                    series:[
                        {name:'Pendapatan',data:@json($trend->pluck('pendapatan')->values())},
                        {name:'Beban',data:@json($trend->pluck('beban')->values())},
                        {name:'Laba',type:'line',data:@json($trend->pluck('laba')->values())}
                    ],
                    colors:['#2d8b68','#dc6572','#4561b1'],stroke:{width:[0,0,3],curve:'smooth'},
                    plotOptions:{bar:{borderRadius:4,columnWidth:'48%'}},
                    dataLabels:{enabled:false},xaxis:{categories:@json($trend->pluck('periode')->values())},
                    yaxis:{labels:{formatter:v=>'Rp '+Intl.NumberFormat('id-ID',{notation:'compact',maximumFractionDigits:1}).format(v)}},
                    tooltip:{y:{formatter:v=>'Rp '+new Intl.NumberFormat('id-ID',{maximumFractionDigits:0}).format(v)}},
                    legend:{position:'top',horizontalAlign:'right'},grid:{borderColor:'#edf1f7'}
                }).render();
            });
        </script>
    </x-slot>
</x-theme.app>
