<tr>
    <td>{{$no+1}}</td>
    <input type="hidden" name="id_persen_budget[]" value="{{$h->id_persen_budget}}">
    <td><input type="text" name="dari[]" class="form-control form-control-sm" value="{{$h->umur_dari}}">
    </td>
    <td><input type="text" name="sampai[]" class="form-control form-control-sm" value="{{$h->umur_sampai}}"></td>
    <td>
        <div class="form-group position-relative has-icon-right">
            <input type="text" name="persen[]" class="form-control form-control-sm" value="{{$h->persen}}">
            <div class="form-control-icon">
                {{-- <i class="bi bi-percent"></i> --}}
                %
            </div>
        </div>
    </td>
</tr>