<form id="search_history_stk">
    <div class="row">
        <div class="col-lg-4 mb-2">
            <label for="">Dari</label>
            <input type="date" class="form-control" id="tgl1" value="{{$tgl1}}">
            <input type="hidden" class="form-control" id="id_pakan" value="{{$id_pakan}}">
        </div>
        <div class="col-lg-4 mb-2">
            <label for="">Sampai</label>
            <input type="date" class="form-control" id="tgl2" value="{{$tgl2}}">
        </div>
        <div class="col-lg-2">
            <label for="">Aksi</label> <br>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </div>
    </div>
</form>
<table class="table table-bordered">
    <thead>
        <tr>
            <td class="dhead">Tanggal</td>
            <td class="dhead">Nama Produk</td>
            <td class="dhead" style="text-align: right">Stok Masuk</td>
            <td class="dhead" style="text-align: right">Stok Keluar</td>
            <td class="dhead" style="text-align: right">Saldo</td>
            <td class="dhead">Opname</td>
            <td class="dhead" style="text-align: right">Admin</td>
        </tr>
    </thead>
    <tbody style="border-color: #435EBE;">
        @php
        $saldo = 0;
        @endphp
        @foreach ($stok as $s)
        @php
        $saldo += $s->pcs - $s->pcs_kredit
        @endphp
        <tr>
            <td>{{tanggal($s->tgl)}}</td>
            <td>{{$s->nm_produk}}</td>
            <td align="right">{{number_format($s->pcs,0)}}</td>
            <td align="right">{{number_format($s->pcs_kredit,0)}}</td>
            <td align="right">{{number_format($saldo,0)}}</td>
            <td align="center">
                <i
                    class="fas {{$s->h_opname == 'Y' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }} "></i>

            </td>
            <td>{{$s->admin}}</td>
        </tr>
        @endforeach

    </tbody>
</table>