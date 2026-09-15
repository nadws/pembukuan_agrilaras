<x-theme.app title="Edit Pelunasan Piutang" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h5 class="mb-1">Edit Pelunasan {{ ucfirst($jenis) }} — {{ $row->no_nota }}</h5><small class="text-muted">Perubahan nominal, akun, dan tanggal ikut menyesuaikan jurnal satu voucher.</small></div>
            <a href="{{ $kembali }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>.settle-box{border:1px solid #dce3f2;border-radius:12px;padding:16px;background:#f5f7fc}.settle-box .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}.settle-box .form-control,.settle-box .form-select{min-height:40px;border-color:#dce3f2;border-radius:8px}.settle-info{padding:14px 16px;border:1px solid #d7e1f5;border-radius:12px;background:#fff}.settle-info small{display:block;color:#68758d;font-size:11px;font-weight:700;text-transform:uppercase}.settle-info strong{color:#193875;font-size:18px}</style>
        @if($errors->any())<div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>@endif
        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="settle-info"><small>Customer</small><strong>{{ $row->nm_customer ?? '-' }}</strong></div></div>
            <div class="col-md-4"><div class="settle-info"><small>Nilai nota</small><strong>Rp {{ number_format($invoiceTotal,0,'.',',') }}</strong></div></div>
            <div class="col-md-4"><div class="settle-info"><small>Sisa di luar baris ini</small><strong>Rp {{ number_format($outstanding,0,'.',',') }}</strong></div></div>
        </div>
        <form method="POST" action="{{ route('transaksi.piutang.pelunasan.update', $row->id) }}">
            @csrf
            @method('PUT')
            <div class="settle-box">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3"><label class="form-label">Tanggal pembayaran</label><input type="date" name="tanggal_bayar" class="form-control" value="{{ old('tanggal_bayar', $row->tanggal_bayar) }}" required></div>
                    <div class="col-md-5"><label class="form-label">Dibayar melalui akun</label><select name="id_akun_pembayaran" class="form-select select2" required><option value="">Pilih kas atau bank</option>@foreach($akunPembayaran as $akun)<option value="{{ $akun->id_akun_perkiraan }}" @selected((int) old('id_akun_pembayaran', $row->id_akun_pembayaran) === (int) $akun->id_akun_perkiraan)>{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Jumlah bayar (Rp)</label><input type="number" name="jumlah_bayar" class="form-control text-end" value="{{ old('jumlah_bayar', $row->jumlah_bayar) }}" min="1" step="1" required></div>
                    <div class="col-md-8"><label class="form-label">Penyelesaian</label><select name="jenis_selisih" class="form-select"><option value="tidak" @selected(old('jenis_selisih', $row->jenis_selisih) === 'tidak')>Tanpa selisih / cicilan</option><option value="lebih" @selected(old('jenis_selisih', $row->jenis_selisih) === 'lebih')>Lebih bayar — lunaskan</option><option value="kurang" @selected(old('jenis_selisih', $row->jenis_selisih) === 'kurang')>Kurang bayar — lunaskan</option></select></div>
                    <div class="col-md-4 text-md-end"><button type="submit" class="btn btn-success w-100"><i class="fas fa-save me-1"></i> Simpan Perubahan</button></div>
                </div>
            </div>
        </form>
        <p class="text-muted small mt-3 mb-0">Nominal lama: Rp {{ number_format($row->jumlah_bayar,0,'.',',') }} (dilunasi Rp {{ number_format($row->nilai_piutang_dilunasi,0,'.',',') }}) · Status nota ikut dihitung ulang.</p>
    </x-slot>
</x-theme.app>
