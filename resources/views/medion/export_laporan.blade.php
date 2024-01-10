<table width="100%">
    <thead>


        <tr>
            <th colspan="26" style="font-size: 20px; font-weight: bold; padding: 5px; text-align: center">CATATAN HARIAN
                PEMELIHARAAN LAYER
                PRODUKSI</th>
        </tr>
        <tr>
            <th colspan="26" style="font-size: 20px; font-weight: bold; padding: 5px">&nbsp;</th>
        </tr>
        <tr>
            <td colspan="3">Farm :</td>
            <td wid>:</td>
            <td colspan="3" style="background-color: #FFE699;">CV.AGRI LARAS</td>

            <td colspan="3">Nama Operator</td>
            <td wid>:</td>
            <td colspan="3" style="background-color: #FFE699;">&nbsp;</td>

            <td colspan="3">Jumlah Ayam</td>
            <td wid>:</td>
            <td colspan="2" style="background-color: #FFE699;">{{ $kandang->stok_awal }}</td>

            <td colspan="3">Tgl. chickin</td>
            <td wid>:</td>
            <td style="background-color: #FFE699;">{{ date('d-M-Y', strtotime($kandang->chick_in)) }}</td>
        </tr>
        <tr>
            <td colspan="3">Owner</td>
            <td wid>:</td>
            <td colspan="3" style="background-color: #FFE699;"></td>

            <td colspan="3">Strain</td>
            <td wid>:</td>
            <td colspan="3" style="background-color: #ED7D31">{{ $kandang->nm_strain }}</td>

            <td colspan="3">Umur (minggu)</td>
            <td wid>:</td>
            <td colspan="2" style="background-color: #ED7D31">13</td>
        </tr>
        <tr>
            <th colspan="26" style="font-size: 20px; font-weight: bold; padding: 5px">&nbsp;</th>
        </tr>
        <tr>
            <th rowspan="3">Umur <br> (minggu)</th>
            <th rowspan="3">Umur <br> (hari)</th>
            <th> </th>
            <th colspan="7">Jumlah Ayam</th>
            <th colspan="3">Konsumsi Pakan</th>
            <th colspan="2">Rataan berat badan</th>
            <th colspan="9">Produksi Telur</th>
            <th rowspan="3">Fcr</th>
            <th>Keterangan dan OVK</th>
        </tr>

        <tr>
            <th>Tanggal</th>
            <th>Mati</th>
            <th>Culling</th>
            <th>Afkir</th>
            <th>Hidup</th>
            <th rowspan="2">Deplesi <br> (%)</th>
            <th width="70px" rowspan="2">Deplesi Komulatif <br> (%)</th>
            <th rowspan="2">Standar</th>
            <th>Total</th>
            <th>Per ekor</th>
            <th>Standar</th>
            <th>Per ekor</th>
            <th>Standar</th>
            <th>utuh</th>
            <th>retak</th>
            <th>pecah</th>
            <th>total</th>
            <th rowspan="2">Berat <br> telur(kg)</th>
            <th colspan="2">henday (%)</th>
            <th height="38" colspan="2">Berat telur/butir <br> (gram)</th>
            <th>(obat/vitamin/vaksin)</th>
        </tr>
        <tr>
            <th></th>
            <th colspan="4">Ekor</th>
            <th>kg/hari</th>
            <th colspan="2">g/ekor/hari</th>
            <th colspan="2">gram</th>
            <th colspan="4">butir</th>
            <th>real</th>
            <th>standar</th>
            <th>real</th>
            <th>standar</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @php
            $deplesi_kum = 0;
        @endphp
        @foreach ($medion as $key => $m)
            @php
                $deplesi_kum += empty($m->deplesi) ? 0 : $m->deplesi;
            @endphp
            <tr>
                <td>{{ $m->umur_minggu }}</td>
                <td>{{ $m->umur_hari }}</td>
                <td>{{ date('d-M-Y', strtotime($m->tgl)) }}</td>
                <td style="background: #FFEA93; color: black">{{ $m->mati }}</td>
                <td style="background: #FFEA93; color: black">{{ $m->jual }}</td>
                <td style="background: #FFEA93; color: black">0</td>
                <td class="td_layer ">{{ $m->hidup }}</td>
                <td class="td_layer ">{{ empty($m->deplesi) ? 0 : $m->deplesi }}</td>
                <td class="td_layer ">{{ $deplesi_kum }}</td>
                <td class="td_layer "></td>
                <td style="background: #FFEA93; color: black">
                    {{ number_format($m->kg_pakan, 2) }}
                </td>
                <td>{{ number_format($m->gr_perekor, 1) }}</td>
                <td>{{ empty($m->feed) ? 'NA' : $m->feed }}</td>
                <td>0</td>
                <td>0</td>
                <td style="background: #FFEA93; color: black">
                    {{ empty($m->normalPcs) ? 0 : $m->normalPcs }}</td>
                <td style="background: #FFEA93; color: black">0</td>
                <td style="background: #FFEA93; color: black">
                    {{ empty($m->abnormalPcs) ? 0 : $m->abnormalPcs }}</td>
                @php
                    $normal_pcs = empty($m->normalPcs) ? 0 : $m->normalPcs;
                    $abnormal_pcs = empty($m->abnormalPcs) ? 0 : $m->abnormalPcs;

                    $normal_kg = empty($m->normalKg) ? 0 : $m->normalKg - $normal_pcs / 180;
                    $abnormal_kg = empty($m->abnormalKg) ? 0 : $m->abnormalKg - $abnormal_pcs / 180;

                    $ttl_pcs = $normal_pcs + $abnormal_pcs;
                    $ttl_kg = $normal_kg + $abnormal_kg;
                @endphp
                <td>{{ $normal_pcs + $abnormal_pcs }}</td>
                <td style="background: #FFEA93; color: black">
                    {{ number_format($normal_kg + $abnormal_kg, 2) }}</td>
                <td>
                    {{ $ttl_pcs == 0 || $m->hidup == 0 ? 0 : number_format($ttl_pcs / $m->hidup, 2) }}
                </td>
                <td>{{ empty($m->hd) ? 'NA' : $m->hd }}</td>
                <td>{{ $ttl_pcs == 0 ? 0 : number_format($ttl_kg / $ttl_pcs, 2) }}
                </td>
                <td>{{ empty($m->berat_telur) ? 'NA' : $m->berat_telur }}</td>
                <td>{{ $ttl_kg == 0 ? 0 : number_format($m->kg_pakan / $ttl_kg, 1) }}
                </td>
                <td width="450px">{{ $m->nama_obat }}</td>

            </tr>
        @endforeach

    </tbody>

</table>
