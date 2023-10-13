@php
    $rot = request()
        ->route()
        ->getName();
@endphp
<ul class="nav nav-pills float-start">
    <li class="nav-item">
        <a class="nav-link  {{ $rot == 'budget.index' ? 'active' : '' }}"
            aria-current="page" href="{{ route('budget.index') }}">Budget</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  {{ $rot == 'dashboard' ? 'active' : '' }}"
            aria-current="page" href="{{ route('dashboard') }}">Profit</a>
    </li>
</ul>
