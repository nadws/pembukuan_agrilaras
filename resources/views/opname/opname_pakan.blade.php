<div class="row">
    <div class="col-lg-4 mb-2">
        <label for="">Tanggal</label>
        <input type="date" class="form-control" name="tgl" value="{{date('Y-m-d')}}">
    </div>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <td class="dhead">Nama Pakan</td>
            <td class="dhead" style="text-align: right">Stok Program</td>
            <td class="dhead" style="text-align: right" width="25%">Stok Aktual</td>
            <td class="dhead" style="text-align: right">Selisih</td>
        </tr>
    </thead>
    <tbody style="border-color: #435EBE;">
        @foreach ($pakan as $no => $p)
        <tr>
            <td>
                {{ucwords(strtolower($p->nm_produk))}}
                <input type="hidden" name="id_pakan[]" value="{{$p->id_pakan}}">
            </td>
            <td style="text-align: right">
                {{$p->pcs_debit - $p->pcs_kredit}}
                <input type="hidden" name="stk_program[]" class="stk_program{{$no+1}}"
                    value="{{$p->pcs_debit - $p->pcs_kredit}}">
            </td>
            <td>
                <input type="text" name="stk_aktual[]" style="text-align: right"
                    class="form-control aktual aktual{{$no+1}}" count="{{$no+1}}"
                    value="{{$p->pcs_debit - $p->pcs_kredit}}">
            </td>
            <td style="text-align: right" class="selisih_pakan{{$no+1}}">0</td>
        </tr>
        @endforeach
    </tbody>
</table>