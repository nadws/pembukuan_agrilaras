<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center"><h5 class="mb-0">{{ $title }}</h5><a href="{{ route('jurnal-perkiraan.index') }}" class="btn btn-light btn-sm">Kembali</a></div>
    </x-slot>
    <x-slot name="cardBody">
        <div class="row mb-3">
            <div class="col-md-4"><strong>{{ $batch->nama_file }}</strong><br><small>{{ $batch->periode_awal->format('d-m-Y') }} s.d. {{ $batch->periode_akhir->format('d-m-Y') }}</small></div>
            <div class="col-md-4">{{ number_format($batch->jumlah_transaksi) }} transaksi / {{ number_format($batch->jumlah_detail) }} detail</div>
            <div class="col-md-4 text-end"><span class="badge bg-{{ $batch->status === 'aktif' ? 'success' : 'danger' }}">{{ ucfirst($batch->status) }}</span></div>
        </div>
        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-md-5">
                <label class="form-label mb-1">Pencarian</label>
                <input type="search" name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Nomor, tipe, kode, nama akun, deskripsi">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Tipe Transaksi</label>
                <select name="tipe" class="form-select">
                    <option value="">Semua tipe</option>
                    @foreach ($tipeOptions as $tipe)<option value="{{ $tipe }}" @selected(request('tipe') === $tipe)>{{ $tipe }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Tampilkan</label>
                <select name="per_page" class="form-select">
                    @foreach ([25, 50, 100] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 50) === $size)>{{ $size }} baris</option>@endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary flex-grow-1">Terapkan</button>
                <a href="{{ route('jurnal-perkiraan.detail-batch', $batch) }}" class="btn btn-light">Reset</a>
            </div>
        </form>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small>Menampilkan {{ number_format($detail->firstItem() ?? 0) }}–{{ number_format($detail->lastItem() ?? 0) }} dari {{ number_format($detail->total()) }} detail</small>
        </div>
        <div class="table-responsive"><table class="table table-hover">
            <thead><tr><th>Tanggal</th><th>No. Transaksi</th><th>Tipe</th><th>Kode</th><th>Nama Akun</th><th>Deskripsi</th><th>Debit</th><th>Kredit</th></tr></thead>
            <tbody>@foreach ($detail as $item)<tr>
                <td>{{ $item->tanggal->format('d-m-Y') }}</td><td>{{ $item->nomor_transaksi }}</td><td>{{ $item->tipe_transaksi ?: '-' }}</td><td>{{ $item->akun->kode_perkiraan }}</td><td>{{ $item->akun->nama }}</td><td>{{ $item->deskripsi }}</td>
                <td class="text-end">{{ number_format((float) $item->debit, 2, ',', '.') }}</td><td class="text-end">{{ number_format((float) $item->kredit, 2, ',', '.') }}</td>
            </tr>@endforeach</tbody>
        </table></div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <small>Halaman {{ number_format($detail->currentPage()) }} dari {{ number_format($detail->lastPage()) }}</small>
            {{ $detail->links('pagination::bootstrap-5') }}
        </div>
    </x-slot>
</x-theme.app>
