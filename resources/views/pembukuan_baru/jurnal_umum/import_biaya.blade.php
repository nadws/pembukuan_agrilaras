<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Akun Biaya Pakan dan Biaya Rak Telur dilewati. Bank satu baris satu jurnal; Cash tanggal sama boleh digabung.</small>
            </div>
            <a href="{{ route('pembukuan-baru.jurnal-umum.biaya.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Tambah Jurnal Biaya
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

        <form method="POST" action="{{ route('pembukuan-baru.jurnal-umum.biaya.import.preview') }}"
            enctype="multipart/form-data" class="card border-0 shadow-sm mb-3">
            @csrf
            <div class="card-body">
                <label for="file_biaya" class="form-label fw-bold">File DAILY REPORT</label>
                <div class="d-flex flex-wrap gap-2">
                    <input type="file" id="file_biaya" name="file_biaya" class="form-control" accept=".xlsx,.xls"
                        required>
                    <a href="{{ route('pembukuan-baru.jurnal-umum.biaya.import.template') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-1"></i> Download Template
                    </a>
                    <button class="btn btn-success" type="submit">
                        <i class="fas fa-search me-1"></i> Tampilkan Review
                    </button>
                </div>
                <small class="text-muted d-block mt-2">Kolom wajib: Via, Tanggal, Akun, Keterangan, Debit, Kredit.</small>
            </div>
        </form>

        @if (isset($rows))
            @php
                $ready = collect($rows)->where('can_import', true)->count();
                $skipped = collect($rows)->where('can_import', false)->count();
            @endphp

            <div class="alert alert-info">
                <strong>{{ $fileName }}</strong><br>
                {{ $ready }} baris bisa dipilih. {{ $skipped }} baris dilewati otomatis atau perlu diperiksa.
                Baris berstatus <strong>Pilih akun biaya</strong> belum dicentang.
            </div>

            <form method="POST" action="{{ route('pembukuan-baru.jurnal-umum.biaya.import.store') }}">
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
                                <th>Bayar</th>
                                <th>Akun Excel</th>
                                <th style="min-width: 260px">Akun Program</th>
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
                                            <input type="hidden" name="import_rows[{{ $index }}][tanggal]"
                                                value="{{ $row['tanggal'] }}">
                                            <input type="hidden" name="import_rows[{{ $index }}][payment_code]"
                                                value="{{ $row['payment_code'] }}">
                                            <input type="hidden" name="import_rows[{{ $index }}][keterangan]"
                                                value="{{ $row['keterangan'] }}">
                                            <input type="hidden" name="import_rows[{{ $index }}][jumlah]"
                                                value="{{ $row['jumlah'] }}">
                                        @endif
                                    </td>
                                    <td>{{ $row['line'] }}</td>
                                    <td>{{ $row['tanggal'] ?? '-' }}</td>
                                    <td>{{ $row['via'] ?? '-' }}<br><small>{{ $row['payment_code'] ?? '' }}</small></td>
                                    <td>{{ $row['akun_excel'] ?? '-' }}</td>
                                    <td>
                                        @if ($row['can_import'])
                                            <select name="import_rows[{{ $index }}][id_akun_biaya]"
                                                class="form-select form-select-sm select-search"
                                                data-placeholder="Cari akun biaya"
                                                {{ !empty($row['selected']) ? 'required' : '' }}>
                                                <option value="">-- pilih akun --</option>
                                                @foreach ($akunBiaya as $akun)
                                                    <option value="{{ $akun->id_akun_perkiraan }}"
                                                        @selected(($row['id_akun_biaya'] ?? '') == $akun->id_akun_perkiraan)>
                                                        {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $row['keterangan'] ?? '-' }}</td>
                                    <td class="text-end">
                                        {{ isset($row['jumlah']) ? number_format($row['jumlah'], 0, ',', '.') : '-' }}
                                    </td>
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
            (function() {
                if (!window.jQuery || !jQuery.fn.select2) {
                    return;
                }

                jQuery('.select-search').select2({
                    width: '100%',
                    placeholder: function() {
                        return jQuery(this).data('placeholder') || '-- Pilih --';
                    },
                    allowClear: true
                });
            })();
        </script>
    @endsection
</x-theme.app>
