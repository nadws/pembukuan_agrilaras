<div class="row">
    <input type="hidden" name="id_akun" value="{{ $akun->id_akun }}">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="">Subklasifikasi</label>
            <select name="id_klasifikasi" id="" class="select2-edit get_kode">
                <option value="">Pilih Subklasifikasi</option>
                @foreach ($subklasifikasi as $s)
                    <option {{ $s->id_subklasifikasi_akun == $akun->id_klasifikasi ? 'selected' : '' }}
                        value="{{ $s->id_subklasifikasi_akun }}">{{ $s->nm_subklasifikasi }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="">Kode Akun</label>
            <input type="text" value="{{ $akun->kode_akun }}" class="form-control kode" name="kode_akun" required>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="">Inisial</label>
            <input type="text" value="{{ $akun->inisial }}" class="form-control" name="inisial" required>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="form-group">
            <label for="">Nama Akun</label>
            <input type="text" value="{{ $akun->nm_akun }}" name="nm_akun" class="form-control">
        </div>
    </div>
    <div class="col-lg-12">
        <div class="form-group">
            <label for="">Iktisar Laba Rugi</label>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-check">
            <input {{ $akun->iktisar == 'Y' ? 'checked' : '' }} value="Y" required class="form-check-input"
                type="radio" name="iktisar" id="check1">
            <label class="form-check-label" for="check1">
                Ya
            </label>
        </div>

    </div>
    <div class="col-lg-2">
        <div class="form-check">
            <input {{ $akun->iktisar == 'T' ? 'checked' : '' }} value="T" required class="form-check-input"
                type="radio" name="iktisar" id="check2">
            <label class="form-check-label" for="check2">
                Tidak
            </label>
        </div>
    </div>
</div>
