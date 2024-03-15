@foreach ($detail as $no2 => $a)
    <tr>
        <td>&nbsp;&nbsp;{{ $no2 + 1 }}</td>
        <td>{{ $a->kode_akun }}</td>
        <td><a target="_blank"
                href="{{ route('summary_buku_besar.detail', ['id_akun' => $a->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{ ucwords(strtolower($a->nm_akun)) }}</a>
        </td>
        <td style="text-align: right">{{ number_format($a->debit + $a->debit_saldo, 2) }}</td>
        <td style="text-align: right">{{ number_format($a->kredit + $a->kredit_saldo, 2) }}</td>
        <td style="text-align: right">
            {{ number_format($a->debit + $a->debit_saldo - ($a->kredit + $a->kredit_saldo), 2) }}
        </td>
    </tr>
@endforeach
