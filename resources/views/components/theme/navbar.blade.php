<header class="mb-5">
    @include('components.theme.header2')
    <nav class="main-navbar ">
        <div class="container font-bold">
            <ul>
                @php
                    $routeName = request()->route()?->getName();
                @endphp
                <li class="menu-item">
                    <a href="{{ route('dashboard') }}"
                        class='menu-link {{ $routeName == 'dashboard' ? 'active_navbar_new' : '' }}'>
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
                        $string = $d->isi;
                        $string = str_replace(['[', ']', "'"], '', $string);
                        $array = explode(', ', $string);
                    @endphp
                    <li class="menu-item">
                        <a href="{{ route($d->route) }}"
                            class='menu-link 
                    {{ in_array($routeName, $array) ? ' active_navbar_new' : '' }}'>
                            <span>{{ ucwords($d->nama) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

</header>
