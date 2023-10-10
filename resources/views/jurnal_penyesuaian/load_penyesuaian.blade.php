@if (empty($id_akun))
@else
    <table class="table">
        <thead>
            <tr>
                <th class="dhead text-center">Bulan & Tahun</th>
                <th class="dhead text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cancel_jurnal as $b)
                <tr>
                    <td class="text-center">{{ date('M-Y', strtotime($b->tgl)) }}</td>
                    <td class="text-center"><input type="radio" name="tgl1" id="" value="{{ $b->tgl }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <input type="hidden" name="tgl2" value="{{ $b->tgl }}">
@endif
