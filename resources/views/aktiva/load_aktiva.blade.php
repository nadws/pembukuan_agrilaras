<table class="table table-striped">
    <thead>
        <tr>
            <th width="15%">Kelompok</th>
            <th width="15%">Nama Aktiva</th>
            <th width="14%">Tanggal Perolehan</th>
            <th width="14%">Nilai Perolehan</th>
            <th width="10%">Nilai/tahun (%)</th>
            <th width="10%" style="text-align: center">Umur</th>
            <th width="14%">Penyusutan Perbulan</th>
            <th width="5%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <tr class="baris1">
            <td>
                <select name="id_kelompok[]" id="" class="select pilih_kelompok pilih_kelompok1"
                    count='1'>
                    <option value="">Pilih Kelompok</option>
                    @foreach ($kelompok as $k)
                        <option value="{{ $k->id_kelompok }}">{{ $k->nm_kelompok }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="nm_aktiva[]" class="form-control "></td>
            <td><input type="date" name="tgl[]" class="form-control"></td>
            <td>
                <input type="text" class="form-control nilai_perolehan nilai_perolehan1" count='1'>
                <input type="hidden" name="h_perolehan[]" class="form-control  nilai_perolehan_biasa1">
            </td>
            <td>
                <p class="nilai_persen1 text-center"></p>
                <input type="hidden" class="inputnilai_persen1">
            </td>
            <td>
                <p class="umur1 text-center"></p>
            </td>
            <td>
                <p class="susut_bulan1 text-center"></p>
            </td>
            <td>
                <button type="button" class="btn rounded-pill remove_baris" count="1"><i
                        class="fas fa-trash text-danger"></i>
                </button>
            </td>
        </tr>
        <tr class="baris2">
            <td>
                <select name="id_kelompok[]" id="" class="select pilih_kelompok pilih_kelompok2"
                    count='2'>
                    <option value="">Pilih Kelompok</option>
                    @foreach ($kelompok as $k)
                        <option value="{{ $k->id_kelompok }}">{{ $k->nm_kelompok }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="nm_aktiva[]" class="form-control"></td>
            <td><input type="date" name="tgl[]" class="form-control"></td>
            <td>
                <input type="text" class="form-control nilai_perolehan nilai_perolehan2" count="2">
                <input type="hidden" name="h_perolehan[]" class="form-control  nilai_perolehan_biasa2">
            </td>
            <td>
                <p class="nilai_persen2 text-center"></p>
                <input type="hidden" class="inputnilai_persen2">
            </td>
            <td>
                <p class="umur2 text-center"></p>
            </td>
            <td>
                <p class="susut_bulan2 text-center"></p>
            </td>
            <td>
                <button type="button" class="btn rounded-pill remove_baris" count="2"><i
                        class="fas fa-trash text-danger"></i>
                </button>
            </td>
        </tr>

    </tbody>
    <tbody id="tb_baris_aktiva">

    </tbody>
    <tfoot>
        <tr>
            <th colspan="9">
                <button type="button" class="btn btn-block btn-lg tbh_baris_aktiva"
                    style="background-color: #F4F7F9; color: #8FA8BD; font-size: 14px; padding: 13px;">
                    <i class="fas fa-plus"></i> Tambah Baris Baru

                </button>
            </th>
        </tr>
    </tfoot>
</table>
