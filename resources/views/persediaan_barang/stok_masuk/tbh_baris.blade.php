<tr class="baris{{$count}}">
    <td style="vertical-align: top;">
        <select required name="id_produk[]" class="form-control select2 produk-change" count="{{ $count }}" id="">
            <option value="">- Pilih Produk -</option>
            @foreach ($produk as $d)
                <option value="{{ $d->id_produk }}">{{ $d->nm_produk }} ({{strtoupper($d->satuan->nm_satuan)}})</option>
            @endforeach
            <option value="tambahProduk">+ Produk</option>
        </select>
    </td>
    <td style="vertical-align: top;" align="center">
        <input class="form-control stok-sebelumnya{{$count}}" style="text-align:right;" type="text" readonly  name="jml_sebelumnya[]" value="0">
    </td>
    <td style="vertical-align: top;" align="center">
        <input required name="debit[]" style="text-align:right;" value="0"
             type="text"
            class="form-control debit-keyup">
    </td>
    <td style="vertical-align: top;" align="center">
        <input type="text" class="form-control dikanan rp-nohide text-end"
            value="Rp 0" count="{{ $count }}">
        <input type="hidden" class="form-control dikanan rp-hide rp-hide{{$count}}" value=""
            name="rp_satuan[]">
    </td>
    <td style="vertical-align: top;">
        <button type="button" class="btn rounded-pill remove_baris" count="{{ $count }}"><i
                class="fas fa-trash text-danger"></i>
        </button>
    </td>

</tr>