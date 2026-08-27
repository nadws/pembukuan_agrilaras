<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0" style="color: #0f172a !important;">Daftar Aktiva</h5>
                <small class="text-muted">Total: {{ $aktiva->total() }} aktiva</small>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @if (!empty($create))
                    <a href="{{ route('aktiva.add') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Buat Baru
                    </a>
                @endif
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importAktiva">
                    <i class="fas fa-file-import me-1"></i> Import Data
                </button>
                <a href="{{ route('aktiva.import.template') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-download me-1"></i> Format Import
                </a>
                @if (!empty($print))
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#view">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                @endif
                <a href="{{ route('pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-chart-line me-1"></i> Penyusutan Aktiva
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .aktiva-table { font-size: 13px; width: 100%; border-collapse: separate; border-spacing: 0; }
            .aktiva-table thead th {
                background: #304f9e !important;
                color: #ffffff !important;
                font-weight: 600;
                font-size: 12.5px;
                padding: 10px 14px;
                border: none;
                white-space: nowrap;
            }
            .aktiva-table tbody td {
                padding: 10px 14px;
                vertical-align: middle;
                border-bottom: 1px solid #eef2f6;
                color: #0f172a !important;
                background-color: #ffffff !important;
            }
            .aktiva-table tbody tr:hover td {
                background-color: #f1f5f9 !important;
            }
            .nilai-buku-positif { color: #16a34a !important; font-weight: 700; }
            .nilai-buku-nol { color: #94a3b8 !important; font-weight: 600; }
            .nm-aktiva-text { color: #0f172a !important; font-weight: 700; font-size: 13px; }
        </style>

        @if ($errors->has('file_aktiva'))
            <div class="alert alert-danger alert-dismissible fade show" style="white-space: pre-line">
                {{ $errors->first('file_aktiva') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Search Bar --}}
        <form method="GET" action="{{ route('aktiva') }}" class="mb-3">
            <div class="input-group" style="max-width: 400px;">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama aktiva atau akun aset..." value="{{ $cari }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                @if ($cari)
                    <a href="{{ route('aktiva') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
                @endif
            </div>
        </form>

        <div class="table-responsive border rounded bg-white">
            <table class="aktiva-table">
                <thead>
                    <tr>
                        <th style="width: 45px;">#</th>
                        <th style="min-width: 110px;">Tanggal Perolehan</th>
                        <th style="min-width: 200px;">Nama Aktiva</th>
                        <th style="min-width: 130px;">Akun Aset</th>
                        <th class="text-end" style="min-width: 140px;">Nilai Perolehan</th>
                        <th class="text-end" style="min-width: 130px;">Penyusutan/Bulan</th>
                        <th class="text-end" style="min-width: 140px;">Akumulasi Penyusutan</th>
                        <th class="text-end" style="min-width: 130px;">Nilai Buku</th>
                        <th class="text-center" style="min-width: 110px;">Umur Aktiva</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($aktiva as $i => $a)
                        @php
                            $nilaiBuku = max(0, (float) $a->h_perolehan - (float) $a->beban);
                            $tahunUmur = !empty($a->umur_aktiva_bulan) ? intdiv((int) $a->umur_aktiva_bulan, 12) : 0;
                            $bulanUmur = !empty($a->umur_aktiva_bulan) ? (int) $a->umur_aktiva_bulan % 12 : 0;
                        @endphp
                        <tr>
                            <td style="color: #94a3b8 !important; font-size: 12px;">{{ $aktiva->firstItem() + $i }}</td>
                            <td style="color: #475569 !important; font-size: 12px;">{{ date('d-m-Y', strtotime($a->tgl)) }}</td>
                            <td>
                                <div class="nm-aktiva-text">{{ $a->nm_aktiva }}</div>
                                @if ($a->nm_kelompok)
                                    <small style="color: #64748b !important; font-size: 11px;">{{ $a->nm_kelompok }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($a->kode_perkiraan)
                                    <span style="color: #2563eb !important; font-weight: 600; font-size: 12px;">{{ $a->kode_perkiraan }}</span>
                                    <div style="color: #475569 !important; font-size: 11.5px; max-width: 140px;" class="text-truncate">{{ $a->nama_akun_aset }}</div>
                                @else
                                    <span style="color: #94a3b8 !important; font-size: 12px;">-</span>
                                @endif
                            </td>
                            <td class="text-end" style="color: #0f172a !important;">Rp {{ number_format($a->h_perolehan, 0, ',', '.') }}</td>
                            <td class="text-end" style="color: #0f172a !important;">Rp {{ number_format($a->biaya_depresiasi, 0, ',', '.') }}</td>
                            <td class="text-end" style="color: #64748b !important;">Rp {{ number_format($a->beban, 0, ',', '.') }}</td>
                            <td class="text-end {{ $nilaiBuku > 0 ? 'nilai-buku-positif' : 'nilai-buku-nol' }}">
                                Rp {{ number_format($nilaiBuku, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if ($tahunUmur > 0 || $bulanUmur > 0)
                                    <div style="color: #0f172a !important; font-weight: 600; font-size: 12.5px;">
                                        {{ $tahunUmur > 0 ? $tahunUmur . ' th' : '' }}{{ $tahunUmur > 0 && $bulanUmur > 0 ? ' ' : '' }}{{ $bulanUmur > 0 ? $bulanUmur . ' bln' : '' }}
                                    </div>
                                    @if ($a->sisa_umur_bulan !== null)
                                        <small style="color: #64748b !important; font-size: 11px;">Sisa: {{ $a->sisa_umur_bulan }} bln</small>
                                    @endif
                                @else
                                    <span style="color: #94a3b8 !important;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5" style="color: #94a3b8 !important;">
                                @if ($cari)
                                    Tidak ada aktiva yang cocok dengan pencarian "<strong>{{ $cari }}</strong>".
                                @else
                                    Belum ada data aktiva.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
            <small class="text-muted" style="color: #64748b !important;">
                Menampilkan {{ $aktiva->firstItem() ?? 0 }}–{{ $aktiva->lastItem() ?? 0 }} dari {{ $aktiva->total() }} aktiva
            </small>
            {{ $aktiva->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>

        {{-- Modal Import --}}
        <div class="modal fade" id="importAktiva" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('aktiva.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Import Data Aktiva</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Pilih file CSV atau Excel</label>
                            <input type="file" name="file_aktiva" class="form-control" accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted d-block mt-2">Maksimal 5 MB. Gunakan susunan kolom dari Format Import terbaru.</small>
                            <a href="{{ route('aktiva.import.template') }}" class="btn btn-link px-0 mt-2">
                                <i class="fas fa-download me-1"></i> Unduh Format Import
                            </a>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-file-import me-1"></i> Import Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Print --}}
        <form action="{{ route('print_aktiva') }}" method="get">
            <x-theme.modal title="Pilih Tahun" idModal="view">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">Tahun</label>
                        <select name="tahun" id="selectView">
                            @foreach ($tahun as $t)
                                <option value="{{ $t->tgl }}">{{ $t->tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-theme.modal>
        </form>
    </x-slot>
</x-theme.app>
