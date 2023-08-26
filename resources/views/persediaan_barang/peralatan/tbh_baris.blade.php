<tr class="baris{{ $count }}">
    <td>
        <select name="id_kelompok[]" id="" class="select pilih_kelompok pilih_kelompok{{ $count }}"
            count='{{ $count }}'>
            <option value="">Pilih Kelompok</option>
            @foreach ($kelompok as $k)
                <option value="{{ $k->id_kelompok }}">{{ $k->nm_kelompok }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="text" name="nm_aktiva[]" class="form-control"></td>
    <td><input type="date" name="tgl[]" class="form-control"></td>
    <td>
        <input type="text" class="form-control nilai_perolehan nilai_perolehan{{ $count }}"
            count="{{ $count }}">
        <input type="hidden" name="h_perolehan[]" class="form-control  nilai_perolehan_biasa{{ $count }}">
    </td>
    <td>
        <p class="umur{{ $count }} text-center"></p>
    </td>
    <input type="hidden" class="periode{{ $count }}">
    <input type="hidden" class="umurInput{{ $count }}">
    <td>
        <p class="susut_bulan{{ $count }} text-center"></p>
    </td>
    <td>
        <button type="button" class="btn rounded-pill remove_baris" count="{{ $count }}"><i
                class="fas fa-trash text-danger"></i>
        </button>
    </td>
</tr>
