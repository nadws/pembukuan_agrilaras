<button type="submit" class="btn btn-primary float-end">Save</button> <br><br><br>
<table class="table table-bordered " id="tableProfitScroll" width="100%">
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
                        {{ $a->profit_loss == 'H' || $a->profit_loss == 'Y' || !empty($a->id_akun) ? 'checked' : '' }}
                        {{ $a->profit_loss == 'Y' || !empty($a->id_akun) ? 'disabled' : '' }}>

                    <input type="hidden" class="hasil_iktisar{{ $no + 1 }}" name="profit_loss[]"
                        value="{{ empty($a->id_akun) ? ($a->profit_loss == 'H' ? 'H' : 'T') : 'Y' }}">
                </td>
                <td><span class="badge {{ empty($a->id_akun) ? 'bg-danger' : 'bg-success' }}">
                        {{-- {{ empty($a->id_akun) ? 'Tidak Masuk' : 'Masuk' }} --}}
                        @php
                            $ket = [
                                'H' => 'Tidak Masuk',
                                'Y' => 'Masuk',
                                'T' => 'Kosong',
                            ];
                        @endphp
                        {{ $ket[$a->profit_loss] }}
                    </span>
                </td>

            </tr>
        @endforeach
    </tbody>

</table>
