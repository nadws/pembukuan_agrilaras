<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Cash: Kas Besar ke Kas Kecil Cost. Bank: Kas Besar ke BCA 0513277722 (Cost-1). Satu baris satu jurnal umum manual.</small>
            </div>
            <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'manual']) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Jurnal Umum Manual
            </a>
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

        <form method="POST" action="{{ route('pembukuan-baru.jurnal-umum.budget.import.preview') }}"
            enctype="multipart/form-data" class="card border-0 shadow-sm mb-3">
            @csrf
            <div class="card-body">
                <label for="file_budget" class="form-label fw-bold">File DAILY REPORT</label>
                <div class="d-flex flex-wrap gap-2">
                    <input type="file" id="file_budget" name="file_budget" class="form-control" accept=".xlsx,.xls"
                        required>
                    <a href="{{ route('pembukuan-baru.jurnal-umum.budget.import.template') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-1"></i> Download Template
                    </a>
                    <button class="btn btn-success" type="submit">
                        <i class="fas fa-search me-1"></i> Tampilkan Review
                    </button>
                </div>
                <small class="text-muted d-block mt-2">Kolom wajib: Via, Tanggal, Keterangan, Debit. Baris yang Debit-nya kosong (biaya) otomatis tidak ikut.</small>
            </div>
        </form>

        @if (isset($rows))
            @php
                $ready = collect($rows)->where('can_import', true)->count();
                $skipped = collect($rows)->where('can_import', false)->count();
            @endphp

            <div class="alert alert-info">
                <strong>{{ $fileName }}</strong><br>
                {{ $ready }} baris budget bisa dipilih. {{ $skipped }} baris dilewati otomatis.
                @if (!empty($dipulihkan))
                    <br><strong>Preview dipulihkan dari isian sebelumnya.</strong>
                    Perbaiki baris yang error lalu simpan lagi. Baris yang dilewati dari file tidak ditampilkan.
                @endif
            </div>

            <form method="POST" action="{{ route('pembukuan-baru.jurnal-umum.budget.import.store') }}">
                @csrf
                <input type="hidden" name="file_hash" value="{{ $fileHash }}">
                <input type="hidden" name="file_name" value="{{ $fileName }}">

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Pilih</th>
                                <th>Baris</th>
                                <th>Tanggal</th>
                                <th>Via</th>
                                <th>Debit Akun (Tujuan)</th>
                                <th>Kredit Akun</th>
                                <th>Keterangan</th>
                                <th class="text-end">Jumlah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $index => $row)
                                <tr class="{{ $row['can_import'] ? '' : 'table-light text-muted' }}">
                                    <td>
                                        @if ($row['can_import'])
                                            <input type="checkbox" name="import_rows[{{ $index }}][selected]" value="1"
                                                @checked($row['selected'] ?? false)>
                                            <input type="hidden" name="import_rows[{{ $index }}][line]"
                                                value="{{ $row['line'] }}">
                                            <input type="hidden" name="import_rows[{{ $index }}][tanggal]"
                                                value="{{ $row['tanggal'] }}">
                                            <input type="hidden" name="import_rows[{{ $index }}][via]"
                                                value="{{ $row['via'] }}">
                                            <input type="hidden" name="import_rows[{{ $index }}][kode_tujuan]"
                                                value="{{ $row['kode_tujuan'] }}">
                                            <input type="hidden" name="import_rows[{{ $index }}][keterangan]"
                                                value="{{ $row['keterangan'] }}">
                                            <input type="hidden" name="import_rows[{{ $index }}][jumlah]"
                                                value="{{ $row['jumlah'] }}">
                                        @endif
                                    </td>
                                    <td>{{ $row['line'] }}</td>
                                    <td>{{ $row['tanggal'] ?? '-' }}</td>
                                    <td>{{ $row['via'] ?: '-' }}</td>
                                    <td>{{ $row['kode_tujuan'] ?? '' }} - {{ $row['nama_tujuan'] }}</td>
                                    <td>110112 - Kas Besar</td>
                                    <td>{{ $row['keterangan'] ?: '-' }}</td>
                                    <td class="text-end">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                                    <td>{{ $row['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan yang Dicentang
                    </button>
                </div>
            </form>
        @endif
    </x-slot>

    @section('scripts')
        <script>
            // Cegah import/simpan dobel: klik/Enter berikutnya diabaikan setelah form terkirim.
            document.querySelectorAll('form[method="POST"]').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.dataset.submitted) {
                        event.preventDefault();
                        return;
                    }
                    form.dataset.submitted = '1';
                    form.querySelectorAll('button[type="submit"]').forEach(function(button) {
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
                    });
                });
            });
        </script>
    @endsection
</x-theme.app>
