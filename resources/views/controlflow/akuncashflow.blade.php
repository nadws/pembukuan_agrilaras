<table class="table table-bordered" id="tableScroll" width="100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Akun</th>
            <th>Tidak Masuk</th>
            <th>Ket</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($akun as $no => $a)
        <tr>
            <td>{{$no+1}}</td>
            <td>{{ucwords(strtolower($a->nm_akun))}}</td>
            <td align="center">
                <input type="checkbox" class="cek_masuk" name="tidak_masuk[]" value="H" {{ empty($a->id_akun) ?
                ($a->cash_uang_ditarik ==
                'H' ?
                'checked' : '') : 'checked' }}>
                <input type="text" class="tidak_masuk" name="tidak_masuk[]" value="{{ empty($a->id_akun) ? ($a->cash_uang_ditarik ==
                    'H' ? 'H' : 'T') : 'H' }}">
            </td>
            <td><span
                    class="badge {{ empty($a->id_akun) ? ($a->cash_uang_ditarik == 'H' ? 'bg-success' : 'bg-danger') : 'bg-success' }}">
                    {{empty($a->id_akun) ? ($a->cash_uang_ditarik == 'H' ? 'Masuk' : 'Tidak Masuk') : 'Masuk' }}
                </span>
            </td>

        </tr>
        @endforeach
    </tbody>

</table>