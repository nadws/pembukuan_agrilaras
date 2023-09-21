<tr class="baris{{$count}}">
    <td>
        <select name="id_produk[]" required class="form-control select2-add produk-change" id="">
            <option value="">- Pilih Produk -</option>
            @foreach ($produk as $d)
            <option value="{{ $d->id_produk }}">{{ $d->nm_produk }}
                ({{ strtoupper($d->satuan->nm_satuan) }})
            </option>
            @endforeach
        </select>
    </td>
    {{-- <td>
        <input type="text" value="0" readonly class="form-control stok{{$count}}">
    </td>
    <td></td> --}}
    <td>
        <input name="qty[]" type="text" class="form-control qty qty{{$count}}" value="0">
    </td>
    <td>
        <input type="text" class="form-control dikanan setor-nohide text-end" value="Rp. 0" count="{{$count}}">
        <input type="hidden" class="form-control dikanan setor-hide setor-hide{{$count}}" value="" name="rp_satuan[]">
    </td>
    <td>
        <input readonly type="text" class="form-control dikanan ttlrp-nohide{{$count}} text-end" value="Rp. 0"
            count="{{$count}}">
        <input type="hidden" class="form-control dikanan ttlrp-hide ttlrp-hide{{$count}}" value="" name="total_rp[]">
    </td>
    <td align="center">
        <button type="button" class="btn rounded-pill remove_baris" count="{{$count}}"><i
                class="fas fa-trash text-danger"></i>
        </button>
    </td>
</tr>