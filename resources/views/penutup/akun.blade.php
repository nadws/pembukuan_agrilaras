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
                <input type="radio" name="iktisar[]" isi="Y" value="Y" id="" onclick="handleRadioClick(this)"
                    {{$a->iktisar == 'Y' ? 'checked' : ''}}>
            </td>
            <td align="center">
                <input type="radio" name="iktisar[]" isi="H" value="H" id="" onclick="handleRadioClick(this)"
                    {{$a->iktisar == 'H' ? 'checked' : ''}}>
            </td>
        </tr>
        @endforeach
    </tbody>


</Table>