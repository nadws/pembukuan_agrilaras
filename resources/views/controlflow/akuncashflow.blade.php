<table class="table table-bordered" id="table3">
    <thead>
        <tr>
            <th>No</th>
            <th>Akun</th>
            <th>Ket</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($akun as $no => $a)
        <tr>
            <td>{{$no+1}}</td>
            <td>{{ucwords(strtolower($a->nm_akun))}}</td>
            <td><span class="badge {{ empty($a->id_akun) ? 'bg-danger' : 'bg-success' }}">
                    {{empty($a->id_akun) ? 'Tidak Masuk' : 'Masuk' }}
                </span></td>
        </tr>
        @endforeach
    </tbody>

</table>