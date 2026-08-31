@foreach ($rows as $row)
    @php $displayValue = bcmul($row['value'], (string) ($multiplier ?? 1), 12); @endphp
    <tr class="account-row" data-account-name="{{ \Illuminate\Support\Str::lower($row['kode'] . ' ' . $row['nama']) }}">
        <td>
            <a href="{{ route('pembukuan-baru.buku-besar.detail', ['id' => $row['id'], 'tgl1' => $firstJournalDate ?: $reportDate->toDateString(), 'tgl2' => $reportDate->toDateString()]) }}"
                class="account-link {{ $row['has_children'] ? 'fw-bold' : '' }}"
                style="padding-left: {{ 8 + ($row['depth'] * 20) }}px">
                <span class="account-code">{{ $row['kode'] }}</span>
                <span>{{ $row['nama'] }}</span>
            </a>
        </td>
        <td class="text-end {{ $row['has_children'] ? 'fw-bold' : '' }}">
            {{ $formatNumber($displayValue) }}
        </td>
    </tr>
@endforeach
