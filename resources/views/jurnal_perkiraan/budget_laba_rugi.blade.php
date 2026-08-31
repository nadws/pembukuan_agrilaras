<x-theme.app title="{{ $title }}" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><h5 class="mb-0">Kelola Budget Laba Rugi</h5><small>Budget bulanan per akun perkiraan</small></div>
            <a href="{{ route('jurnal-perkiraan.laba-rugi', ['bulan_dari'=>1,'tahun_dari'=>$tahun,'bulan_sampai'=>12,'tahun_sampai'=>$tahun]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Laporan Laba Rugi</a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .budget-toolbar{padding:15px;border:1px solid #dce4f2;border-radius:12px;background:#f7f9fd}.budget-table-wrap{max-height:68vh;overflow:auto;border:1px solid #dce4f2;border-radius:12px}.budget-table{min-width:2050px;margin:0}.budget-table thead th{position:sticky;top:0;z-index:3;padding:11px 8px;background:#304f9e;color:#fff;font-size:11px;text-align:center;white-space:nowrap}.budget-table thead th:first-child{left:0;z-index:4;text-align:left}.budget-table tbody td{padding:7px 6px;vertical-align:middle}.budget-table tbody td:first-child{position:sticky;left:0;z-index:2;min-width:290px;background:#fff}.budget-table tbody tr:hover td{background:#f4f7ff}.budget-input{min-width:115px;text-align:right}.budget-annual-input{min-width:155px;text-align:right;font-weight:600}.budget-annual-help{display:block;margin-top:2px;color:#7b879b;font-size:9px;text-align:right}.budget-code{display:block;color:#7b879b;font-size:10px}.budget-save{position:sticky;bottom:12px;display:flex;justify-content:flex-end;margin-top:16px;pointer-events:none}.budget-save .btn{box-shadow:0 8px 22px rgba(48,79,158,.25);pointer-events:auto}@media(max-width:575px){.budget-table-wrap{max-height:65vh}.budget-toolbar .btn{width:100%}}
        </style>
        @if(session('sukses'))<div class="alert alert-success">{{ session('sukses') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="budget-toolbar mb-3">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label fw-semibold">Tahun budget</label><select name="tahun" class="form-select">@foreach($years as $year)<option value="{{ $year }}" @selected($tahun===$year)>{{ $year }}</option>@endforeach</select></div>
                <div class="col-md-5"><label class="form-label fw-semibold">Cari akun</label><input id="searchBudgetAccount" type="search" class="form-control" placeholder="Kode atau nama akun"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-calendar-alt me-1"></i> Buka Tahun</button></div>
                <div class="col-md-2"><button id="copyPreviousMonth" type="button" class="btn btn-outline-primary w-100" title="Isi seluruh bulan kosong dari nominal bulan yang sudah diisi"><i class="fas fa-copy me-1"></i> Isi Bulan Kosong</button></div>
            </form>
            <small class="d-block mt-2 text-muted"><i class="fas fa-info-circle me-1"></i>Isi <strong>Total Budget Tahun</strong> untuk membagi nominal rata ke 12 bulan, atau isi salah satu bulan lalu klik <strong>Isi Bulan Kosong</strong>.</small>
        </div>

        <form method="post" action="{{ route('jurnal-perkiraan.laba-rugi.budget.simpan') }}">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <div class="budget-table-wrap">
                <table class="table table-sm table-bordered budget-table">
                    <thead><tr><th>Akun Perkiraan</th>@foreach($months as $month)<th>{{ $month }}</th>@endforeach<th>Total Budget Tahun</th></tr></thead>
                    <tbody>
                        @foreach($accounts as $account)
                            <tr class="budget-account-row" data-search="{{ Str::lower($account->kode_perkiraan.' '.$account->nama) }}">
                                <td><strong>{{ $account->nama }}</strong><span class="budget-code">{{ $account->kode_perkiraan }}</span></td>
                                @foreach($months as $number=>$month)
                                    @php $value=old("budget.{$account->id_akun_perkiraan}.{$number}", optional(optional($budget->get($account->id_akun_perkiraan))->get($number))->nominal); @endphp
                                    <td><input type="number" min="0" step="0.01" name="budget[{{ $account->id_akun_perkiraan }}][{{ $number }}]" value="{{ $value }}" class="form-control form-control-sm budget-input" data-month="{{ $number }}" placeholder="0"></td>
                                @endforeach
                                <td>
                                    <input type="number" min="0" step="0.01" class="form-control form-control-sm budget-annual-input" placeholder="Isi total setahun">
                                    <small class="budget-annual-help">Dibagi otomatis ke 12 bulan</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="budget-save"><button class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Budget {{ $tahun }}</button></div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rows=[...document.querySelectorAll('.budget-account-row')];
                const roundMoney=value=>Math.round((value+Number.EPSILON)*100)/100;
                const recalc=row=>{
                    const total=[...row.querySelectorAll('.budget-input')].reduce((sum,input)=>sum+Number(input.value||0),0);
                    row.querySelector('.budget-annual-input').value=roundMoney(total) || '';
                };
                rows.forEach(row=>{
                    const monthInputs=[...row.querySelectorAll('.budget-input')];
                    const annualInput=row.querySelector('.budget-annual-input');
                    recalc(row);
                    monthInputs.forEach(input=>input.addEventListener('input',()=>recalc(row)));
                    annualInput.addEventListener('change',function(){
                        const total=Math.max(0,Number(this.value||0));
                        const monthly=roundMoney(total/12);
                        monthInputs.forEach((input,index)=>{
                            input.value=index===monthInputs.length-1
                                ? roundMoney(total-(monthly*(monthInputs.length-1)))
                                : monthly;
                        });
                        recalc(row);
                    });
                });
                document.getElementById('searchBudgetAccount').addEventListener('input',function(){const q=this.value.toLowerCase().trim();rows.forEach(row=>row.hidden=q&&!row.dataset.search.includes(q))});
                document.getElementById('copyPreviousMonth').addEventListener('click',function(){
                    rows.filter(row=>!row.hidden).forEach(row=>{
                        const inputs=[...row.querySelectorAll('.budget-input')];
                        const firstFilled=inputs.find(input=>input.value!=='');
                        if(!firstFilled)return;
                        let lastValue=firstFilled.value;
                        inputs.forEach(input=>{
                            if(input.value==='')input.value=lastValue;
                            else lastValue=input.value;
                        });
                        recalc(row);
                    });
                });
            });
        </script>
    </x-slot>
</x-theme.app>
