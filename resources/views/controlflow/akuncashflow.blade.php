<button type="submit" class="btn btn-primary float-end">Save</button> <br><br><br>
<table class="table table-bordered " id="tableScroll" width="100%">
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
                <td>{{ $no + 1 }}</td>
                <td>{{ ucwords(strtolower($a->nm_akun)) }}</td>
                <td align="center">
                    <input type="hidden" name="id_akun[]" value="{{ $a->akun_id }}">
                    <input type="checkbox" class="iktisar iktisar{{ $no + 1 }}" urutan="{{ $no + 1 }}"
                        isi="H" value="H" id=""
                        {{ $a->cash_uang_ditarik == 'H' || $a->cash_uang_ditarik == 'Y' || !empty($a->id_akun) ? 'checked' : '' }}
                        {{ $a->cash_uang_ditarik == 'Y' || !empty($a->id_akun) ? 'disabled' : '' }}>

                    {{-- <input type="text" class="hasil_iktisar{{$no+1}}" name="cash_uang_ditarik[]"
                    value="{{$a->cash_uang_ditarik}}"> --}}

                    <input type="hidden" class="hasil_iktisar{{ $no + 1 }}" name="cash_uang_ditarik[]"
                        value="{{ empty($a->id_akun) ? ($a->cash_uang_ditarik == 'H' ? 'H' : 'T') : 'Y' }}">
                </td>
                <td><span class="badge {{ empty($a->id_akun) ? 'bg-danger' : 'bg-success' }}">
                        @php
                            $ket = [
                                'H' => 'Tidak Masuk',
                                'Y' => 'Masuk',
                                'T' => 'Kosong',
                            ];
                        @endphp
                        {{ $ket[$a->cash_uang_ditarik] }}

                    </span>
                </td>

            </tr>
        @endforeach
    </tbody>

</table>
