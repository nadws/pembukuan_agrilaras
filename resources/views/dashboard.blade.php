<x-theme.app title="{{ $title }}" table="T" cont="container-fluid">
    <x-slot name="slot">
        <style>
            .compare-dashboard{max-width:1500px;margin:0 auto;color:#17366d}.compare-head{width:100%;background:#fff;border:1px solid #dce5f3;border-radius:18px;padding:23px 24px;box-shadow:0 8px 24px rgba(36,65,130,.06);display:flex;align-items:center;justify-content:space-between;gap:24px}.compare-title{font-size:27px;font-weight:800;margin:0 0 5px}.compare-date{font-size:14px;color:#63779c}.compare-filter{display:grid;grid-template-columns:175px 175px 124px;gap:8px;align-items:end}.compare-filter label{display:block;font-size:11px;font-weight:700;color:#63708a;margin-bottom:5px}.compare-layout{display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start}.compare-panel{width:100%;background:#fff;border:1px solid #dde5f2;border-radius:18px;box-shadow:0 9px 28px rgba(35,63,122,.07);margin-top:20px;overflow:hidden}.compare-panel-head{padding:20px 24px;border-bottom:1px solid #edf1f7}.compare-panel-title{font-size:19px;font-weight:800;margin:0}.compare-note{font-size:13px;color:#75839a;margin-top:4px}.compare-totals{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;padding:20px 24px 0}.compare-total{border-radius:13px;padding:15px 18px;background:#f5f8fd}.compare-total.feed{border-left:5px solid #4a9560}.compare-total.egg{border-left:5px solid #e0a13d}.compare-total.fcr{border-left:5px solid #526fc4}.compare-label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:800;color:#75839a}.compare-value{font-size:25px;font-weight:900;margin-top:5px}.split-charts{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:10px 16px 20px}.split-chart{min-height:340px}.compare-empty{height:340px;display:flex;align-items:center;justify-content:center;color:#8290a6;text-align:center}.feed-breakdown{margin-top:20px}.feed-breakdown-body{padding:16px 18px;max-height:450px;overflow:auto}.feed-house{font-size:14px;font-weight:800;color:#193b78;margin:0 0 8px}.feed-item{margin:0 0 10px}.feed-item-head{display:flex;justify-content:space-between;gap:8px;font-size:12px;color:#536582}.feed-bar{height:6px;border-radius:8px;background:#edf1f7;margin-top:4px;overflow:hidden}.feed-bar span{display:block;height:100%;background:#4a9560;border-radius:8px}.pnl-item{display:flex;justify-content:space-between;gap:10px;align-items:center;font-size:13px;color:#536582;padding:9px 0;border-bottom:1px dashed #edf1f7}.pnl-item:last-child{border-bottom:0}.pnl-item strong{font-size:15px;font-weight:900}.pnl-item .profit-up{color:#2f8f57}.pnl-item .profit-down{color:#c5303f}.pnl-total{display:flex;justify-content:space-between;gap:10px;align-items:center;padding:14px 18px;border-top:1px solid #edf1f7;background:#f5f8fd;font-size:13px;font-weight:800;color:#193b78}.pnl-total strong{font-size:19px;font-weight:900}.coop-side .feed-bar span{background:#e0a13d}@media(max-width:900px){.compare-head{align-items:stretch;flex-direction:column}.compare-filter{grid-template-columns:1fr 1fr 110px}.compare-layout{grid-template-columns:1fr}}@media(max-width:600px){.compare-head{padding:20px}.compare-title{font-size:23px}.compare-filter{grid-template-columns:1fr}.compare-totals{grid-template-columns:1fr;padding:16px 18px 0}.split-charts{grid-template-columns:1fr;padding:8px 4px 15px}}
        </style>
        <main class="compare-dashboard">
            <header class="compare-head"><div><h1 class="compare-title">Dashboard Agrilaras</h1><div class="compare-date">Ringkasan global pakan dan telur • {{ tanggal($tanggalMulai) }} – {{ tanggal($tanggalAkhir) }}</div></div><form class="compare-filter" method="GET" action="{{ route('dashboard') }}"><div><label>Dari tanggal</label><input type="date" name="tgl1" class="form-control" value="{{ $tanggalMulai }}"></div><div><label>Sampai tanggal</label><input type="date" name="tgl2" class="form-control" value="{{ $tanggalAkhir }}"></div><button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i> Filter</button></form></header>
            <div class="compare-layout"><section class="compare-panel">
                @php($pakanCoop = $pakanKandang->map(function ($rows, $id) { $first = $rows->first(); return (object) ['nama' => (string) ($first->nm_kandang ?: 'Kandang ' . $id), 'kg' => (float) $rows->sum('jumlah_kg')]; })->sortBy('nama')->values())
                @php($pakanCoopNama = $pakanCoop->pluck('nama')->values())
                @php($pakanCoopKg = $pakanCoop->pluck('kg')->values())
                @php($ringkasPakanKg = (float) $pakanCoop->sum('kg'))
                <div class="compare-panel-head"><h2 class="compare-panel-title">Pemakaian Pakan</h2><div class="compare-note">Total {{ number_format($ringkasPakanKg, 0, ',', '.') }} kg &middot; {{ tanggal($tanggalMulai) }} &ndash; {{ tanggal($tanggalAkhir) }}</div></div>
                @if($totalPakanKg > 0 || $totalTelurKg > 0)
                    <div style="padding:6px 16px 20px"><div id="mixChart" class="split-chart"></div></div>
                @else
                    <div class="compare-empty">Belum ada pemakaian pakan atau produksi telur yang tercatat hari ini.</div>
                @endif
            </section>
            <aside class="compare-panel feed-breakdown">
                <div class="compare-panel-head"><h2 class="compare-panel-title">Pakan per Kandang</h2><div class="compare-note">Komposisi pemakaian pada periode terpilih</div></div>
                <div class="feed-breakdown-body">
                    @forelse($pakanKandang as $rows)
                        @php($first = $rows->first())
                        <h3 class="feed-house">{{ $first->nm_kandang ?: 'Kandang '.$first->id_kandang }}</h3>
                        @php($totalKandang = $rows->sum('jumlah_kg'))
                        @foreach($rows as $item)
                            @php($persen = $totalKandang > 0 ? $item->jumlah_kg / $totalKandang * 100 : 0)
                            <div class="feed-item"><div class="feed-item-head"><span>{{ $item->nm_produk }}</span><strong>{{ number_format($persen, 0, ',', '.') }}%</strong></div><div class="feed-bar"><span style="width:{{ min(100, max(0, $persen)) }}%"></span></div></div>
                        @endforeach
                    @empty
                        <div class="compare-empty" style="height:200px">Belum ada pemakaian pakan pada periode ini.</div>
                    @endforelse
                </div>
            </aside>
            </div>
        <div class="compare-layout">
            <section class="compare-panel">
                @php($coopTotals = $produksiTelur->groupBy('id_kandang')->map(function ($rows, $id) { return (object) ['nama' => (string) ($rows->first()->nm_kandang ?: 'Kandang ' . $id), 'kg' => (float) $rows->sum('jumlah_kg'), 'pcs' => (float) $rows->sum('jumlah_pcs')]; })->sortBy('nama')->values())
                @php($ringkasTelurKg = (float) $coopTotals->sum('kg'))
                @php($ringkasTelurPcs = (float) $coopTotals->sum('pcs'))
                <div class="compare-panel-head"><h2 class="compare-panel-title">Produksi Telur per Kandang</h2><div class="compare-note">Total {{ number_format($ringkasTelurKg, 0, ',', '.') }} kg &middot; {{ number_format($ringkasTelurPcs, 0, ',', '.') }} butir &middot; {{ tanggal($tanggalMulai) }} &ndash; {{ tanggal($tanggalAkhir) }}</div></div>
                @if($ringkasTelurKg > 0)
                    <div style="padding:6px 16px 20px"><div id="coopChart" class="split-chart"></div></div>
                @else
                    <div class="compare-empty">Belum ada produksi telur pada periode ini.</div>
                @endif
            </section>
            <aside class="compare-panel feed-breakdown">
                <div class="compare-panel-head"><h2 class="compare-panel-title">Laba Rugi per Kandang</h2><div class="compare-note">Pendapatan dikurangi biaya (baris terakhir laporan laba rugi kandang) &middot; {{ tanggal($tanggalMulai) }} &ndash; {{ tanggal($tanggalAkhir) }}</div></div>
                <div class="feed-breakdown-body" style="max-height:none">
                    @forelse($labaRugiPerKandang as $row)
                        <div class="pnl-item"><span>{{ $row->nama }}</span><strong class="{{ $row->laba >= 0 ? 'profit-up' : 'profit-down' }}">{{ number_format($row->laba, 0, ',', '.') }}</strong></div>
                    @empty
                        <div class="compare-empty" style="height:200px">Belum ada data laba rugi kandang pada periode ini.</div>
                    @endforelse
                </div>
                <div class="pnl-total"><span>Total</span><strong class="{{ $labaRugiTotal >= 0 ? 'profit-up' : 'profit-down' }}">{{ number_format($labaRugiTotal, 0, ',', '.') }}</strong></div>
            </aside>
        </div>
        </main>
        <script src="{{ asset('theme/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded',function(){if(typeof ApexCharts==='undefined')return;const el=document.getElementById('mixChart');if(!el)return;const fmt=v=>new Intl.NumberFormat('id-ID',{maximumFractionDigits:0}).format(v);new ApexCharts(el,{chart:{type:'bar',height:340,toolbar:{show:false}},series:[{name:'Pakan (kg)',data:@json($pakanCoopKg)}],xaxis:{categories:@json($pakanCoopNama)},colors:['#4a9560'],plotOptions:{bar:{borderRadius:5,columnWidth:'52%'}},dataLabels:{enabled:false},yaxis:{title:{text:'kg'},labels:{formatter:v=>fmt(v)+' kg'}},tooltip:{y:{formatter:v=>fmt(v)+' kg'}},grid:{borderColor:'#edf1f7'},legend:{show:true,position:'top',horizontalAlign:'right'}}).render();const el2=document.getElementById('coopChart');if(el2){const cn=@json($coopTotals);new ApexCharts(el2,{chart:{type:'bar',height:340,toolbar:{show:false}},series:[{name:'Produksi (kg)',data:cn.map(c=>Math.round(c.kg))}],xaxis:{categories:cn.map(c=>c.nama)},colors:['#e0a13d'],plotOptions:{bar:{borderRadius:5,columnWidth:'55%',distributed:true}},dataLabels:{enabled:false},yaxis:{title:{text:'kg'},labels:{formatter:v=>fmt(v)+' kg'}},tooltip:{y:{formatter:v=>fmt(v)+' kg'}},grid:{borderColor:'#edf1f7'},legend:{show:false}}).render();}});
        </script>
    </x-slot>
</x-theme.app>
