<x-theme.app title="{{ $title }}" sizeCard="8">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Edit Saldo Awal Aktiva Gantung</h5>
                <small class="text-muted">{{ $aset->kode }} - {{ $aset->nama_aset }}</small>
            </div>
            <a href="{{ route('pembukuan-baru.aktiva-gantung.index') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        @if ($errors->any())
            <div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><div>{{ $errors->first() }}</div></div>
        @endif

        <form method="POST" action="{{ route('pembukuan-baru.aktiva-gantung.saldo-awal.update', $aset->id) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Tanggal saldo</label><input type="date" class="form-control" name="tanggal" value="{{ old('tanggal', $transaksi->tanggal) }}" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Nama aktiva gantung</label><input class="form-control" name="nama_aset" value="{{ old('nama_aset', $aset->nama_aset) }}" placeholder="Contoh: Kandang I" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Akun aktiva gantung</label><select class="form-select select2" name="id_akun_aktiva_gantung" required><option value="">Cari kode atau nama akun</option>@foreach($akunAktivaGantung as $akun)<option value="{{ $akun->id_akun_perkiraan }}" @selected((int) old('id_akun_aktiva_gantung', $transaksi->id_akun_aktiva_gantung) === (int) $akun->id_akun_perkiraan)>{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Saldo awal</label><input type="number" min="0.01" step="0.01" class="form-control text-end" name="jumlah" value="{{ old('jumlah', $transaksi->jumlah) }}" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Keterangan aktiva</label><input class="form-control" name="keterangan_aset" value="{{ old('keterangan_aset', $aset->keterangan) }}" placeholder="Opsional"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Keterangan transaksi</label><input class="form-control" name="keterangan_transaksi" value="{{ old('keterangan_transaksi', $transaksi->keterangan) }}" placeholder="Opsional"></div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('pembukuan-baru.aktiva-gantung.index') }}" class="btn btn-light">Batal</a>
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
            </div>
            <p class="text-muted small mt-3 mb-0">Perubahan tidak membuat jurnal baru karena jurnal perkiraan sudah diimpor sebelumnya.</p>
        </form>
    </x-slot>
</x-theme.app>
