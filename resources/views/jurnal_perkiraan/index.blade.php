<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">{{ $title }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('jurnal-perkiraan.laba-rugi') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-chart-line me-1"></i> Laba Rugi
                </a>
                <a href="{{ route('jurnal-perkiraan.template') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-download me-1"></i> Template
                </a>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="fas fa-upload me-1"></i> Import Jurnal
                </button>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Periode</th>
                        <th>Transaksi</th>
                        <th>Detail</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batch as $item)
                        <tr>
                            <td>{{ $item->nama_file }}</td>
                            <td>{{ $item->periode_awal->format('d-m-Y') }} s.d.
                                {{ $item->periode_akhir->format('d-m-Y') }}</td>
                            <td>{{ number_format($item->jumlah_transaksi) }}</td>
                            <td>{{ number_format($item->jumlah_detail) }}</td>
                            <td class="text-end">{{ number_format((float) $item->total_debit, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) $item->total_kredit, 2, ',', '.') }}</td>
                            <td><span
                                    class="badge bg-{{ $item->status === 'aktif' ? 'success' : 'danger' }}">{{ ucfirst($item->status) }}</span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('jurnal-perkiraan.detail-batch', $item) }}"
                                    class="btn btn-info btn-sm text-white"><i class="fas fa-eye me-1"></i> Detail</a>
                                <form action="{{ route('jurnal-perkiraan.batalkan', $item) }}" method="post"
                                    class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus permanen batch dan seluruh detail jurnal ini? Data tidak dapat dipulihkan.')"><i
                                            class="fas fa-trash me-1"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form action="{{ route('jurnal-perkiraan.pratinjau') }}" method="post" enctype="multipart/form-data">
            @csrf
            <x-theme.modal title="Import Jurnal Perkiraan" idModal="modalImport" size="modal-lg">
                <div class="alert alert-info">Upload template datar. Data disimpan hanya setelah preview valid
                    dikonfirmasi.</div>
                <label class="form-label">File Excel</label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
            </x-theme.modal>
        </form>

        @if ($preview)
            @php($summary = $preview['ringkasan'])
            <div class="modal fade" id="modalPreviewJurnal" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Preview Import Jurnal</h5><button class="btn-close"
                                data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <div class="card border p-3">
                                        <small>Periode</small><strong>{{ $summary['periode_awal'] ?: '-' }} s.d.
                                            {{ $summary['periode_akhir'] ?: '-' }}</strong></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border p-3">
                                        <small>Transaksi</small><strong>{{ number_format($summary['jumlah_transaksi']) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border p-3">
                                        <small>Detail</small><strong>{{ number_format($summary['jumlah_detail']) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border p-3">
                                        <small>Selisih</small><strong>{{ number_format((float) $summary['total_debit'] - (float) $summary['total_kredit'], 6, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>
                            @if (!empty($summary['ringkasan_tipe']))
                                <div class="mb-3">
                                    <strong>Ringkasan Tipe Transaksi</strong>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @foreach ($summary['ringkasan_tipe'] as $tipe => $jumlah)
                                            <span class="badge bg-light text-dark border">{{ $tipe }}:
                                                {{ number_format($jumlah) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Baris</th>
                                            <th>Kode/Transaksi</th>
                                            <th>Masalah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse (array_slice($summary['errors'], 0, 100) as $error)
                                            <tr>
                                                <td>{{ $error['baris'] }}</td>
                                                <td>{{ $error['kode'] }}</td>
                                                <td class="text-danger">{{ $error['pesan'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-success">Seluruh validasi
                                                    lulus. Debit dan kredit seimbang.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if (count($summary['errors']) > 100)
                                <p class="text-danger">Menampilkan 100 dari {{ count($summary['errors']) }} masalah.
                                </p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <form action="{{ route('jurnal-perkiraan.simpan') }}" method="post">
                                @csrf<input type="hidden" name="token" value="{{ $preview['token'] }}">
                                <button class="btn btn-success" @disabled(count($summary['errors']) > 0)>Konfirmasi Import</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-slot>

    @section('scripts')
        @if ($preview)
            <script>
                new bootstrap.Modal(document.getElementById('modalPreviewJurnal')).show();
            </script>
        @endif
    @endsection
</x-theme.app>
