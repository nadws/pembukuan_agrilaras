<div class="row">
    <div class="col-lg-12" id="loading" style="display: none;">
        <h3 class="text-center">Loading .... <i class="fas fa-spinner fa-spin"></i></h3>
    </div>

    <div class="col-lg-6 loading-hide">
        <table class="table table-bordered table-striped">
            <thead>
                <tr class="text-center">
                    <th class="dhead" rowspan="2">No</th>
                    <th class="dhead" colspan="2">Range Umur</th>
                    <th class="dhead" width="85" rowspan="2">Persen</th>
                </tr>
                <tr class="text-center">
                    <th class="dhead">Dari</th>
                    <th class="dhead">Sampai</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($hd_persen as $no => $h)
                <tr>
                    <td>{{$no+1}}</td>
                    <input type="hidden" name="id_persen_budget[]" value="{{$h->id_persen_budget}}">
                    <td><input type="text" name="dari[]" class="form-control form-control-sm" value="{{$h->umur_dari}}">
                    </td>
                    <td><input type="text" name="sampai[]" class="form-control form-control-sm"
                            value="{{$h->umur_sampai}}"></td>
                    <td>
                        <div class="form-group position-relative has-icon-right">
                            <input type="text" name="persen[]" class="form-control form-control-sm"
                                value="{{$h->persen}}">
                            <div class="form-control-icon">
                                {{-- <i class="bi bi-percent"></i> --}}
                                %
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
        {{-- <button type="submit" class="btn btn-sm btn-primary float-end">Simpan</button> --}}

    </div>
    <div class="col-lg-3 loading-hide">
        <label for="">Gr per Butir</label>
        <input type="text" class="form-control" name="jumlah[]" value="{{$kg_butir->jumlah}}">
        <input type="hidden" class="form-control" name="id_rules_budget[]" value="{{$kg_butir->id_rules_budget}}">
    </div>
    <div class="col-lg-3 loading-hide">
        <label for="">Rp Per Kg</label>
        <input type="text" class="form-control" name="jumlah[]" value="{{$rp_kg->jumlah}}">
        <input type="hidden" class="form-control" name="id_rules_budget[]" value="{{$rp_kg->id_rules_budget}}">
    </div>
</div>