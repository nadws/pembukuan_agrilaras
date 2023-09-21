@php
    $kategoriProfit = [
        1 => 'Penjualan',
        4 => 'Biaya',
        5 => 'Penyesuaian',
        9 => 'Uang Keluar',
    ];
@endphp

<div class="card-body">
    <div class="row">
        <div class="col-lg-6">
            <label for="" class="">Pilih Kategori</label>
            <select name="example" class="form-control select2 " id="">
                <option value="">- Pilih Kategori -</option>
                @foreach ($kategoriProfit as $d => $i)
                    <option value="{{ $d }}">{{ ucwords($i) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6">
            <label for="">Aksi</label><br>
            <button type="submit" class="btn btn-primary">Save</button> <br><br><br>
        </div>

        <div class="col-lg-12">
            <table class="table table-bordered " id="table4" width="100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Akun</th>
                        <th>Tidak Masuk</th>
                        <th>Ket</th>
                        <th>Masuk Mana</th>
                        <th>Sk</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $akun = DB::table('akun')
                            ->select('*', 'id_akun as akun_id')
                            ->get();
                    @endphp
                    @foreach ($akun as $no => $a)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ ucwords(strtolower($a->nm_akun)) }}</td>
                            <td align="center">
                                <input type="hidden" name="id_akun[]" value="{{ $a->akun_id }}">
                                <input type="checkbox" class="iktisar iktisar{{ $no + 1 }}"
                                    urutan="{{ $no + 1 }}" isi="H" value="H" id=""
                                    {{ $a->profit_loss == 'H' || $a->profit_loss == 'Y' || !empty($a->id_akun) ? 'checked' : '' }}
                                    {{ $a->profit_loss == 'Y' || !empty($a->id_akun) ? 'disabled' : '' }}>

                                <input type="hidden" class="hasil_iktisar{{ $no + 1 }}" name="profit_loss[]"
                                    value="{{ empty($a->id_akun) ? ($a->profit_loss == 'H' ? 'H' : 'T') : 'Y' }}">
                            </td>
                            <td>
                                <span class="badge {{ empty($a->id_akun) ? 'bg-danger' : 'bg-success' }}">
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
                            @php
                                
                                $cek = DB::table('akunprofit')
                                    ->where('id_akun', $a->id_akun)
                                    ->first();
                            @endphp
                            <td>
                                @empty($cek)
                                    kosong
                                @else
                                    {{ $kategoriProfit[$cek->kategori] }}
                                @endempty
                            </td>
                            <td>
                                <input type="checkbox" id_akun="{{ $a->id_akun }}" class="form-check klikCek "
                                    value="T" count="{{ $no + 1 }}">
                                <input type="text" name="ceklis[]" class="klikCek{{ $no + 1 }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
    {{-- <table class="table table-bordered " id="table4" width="100%">
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
    
    </table> --}}
</div>
