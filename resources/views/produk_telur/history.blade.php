<style>
    .dhead {
        background-color: #435EBE !important;
        color: white;
        vertical-align: middle;
    }
</style>
<form id="search_history_mtd">
    <div class="row">
        <div class="col-lg-2">
            <label for="">Dari</label>
            <input type="date" id="tgl1" class="form-control" id="">
        </div>
        <div class="col-lg-2">
            <label for="">Sampai</label>
            <input type="date" id="tgl2" class="form-control" id="">
        </div>
        <div class="col-lg-1">
            <label for="">Aksi</label> <br>
            <button type="submit" class="btn btn-sm btn-primary">Search</button>
        </div>
        <div class="col-lg-2">
            <label for="">&nbsp;</label> <br>
            <a class="btn btn-success btn-sm" href="{{route('export_telur',['tgl1' => $tgl1, 'tgl2' => $tgl2])}}"><i
                    class="fas fa-download"></i> Export</a>
        </div>
</form>

<div class="col-lg-12 mt-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th rowspan="2" class="dhead">#</th>
                <th rowspan="2" class="dhead">Tanggal</th>
                <th rowspan="2" class="dhead">Kandang</th>
                @foreach ($produk as $p)
                <th colspan="2" class="dhead" style="text-align: center">{{$p->nm_telur}}</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($produk as $p)
                <th class="dhead" style="text-align: right">pcs</th>
                <th class="dhead" style="text-align: right">kg</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice as $no => $i)
            <tr>
                <td>{{$no+1}}</td>
                <td>{{tanggal($i->tgl)}}</td>
                <td>{{$i->nm_kandang}}</td>
                @foreach ($produk as $p)
                @php
                $telur = DB::selectOne("SELECT a.pcs, a.kg
                FROM stok_telur as a
                where a.tgl = '$i->tgl' and a.id_kandang = '$i->id_kandang' and a.id_telur = '$p->id_produk_telur'
                and a.nota_transfer = '0' ")
                @endphp
                <td align="right">{{empty($telur->pcs) ? '0' : number_format($telur->pcs,0)}}</td>
                <td align="right">{{empty($telur->kg) ? '0' : number_format($telur->kg,2)}}</td>
                @endforeach
            </tr>
            @endforeach

        </tbody>
    </table>
</div>
</div>