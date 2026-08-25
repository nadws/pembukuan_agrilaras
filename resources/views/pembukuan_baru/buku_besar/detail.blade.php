<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</h5>
                <small class="text-muted">Detail transaksi Buku Besar</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pembukuan-baru.buku-besar.detail.export', ['id' => $akun->id_akun_perkiraan, 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'cari' => request('cari')]) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </a>
                <a href="{{ route('pembukuan-baru.buku-besar.index', ['tgl1' => $tgl1, 'tgl2' => $tgl2]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .detail-filter { background: #f7f9fc; border: 1px solid #dce4f2; border-radius: 12px; padding: 16px; }
            .detail-filter label { font-weight: 600; color: #52627a; }
            .detail-table { min-width: 950px; }
            .detail-table thead th { background: #304f9e !important; color: #fff !important; white-space: nowrap; }
            .detail-table td { vertical-align: middle; }
            .pagination { margin-bottom: 0; }
        </style>
        <form method="get" class="detail-filter mb-4">
            <input type="hidden" name="tgl1" value="{{ $tgl1 }}">
            <input type="hidden" name="tgl2" value="{{ $tgl2 }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-7">
                    <label class="form-label">Cari nomor atau keterangan</label>
                    <input name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Masukkan nomor transaksi atau keterangan">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan</button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover detail-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Transaksi</th>
                        <th>Tipe Transaksi</th>
                        <th>Keterangan</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detail as $d)
                        <tr>
                            <td>{{ date('d-m-Y', strtotime($d->tanggal)) }}</td>
                            <td class="fw-semibold">{{ $d->nomor_transaksi }}</td>
                            <td><span class="badge bg-light text-primary">{{ $d->tipe_transaksi }}</span></td>
                            <td>{{ $d->deskripsi }}</td>
                            <td class="text-end">Rp {{ number_format($d->debit, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($d->kredit, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($d->saldo, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
            <small class="text-muted">Menampilkan {{ $detail->firstItem() ?? 0 }}–{{ $detail->lastItem() ?? 0 }} dari {{ $detail->total() }} transaksi</small>
            {{ $detail->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </x-slot>
</x-theme.app>
