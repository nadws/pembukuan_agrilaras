{{-- <table class="table">
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
</table> --}}
<div class="row">
    <div class="col-lg-7"></div>
    <div class="col-lg-5 mb-2">
        <table class="float-end">
            <td>Search :</td>
            <td><input type="text" id="pencarian" class="form-control float-end"></td>
        </table>
    </div>
</div>
<table class="table table-bordered" id="table_sc">
    <thead>
        <tr>
            <th class="dhead" width="1%">#</th>
            <th class="dhead">Nama Akun</th>
            <th class="dhead" width="5%">Masuk</th>
            <th class="dhead" width="5%">Keterangan</th>
            <th class="dhead" width="5%">Aksi</th>
        </tr>
    </thead>
    <input type="hidden" value="{{$id_kategori}}" name="kategori">

    <tbody>
        @foreach ($akun1 as $no => $a)
        @php
        $akun_profit = DB::selectOne("SELECT a.id_akunprofit, a.kategori
        FROM akunprofit as a
        where a.id_akun = $a->id_akun
        ");

        $akun_tes = empty($akun_profit->kategori) ? ' ' : $akun_profit->kategori;

        if ($akun_tes == '1') {
        $ket = 'Penjualan';
        } elseif($akun_tes == '4') {
        $ket = 'Biaya';
        } elseif($akun_tes == '5') {
        $ket = 'Biaya Penyesuaian';
        } elseif($akun_tes == '9') {
        $ket = 'Uang Keluar';
        }


        @endphp


        <tr>
            <td width="15%">
                {{ $no+1 }}
            </td>
            <td>
                {{ $a->nm_akun }}
            </td>
            <td width="15%" align="center">
                <input type="hidden" name="id_akun[]" value="{{$a->id_akun}}">
                <input type="checkbox" name="" id="" class="iktisar iktisar{{ $no + 1 }}" urutan="{{ $no + 1 }}"
                    {{$a->profit_loss == 'T' ? '' : 'Checked disabled'}}>
                <input type="hidden" class="hasil_iktisar{{ $no + 1 }}" name="profit_loss[]" value="T">
            </td>
            <td>
                <span
                    class="badge {{$a->profit_loss == 'T' ? 'bg-danger' : ($a->profit_loss == 'H' ? 'bg-warning' : 'bg-success' )}}">
                    {{$a->profit_loss == 'T' ? 'Kosong' : ($a->profit_loss == 'H' ? 'Tidak Masuk' : $ket )}}
                </span>

            </td>
            <td>
                @if (empty($akun_profit->id_akunprofit))

                @else
                <button type="button" class="btn btn-danger btn-sm btnHapus"
                    id_profit="{{ $akun_profit->id_akunprofit }}" id_kategori="{{ $akun_profit->kategori }}"><i
                        class="fas fa-trash"></i></button>
                @endif

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
                $akunProfit = DB::table('akunprofit')->where([['kategori', $id_kategori], ['id_akun',
                $d->id_akun]])->first();
                @endphp
                <tr>
                    <td>{{ $no + 1 }}</td>
                    <td>{{ ucwords($d->nm_akun) }}</td>
                    <td class="text-center">
                        <input name="id_akun[]" type="hidden" value="{{ $d->id_akun }}" class="checkbox">
                        <input name="kategori" value="{{$id_kategori}}" type="hidden">
                        <input name="ceklis[]" {{!empty($akunProfit) ? 'checked' : '' }} value="{{$d->id_akun}}"
                            type="checkbox" class="checkbox">
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> --}}