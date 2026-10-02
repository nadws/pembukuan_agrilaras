<h3>Akun :{{$nm_akun}}</h3>
<table>
    <thead>
        <tr>
            <th width="39px">#</th>
            <th width="117px">No Urut Akun</th>
            <th width="110px">Tanggal</th>
            <th width="350px">Nama Akun Lawan</th>
            <th width="350px">Sub Akun</th>
            <th width="149px">Keterangan</th>
            <th width="129px" style="text-align: right">Debit</th>
            <th width="129px" style="text-align: right">Kredit</th>
            <th width="129px" style="text-align: right">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @php
        $saldo = 0;
        @endphp
        @foreach ($detail as $n => $d)
        @php
        $saldo += $d->debit - $d->kredit;
        @endphp
        <tr>
            <td>{{ $n+1 }}</td>
            <td>{{ $d->no_cfm }}</td>
            <td class="nowrap">{{ date('d-m-Y',strtotime($d->tgl)) }}</td>
            <td>{{ $d->saldo == 'Y' ? 'Saldo Awal' : ucwords(strtolower($d->nm_akun)) }}</td>
            <td>{{ $d->nm_post }}</td>
            <td>{{ $d->ket }}</td>
            <td style="text-align: right">{{ number_format($d->debit, 2) }}</td>
            <td style="text-align: right">{{ number_format($d->kredit, 2) }}</td>
            <td style="text-align: right">{{ number_format($saldo, 2) }}</td>
        </tr>
        @endforeach
    </tbody>

</table>