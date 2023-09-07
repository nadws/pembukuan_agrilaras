<table class="table table-striped ">
    <thead>
        <tr>
            <th class="dhead" width="2%">#</th>
            <th class="dhead" width="10%">Produk</th>
            <th class="dhead" width="10%" style="text-align: right">Pcs</th>
            <th class="dhead" width="10%" style="text-align: right">Kg</th>
            <th class="dhead" width="10%" style="text-align: right">Ikat</th>
            <th class="dhead" width="10%" style="text-align: right">Kg(-rak)</th>
            <th class="dhead" width="10%" style="text-align: right">Rp Satuan</th>
            <th class="dhead" width="10%" style="text-align: right">Total Rp</th>
            <th class="dhead" width="5%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoice as $no => $a)
        <tr class="baris{{$no+1}}">
            <td></td>
            <td>
                <select name="id_produk[]" class="select" required>
                    <option value="">-Pilih Produk-</option>
                    @foreach ($produk as $p)
                    <option value="{{$p->id_produk_telur}}" {{$a->id_produk == $p->id_produk_telur ? 'selected' :
                        ''}}>{{$p->nm_telur}}</option>
                    @endforeach
                </select>
            </td>
            <td align="right">
                <input type="text" class="form-control pcs pcs{{$no+1}}" count="{{$no+1}}" style="text-align: right"
                    required value="{{number_format($a->pcs,0,',','.')}}">
                <input type="hidden" class="form-control  pcs_biasa{{$no+1}}" name="pcs[]" value="{{$a->pcs}}">
            </td>
            <td align="right">
                <input type="text" class="form-control kg kg{{$no+1}}" count="{{$no+1}}" style="text-align: right"
                    required value="{{number_format($a->kg,2,',','.')}}">
                <input type="hidden" class="form-control kgbiasa kgbiasa{{$no+1}}" name="kg[]" count="{{$no+1}}"
                    value="{{$a->kg}}">
            </td>
            <td align="right" class="ikat_text{{$no+1}}">{{number_format($a->pcs / 180,1)}}</td>
            <td align="right" class="kgminrak{{$no+1}}">{{number_format($a->kg_jual,1)}}</td>
            <td align="right">
                <input type="text" class="form-control rp_satuan rp_satuan{{$no+1}}" count="{{$no+1}}"
                    style="text-align: right" required value="Rp {{number_format($a->rp_satuan,0,',','.')}}">

                <input type="hidden" class="kgminrakbiasa{{$no+1}}" name="kg_jual[]" value="{{$a->kg_jual}}">
                <input type="hidden" class="rp_satuanbiasa{{$no+1}}" name="rp_satuan[]" value="{{$a->rp_satuan}}">
                <input type="hidden" class="ttl_rpbiasa ttl_rpbiasa{{$no+1}}" name="total_rp[]"
                    value="{{$a->total_rp}}">


            </td>
            <td align="right" class="ttl_rp{{$no+1}}">Rp {{number_format($a->total_rp,2,',','.')}}</td>
            <td style="vertical-align: top;">
                <button type="button" class="btn rounded-pill remove_baris_kg" count="{{$no+1}}"><i
                        class="fas fa-trash text-danger"></i>
                </button>
            </td>
        </tr>
        @endforeach


    </tbody>
    <tbody id="tb_baris_kg">

    </tbody>
    <tfoot>
        <tr>
            <th colspan="9">
                <button type="button" class="btn btn-block btn-lg tbh_baris_kg"
                    style="background-color: #F4F7F9; color: #8FA8BD; font-size: 14px; padding: 13px;">
                    <i class="fas fa-plus"></i> Tambah Baris Baru

                </button>
            </th>
        </tr>
    </tfoot>


</table>