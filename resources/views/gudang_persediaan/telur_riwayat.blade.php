<x-theme.app title="{{ $title }}" table="T" sizeCard="12">
    <x-slot name="slot">
        <style>
            .history-page{padding:24px;border-radius:16px;background:#fff}.history-page h4{margin:0;color:#18366f;font-weight:700}
            .history-filter{padding:14px;border:1px solid #dfe6f3;border-radius:12px;background:#f7f9fd}.history-wrap{overflow-x:auto;border:1px solid #dfe6f3;border-radius:12px}
            .history-table{min-width:1050px;margin:0}.history-table thead th{padding:11px;background:#304f9e;color:#fff;font-size:12px;white-space:nowrap}.history-table td{padding:10px;vertical-align:middle}
            .detail-row td{padding:0;background:#f7f9fd}.detail-box{padding:14px}.detail-table{margin:0;background:#fff}.detail-table th{font-size:11px;color:#596a86}.difference{font-weight:700}
            .pagination-wrap nav{display:flex;justify-content:flex-end}.pagination-wrap .pagination{margin:0}
            @media(max-width:700px){.history-page{padding:16px}}
        </style>
        <div class="history-page">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div><h4>Riwayat Stok Opname Telur</h4><small class="text-muted">Catatan opname dan perubahan saldo telur per gudang.</small></div>
                <a href="{{ route('gudang-persediaan.telur') }}" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i> Kembali ke Stok Telur</a>
            </div>
            <form method="GET" action="{{ route('gudang-persediaan.telur.riwayat') }}" class="history-filter mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-4"><label class="form-label">Dari tanggal</label><input type="date" name="tgl1" value="{{ request('tgl1') }}" class="form-control"></div>
                    <div class="col-lg-2 col-md-4"><label class="form-label">Sampai tanggal</label><input type="date" name="tgl2" value="{{ request('tgl2') }}" class="form-control"></div>
                    <div class="col-lg-2 col-md-4"><label class="form-label">Gudang</label><select name="id_gudang" class="form-select"><option value="">Semua gudang</option>@foreach($gudang as $item)<option value="{{ $item->id_gudang_telur }}" @selected((string)request('id_gudang') === (string)$item->id_gudang_telur)>{{ $item->nm_gudang }}</option>@endforeach</select></div>
                    <div class="col-lg-4 col-md-8"><label class="form-label">Cari</label><input type="search" name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Nomor opname, gudang, atau admin"></div>
                    <div class="col-lg-2 col-md-4"><button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan</button></div>
                </div>
            </form>
            <div class="history-wrap">
                <table class="table table-hover history-table">
                    <thead><tr><th>Tanggal</th><th>No. Opname</th><th>Gudang</th><th>Admin</th><th class="text-end">Jenis Telur</th><th class="text-end">Ada Selisih</th><th class="text-end">Total Selisih PCS</th><th class="text-end">Total Selisih KG</th><th class="text-center">Detail</th></tr></thead>
                    <tbody>
                    @forelse($riwayat as $item)
                        <tr>
                            <td>{{ date('d-m-Y', strtotime($item->tanggal)) }}</td><td class="fw-semibold">{{ $item->nomor_opname }}</td><td>{{ $item->nm_gudang }}</td><td>{{ $item->admin }}</td>
                            <td class="text-end">{{ number_format($item->jumlah_produk, 0, ',', '.') }}</td><td class="text-end">{{ number_format($item->jumlah_selisih, 0, ',', '.') }}</td>
                            <td class="text-end difference {{ $item->total_selisih_pcs < 0 ? 'text-danger' : ($item->total_selisih_pcs > 0 ? 'text-success' : '') }}">{{ number_format($item->total_selisih_pcs, 0, ',', '.') }}</td>
                            <td class="text-end difference {{ $item->total_selisih_kg < 0 ? 'text-danger' : ($item->total_selisih_kg > 0 ? 'text-success' : '') }}">{{ number_format($item->total_selisih_kg, 2, ',', '.') }}</td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary toggle-detail" data-target="detail-{{ $item->id }}"><i class="fas fa-eye"></i></button></td>
                        </tr>
                        <tr id="detail-{{ $item->id }}" class="detail-row" hidden><td colspan="9"><div class="detail-box"><div class="table-responsive"><table class="table table-sm table-bordered detail-table"><thead><tr><th>Jenis Telur</th><th class="text-end">Sistem PCS</th><th class="text-end">Fisik PCS</th><th class="text-end">Selisih PCS</th><th class="text-end">Sistem KG</th><th class="text-end">Fisik KG</th><th class="text-end">Selisih KG</th></tr></thead><tbody>
                            @foreach($detail->get($item->id, collect()) as $baris)<tr><td><strong>{{ $baris->nm_telur }}</strong><br><small class="text-muted">{{ $baris->kode_produk ?: '-' }}</small></td><td class="text-end">{{ number_format($baris->stok_sistem_pcs, 0, ',', '.') }}</td><td class="text-end">{{ number_format($baris->stok_fisik_pcs, 0, ',', '.') }}</td><td class="text-end {{ $baris->selisih_pcs < 0 ? 'text-danger' : ($baris->selisih_pcs > 0 ? 'text-success' : '') }}">{{ number_format($baris->selisih_pcs, 0, ',', '.') }}</td><td class="text-end">{{ number_format($baris->stok_sistem_kg, 2, ',', '.') }}</td><td class="text-end">{{ number_format($baris->stok_fisik_kg, 2, ',', '.') }}</td><td class="text-end {{ $baris->selisih_kg < 0 ? 'text-danger' : ($baris->selisih_kg > 0 ? 'text-success' : '') }}">{{ number_format($baris->selisih_kg, 2, ',', '.') }}</td></tr>@endforeach
                        </tbody></table></div></div></td></tr>
                    @empty<tr><td colspan="9" class="text-center text-muted py-4">Belum ada riwayat stok opname telur.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pagination-wrap"><small class="text-muted">Menampilkan {{ $riwayat->firstItem() ?? 0 }}–{{ $riwayat->lastItem() ?? 0 }} dari {{ $riwayat->total() }} opname</small>{{ $riwayat->links() }}</div>
        </div>
        <script>document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.toggle-detail').forEach(function(button){button.addEventListener('click',function(){const row=document.getElementById(this.dataset.target);row.hidden=!row.hidden;this.innerHTML=row.hidden?'<i class="fas fa-eye"></i>':'<i class="fas fa-eye-slash"></i>';});});});</script>
    </x-slot>
</x-theme.app>
