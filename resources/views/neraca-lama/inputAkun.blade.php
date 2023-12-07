<form id="formTambahAkun">
    <div class="row">
        <input type="hidden" name="id_sub_kategori" class="form-control id_sub_kategori" value="{{$id_sub_kategori}}">
        <div class="col-lg-5">
            <label for="">Akun</label>
            <select name="id_akun" id="" class="form-control select">
                <option value="">-Pilih Akun-</option>
                @foreach ($akun as $a)
                <option value="{{$a->id_akun}}">{{$a->nm_akun}}</option>
                @endforeach

            </select>
        </div>
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
<form id="Editinputanakun">
    <div class="row">
        <div class="col-lg-12 mt-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="dhead">Akun</th>
                        <th class="dhead">Nominal</th>
                        <th class="dhead">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($akun_neraca as $c)
                    <tr>
                        <td>{{$c->nm_akun}}</td>
                        <td align="right">Rp {{number_format($c->debit - $c->kredit,0)}}</td>

                        <td align="center">
                            <a href="#" onclick="event.preventDefault();"
                                class="btn btn-sm btn-danger delete_akun_neraca"
                                id_akun_neraca="{{$c->id_akun_neraca }}" id_sub_kategori="{{$id_sub_kategori}}"><i
                                    class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</form>