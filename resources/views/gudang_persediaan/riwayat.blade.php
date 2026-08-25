<x-theme.app title="{{ $title }}" table="T" sizeCard="12">
    <x-slot name="slot">
        <style>
            .history-page{padding:24px;border-radius:16px;background:#fff}.history-page h4{color:#18366f;font-weight:700}
            .history-table-wrap{overflow-x:auto;border:1px solid #dfe6f3;border-radius:12px}.history-table{min-width:900px;margin-bottom:0}
            .history-table thead th,.history-detail thead th{padding:12px;background:#304f9e;color:#fff;font-size:12px;white-space:nowrap}.history-table td{padding:11px;vertical-align:middle}
            .history-detail{margin:12px 0;border:1px solid #e2e7f1}.history-detail td{padding:9px}
        </style>
        <div class="history-page">
            <div class="mb-3"><h4 class="mb-1">Riwayat Stok Opname</h4><small class="text-muted">Audit stok sistem, stok fisik, dan selisih setiap pelaksanaan opname.</small></div>
            @include('gudang_persediaan.partials.nav')
            <div class="history-table-wrap">
                <table class="table table-hover history-table" id="table1">
                    <thead><tr><th>Tanggal</th><th>No. Opname</th><th>Admin</th><th class="text-end">Produk</th><th class="text-end">Ada Selisih</th><th class="text-end">Nilai Selisih</th><th class="text-center">Detail</th></tr></thead>
                    <tbody>
                        @foreach($riwayat as $item)
                            <tr>
                                <td>{{ tanggal($item->tanggal) }}</td><td class="fw-semibold">{{ $item->nomor_opname }}</td><td>{{ $item->admin }}</td>
                                <td class="text-end">{{ number_format($item->jumlah_produk,0,',','.') }}</td><td class="text-end">{{ number_format($item->jumlah_selisih,0,',','.') }}</td>
                                <td class="text-end {{ $item->total_nilai_selisih < 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($item->total_nilai_selisih,0,',','.') }}</td>
                                <td class="text-center"><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detailOpname{{ $item->id }}"><i class="fas fa-eye"></i></button></td>
                            </tr>
                            <tr class="collapse" id="detailOpname{{ $item->id }}"><td colspan="7">
                                <div class="table-responsive"><table class="table history-detail mb-0"><thead><tr><th>Produk</th><th>Kategori</th><th>Satuan</th><th class="text-end">Sistem</th><th class="text-end">Fisik</th><th class="text-end">Selisih</th><th class="text-end">Nilai Selisih</th></tr></thead><tbody>
                                    @foreach($detail->get($item->id, collect()) as $row)<tr><td>{{ $row->nm_produk }}</td><td>{{ ucwords(str_replace('_',' ',$row->kategori)) }}</td><td>{{ $row->nm_satuan ?: '-' }}</td><td class="text-end">{{ number_format($row->stok_sistem,4,',','.') }}</td><td class="text-end">{{ number_format($row->stok_fisik,4,',','.') }}</td><td class="text-end">{{ number_format($row->selisih,4,',','.') }}</td><td class="text-end">Rp {{ number_format($row->nilai_selisih,0,',','.') }}</td></tr>@endforeach
                                </tbody></table></div>
                            </td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-slot>
</x-theme.app>
