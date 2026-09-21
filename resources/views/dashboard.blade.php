<x-theme.app title="{{ $title }}" table="T" cont="container-fluid">
    <x-slot name="slot">
        <style>
            .compare-dashboard{max-width:1500px;margin:0 auto;color:#17366d}.compare-head{width:100%;background:#fff;border:1px solid #dce5f3;border-radius:18px;padding:23px 24px;box-shadow:0 8px 24px rgba(36,65,130,.06);display:flex;align-items:center;justify-content:space-between;gap:24px}.compare-title{font-size:27px;font-weight:800;margin:0 0 5px}.compare-date{font-size:14px;color:#63779c}.compare-filter{display:grid;grid-template-columns:175px 175px 124px;gap:8px;align-items:end}.compare-filter label{display:block;font-size:11px;font-weight:700;color:#63708a;margin-bottom:5px}.compare-layout{display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start}.compare-panel{width:100%;background:#fff;border:1px solid #dde5f2;border-radius:18px;box-shadow:0 9px 28px rgba(35,63,122,.07);margin-top:20px;overflow:hidden}.compare-panel-head{padding:20px 24px;border-bottom:1px solid #edf1f7}.compare-panel-title{font-size:19px;font-weight:800;margin:0}.compare-note{font-size:13px;color:#75839a;margin-top:4px}.compare-totals{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;padding:20px 24px 0}.compare-total{border-radius:13px;padding:15px 18px;background:#f5f8fd}.compare-total.feed{border-left:5px solid #4a9560}.compare-total.egg{border-left:5px solid #e0a13d}.compare-total.fcr{border-left:5px solid #526fc4}.compare-label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:800;color:#75839a}.compare-value{font-size:25px;font-weight:900;margin-top:5px}.split-charts{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:10px 16px 20px}.split-chart{min-height:340px}.compare-empty{height:340px;display:flex;align-items:center;justify-content:center;color:#8290a6;text-align:center}.feed-breakdown{margin-top:20px}.feed-breakdown-body{padding:16px 18px;max-height:450px;overflow:auto}.feed-house{font-size:14px;font-weight:800;color:#193b78;margin:0 0 8px}.feed-item{margin:0 0 10px}.feed-item-head{display:flex;justify-content:space-between;gap:8px;font-size:12px;color:#536582}.feed-bar{height:6px;border-radius:8px;background:#edf1f7;margin-top:4px;overflow:hidden}.feed-bar span{display:block;height:100%;background:#4a9560;border-radius:8px}@media(max-width:900px){.compare-head{align-items:stretch;flex-direction:column}.compare-filter{grid-template-columns:1fr 1fr 110px}.compare-layout{grid-template-columns:1fr}}@media(max-width:600px){.compare-head{padding:20px}.compare-title{font-size:23px}.compare-filter{grid-template-columns:1fr}.compare-totals{grid-template-columns:1fr;padding:16px 18px 0}.split-charts{grid-template-columns:1fr;padding:8px 4px 15px}}
        </style>
        <main class="compare-dashboard">
            <header class="compare-head"><div><h1 class="compare-title">Dashboard Agrilaras</h1><div class="compare-date">Ringkasan global pakan dan telur • {{ tanggal($tanggalMulai) }} – {{ tanggal($tanggalAkhir) }}</div></div><form class="compare-filter" method="GET" action="{{ route('dashboard') }}"><div><label>Dari tanggal</label><input type="date" name="tgl1" class="form-control" value="{{ $tanggalMulai }}"></div><div><label>Sampai tanggal</label><input type="date" name="tgl2" class="form-control" value="{{ $tanggalAkhir }}"></div><button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i> Filter</button></form></header>
            <div class="compare-layout"><section class="compare-panel">
                <div class="compare-panel-head"><h2 class="compare-panel-title">Histogram Harian</h2><div class="compare-note">Pemakaian pakan dan produksi telur dipisahkan per diagram</div></div>
                <div class="compare-totals"><div class="compare-total feed"><div class="compare-label">Total pakan 7 hari</div><div class="compare-value">{{ number_format($totalPakanKg, 0, ',', '.') }} kg</div></div><div class="compare-total egg"><div class="compare-label">Total telur 7 hari</div><div class="compare-value">{{ number_format($totalTelurKg, 0, ',', '.') }} kg</div></div><div class="compare-total fcr"><div class="compare-label">FCR Week</div><div class="compare-value">{{ number_format($fcrWeek, 2, ',', '.') }}</div></div></div>
                @if($totalPakanKg > 0 || $totalTelurKg > 0)
                    <div class="split-charts"><div><div class="compare-note px-2">Pemakaian pakan (kg)</div><div id="feedChart" class="split-chart"></div></div><div><div class="compare-note px-2">Produksi telur bersih (kg)</div><div id="eggChart" class="split-chart"></div></div></div>
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
        </main>
        <script src="{{ asset('theme/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded',function(){if(typeof ApexCharts==='undefined')return;const fmt=v=>new Intl.NumberFormat('id-ID',{maximumFractionDigits:0}).format(v);const categories=@json($labelHari);const make=(id,series,color,stacked=false)=>{const el=document.getElementById(id);if(!el)return;new ApexCharts(el,{chart:{type:'bar',height:300,toolbar:{show:false},stacked:stacked},series:series,xaxis:{categories:categories},colors:color,plotOptions:{bar:{borderRadius:5,columnWidth:'52%'}},dataLabels:{enabled:false},yaxis:{labels:{formatter:v=>fmt(v)}},tooltip:{y:{formatter:v=>fmt(v)+' kg'}},grid:{borderColor:'#edf1f7'},legend:{show:stacked,position:'top',horizontalAlign:'right'}}).render();};make('feedChart',[{name:'Pakan (kg)',data:@json($pakanHarian)}],['#4a9560']);make('eggChart',@json($telurSeries),['#e0a13d','#526fc4','#8b63b5','#d56565','#3b8ca4','#8a9b42','#d17a38'],true);});
        </script>
    </x-slot>
</x-theme.app>
