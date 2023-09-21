<style>
    .dhead {
        background-color: #435EBE !important;
        color: white;
    }
</style>
<table class="table table-bordered">
    <thead>
        <tr>
            <th class="dhead">Tanggal</th>
            <th class="dhead">Nota</th>
            <th class="dhead">Akun</th>
            <th class="dhead" style="text-align: right">Total Rupiah</th>
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
            <td>
                <a href="#" onclick="event.preventDefault();" class="btn btn-sm btn-success perencanaan"
                    nota_setor="{{$i->nota_setor}}">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{route('penjualan_ayam.print_setoran',['no_nota' => $i->nota_setor])}}" target="_blank"
                    class="btn btn-sm btn-success"><i class="fas fa-print"></i></a>
                <a onclick="return confirm('Yakin dihapus ?')"
                    href="{{route('penjualan_ayam.delete_perencanaan',['no_nota' => $i->nota_setor])}}"
                    class="btn btn-sm btn-danger delete_nota"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        @endforeach
    </tbody>

</table>