@props([
    'title' => '',
    'rot1' => '',
    'rot2' => '',
    'rot3' => '',
    'table' => 'Y',
    'nav' => 'T',
    'sizeCard' => '12',
    'cont' => 'container',
])
<x-theme.head :title="$title" />

<x-theme.navbar />

<div class="content-wrapper  {{ $cont }}">
    <div class="page-content">
        @if ($table == 'T')
            {{ $slot }}
        @else
            <div class="row justify-content-center">
                <div class="col-lg-{{ $sizeCard }}">
                    <div class="card">
                        <div class="card-header">
                            @if ($nav == 'Y')
                                <div class="row">
                                    <div class="col-lg-6">
                                        <ul class="nav nav-pills">
                                            @php
                                                $rotName = request()
                                                    ->route()
                                                    ->getName();
                                            @endphp
                                            <li class="nav-item">
                                                <a class="nav-link {{ $rotName == $rot1 ? 'active' : '' }}"
                                                    aria-current="page" href="{{ route($rot1) }}">Produk</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link {{ $rotName == $rot2 ? 'active' : '' }}"
                                                    aria-current="page" href="{{ route($rot2) }}">Stok Masuk</a>
                                            </li>
                                            {{-- <li class="nav-item">
                                                <a class="nav-link {{ $rotName == $rot3 ? 'active' : '' }}"
                                                    href="{{ route($rot3) }}">Opname</a>
                                            </li> --}}
                                        </ul>
                                    </div>
                                    @if (Schema::hasTable('route_agl'))
                                        <div class="col-lg-6">
                                            <x-theme.btn_dashboard route="dashboard_kandang.index" />

                                        </div>
                                    @endif
                                </div>
                            @endif
                            {{ $cardHeader }}

                        </div>
                        <div class="card-body">

                            {{ $cardBody }}
                        </div>
                        @if (!empty($cardFooter))
                            <div class="card-footer">
                                {{ $cardFooter }}
                            </div>
                        @else
                        @endif

                    </div>
                </div>

            </div>
        @endif

    </div>
</div>

<x-theme.footer />
