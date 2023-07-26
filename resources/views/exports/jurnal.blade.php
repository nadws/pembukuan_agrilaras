<table>
    <thead>
        <tr>
            <th width="39px">#</th>
            <th width="110px">Tanggal</th>
            <th width="117px">No Nota</th>
            <th width="90px">No Urut</th>
            <th width="90px">No Urutan Pengeluaran</th>
            <th width="100px">Kode Akun</th>
            <th width="200px">Akun</th>
            <th width="200px">Akun VS</th>
            <th width="100px">Sub Akun</th>
            <th width="149px">Proyek</th>
            <th width="149px">Keterangan</th>
            <th width="129px" style="text-align: right">Debit</th>
            <th width="70px">Admin</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($jurnal as $no => $a)
        <tr>
            <td>{{$no + 1}}</td>
            <td>{{$a->tgl}}</td>
            <td>{{$a->no_nota}}</td>
            <td>{{$a->no_urut}}</td>
            <td>{{$a->no_dokumen}}</td>
            <td>{{$a->kode_akun}}</td>
            <td>{{$a->nm_akun}}</td>
            <td>{{$a->nm_akun_vs}}</td>
            <td>{{$a->nm_post}}</td>
            <td>{{empty($a->nm_proyek) ? '' : $a->nm_proyek }}</td>
            <td>{{$a->ket}}</td>
            <td align="right">{{$a->debit}}</td>
            <td>{{$a->admin}}</td>
        </tr>
        @endforeach
    </tbody>
</table>