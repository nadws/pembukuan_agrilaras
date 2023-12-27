{{-- pasiva --}}
<tr>
    <th class="dhead" colspan="13"><b>Passiva</b></th>

</tr>
<tr>
    <th class="dhead ps-3" colspan="13">Hutang</th>
</tr>
@php

    $totalPerAkunHutang = [];
    foreach ($bulans as $d) {
        $bln = $d->bulan;
        $tgl1 = '2020-01-01';
        $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

        $akun = \App\Models\NeracaAldi::GetAkun($tgl1, $tgl2, 9);
        foreach ($akun as $a) {
            $totalPerAkunHutang[$bln][$i][$a->nm_akun] = $a->kredit - $a->debit;
        }
    }

    $totalPerBulanHutang = [];
    foreach ($bulans as $d) {
        $bln = $d->bulan;
        $totalPerBulanHutang[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
    }
    foreach ($totalPerAkunHutang as $bulan => $nilai) {
        $totalPerBulanHutang[$bulan] += array_sum($nilai[59]);
    }

@endphp
@foreach ($totalPerAkunHutang[1]['59'] as $d => $i)
    <tr>

        <th class="ps-4">{{ $d }}</th>

        @php
            $total = 0;
        @endphp
        @foreach ($bulans as $b)
            @php
                $total += $totalPerAkunHutang[$b->bulan]['59'][$d];
            @endphp
            <td class="ps-4 text-end">
                {{ number_format($totalPerAkunHutang[$b->bulan]['59'][$d], 0) }}
            </td>
        @endforeach
        {{-- <td class="text-end">
            {{ number_format($total, 0) }}
        </td> --}}
    </tr>
@endforeach
<tr>
    <th class="dhead"><b>Jumlah Kewajiban Lancar</b></th>
    @php
        $totalSemuaHutang = 0;
    @endphp
    @foreach ($bulans as $d)
        @php
            $totalSemuaHutang += $totalPerBulanHutang[$d->bulan];
        @endphp
        <th class="text-end dhead">
            {{ number_format($totalPerBulanHutang[$d->bulan], 0) }}</th>
    @endforeach
    {{-- <th class="text-end dhead">{{ number_format($totalSemuaHutang, 0) }}</th> --}}
</tr>
<tr>
    <th class="dhead ps-3" colspan="13">Ekuitas</th>
</tr>

@php
    $totalPerAkun = [];
    foreach ($bulans as $d) {
        $bln = $d->bulan;
        $tgl1 = '2020-01-01';
        $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

        $akun = \App\Models\NeracaAldi::GetKas2($tgl1, $tgl2);
        foreach ($akun as $a) {
            $totalPerAkun[$bln][$i][$a->nm_akun] = $a->kredit - $a->debit;
        }
    }

@endphp

@foreach ($totalPerAkun[1]['0'] as $d => $i)
    <tr>

        <th class="ps-4">{{ ucwords($d) }}</th>

        @php
            $total = 0;
        @endphp
        @foreach ($bulans as $b)
            @php
                $total += $totalPerAkun[$b->bulan]['0'][$d];
            @endphp
            <td class="ps-4 text-end">
                {{ number_format($totalPerAkun[$b->bulan]['0'][$d], 0) }}
            </td>
        @endforeach
        {{-- <td class="text-end">
            {{ number_format($total, 0) }}
        </td> --}}
    </tr>
@endforeach

@php
    $totalPerAkunEkuitas2 = [];
    foreach ($bulans as $d) {
        $bln = $d->bulan;
        $tgl1 = '2020-01-01';
        $tgl2 = date('Y-m-t', strtotime("$thn-$bln-1"));

        $ekuitas2 = \App\Models\NeracaAldi::GetKas3($tgl1, $tgl2);
        $laba_pendapatan = \App\Models\NeracaAldi::laba_berjalan_pendapatan($tgl1, $tgl2);
        $laba_biaya = \App\Models\NeracaAldi::laba_berjalan_biaya($tgl1, $tgl2);

        $laba_berjalan_sebelum_penutup = $laba_pendapatan->pendapatan - $laba_biaya->biaya;
        $totalPerAkunEkuitas2[$bln]['total'] = $ekuitas2->kredit + $ekuitas2->kredit_saldo - $ekuitas2->debit - $ekuitas2->debit_saldo + $laba_berjalan_sebelum_penutup;
        // $totalPerAkun[$bln]['labaBerjalan'] = $ekuitas2->kredit - $ekuitas2->debit + $laba_berjalan_sebelum_penutup;
    }

    $totalPerBulanEkuitas = [];
    foreach ($bulans as $d) {
        $bln = $d->bulan;
        $totalPerBulanEkuitas[$bln] = 0; // Setiap bulan diinisialisasi dengan nilai 0
    }
    foreach ($totalPerAkun as $bulan => $nilai) {
        $totalPerBulanEkuitas[$bulan] += array_sum($nilai[0]);
    }
@endphp
<tr>

    <th class="ps-4">{{ ucwords($ekuitas2->nm_akun) }}</th>

    @php
        $total = 0;
    @endphp
    @foreach ($bulans as $b)
        @php
            $total += $totalPerAkunEkuitas2[$b->bulan]['total'];
        @endphp
        <td class="ps-4 text-end">
            {{ number_format($totalPerAkunEkuitas2[$b->bulan]['total'], 0) }}
        </td>
    @endforeach
    {{-- <td class="text-end">
        {{ number_format($total, 0) }}
    </td> --}}
</tr>

<tr>
    <th class="dhead"><b>Total Ekuitas</b></th>
    @php
        $totalSemuaEkuitas = 0;
    @endphp
    @foreach ($bulans as $b)
        @php
            $totalSemuaEkuitas += $totalPerAkunEkuitas2[$b->bulan]['total'] + $totalPerBulanEkuitas[$b->bulan];
        @endphp
        <th class="text-end dhead">
            {{ number_format($totalPerAkunEkuitas2[$b->bulan]['total'] + $totalPerBulanEkuitas[$b->bulan], 0) }}
        </th>
    @endforeach
    {{-- <th class="text-end dhead">{{ number_format($totalSemuaEkuitas, 0) }}</th> --}}
</tr>

<tr>
    <th class="dhead"><b>Total Passiva</b></th>
    @php
        $totalSemuaPassiva = 0;
    @endphp
    @foreach ($bulans as $b)
        @php
            $totalSemuaPassiva += $totalPerBulanHutang[$b->bulan] + $totalPerAkunEkuitas2[$b->bulan]['total'] + $totalPerBulanEkuitas[$b->bulan];
        @endphp
        <th class="text-end dhead">
            {{ number_format($totalPerBulanHutang[$b->bulan] + $totalPerAkunEkuitas2[$b->bulan]['total'] + $totalPerBulanEkuitas[$b->bulan], 0) }}
        </th>
    @endforeach
    {{-- <th class="text-end dhead">{{ number_format($totalSemuaPassiva, 0) }}</th> --}}
</tr>
