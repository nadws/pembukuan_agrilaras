<form id="formTambahSubAkun">
    <div class="row">
        <div class="col-lg-7">
            <input type="hidden" class="id_kategori_akun" name="id_kategori" value="{{$id_kategori}}">
            <label for="">Akun</label>
            <select name="id_akun" id="" class="select">
                <option value="">--Pilih Akun--</option>
                @foreach ($akun as $a)
                <option value="{{$a->id_akun}}">{{$a->nm_akun}}</option>
                @endforeach
            </select>
        </div>
        {{-- <div class="col-lg-5">
            <label for="">Kategori</label>
            <input type="text" name="nama" class="form-control">
        </div> --}}
        <div class="col-lg-2">
            <label for="">Aksi</label> <br>
            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
        </div>
    </div>
</form>

<style>
    .dhead {
        background-color: #435EBE !important;
        color: white;
    }
</style>
<div class="row">
    <div class="col-lg-12 mt-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="dhead">Akun</th>
                    {{-- <th class="dhead" style="text-align: right">Rupiah</th> --}}
                    <th class="dhead">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($akun2 as $c)
                <tr>
                    <td>{{ucwords(strtolower($c->nm_akun))}}</td>
                    {{-- <td align="right">Rp. {{$c->jenis == '1' ? number_format($c->kredit,0) :
                        number_format($c->debit,0)}}
                    </td> --}}
                    <td align="center"><a href="#" onclick="event.preventDefault();"
                            class="btn btn-sm btn-danger delete_akun" id_kategori="{{$c->id_kategori_cashcontrol}}"
                            id_akuncontrol="{{$c->id_akuncontrol}}"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>