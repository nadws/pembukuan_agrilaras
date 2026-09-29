<x-theme.app title="{{ $title }}" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Tagihan Customer</h5>
                <small class="text-muted">Export format tagihan sesuai customer dan periode transaksi.</small>
            </div>
            <a href="{{ route('laporan') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Daftar Laporan
            </a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <form method="get" class="border rounded p-3 bg-light mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal awal</label>
                    <input type="date" name="tgl1" value="{{ $tgl1 }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal akhir</label>
                    <input type="date" name="tgl2" value="{{ $tgl2 }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Format tagihan</label>
                    <select name="format" class="form-select">
                        <option value="mcd" @selected($format === 'mcd')>McDonald's</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-fill"><i class="fas fa-search me-1"></i> Tampilkan</button>
                    <a href="{{ route('laporan.tagihan-customer.export', compact('tgl1', 'tgl2', 'format')) }}"
                       class="btn btn-success flex-fill">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                </div>
            </div>
        </form>

        <div class="alert alert-info py-2">
            <i class="fas fa-info-circle me-1"></i>
            Ditemukan {{ $rows->count() }} nota McDonald's. Kolom PSI, TAX NUMBER, dan PO NO tetap kosong untuk diisi manual.
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No Nota</th>
                        <th>Customer</th>
                        <th>Segment3</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Unit Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($row['tgl'])->format('d-m-Y') }}</td>
                            <td class="fw-semibold">{{ $row['no_nota'] }}</td>
                            <td>{{ $row['segment3'] === '36901' ? "MC DONALD'S BANJARBARU" : "MC DONALD'S BANJARMASIN" }}</td>
                            <td>{{ $row['segment3'] }}</td>
                            <td class="text-end">{{ number_format($row['quantity'], 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada nota McDonald's pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-slot>
</x-theme.app>
