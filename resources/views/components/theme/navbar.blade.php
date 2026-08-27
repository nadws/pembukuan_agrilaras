<style>
    .layout-horizontal .main-navbar ul .menu-link {
        padding: .5rem .9rem;
        border: 1px solid transparent;
        border-radius: 9px;
        text-decoration: none;
        transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .layout-horizontal .main-navbar ul .menu-link:hover {
        border-color: rgba(255, 255, 255, .12);
        background: rgba(255, 255, 255, .08);
        color: #fff;
    }

    .layout-horizontal .main-navbar ul .menu-link.active_navbar_new {
        border-color: rgba(255, 255, 255, .3);
        background: rgba(255, 255, 255, .17);
        box-shadow: 0 4px 12px rgba(25, 44, 96, .16), inset 0 0 0 1px rgba(255, 255, 255, .06);
        color: #fff !important;
        text-decoration: none;
    }

    @media (max-width: 1199.98px) {
        .layout-horizontal .main-navbar ul .menu-link.active_navbar_new {
            border-color: #435ebe;
            background: #435ebe;
            box-shadow: none;
            color: #fff !important;
        }
    }
</style>

<header class="mb-5">
    @include('components.theme.header2')
    <nav class="main-navbar" aria-label="Navigasi utama">
        <div class="container font-bold">
            <ul>
                @php
                    $routeName = request()->route()?->getName();
                @endphp
                <li class="menu-item">
                    <a href="{{ route('dashboard') }}"
                        class="menu-link {{ $routeName == 'dashboard' ? 'active_navbar_new' : '' }}"
                        @if ($routeName == 'dashboard') aria-current="page" @endif>
                        <span>Dashboard</span>
                    </a>
                </li>
                @php
                    $hiddenNavbar = ['gudang alpa', 'persediaan & penyesuaian', 'import accurate'];

                    $navbar = DB::table('navbar')
                        ->whereNotIn(DB::raw('LOWER(nama)'), $hiddenNavbar)
                        ->orderBy('urutan', 'ASC')
                        ->get();
                @endphp
                @foreach ($navbar as $d)
                    @php
                        $string = str_replace(['[', ']', "'"], '', $d->isi);
                        $array = array_map('trim', explode(',', $string));
                        $isActive = in_array($routeName, $array, true);
                    @endphp
                    <li class="menu-item">
                        <a href="{{ route($d->route) }}"
                            class="menu-link {{ $isActive ? 'active_navbar_new' : '' }}"
                            @if ($isActive) aria-current="page" @endif>
                            <span>{{ ucwords($d->nama) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>
</header>
