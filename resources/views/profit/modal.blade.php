<table class="table">
    <tbody>
        <tr>
            <td width="15%">
                <input type="text" min="0" class="form-control" id="urutan" value="">
                <input type="hidden" min="0" class="form-control" id="kategori_idInput">
            </td>
            <td>
                <select id="id_akun" class="form-control select2-profit" multiple>
                    <option value="">- Pilih Akun -</option>
                    @foreach ($akun1 as $d)
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
{{-- <div class="row">
    <div class="col-lg-4 mb-2">
        <table class="float-end">
            <td>Search :</td>
            <td><input type="text" id="pencarian" class="form-control float-end"></td>
        </table>
    
    
    </div>
    <div class="col-lg-12">
        <table class="table table-striped table-bordered" id="tablealdi">
            <thead>
                <tr>
                    <th width="1%">#</th>
                    <th>Nama Akun</th>
                    <th class="text-center">Masuk</th>
                </tr>
            </thead>
    
            <tbody>
                @php
                    $akunAll = DB::table('akun')->get();
                @endphp
                @foreach ($akunAll as $no => $d)
                    @php
                        $akunProfit = DB::table('akunprofit')->where([['kategori', $id_kategori], ['id_akun', $d->id_akun]])->first();
                    @endphp
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td>{{ ucwords($d->nm_akun) }}</td>
                        <td class="text-center">
                            <input name="id_akun[]" type="hidden" value="{{ $d->id_akun }}" class="checkbox">
                            <input name="kategori" value="{{$id_kategori}}" type="hidden" >
                            <input name="ceklis[]" {{!empty($akunProfit) ? 'checked' : ''}} value="{{$d->id_akun}}" type="checkbox" class="checkbox">
                        </td>
                        
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> --}}
    

