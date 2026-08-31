<tr class="{{ !empty($highlight) ? 'report-highlight' : 'report-total' }}">
    <td><b>{{ $label }}</b></td>
    @foreach ($periods as $period)
        <td class="text-end"><b>{{ $formatNumber($values[$period->format('Y-m')]) }}</b></td>
    @endforeach
    <td class="text-end"><b>{{ $formatNumber($totalPeriods($values)) }}</b></td>
    @php
        $actualTotal = $totalPeriods($values);
        $budgetTotalValue = $totalPeriods($budget ?? array_fill_keys($periods->map->format('Y-m')->all(), '0'));
        $varianceTotal = !empty($isIncome)
            ? bcsub($actualTotal, $budgetTotalValue, 12)
            : bcsub($budgetTotalValue, $actualTotal, 12);
    @endphp
    <td class="text-end budget-column"><b>{{ $formatNumber($budgetTotalValue) }}</b></td>
    <td class="text-end {{ (float)$varianceTotal >= 0 ? 'variance-good' : 'variance-bad' }}"><b>{{ $formatNumber($varianceTotal) }}</b></td>
</tr>
