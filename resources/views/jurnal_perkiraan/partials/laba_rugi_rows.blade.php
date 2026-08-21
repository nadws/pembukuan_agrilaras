@foreach ($rows as $row)
    <tr class="account-row" data-account-name="{{ Str::lower($row['nama']) }}">
        <td style="padding-left: {{ $row['depth'] * 24 }}px; {{ $row['has_children'] ? 'font-weight:600' : '' }}">
            <a href="{{ route('jurnal-perkiraan.detail-akun', ['akun_perkiraan' => $row['id'], 'tanggal_awal' => $start->copy()->startOfMonth()->toDateString(), 'tanggal_akhir' => $end->copy()->endOfMonth()->toDateString()]) }}"
                class="text-dark">{{ $row['nama'] }}</a>
        </td>
        @foreach ($periods as $period)
            <td class="text-end">
                <a class="report-amount-link" title="Lihat detail jurnal {{ $period->translatedFormat('F Y') }}"
                    href="{{ route('jurnal-perkiraan.detail-akun', [
                        'akun_perkiraan' => $row['id'],
                        'tanggal_awal' => $period->copy()->startOfMonth()->toDateString(),
                        'tanggal_akhir' => $period->copy()->endOfMonth()->toDateString(),
                    ]) }}">{{ $formatNumber($row['values'][$period->format('Y-m')]) }}</a>
            </td>
        @endforeach
        <td class="text-end">
            <a class="report-amount-link" title="Lihat detail jurnal seluruh periode"
                href="{{ route('jurnal-perkiraan.detail-akun', [
                    'akun_perkiraan' => $row['id'],
                    'tanggal_awal' => $start->copy()->startOfMonth()->toDateString(),
                    'tanggal_akhir' => $end->copy()->endOfMonth()->toDateString(),
                ]) }}">{{ $formatNumber($row['total']) }}</a>
        </td>
    </tr>
@endforeach
