<style>
    .dhead {
        background-color: #435EBE !important;
        color: white;
    }
</style>
<div class="row">
    <div class="col-lg-6">
        <form id="history_serach">
            <div class="row">
                <div class="col-lg-5">
                    <label for="">Dari</label>
                    <input type="date" class="form-control tgl1" name="tgl1" value="{{$tgl1}}">
                </div>
                <div class="col-lg-5">
                    <label for="">Sampai</label>
                    <input type="date" class="form-control tgl2" name="tgl2" value="{{$tgl2}}">
                </div>
                <div class="col-lg-2">
                    <label for="">Aksi</label>
                    <br>
                    <button type="submit" class="btn btn-sm btn-primary">Search</button>
                </div>
            </div>
        </form>
    </div>

</div>
<br>
<br>
<table class="table table-bordered">
    <thead>
        <tr>
            <th class="dhead">Tanggal</th>
            <th class="dhead">Nota</th>
            <th class="dhead">Akun</th>
            <th class="dhead" style="text-align: right">Total Rupiah</th>
            <th class="dhead">Keterangan</th>
            <th class="dhead">Aksi</th>

        </tr>
    </thead>
    <tbody>
        @foreach ($invoice as $i)
        <tr>
            <td>{{tanggal($i->tgl)}}</td>
            <td>{{$i->nota_setor}}</td>
            <td>{{$i->nm_akun}}</td>
            <td align="right">Rp {{number_format($i->nominal,0)}}</td>
            <td>{{$i->selesai == 'Y' ? 'Sudah setor' : 'Perencanaan'}}</td>
            <td>
                <a href="{{route('penjualan_ayam.print_setoran',['no_nota' => $i->nota_setor])}}" target="_blank"
                    class="btn btn-sm btn-success"><i class="fas fa-print"></i></a>

                <a href="{{route('penjualan_ayam.delete_perencanaan',['no_nota' => $i->nota_setor])}}" class="btn btn-sm btn-danger"><i
                        class="fas fa-trash-alt"></i></a>
            </td>
        </tr>
        @endforeach
    </tbody>

</table>