@php($depreciation = $depreciation ?? false)
@php($detailRoute = $depreciation ? 'jurnal-perkiraan.detail-penyusutan-aktiva' : 'jurnal-perkiraan.detail-akun')
@foreach ($rows as $row)
    @php($accountDetail = route($detailRoute, ['akun_perkiraan' => $row['id'], 'tanggal_awal' => $start->copy()->startOfMonth()->toDateString(), 'tanggal_akhir' => $end->copy()->endOfMonth()->toDateString()]))
    <tr class="account-row" data-account-name="{{ Str::lower($row['nama']) }}">
        <td style="padding-left: {{ $row['depth'] * 24 }}px; {{ $row['has_children'] ? 'font-weight:600' : '' }}">
            <a href="{{ $accountDetail }}"
                class="text-dark">{{ $row['nama'] }}</a>
        </td>
        @foreach ($periods as $period)
            <td class="text-end period-column">
                <a class="report-amount-link" title="{{ $depreciation ? 'Lihat aktiva ' : 'Lihat detail jurnal ' }}{{ $period->translatedFormat('F Y') }}"
                    href="{{ route($detailRoute, [
                        'akun_perkiraan' => $row['id'],
                        'tanggal_awal' => $period->copy()->startOfMonth()->toDateString(),
                        'tanggal_akhir' => $period->copy()->endOfMonth()->toDateString(),
                    ]) }}">{{ $formatNumber($row['values'][$period->format('Y-m')]) }}</a>
            </td>
        @endforeach
        <td class="text-end period-total-column">
            <a class="report-amount-link" title="{{ $depreciation ? 'Lihat aktiva seluruh periode' : 'Lihat detail jurnal seluruh periode' }}"
                href="{{ $accountDetail }}">{{ $formatNumber($row['total']) }}</a>
        </td>
    </tr>
@endforeach
