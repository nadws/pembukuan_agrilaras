<x-theme.app title="{{ $title }}" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Aktiva: {{ $aktiva->nm_aktiva }}</h5>
            <a href="{{ route('aktiva') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
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

        @if ($sudahAdaDepresiasi)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                <strong>Perhatian:</strong> Aktiva ini sudah memiliki riwayat jurnal penyusutan. Nilai Perolehan dan Tanggal Perolehan tidak dapat diubah untuk menjaga konsistensi jurnal penyesuaian.
            </div>
        @endif

        <form action="{{ route('aktiva.update', $aktiva->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold" for="id_akun_aset">Akun Aset Tetap Tujuan</label>
                    <select name="id_akun_aset" id="id_akun_aset" class="form-select select2" required>
                        <option value="">-- Pilih Akun Aset Tetap --</option>
                        @foreach ($akunAset as $akun)
                            <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_aset', $aktiva->id_akun_aset) == $akun->id_akun_perkiraan)>
                                {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold" for="nm_aktiva">Nama Aktiva</label>
                    <input type="text" name="nm_aktiva" id="nm_aktiva" class="form-control"
                        value="{{ old('nm_aktiva', $aktiva->nm_aktiva) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold" for="tgl">Tanggal Perolehan</label>
                    <input type="date" name="tgl" id="tgl" class="form-control"
                        value="{{ old('tgl', $aktiva->tgl) }}" @readonly($sudahAdaDepresiasi) required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold" for="h_perolehan">Nilai Perolehan (Rp)</label>
                    <input type="number" step="any" min="0.01" name="h_perolehan" id="h_perolehan" class="form-control"
                        value="{{ old('h_perolehan', $aktiva->h_perolehan) }}" @readonly($sudahAdaDepresiasi) required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold" for="nilai_sisa_aset">Nilai Buku Saat Ini (Rp)</label>
                    <input type="number" step="any" min="0" name="nilai_sisa_aset" id="nilai_sisa_aset" class="form-control"
                        value="{{ old('nilai_sisa_aset', $aktiva->nilai_buku_awal ?? $aktiva->h_perolehan) }}" required>
                </div>

                @php
                    $tahunVal = !empty($aktiva->umur_aktiva_bulan) ? intdiv((int)$aktiva->umur_aktiva_bulan, 12) : 0;
                    $bulanVal = !empty($aktiva->umur_aktiva_bulan) ? (int)$aktiva->umur_aktiva_bulan % 12 : 0;
                @endphp

                <div class="col-md-3">
                    <label class="form-label fw-bold" for="umur_tahun">Umur (Tahun)</label>
                    <input type="number" min="0" name="umur_tahun" id="umur_tahun" class="form-control"
                        value="{{ old('umur_tahun', $tahunVal) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold" for="umur_bulan">Umur (Bulan)</label>
                    <input type="number" min="0" max="11" name="umur_bulan" id="umur_bulan" class="form-control"
                        value="{{ old('umur_bulan', $bulanVal) }}" required>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('aktiva') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
            </div>
        </form>
    </x-slot>
</x-theme.app>
