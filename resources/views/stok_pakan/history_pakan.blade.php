<x-theme.app title="History Perencanaan" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><h6 class="mb-0">History {{ $kategori === 'pakan' ? 'Pakan' : 'Vitamin & Vaksin' }}</h6><small class="text-muted">Data pemakaian dari kandang. Jurnal PPH otomatis; koreksi berdasarkan tanggal dan kandang.</small></div>
            <div class="d-flex gap-2">
                @if($canCreate)<a class="btn btn-primary btn-sm" href="{{ route('history_perencanaan_pakan.create') }}">Tambah Tertinggal</a>@endif
                <a class="btn btn-outline-primary btn-sm" href="{{ route('history_perencanaan') }}">Kembali</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        @include('laporan.perencanaan.style')
        @include('laporan.perencanaan.messages')
        <form method="get" class="planning-filter mb-3">
            <input type="hidden" name="kategori" value="{{ $kategori }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label for="tgl1">Tanggal awal</label><input required id="tgl1" type="date" name="tgl1" value="{{ $tgl1 }}" class="form-control"></div>
                <div class="col-md-3"><label for="tgl2">Tanggal akhir</label><input required id="tgl2" type="date" name="tgl2" value="{{ $tgl2 }}" class="form-control"></div>
                <div class="col-md-3"><label for="filter-kandang">Kandang</label><select id="filter-kandang" name="id_kandang" class="form-select select2"><option value="">Semua kandang</option>@foreach($kandang as $k)<option value="{{ $k->id_kandang }}" @selected((int)$idKandang === (int)$k->id_kandang)>{{ $k->nm_kandang }}</option>@endforeach</select></div>
                <div class="col-md-1"><label for="per-page">Baris</label><select id="per-page" name="per_page" class="form-select">@foreach([25,50,100] as $size)<option value="{{ $size }}" @selected($stok->perPage() === $size)>{{ $size }}</option>@endforeach</select></div>
                <div class="col-12 d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary">Tampilkan</button><button type="submit" formaction="{{ route('history_perencanaan_pakan.export') }}" class="btn btn-success">Export Lengkap</button></div>
            </div>
        </form>
        <p class="small text-muted">Export memuat seluruh pakan, obat, vaksin, stok dan jurnal PPH sesuai tanggal/kandang, termasuk baris di halaman lain. Koreksi melalui tombol tiap baris.</p>
        <div class="planning-table">
            <table class="table table-hover table-striped"><thead><tr><th>No</th><th>Tanggal</th><th>Kandang</th><th>{{ $kategori === 'pakan' ? 'Nama Pakan' : 'Vitamin / Obat / Vaksin' }}</th><th class="text-end">Pemakaian</th><th>Satuan</th><th class="text-end">HPP / Satuan</th><th class="text-end">Total Rp</th><th>Admin</th><th>Aksi</th></tr></thead>
                <tbody>@forelse($stok as $s)
                    <tr><td>{{ $stok->firstItem() + $loop->index }}</td><td>{{ tanggal($s->tgl) }}</td><td>{{ $s->nm_kandang }}</td><td>{{ $s->nm_produk }}</td><td class="text-end">{{ number_format($s->pcs_kredit, 2, ',', '.') }}</td><td>{{ $s->nm_satuan }}</td><td class="text-end">Rp {{ number_format($s->total_rp / $s->pcs_kredit, 2, ',', '.') }}</td><td class="text-end">Rp {{ number_format($s->total_rp, 2, ',', '.') }}</td><td>{{ $s->admin }}</td>
                        <td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('history_perencanaan_pakan.detail', ['tgl'=>$s->tgl,'id_kandang'=>$s->id_kandang]) }}">Detail</a>@if($canUpdate) <a class="btn btn-sm btn-warning" href="{{ route('history_perencanaan_pakan.edit', ['tgl'=>$s->tgl,'id_kandang'=>$s->id_kandang]) }}">Koreksi</a>@endif</td></tr>
                @empty<tr><td colspan="10" class="text-center text-muted py-5">Tidak ada pemakaian sesuai filter.</td></tr>@endforelse</tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"><small class="text-muted">Menampilkan {{ $stok->firstItem() ?? 0 }}–{{ $stok->lastItem() ?? 0 }} dari {{ $stok->total() }} pemakaian</small><div class="planning-pagination">{{ $stok->onEachSide(1)->links('pagination::bootstrap-5') }}</div></div>
    </x-slot>
</x-theme.app>
