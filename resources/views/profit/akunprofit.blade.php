<button type="submit" class="btn btn-primary float-end">Save</button> <br><br><br>
<div class="card-body">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Penjualan</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Biaya</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false" tabindex="-1">Uang Keluar</a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade active show" id="home" role="tabpanel" aria-labelledby="home-tab">
            <table class="table table-bordered " id="table123" width="100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Akun</th>
                        <th>Tidak Masuk</th>
                        <th>Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($akunPenjualan as $no => $a)
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
            
            </table>
        </div>
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <table class="table table-bordered " id="tablepro2" width="100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Akun</th>
                        <th>Tidak Masuk</th>
                        <th>Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($akunBiaya as $no => $a)
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
            
            </table>
            
        </div>
        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
            <table class="table table-bordered " id="tablepro3" width="100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Akun</th>
                        <th>Tidak Masuk</th>
                        <th>Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($akunUangKeluar as $no => $a)
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
            
            </table>

        </div>
    </div>
</div>
<table class="table table-bordered " id="tableProfitScroll" width="100%">
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

</table>
