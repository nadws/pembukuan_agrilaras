<x-theme.app title="{{ $title }}" table="T" sizeCard="12">
    <x-slot name="slot">
        <style>
            .egg-opname{padding:24px;border-radius:16px;background:#fff}.egg-opname h4{margin:0;color:#18366f;font-weight:700}
            .egg-guide,.egg-filter{padding:14px;border:1px solid #dfe6f3;border-radius:12px;background:#f7f9fd}
            .egg-opname-table{overflow-x:auto;border:1px solid #dfe6f3;border-radius:12px}.egg-opname-table table{min-width:900px;margin:0}
            .egg-opname-table thead th{padding:11px;background:#304f9e;color:#fff;font-size:12px;white-space:nowrap}.egg-opname-table td{padding:10px;vertical-align:middle}
            .egg-physical{min-width:130px;text-align:right}.egg-difference{font-weight:700}.egg-unit{color:#7a879d;font-size:11px}
            @media(max-width:700px){.egg-opname{padding:16px}}
        </style>
        <div class="egg-opname">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div><h4>Stok Opname Telur — {{ $gudang->nm_gudang }}</h4><small class="text-muted">Isi hasil hitung fisik untuk setiap jenis telur.</small></div>
                <a href="{{ route('gudang-persediaan.telur') }}" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i> Kembali ke Stok Telur</a>
            </div>
            @if($errors->any())<div class="alert alert-danger"><strong>Opname belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>@endif
            <div class="egg-guide mb-3"><strong>Cara opname:</strong> hitung telur fisik di gudang ini, isi PCS dan KG, periksa kolom selisih, lalu klik <strong>Simpan Stok Opname</strong>. Setelah disimpan, saldo gudang mengikuti jumlah fisik.</div>
            <form method="GET" action="{{ route('gudang-persediaan.telur.opname', $gudang->id_gudang_telur) }}" class="egg-filter mb-3">
                <div class="row g-2 align-items-end"><div class="col-md-4"><label class="form-label">Tanggal opname</label><input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ date('Y-m-d') }}" class="form-control" required></div><div class="col-md-3"><button class="btn btn-outline-primary w-100"><i class="fas fa-calendar me-1"></i> Tampilkan Saldo</button></div></div>
            </form>
            <form method="POST" action="{{ route('gudang-persediaan.telur.opname.store', $gudang->id_gudang_telur) }}" id="eggOpnameForm">
                @csrf<input type="hidden" name="tanggal" value="{{ $tanggal }}">
                <div class="egg-opname-table">
                    <table class="table table-hover">
                        <thead><tr><th>Jenis Telur</th><th class="text-end">Stok Sistem PCS</th><th class="text-end">Stok Sistem KG</th><th>Stok Fisik PCS</th><th>Stok Fisik KG</th><th class="text-end">Selisih PCS</th><th class="text-end">Selisih KG</th></tr></thead>
                        <tbody>
                        @foreach($stok as $item)
                            <tr class="egg-row" data-system-pcs="{{ $item->stok_pcs }}" data-system-kg="{{ $item->stok_kg }}">
                                <td><input type="hidden" name="produk[]" value="{{ $item->id_produk_telur }}"><strong>{{ $item->nm_telur }}</strong><br><span class="egg-unit">{{ $item->kode_produk ?: '-' }}</span></td>
                                <td class="text-end">{{ number_format($item->stok_pcs, 0, ',', '.') }}</td><td class="text-end">{{ number_format($item->stok_kg, 2, ',', '.') }}</td>
                                <td><input type="number" name="stok_fisik_pcs[{{ $item->id_produk_telur }}]" value="{{ old('stok_fisik_pcs.'.$item->id_produk_telur, max(0, $item->stok_pcs)) }}" min="0" step="1" class="form-control egg-physical physical-pcs" required></td>
                                <td><input type="number" name="stok_fisik_kg[{{ $item->id_produk_telur }}]" value="{{ old('stok_fisik_kg.'.$item->id_produk_telur, max(0, $item->stok_kg)) }}" min="0" step="0.01" class="form-control egg-physical physical-kg" required></td>
                                <td class="text-end egg-difference difference-pcs">0</td><td class="text-end egg-difference difference-kg">0</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"><small class="text-muted">Nilai minus berarti stok fisik lebih sedikit daripada sistem.</small><button type="submit" class="btn btn-success" onclick="return confirm('Simpan opname dan jadikan stok fisik sebagai saldo terbaru gudang ini?')"><i class="fas fa-save me-1"></i> Simpan Stok Opname</button></div>
            </form>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.egg-row').forEach(function(row){function calculate(){const systemPcs=parseFloat(row.dataset.systemPcs)||0,systemKg=parseFloat(row.dataset.systemKg)||0,physicalPcs=parseFloat(row.querySelector('.physical-pcs').value)||0,physicalKg=parseFloat(row.querySelector('.physical-kg').value)||0;[['.difference-pcs',physicalPcs-systemPcs,0],['.difference-kg',physicalKg-systemKg,2]].forEach(function(item){const cell=row.querySelector(item[0]),value=item[1];cell.textContent=value.toLocaleString('id-ID',{minimumFractionDigits:item[2],maximumFractionDigits:4});cell.className='text-end egg-difference '+item[0].slice(1)+' '+(value<0?'text-danger':value>0?'text-success':'');});}row.querySelectorAll('.egg-physical').forEach(input=>input.addEventListener('input',calculate));calculate();});});
        </script>
    </x-slot>
</x-theme.app>
