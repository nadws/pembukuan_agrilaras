<div class="row baris{{ $count }}">
    <div class="col-lg-3 mt-2">

        <input type="date" name="tgl[]" id="" class="form-control">
    </div>
    <div class="col-lg-2 mt-2">

        <select name="id_pakan[]" id="" class="select">
            <option value="">-Pilih Pakan-</option>
            @foreach ($pakan_table as $p)
                <option value="{{ $p->id_produk }}">{{ $p->nm_produk }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-2 mt-2">

        <input type="text" name="sak[]" class="form-control">
    </div>
    <div class="col-lg-2 mt-2">

        <input type="text" name="total_rp[]" class="form-control">
    </div>
    <div class="col-lg-2 mt-2">

        <input type="text" name="rp_lain[]" class="form-control">
    </div>
    <div class="col-lg-1 mt-2">

        <button type="button" class="btn btn-sm btn-danger minus_hrga_pakan" count="{{ $count }}"><i
                class="fas fa-minus"></i></button>
    </div>
</div>
