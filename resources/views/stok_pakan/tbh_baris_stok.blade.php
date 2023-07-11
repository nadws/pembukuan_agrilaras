<tr class="baris{{$count}}">

    <td>
        <select name="id_pakan[]" id="" class="select">
            <option value="">- Pilih Produk -</option>
            @foreach ($produk as $p)
            <option value="{{$p->id_produk}}">{{$p->nm_produk}}</option>
            @endforeach
        </select>
    </td>
    <td><input type="text" name="pcs[]" class="form-control"></td>
    <td><input type="text" name="ttl_rp[]" class="form-control"></td>
    <td>
        <button type="button" class="btn rounded-pill remove_baris" count="{{$count}}"><i
                class="fas fa-trash text-danger"></i>
        </button>
    </td>
</tr>