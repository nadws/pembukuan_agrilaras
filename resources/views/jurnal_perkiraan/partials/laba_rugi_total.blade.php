<tr class="{{ !empty($highlight) ? 'report-highlight' : 'report-total' }}">
    <td><b>{{ $label }}</b></td>
    @foreach ($periods as $period)<td class="text-end"><b>{{ $formatNumber($values[$period->format('Y-m')]) }}</b></td>@endforeach
    <td class="text-end"><b>{{ $formatNumber($totalPeriods($values)) }}</b></td>
</tr>
