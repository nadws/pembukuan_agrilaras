@php
    $filename = 'bukubesar.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=\"$filename\"");

@endphp
@php
    $ttlDebit = 0;
    $ttlKredit = 0;
    $ttlSaldo = 0;

    foreach ($buku as $d) {
        $ttlDebit += $d->debit + $d->debit_saldo;
        $ttlKredit += $d->kredit + $d->kredit_saldo;
        $ttlSaldo += $d->debit + $d->debit_saldo - ($d->kredit + $d->kredit_saldo);
    }
@endphp
<table class="table table-hover table-striped" id="table1" border="1">
    <thead>
        <tr>
            <th width="5">#</th>
            <th>Kode Akun</th>
            <th>Akun</th>
            <th style="text-align: right">Debit ({{ number_format($ttlDebit, 2) }})</th>
            <th style="text-align: right">Kredit ({{ number_format($ttlKredit, 2) }})</th>
            <th style="text-align: right">Saldo ({{ number_format($ttlSaldo, 2) }})</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sldo = 0;
        @endphp
        @foreach ($buku as $no => $a)
            @php
                $sldo += $a->debit + $a->debit_saldo - ($a->kredit + $a->kredit_saldo);
            @endphp
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ $a->kode_akun }}</td>
                <td>{{ ucwords(strtolower($a->nm_akun)) }}
                </td>
                <td style="text-align: right">{{ number_format($a->debit + $a->debit_saldo, 2) }}</td>
                <td style="text-align: right">{{ number_format($a->kredit + $a->kredit_saldo, 2) }}</td>
                <td style="text-align: right">
                    {{ number_format($a->debit + $a->debit_saldo - ($a->kredit + $a->kredit_saldo), 2) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
