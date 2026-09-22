<x-theme.app title="{{ $title }}" table="T" cont="container-fluid">
    <x-slot name="slot">
        <style>
            .compare-dashboard{max-width:1500px;margin:0 auto;color:#17366d}.compare-head{width:100%;background:#fff;border:1px solid #dce5f3;border-radius:18px;padding:23px 24px;box-shadow:0 8px 24px rgba(36,65,130,.06);display:flex;align-items:center;justify-content:space-between;gap:24px}.compare-title{font-size:27px;font-weight:800;margin:0 0 5px}.compare-date{font-size:14px;color:#63779c}.compare-filter{display:grid;grid-template-columns:175px 175px 124px;gap:8px;align-items:end}.compare-filter label{display:block;font-size:11px;font-weight:700;color:#63708a;margin-bottom:5px}.compare-layout{display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start}.compare-panel{width:100%;background:#fff;border:1px solid #dde5f2;border-radius:18px;box-shadow:0 9px 28px rgba(35,63,122,.07);margin-top:20px;overflow:hidden}.compare-panel-head{padding:20px 24px;border-bottom:1px solid #edf1f7}.compare-panel-title{font-size:19px;font-weight:800;margin:0}.compare-note{font-size:13px;color:#75839a;margin-top:4px}.compare-totals{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;padding:20px 24px 0}.compare-total{border-radius:13px;padding:15px 18px;background:#f5f8fd}.compare-total.feed{border-left:5px solid #4a9560}.compare-total.egg{border-left:5px solid #e0a13d}.compare-total.fcr{border-left:5px solid #526fc4}.compare-label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:800;color:#75839a}.compare-value{font-size:25px;font-weight:900;margin-top:5px}.split-charts{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:10px 16px 20px}.split-chart{min-height:340px}.compare-empty{height:340px;display:flex;align-items:center;justify-content:center;color:#8290a6;text-align:center}.feed-breakdown{margin-top:20px}.feed-breakdown-body{padding:16px 18px;max-height:450px;overflow:auto}.feed-house{font-size:14px;font-weight:800;color:#193b78;margin:0 0 8px}.feed-item{margin:0 0 10px}.feed-item-head{display:flex;justify-content:space-between;gap:8px;font-size:12px;color:#536582}.feed-bar{height:6px;border-radius:8px;background:#edf1f7;margin-top:4px;overflow:hidden}.feed-bar span{display:block;height:100%;background:#4a9560;border-radius:8px}.pnl-item{display:flex;justify-content:space-between;gap:10px;align-items:center;font-size:13px;color:#536582;padding:9px 0;border-bottom:1px dashed #edf1f7}.pnl-item:last-child{border-bottom:0}.pnl-item strong{font-size:15px;font-weight:900}.pnl-item .profit-up{color:#2f8f57}.pnl-item .profit-down{color:#c5303f}.pnl-total{display:flex;justify-content:space-between;gap:10px;align-items:center;padding:14px 18px;border-top:1px solid #edf1f7;background:#f5f8fd;font-size:13px;font-weight:800;color:#193b78}.pnl-total strong{font-size:19px;font-weight:900}.coop-side .feed-bar span{background:#e0a13d}@media(max-width:900px){.compare-head{align-items:stretch;flex-direction:column}.compare-filter{grid-template-columns:1fr 1fr 110px}.compare-layout{grid-template-columns:1fr}}@media(max-width:600px){.compare-head{padding:20px}.compare-title{font-size:23px}.compare-filter{grid-template-columns:1fr}.compare-totals{grid-template-columns:1fr;padding:16px 18px 0}.split-charts{grid-template-columns:1fr;padding:8px 4px 15px}}
        </style>
        <style>
            .widget-toolbar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:20px;padding:12px 16px;background:#fff;border:1px solid #dde5f2;border-radius:14px;box-shadow:0 9px 28px rgba(35,63,122,.07);font-size:13px;color:#536582}.widget-hint{margin-right:auto}.hidden-chips{display:flex;flex-wrap:wrap;gap:6px}.chip-tampil{border:1px dashed #8fa5d6;background:#f0f4ff;color:#2949ad;border-radius:20px;padding:5px 12px;font-size:12px;font-weight:700;cursor:pointer}.widget-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}.widget-ctl{display:flex;gap:4px;flex:none}.widget-btn{width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #dce5f3;background:#f5f8fd;color:#52648a;border-radius:8px;font-size:12px;cursor:pointer}.widget-btn:hover{background:#e8eeff;color:#2949ad}.widget-handle{cursor:grab;touch-action:none;user-select:none;-webkit-user-select:none}.widget-handle:active{cursor:grabbing}[data-widget].dragging{opacity:.45}[data-widget].drop-target{outline:2px dashed #435ebe;outline-offset:-2px}#widgetGrid{display:grid;grid-template-columns:repeat(12,1fr);gap:20px;align-items:stretch}.compare-panel{display:flex;flex-direction:column}.feed-breakdown-body{flex:1 1 auto;min-height:0}.widget-size{width:auto;min-width:66px;padding:0 10px;font-weight:800;font-size:11px}@media(max-width:900px){#widgetGrid{grid-template-columns:1fr}#widgetGrid>[data-widget]{grid-column:span 12 !important}}
        </style>
        <main class="compare-dashboard">
            <header class="compare-head"><div><h1 class="compare-title">Dashboard Agrilaras</h1><div class="compare-date">Ringkasan global pakan dan telur • {{ tanggal($tanggalMulai) }} – {{ tanggal($tanggalAkhir) }}</div></div><form class="compare-filter" method="GET" action="{{ route('dashboard') }}"><div><label>Dari tanggal</label><input type="date" name="tgl1" class="form-control" value="{{ $tanggalMulai }}"></div><div><label>Sampai tanggal</label><input type="date" name="tgl2" class="form-control" value="{{ $tanggalAkhir }}"></div><button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i> Filter</button></form></header>
            <div class="widget-toolbar"><span class="widget-hint"><i class="fas fa-grip-vertical me-1"></i>Seret panel untuk memindah posisi &middot; <i class="fas fa-eye-slash"></i> untuk menyembunyikan</span><span id="hiddenChips" class="hidden-chips"></span><button type="button" id="resetLayout" class="btn btn-sm btn-outline-secondary">Reset</button></div>
            <div id="emptyWidgets" class="compare-empty" style="height:auto;padding:28px;margin-top:20px" hidden>Semua panel disembunyikan. Aktifkan lagi lewat tombol + di atas.</div>
            <div class="compare-layout" id="widgetGrid" data-save-url="{{ route('dashboard.layout') }}"><section class="compare-panel" data-widget="pakan" data-span="{{ $widgetSpan['pakan'] ?? 8 }}" style="order:{{ $widgetOrder['pakan'] ?? 0 }};grid-column:span {{ $widgetSpan['pakan'] ?? 8 }}" @if(in_array('pakan', $widgetHidden)) hidden @endif>
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
            <aside class="compare-panel feed-breakdown" data-widget="pakan-rincian" data-span="{{ $widgetSpan['pakan-rincian'] ?? 4 }}" style="order:{{ $widgetOrder['pakan-rincian'] ?? 1 }};grid-column:span {{ $widgetSpan['pakan-rincian'] ?? 4 }}" @if(in_array('pakan-rincian', $widgetHidden)) hidden @endif>
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
            <section class="compare-panel" data-widget="telur" data-span="{{ $widgetSpan['telur'] ?? 8 }}" style="order:{{ $widgetOrder['telur'] ?? 2 }};grid-column:span {{ $widgetSpan['telur'] ?? 8 }}" @if(in_array('telur', $widgetHidden)) hidden @endif>
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
            <aside class="compare-panel feed-breakdown" data-widget="laba-rugi" data-span="{{ $widgetSpan['laba-rugi'] ?? 4 }}" style="order:{{ $widgetOrder['laba-rugi'] ?? 3 }};grid-column:span {{ $widgetSpan['laba-rugi'] ?? 4 }}" @if(in_array('laba-rugi', $widgetHidden)) hidden @endif>
                <div class="compare-panel-head"><h2 class="compare-panel-title">Laba Rugi per Kandang</h2><div class="compare-note">Pendapatan dikurangi biaya (baris terakhir laporan laba rugi kandang) &middot; {{ tanggal($tanggalMulai) }} &ndash; {{ tanggal($tanggalAkhir) }}</div></div>
                <div class="feed-breakdown-body">
                    @forelse($labaRugiPerKandang as $row)
                        <div class="pnl-item"><span>{{ $row->nama }}</span><strong class="{{ $row->laba >= 0 ? 'profit-up' : 'profit-down' }}">{{ number_format($row->laba, 0, ',', '.') }}</strong></div>
                    @empty
                        <div class="compare-empty" style="height:200px">Belum ada data laba rugi kandang pada periode ini.</div>
                    @endforelse
                </div>
                <div class="pnl-total"><span>Total</span><strong class="{{ $labaRugiTotal >= 0 ? 'profit-up' : 'profit-down' }}">{{ number_format($labaRugiTotal, 0, ',', '.') }}</strong></div>
            </aside>
            <aside class="compare-panel feed-breakdown" data-widget="piutang" data-span="{{ $widgetSpan['piutang'] ?? 4 }}" style="order:{{ $widgetOrder['piutang'] ?? 4 }};grid-column:span {{ $widgetSpan['piutang'] ?? 4 }}" @if(in_array('piutang', $widgetHidden)) hidden @endif>
                <div class="compare-panel-head"><h2 class="compare-panel-title">Piutang Telur Belum Lunas</h2><div class="compare-note">Umur min. 10 hari &middot; per hari ini &middot; total {{ number_format($piutangTotal, 0, ',', '.') }}</div></div>
                <div class="feed-breakdown-body">
                    @forelse($piutangBelumLunas as $row)
                        <div class="pnl-item"><span>{{ $row->customer }}<small class="text-muted d-block">{{ $row->no_nota }} &middot; {{ tanggal($row->tgl) }} &middot; {{ $row->umur }} hari</small></span><strong>{{ number_format($row->sisa, 0, ',', '.') }}</strong></div>
                    @empty
                        <div class="compare-empty" style="height:200px">Tidak ada piutang telur berumur &ge; 10 hari.</div>
                    @endforelse
                </div>
                <div class="pnl-total"><span>Total</span><strong>{{ number_format($piutangTotal, 0, ',', '.') }}</strong></div>
            </aside>
        </div>
        </main>
        <script src="{{ asset('theme/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
        <script>
            window._agrCharts={};const fmtAgr=v=>new Intl.NumberFormat('id-ID',{maximumFractionDigits:0}).format(v);function renderMix(){const el=document.getElementById('mixChart');if(window._agrCharts.mix||!el||typeof ApexCharts==='undefined'||el.closest('[data-widget]').hidden)return;window._agrCharts.mix=new ApexCharts(el,{chart:{type:'bar',height:340,toolbar:{show:false}},series:[{name:'Pakan (kg)',data:@json($pakanCoopKg)}],xaxis:{categories:@json($pakanCoopNama)},colors:['#4a9560'],plotOptions:{bar:{borderRadius:5,columnWidth:'52%'}},dataLabels:{enabled:false},yaxis:{title:{text:'kg'},labels:{formatter:v=>fmtAgr(v)+' kg'}},tooltip:{y:{formatter:v=>fmtAgr(v)+' kg'}},grid:{borderColor:'#edf1f7'},legend:{show:true,position:'top',horizontalAlign:'right'}});window._agrCharts.mix.render();}window.renderCoop=function(){const el2=document.getElementById('coopChart');if(window._agrCharts.coop||!el2||typeof ApexCharts==='undefined'||el2.closest('[data-widget]').hidden)return;const cn=@json($coopTotals);window._agrCharts.coop=new ApexCharts(el2,{chart:{type:'bar',height:340,toolbar:{show:false}},series:[{name:'Produksi (kg)',data:cn.map(c=>Math.round(c.kg))}],xaxis:{categories:cn.map(c=>c.nama)},colors:['#e0a13d'],plotOptions:{bar:{borderRadius:5,columnWidth:'55%',distributed:true}},dataLabels:{enabled:false},yaxis:{title:{text:'kg'},labels:{formatter:v=>fmtAgr(v)+' kg'}},tooltip:{y:{formatter:v=>fmtAgr(v)+' kg'}},grid:{borderColor:'#edf1f7'},legend:{show:false}});window._agrCharts.coop.render();};document.addEventListener('DOMContentLoaded',function(){initWidgetGrid();renderMix();if(window.renderCoop)window.renderCoop();});
        </script>
        <script>
            const WIDGET_JUDUL={pakan:'Pemakaian Pakan','pakan-rincian':'Pakan per Kandang',telur:'Produksi Telur per Kandang','laba-rugi':'Laba Rugi per Kandang',piutang:'Piutang Telur Belum Lunas'};
            const WIDGET_DEFAULT=['pakan','pakan-rincian','telur','laba-rugi','piutang'];
            const SPAN_DEFAULT={pakan:8,'pakan-rincian':4,telur:8,'laba-rugi':4,piutang:4};
            const SPAN_PILIHAN=[4,6,8,12];
            const SPAN_LABEL={12:'Penuh',8:'Lebar',6:'Setengah',4:'Sempit'};
            function initWidgetGrid(){
                const grid=document.getElementById('widgetGrid');if(!grid)return;
                const chips=document.getElementById('hiddenChips');
                const emptyNote=document.getElementById('emptyWidgets');
                const saveUrl=grid.dataset.saveUrl;
                const token=document.querySelector('meta[name="csrf-token"]')?.content;
                const cards=()=>[...grid.querySelectorAll('[data-widget]')];
                const kunciKartu=()=>cards().map(c=>c.dataset.widget);
                const kunciSembunyi=()=>cards().filter(c=>c.hidden).map(c=>c.dataset.widget);
                cards().forEach(c=>{
                    const head=c.querySelector('.compare-panel-head');
                    if(head&&!head.querySelector('.widget-ctl')){
                        head.classList.add('widget-head');
                        const wrap=document.createElement('div');while(head.firstChild)wrap.appendChild(head.firstChild);head.appendChild(wrap);
                        const ctl=document.createElement('div');ctl.className='widget-ctl';
                        ctl.innerHTML='<button type="button" class="widget-btn widget-handle" title="Tahan lalu seret untuk memindah"><i class="fas fa-grip-vertical"></i></button><button type="button" class="widget-btn widget-size" data-ukuran title="Ukuran panel, klik untuk ubah">8</button><button type="button" class="widget-btn" data-gerak="-1" title="Pindah ke atas"><i class="fas fa-chevron-up"></i></button><button type="button" class="widget-btn" data-gerak="1" title="Pindah ke bawah"><i class="fas fa-chevron-down"></i></button><button type="button" class="widget-btn" data-sembunyi title="Sembunyikan panel"><i class="fas fa-eye-slash"></i></button>';
                        head.appendChild(ctl);
                    }
                });
                let menyimpan=false,kotor=false;
                function kirim(){
                    fetch(saveUrl,{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':token},body:JSON.stringify({order:kunciKartu(),hidden:kunciSembunyi(),span:kunciSpan()})}).finally(()=>{if(kotor){kotor=false;kirim();}else menyimpan=false;});
                }
                function simpan(){if(menyimpan){kotor=true;return;}menyimpan=true;kirim();}
                function segarkanChips(){
                    chips.innerHTML='';
                    kunciSembunyi().forEach(k=>{const b=document.createElement('button');b.type='button';b.className='chip-tampil';b.textContent='+ '+(WIDGET_JUDUL[k]||k);b.addEventListener('click',()=>tampilkan(k));chips.appendChild(b);});
                    emptyNote.hidden=cards().some(c=>!c.hidden);
                }
                function renderKartu(k){tampilkanUlangChart(k);}
                function tampilkanUlangChart(k){
                    const slot=k==='pakan'?'mix':(k==='telur'?'coop':null);
                    if(!slot)return;
                    const lama=window._agrCharts[slot];
                    if(lama){try{lama.destroy();}catch(_){}delete window._agrCharts[slot];}
                    if(k==='pakan'&&typeof renderMix==='function')renderMix();
                    if(k==='telur'&&window.renderCoop)window.renderCoop();
                }
                function kunciSpan(){const s={};cards().forEach(c=>{let v=parseInt(c.dataset.span,10);if(!SPAN_PILIHAN.includes(v))v=SPAN_DEFAULT[c.dataset.widget]||12;s[c.dataset.widget]=v;});return s;}
                function segarkanUkuran(){cards().forEach(c=>{let v=parseInt(c.dataset.span,10);if(!SPAN_PILIHAN.includes(v))v=SPAN_DEFAULT[c.dataset.widget]||12;c.dataset.span=v;c.style.gridColumn='span '+v;const b=c.querySelector('[data-ukuran]');if(b){b.textContent=SPAN_LABEL[v]||v;b.title='Ukuran panel: '+(SPAN_LABEL[v]||v)+' (klik untuk ubah)';}});}
                function tampilkan(k){const c=grid.querySelector('[data-widget="'+k+'"]');if(!c)return;c.hidden=false;renderKartu(k);segarkanChips();simpan();}
                function sembunyikan(k){const c=grid.querySelector('[data-widget="'+k+'"]');if(!c)return;c.hidden=true;segarkanChips();simpan();}
                function pindah(k,arah){const list=cards();const i=list.findIndex(c=>c.dataset.widget===k);const j=i+arah;if(i<0||j<0||j>=list.length)return;grid.insertBefore(list[i],arah<0?list[j]:list[j].nextSibling);tataUlang();segarkanChips();simpan();}
                function tataUlang(){cards().forEach((c,i)=>{c.style.order=i;});}
                let seret=null;
                grid.addEventListener('pointerdown',e=>{
                    const h=e.target.closest('.widget-handle');if(!h)return;
                    if(e.pointerType==='mouse'&&e.button!==0)return;
                    const card=h.closest('[data-widget]');if(!card)return;
                    const r=card.getBoundingClientRect();
                    seret={kunci:card.dataset.widget,el:card,x0:e.clientX,y0:e.clientY,dx:e.clientX-r.left,dy:e.clientY-r.top,aktif:false};
                    document.body.style.userSelect='none';
                });
                window.addEventListener('pointermove',e=>{
                    if(!seret)return;
                    if(!seret.aktif){
                        if(Math.hypot(e.clientX-seret.x0,e.clientY-seret.y0)<7)return;
                        seret.aktif=true;
                        const r=seret.el.getBoundingClientRect();
                        seret.el.style.width=r.width+'px';
                        seret.el.style.position='fixed';
                        seret.el.style.zIndex='60';
                        seret.el.style.pointerEvents='none';
                        seret.el.classList.add('dragging');
                    }
                    seret.el.style.left=(e.clientX-seret.dx)+'px';
                    seret.el.style.top=(e.clientY-seret.dy)+'px';
                    const t=document.elementFromPoint(e.clientX,e.clientY);
                    const over=t&&t.closest?t.closest('[data-widget]'):null;
                    grid.querySelectorAll('.drop-target').forEach(x=>x.classList.remove('drop-target'));
                    if(!over||over.dataset.widget===seret.kunci)return;
                    const r=over.getBoundingClientRect();
                    if((e.clientY-r.top)>r.height/2)over.after(seret.el);else over.before(seret.el);
                    over.classList.add('drop-target');
                },{passive:true});
                function akhiriSeret(){
                    if(!seret)return;
                    const was=seret.aktif;
                    seret.el.style.position='';seret.el.style.left='';seret.el.style.top='';seret.el.style.width='';seret.el.style.zIndex='';seret.el.style.pointerEvents='';
                    seret.el.classList.remove('dragging');
                    document.body.style.userSelect='';
                    grid.querySelectorAll('.drop-target').forEach(x=>x.classList.remove('drop-target'));
                    seret=null;
                    if(was){tataUlang();segarkanChips();simpan();}
                }
                window.addEventListener('pointerup',akhiriSeret);
                window.addEventListener('pointercancel',akhiriSeret);
                grid.addEventListener('click',e=>{
                    const bU=e.target.closest('[data-ukuran]');if(bU){const card=bU.closest('[data-widget]');if(card){const cur=parseInt(card.dataset.span,10);const nx=SPAN_PILIHAN[(SPAN_PILIHAN.indexOf(cur)+1)%SPAN_PILIHAN.length]||SPAN_PILIHAN[0];card.dataset.span=nx;segarkanUkuran();if(card.dataset.widget==='pakan'||card.dataset.widget==='telur')tampilkanUlangChart(card.dataset.widget);simpan();}return;}
                    const bS=e.target.closest('[data-sembunyi]');if(bS){const card=bS.closest('[data-widget]');if(card)sembunyikan(card.dataset.widget);return;}
                    const bG=e.target.closest('[data-gerak]');if(bG){const card=bG.closest('[data-widget]');if(card)pindah(card.dataset.widget,parseInt(bG.dataset.gerak,10));}
                });
                document.getElementById('resetLayout').addEventListener('click',()=>{fetch(saveUrl,{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':token},body:JSON.stringify({order:WIDGET_DEFAULT,hidden:[],span:SPAN_DEFAULT})}).finally(()=>location.reload());});
                segarkanChips();segarkanUkuran();
            }
        </script>
    </x-slot>
</x-theme.app>
