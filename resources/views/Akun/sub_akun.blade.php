<table class="table" id="table-edit">
    <thead>
        <tr>
            <th>#</th>
            <th>Sub Akun</th>
            <th class="text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <td><input type="text" class="form-control" id="name-sub"></td>
            <td align="center"><button type="button" id_akun="{{ $id_akun }}"
                    class="save-sub btn btn-info">Save</button></td>
        </tr>
        @foreach ($detail as $no => $d)
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ ucwords(strtoupper($d->nm_post)) }}</td>
                <td align="center">
                    <button type="button" class="btn rounded-pill edit-sub" id_sub="{{ $d->id_post_center }}"><i
                            class="fas fa-pen text-info"></i>
                    </button>
                    <button type="button" id_akun="{{ $id_akun }}" id_sub_akun="{{ $d->id_post_center }}"
                        class="btn rounded-pill remove-sub" count="1"><i class="fas fa-trash text-danger"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
