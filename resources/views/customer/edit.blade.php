<div class="row">
    <input type="hidden" value="{{ $customer->id_customer }}" name="id_customer">
    <div class="col-lg-12">
        <div class="form-group">
            <label for="">Nama</label>
            <input value="{{ $customer->nm_customer }}" type="text" name="nm_customer" class="form-control">
        </div>
    </div>
    <div class="col-lg-12">
        <div class="form-group">
            <label for="">Alamat</label>
            <input value="{{ $customer->alamat }}" type="text" name="alamat" class="form-control">
        </div>
    </div>

    <div class="col-lg-12">
        <div class="form-group">
            <label for="">Telepon</label>
            <input value="{{ $customer->no_telp }}" type="text" name="telepon" class="form-control">
        </div>
    </div>
    <div class="col-lg-12">
        <div class="form-group">
            <label for="">Npwp</label>
            <input value="{{ $customer->npwp }}" type="text" name="npwp" class="form-control">
        </div>
    </div>
    <div class="col-lg-12">
        <div class="form-group">
            <label for="">KTP</label>
            <input value="{{ $customer->ktp }}" type="text" name="ktp" class="form-control">
        </div>
    </div>
</div>
