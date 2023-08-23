<table class="table">
    <tbody>
        <tr>
            <td width="15%">
                <input type="text" min="0" class="form-control" id="urutan" value="">
                <input type="hidden" min="0" class="form-control" id="kategori_idInput">
            </td>
            <td>
                <select id="id_akun" class="form-control select2-profit">
                    <option value="">- Pilih Akun -</option>
                    @foreach ($akun as $d)
                    <option value="{{ $d->id_akun }}">{{ ucwords($d->nm_akun) }}</option>
                    @endforeach
                </select>
            </td>
            <td width="15%">
                <button type="button" class="btn btn-primary btn-sm" id="btnSave"><i class="fas fa-check"></i>
                    Simpan</button>
            </td>
        </tr>
    </tbody>
</table>
<table class="table table-striped" id="table">
    <thead>
        <tr>
            <th width="1%">#</th>
            <th>Nama Akun</th>
            <th width="5%">Aksi</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($akunProfit as $a)
        <tr>
            <td width="15%">
                {{ $a->urutan }}
            </td>
            <td>
                {{ ucwords($a->nm_akun) }}
            </td>
            <td width="15%">
                <button type="button" class="btn btn-danger btn-sm btnHapus" id_profit="{{ $a->id_akunprofit }}"
                    id_kategori="{{ $a->kategori }}" ><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>