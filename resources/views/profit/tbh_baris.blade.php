<tr class="baris{{ $count }}">
    <td></td>
    <td><input type="text" name="dari[]" class="form-control form-control-sm">
    </td>
    <td><input type="text" name="sampai[]" class="form-control form-control-sm"></td>
    <td>
        <div class="form-group position-relative has-icon-right">
            <input type="text" name="persen[]" class="form-control form-control-sm">
            <div class="form-control-icon">
                {{-- <i class="bi bi-percent"></i> --}}
                %
            </div>
        </div>
    </td>
    <td style="vertical-align: top;">
        <button type="button" class="btn rounded-pill remove_baris" count="{{ $count }}"><i
                class="fas fa-trash text-danger"></i>
        </button>
    </td>
</tr>