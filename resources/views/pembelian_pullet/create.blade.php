<x-theme.app title="Input Uang Muka Pullet" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">Input Uang Muka Pullet</h5>
                <small class="text-muted">Mencatat uang muka pullet sebelum ayam masuk kandang</small>
            </div>
            <a href="{{ route('pembelian-pullet.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Pembelian Pullet
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .pullet-form{border:1px solid #dce3f2;border-radius:12px;background:#fff;overflow:hidden}
            .pullet-form-head{padding:14px 16px;border-bottom:1px solid #dce3f2;background:#f5f7fc;color:#1d3167;font-weight:800}
            .pullet-form-body{padding:18px}.pullet-form .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}
            .mode-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.mode-card{display:flex;align-items:center;gap:10px;min-height:68px;padding:12px;border:1px solid #dce3f2;border-radius:10px;background:#f8fafc;cursor:pointer}.mode-card.is-active{border-color:#29468f;background:#eef3ff;box-shadow:0 0 0 3px rgba(41,70,143,.12)}.mode-card strong{color:#1d3167}
            .account-fixed{background:#edf1f7!important;color:#46556f}.flow-box{display:grid;grid-template-columns:1fr auto 1fr auto 1fr;align-items:center;gap:10px;padding:14px;border:1px solid #dce3f2;border-radius:10px;background:#f8fafc}.flow-step{text-align:center;color:#29468f;font-weight:700}.flow-step small{display:block;color:#718096;font-weight:400}.journal-box{padding:14px;border:1px solid #ffd38a;border-radius:10px;background:#fff9ed}.select2-container{width:100%!important}.select2-selection--single{height:40px!important;border-color:#dce3f2!important}.select2-selection__rendered{line-height:40px!important}.select2-selection__arrow{height:40px!important}
            @media(max-width:700px){.mode-grid{grid-template-columns:1fr}.flow-box{display:block}.flow-step{margin:8px 0}.flow-arrow{transform:rotate(90deg);text-align:center}}
        </style>

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger"><strong>Data belum dapat disimpan:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @php($mode = old('mode_pullet', $modeAwal ?? 'baru'))
        <div class="flow-box mb-3">
            <div class="flow-step"><i class="fas fa-money-check-alt me-1"></i> Uang Muka<small>Dicicil selama proses</small></div><div class="flow-arrow">→</div>
            <div class="flow-step"><i class="fas fa-warehouse me-1"></i> Masuk Kandang<small>Jumlah ekor ditentukan</small></div><div class="flow-arrow">→</div>
            <div class="flow-step"><i class="fas fa-boxes me-1"></i> Persediaan Ayam<small>Uang muka dibalik</small></div>
        </div>

        <form method="POST" action="{{ route('pembelian-pullet.store') }}" class="pullet-form">
            @csrf
            <div class="pullet-form-head">Input Pembayaran Pullet</div>
            <div class="pullet-form-body">
                <div class="mb-3">
                    <label class="form-label d-block">Jenis transaksi</label>
                    <div class="mode-grid">
                        <label class="mode-card {{ $mode === 'baru' ? 'is-active' : '' }}" data-mode="baru">
                            <input type="radio" name="mode_pullet" value="baru" @checked($mode === 'baru')>
                            <span><strong class="d-block">Buat uang muka baru</strong><small class="text-muted">Catat total pembelian dan pembayaran pertama</small></span>
                        </label>
                        <label class="mode-card {{ $mode === 'lama' ? 'is-active' : '' }}" data-mode="lama">
                            <input type="radio" name="mode_pullet" value="lama" @checked($mode === 'lama')>
                            <span><strong class="d-block">Tambah ke uang muka lama</strong><small class="text-muted">Lanjutkan pembayaran sampai lunas</small></span>
                        </label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 mode-lama">
                        <label class="form-label">Pilih uang muka pullet berjalan</label>
                        <select name="pembelian_pullet_id" class="form-select select-search" data-placeholder="Cari nama atau nomor pullet">
                            <option value="">-- Pilih Uang Muka Pullet --</option>
                            @foreach($pulletBerjalan as $p)
                                <option value="{{ $p->id }}" @selected(old('pembelian_pullet_id', $pulletTerpilih ?? null) == $p->id)>{{ $p->nomor }} - {{ $p->nama_pullet }}{{ $p->nm_suplier ? ' | '.$p->nm_suplier : '' }} | Sisa Rp {{ number_format(max((float)$p->total_nilai-(float)$p->total_dibayar,0),0,',','.') }}</option>
                            @endforeach
                        </select>
                        @if($pulletBerjalan->isEmpty())<small class="text-danger">Belum ada uang muka pullet yang masih berjalan.</small>@endif
                    </div>
                    <div class="col-md-4"><label class="form-label">Tanggal pembayaran</label><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal',now()->toDateString()) }}" required></div>
                    <div class="col-md-8 mode-baru"><label class="form-label">Supplier/Pemasok</label><select name="id_suplier" class="form-select select-search" data-placeholder="Cari supplier"><option value="">-- Pilih Supplier --</option>@foreach($suppliers as $sp)<option value="{{ $sp->id_suplier }}" @selected(old('id_suplier') == $sp->id_suplier)>{{ $sp->nm_suplier }}</option>@endforeach</select></div>
                    <div class="col-md-8 mode-baru"><label class="form-label">Nama/kelompok pullet</label><input name="nama_pullet" class="form-control" value="{{ old('nama_pullet') }}" placeholder="Contoh: Pullet Kandang A Periode 2026"></div>
                    <div class="col-md-4 mode-baru"><label class="form-label">Total nilai pembelian pullet</label><input type="number" name="nilai_pembelian" class="form-control" min="1" value="{{ old('nilai_pembelian') }}" placeholder="0"></div>
                    <div class="col-md-4"><label class="form-label"><span class="mode-baru">Pembayaran uang muka pertama</span><span class="mode-lama">Pembayaran uang muka lanjutan</span></label><input type="number" name="nominal" class="form-control" min="1" value="{{ old('nominal') }}" placeholder="0" required></div>
                    <div class="col-md-6"><label class="form-label">Akun penampung uang muka</label><input class="form-control account-fixed" value="110302 - Uang Muka Pembelian IDR" readonly></div>
                    <div class="col-md-6"><label class="form-label">Uang muka dibayar dari</label><select name="id_akun_pembayaran" class="form-select select-search" data-placeholder="Cari kas atau bank" required><option value="">-- Pilih Akun --</option>@foreach($accounts as $a)<option value="{{ $a->id_akun_perkiraan }}" @selected(old('id_akun_pembayaran') == $a->id_akun_perkiraan)>{{ $a->kode_perkiraan }} - {{ $a->nama }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">Keterangan / catatan pembayaran</label><textarea name="keterangan" class="form-control" rows="3" maxlength="1000" placeholder="Contoh: Uang muka tahap pertama sesuai kesepakatan supplier">{{ old('keterangan') }}</textarea></div>
                </div>

                <div class="journal-box mt-4"><strong class="d-block mb-1">Jurnal setiap pembayaran uang muka</strong><div>Debit: <b>Uang Muka Pembelian IDR</b></div><div>Kredit: <b>kas/bank yang dipilih</b></div><small class="text-muted">Setelah lunas dan ayam masuk kandang: Debit Persediaan Ayam, Kredit Uang Muka Pembelian IDR.</small></div>
                <div class="text-end mt-4"><a href="{{ route('pembelian-pullet.index') }}" class="btn btn-light me-1">Batal</a><button class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Pembayaran</button></div>
            </div>
        </form>

    </x-slot>

    @section('scripts')
        <script>
            (function () {
                function setMode() {
                    const checked = document.querySelector('input[name="mode_pullet"]:checked');
                    const mode = checked ? checked.value : 'baru';
                    document.querySelectorAll('.mode-card').forEach(card => card.classList.toggle('is-active', card.dataset.mode === mode));
                    document.querySelectorAll('.mode-baru').forEach(field => field.hidden = mode !== 'baru');
                    document.querySelectorAll('.mode-lama').forEach(field => field.hidden = mode !== 'lama');
                    const supplier = document.querySelector('[name="id_suplier"]');
                    const nama = document.querySelector('[name="nama_pullet"]');
                    const nilai = document.querySelector('[name="nilai_pembelian"]');
                    const pullet = document.querySelector('[name="pembelian_pullet_id"]');
                    if (supplier) supplier.required = mode === 'baru';
                    if (nama) nama.required = mode === 'baru';
                    if (nilai) nilai.required = mode === 'baru';
                    if (pullet) pullet.required = mode === 'lama';
                }
                function init() {
                    document.querySelectorAll('input[name="mode_pullet"]').forEach(input => input.addEventListener('change', setMode));
                    setMode();
                    if (window.jQuery && jQuery.fn.select2) {
                        jQuery('.select-search').select2({
                            width: '100%',
                            placeholder: function () { return jQuery(this).data('placeholder') || '-- Pilih --'; },
                            allowClear: true
                        });
                    }
                }
                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
            })();
        </script>
    @endsection
</x-theme.app>
