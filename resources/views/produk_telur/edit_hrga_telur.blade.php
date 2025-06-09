<div class="row">
    <div class="col-lg-4">
        <label for="">Tanggal</label>
        <input type="date" class="form-control" name="tgl" value="{{ $get->tgl }}" required>
    </div>
    <div class="col-lg-4">
        <label for="">Grade</label>
        <input type="text" class="form-control" value="{{ $get->nm_telur }}" readonly>
    </div>
    <div class="col-lg-4">
        <label for="">Harga</label>
        <input type="text" class="form-control" name="harga" value="{{ $get->harga }}" required>
        <input type="hidden" class="form-control" name="id" value="{{ $get->id }}" required>
    </div>
</div>
