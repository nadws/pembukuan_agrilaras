<x-theme.app title="{{ $title }}" table="Y" sizeCard="8">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <small class="text-muted">Koreksi akun kas/bank untuk pembayaran {{ $nomor_transaksi }}</small>
            </div>
            <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pelunasan-hutang']) }}"
                class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST"
            action="{{ route('pembukuan-baru.jurnal-umum.pelunasan-hutang.update', $nomor_transaksi) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nomor transaksi</label>
                    <input type="text" class="form-control" value="{{ $nomor_transaksi }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal bayar</label>
                    <input type="date" class="form-control" value="{{ $tanggal }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jumlah bayar</label>
                    <input type="text" class="form-control"
                        value="Rp {{ number_format($jumlah_bayar, 0, ',', '.') }}" readonly>
                </div>
                <div class="col-12">
                    <label class="form-label" for="id_akun_kas">Dibayar melalui akun</label>
                    <select id="id_akun_kas" name="id_akun_kas" class="form-select select-search" required>
                        <option value="">-- Pilih kas / bank --</option>
                        @foreach ($akunKas as $akun)
                            <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_kas', $id_akun_kas) == $akun->id_akun_perkiraan)>
                                {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pelunasan-hutang']) }}"
                    class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan perubahan
                </button>
            </div>
        </form>
    </x-slot>
</x-theme.app>
