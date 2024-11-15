<div class="row">
    <div class="col-lg-3">
        <label for="">Tanggal</label>
        <input type="date" name="tgl" id="" class="form-control" value="{{ $pakan->tgl }}">
        <input type="hidden" name="id_harga_pakan" id="" class="form-control"
            value="{{ $pakan->id_harga_pakan }}">
    </div>
    <div class="col-lg-3">
        <label for="">Pakan</label>
        <select name="id_pakan" id="" class="select">
            <option value="">-Pilih Pakan-</option>
            @foreach ($pakan_table as $p)
                <option value="{{ $p->id_produk }}" @selected($pakan->id_pakan == $p->id_produk)>{{ $p->nm_produk }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-2">
        <label for="">Kg</label>
        <input type="text" name="sak" class="form-control" value="{{ $pakan->ttl_gr }}">
    </div>
    <div class="col-lg-2">
        <label for="">total rp</label>
        <input type="text" name="total_rp" class="form-control" value="{{ $pakan->ttl_rp }}">
    </div>
    <div class="col-lg-2">
        <label for="">rp lain-lain</label>
        <input type="text" name="rp_lain" class="form-control" value="{{ $pakan->rp_lain }}">
    </div>

</div>
