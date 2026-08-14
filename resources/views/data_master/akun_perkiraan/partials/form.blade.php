<div class="row">
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tipe Akun</label>
        <input id="{{ $prefix }}_tipe_akun" name="tipe_akun" class="form-control" maxlength="20" required>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Kode Perkiraan</label>
        <input id="{{ $prefix }}_kode_perkiraan" name="kode_perkiraan" class="form-control" maxlength="50" required>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Akun Induk</label>
        <select id="{{ $prefix }}_id_akun_induk" name="id_akun_induk" class="form-select select2">
            <option value="">Tanpa akun induk</option>
            @foreach ($akunInduk as $induk)
                <option value="{{ $induk->id_akun_perkiraan }}">{{ $induk->kode_perkiraan }} - {{ $induk->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-8 mb-3">
        <label class="form-label">Nama Akun</label>
        <input id="{{ $prefix }}_nama" name="nama" class="form-control" maxlength="255" required>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Cabang Saldo</label>
        <input id="{{ $prefix }}_cabang_saldo" name="cabang_saldo" class="form-control" maxlength="255">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Catatan</label>
        <textarea id="{{ $prefix }}_catatan" name="catatan" class="form-control" rows="3"></textarea>
    </div>
</div>
