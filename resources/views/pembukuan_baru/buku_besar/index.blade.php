<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0">Buku Besar</h5>
            <a href="{{ route('pembukuan-baru.buku-besar.export', ['tgl1' => $tgl1, 'tgl2' => $tgl2, 'cari' => request('cari')]) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .ledger-filter { background: #f7f9fc; border: 1px solid #e1e7f0; border-radius: 10px; padding: 16px; }
            .ledger-table th { white-space: nowrap; background: #304f9e !important; color: #fff !important; }
            .ledger-table td { vertical-align: middle; }
            .ledger-table .account-name { font-weight: 600; text-decoration: none; }
            .pagination { margin-bottom: 0; }
        </style>
        <form method="get" class="ledger-filter mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Tanggal Awal</label>
                    <input type="date" name="tgl1" value="{{ $tgl1 }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="tgl2" value="{{ $tgl2 }}" class="form-control">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Cari Akun</label>
                    <input name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Kode atau nama akun">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover ledger-table mb-0">
                <thead>
                    <tr>
                        <th style="width:18%">Kode Akun</th>
                        <th>Nama Akun</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($buku as $a)
                        <tr>
                            <td>{{ $a->kode_perkiraan }}</td>
                            <td>
                                <a class="account-name" href="{{ route('pembukuan-baru.buku-besar.detail', ['id' => $a->id_akun_perkiraan, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">
                                    {{ $a->nama }}
                                </a>
                            </td>
                            <td class="text-end">Rp {{ number_format($a->debit, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($a->kredit, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($a->saldo, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Data akun tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3">
            <small class="text-muted">Menampilkan {{ $buku->firstItem() ?? 0 }}–{{ $buku->lastItem() ?? 0 }} dari {{ $buku->total() }} akun</small>
            <div>{{ $buku->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
        </div>
    </x-slot>
</x-theme.app>
