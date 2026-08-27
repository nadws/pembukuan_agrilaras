<x-theme.app title="{{ $title }}" table="T">
    <x-slot name="slot">
        <style>
            .menu-catalog { max-width: 1240px; margin: 0 auto; padding: 12px 4px 30px; }
            .menu-catalog__heading { margin-bottom: 24px; }
            .menu-catalog__heading h3 { margin-bottom: 6px; color: #18366f; font-size: 25px; font-weight: 750; letter-spacing: -.3px; }
            .menu-catalog__heading p { max-width: 620px; margin: 0; color: #74829a; font-size: 14px; }
            .menu-catalog__grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
            .menu-tile { position: relative; display: flex; min-height: 265px; overflow: hidden; border: 1px solid #e1e7f2; border-radius: 18px; background: #fff; box-shadow: 0 8px 25px rgba(35, 60, 115, .07); color: inherit; text-decoration: none !important; transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
            .menu-tile::before { position: absolute; top: 0; right: 0; left: 0; height: 4px; background: linear-gradient(90deg, #3457b2, #6d8be1); content: ''; }
            .menu-tile:hover, .menu-tile:focus { border-color: #b9c8ec; box-shadow: 0 15px 36px rgba(35, 60, 115, .14); color: inherit; outline: none; transform: translateY(-4px); }
            .menu-tile__body { display: flex; width: 100%; flex-direction: column; padding: 24px; }
            .menu-tile__icon { display: flex; width: 82px; height: 82px; align-items: center; justify-content: center; margin-bottom: 18px; border-radius: 18px; background: linear-gradient(145deg, #eef3ff, #e4ebfb); }
            .menu-tile__icon img { width: 62px; height: 62px; object-fit: contain; }
            .menu-tile__eyebrow { margin-bottom: 5px; color: #63769c; font-size: 11px; font-weight: 750; letter-spacing: .8px; text-transform: uppercase; }
            .menu-tile__title { margin: 0 0 8px; color: #18366f; font-size: 18px; font-weight: 750; line-height: 1.35; }
            .menu-tile__description { margin: 0 0 20px; color: #74829a; font-size: 13px; line-height: 1.6; }
            .menu-tile__action { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 15px; border-top: 1px solid #edf0f6; color: #3457b2; font-size: 13px; font-weight: 700; }
            .menu-tile__arrow { display: inline-flex; width: 30px; height: 30px; align-items: center; justify-content: center; border-radius: 50%; background: #edf2ff; transition: background .2s ease, transform .2s ease; }
            .menu-tile:hover .menu-tile__arrow { background: #3457b2; color: #fff; transform: translateX(3px); }
            @media (max-width: 991.98px) { .menu-catalog__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            @media (max-width: 575.98px) {
                .menu-catalog {
                    padding: 0 2px 20px;
                }

                .menu-catalog__heading {
                    margin-bottom: 16px;
                }

                .menu-catalog__heading h3 {
                    margin-bottom: 4px;
                    font-size: 21px;
                }

                .menu-catalog__heading p {
                    font-size: 12px;
                    line-height: 1.5;
                }

                .menu-catalog__grid {
                    grid-template-columns: 1fr;
                    gap: 11px;
                }

                .menu-tile {
                    min-height: 0;
                    border-radius: 14px;
                    box-shadow: 0 5px 16px rgba(35, 60, 115, .07);
                }

                .menu-tile::before {
                    right: auto;
                    bottom: 0;
                    width: 4px;
                    height: auto;
                }

                .menu-tile:hover,
                .menu-tile:focus {
                    transform: none;
                }

                .menu-tile__body {
                    display: grid;
                    grid-template-columns: 58px minmax(0, 1fr) 32px;
                    grid-template-areas:
                        "icon eyebrow action"
                        "icon title action"
                        "icon description action";
                    align-items: center;
                    column-gap: 13px;
                    padding: 14px 13px 14px 16px;
                }

                .menu-tile__icon {
                    grid-area: icon;
                    width: 58px;
                    height: 58px;
                    margin: 0;
                    border-radius: 13px;
                }

                .menu-tile__icon img {
                    width: 43px;
                    height: 43px;
                }

                .menu-tile__eyebrow {
                    grid-area: eyebrow;
                    margin-bottom: 2px;
                    font-size: 9px;
                    letter-spacing: .6px;
                }

                .menu-tile__title {
                    grid-area: title;
                    margin-bottom: 3px;
                    font-size: 15px;
                    line-height: 1.3;
                }

                .menu-tile__description {
                    display: -webkit-box;
                    grid-area: description;
                    overflow: hidden;
                    margin: 0;
                    color: #7b879c;
                    font-size: 11px;
                    line-height: 1.4;
                    -webkit-box-orient: vertical;
                    -webkit-line-clamp: 2;
                }

                .menu-tile__action {
                    grid-area: action;
                    margin: 0;
                    padding: 0;
                    border: 0;
                }

                .menu-tile__action > span:first-child {
                    display: none;
                }

                .menu-tile__arrow {
                    width: 32px;
                    height: 32px;
                }
            }
        </style>

        <section class="menu-catalog">
            <header class="menu-catalog__heading">
                <h3>{{ $title }}</h3>
                <p>Pilih menu yang ingin dibuka untuk melihat dan mengelola data.</p>
            </header>

            <div class="menu-catalog__grid">
                @foreach ($data as $d)
                    <a href="{{ route($d['route'], $d['params'] ?? []) }}" class="menu-tile">
                        <div class="menu-tile__body">
                            <div class="menu-tile__icon">
                                <img src="{{ asset('img/' . $d['img']) }}" alt="" aria-hidden="true">
                            </div>
                            <div class="menu-tile__eyebrow">Menu {{ $title }}</div>
                            <h4 class="menu-tile__title">{{ $d['judul'] }}</h4>
                            <p class="menu-tile__description">{{ $d['deskripsi'] }}</p>
                            <div class="menu-tile__action">
                                <span>Buka menu</span>
                                <span class="menu-tile__arrow"><i class="fas fa-arrow-right"></i></span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </x-slot>
</x-theme.app>
