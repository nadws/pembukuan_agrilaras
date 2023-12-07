@php
    $rot = request()
        ->route()
        ->getName();
@endphp
<ul class="nav nav-pills float-start">
    <li class="nav-item">
        <a class="nav-link  {{ $rot == 'budget.index' ? 'active' : '' }}" aria-current="page"
            href="{{ route('budget.index') }}">Budget</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  {{ $rot == 'profit_setahun' ? 'active' : '' }}" aria-current="page"
            href="{{ route('profit_setahun') }}">Profit</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  {{ $rot == 'neraca' ? 'active' : '' }}" aria-current="page"
            href="{{ route('neraca') }}">Neraca</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  {{ $rot == 'cashflow_setahun' ? 'active' : '' }}" aria-current="page"
            href="{{ route('cashflow_setahun') }}">CashFlow</a>
    </li>
</ul>
