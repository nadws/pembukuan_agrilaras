<button type="submit" class="btn btn-primary float-end">Save</button> <br><br><br>
<Table class="table table-bordered " id="tableScroll" width="100%">
    <thead>
        <tr>
            <th class="dhead">#</th>
            <th class="dhead">Nama Akun</th>
            <th class="dhead" style="text-align: center">Masuk</th>
            <th class="dhead" style="text-align: center">Tidak Masuk</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($akun as $no => $a)
        <tr>
            <td>{{$no+1}}</td>
            <td>{{$a->nm_akun}}</td>
            <td align="center">
                <input type="hidden" name="id_akun[]" value="{{$a->id_akun}}">
                <input type="checkbox" class="iktisar1 iktisarA{{$no+1}} " urutan="{{$no+1}}" isi="Y" value="Y" id=""
                    {{$a->iktisar == 'Y' ? 'checked' : ''}}>
            </td>
            <td align="center">
                <input type="checkbox" class="iktisar2 iktisarB{{$no+1}}" urutanB="{{$no+1}}" isi="H" value="H" id=""
                    {{$a->iktisar == 'H'? 'checked' : ''}}>
                <input type="hidden" class="hasil_iktisar{{$no+1}}" name="iktisar[]" value="{{$a->iktisar}}">
            </td>
        </tr>
        @endforeach
    </tbody>
</Table>