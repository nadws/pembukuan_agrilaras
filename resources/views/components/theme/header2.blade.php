<div class="header-top">
    <div class="container">
        <div class="logo">
            <a href="dashboard">
                <center>
                    <img src="/assets/login/img/agri_laras2.png" alt="Logo">
                </center>
            </a>
            <h5>AGRI LARAS</h5>
        </div>
        <div class="header-top-right">

            <div class="dropdown">
                <a href="#" id="topbarUserDropdown"
                    class="user-dropdown d-flex align-items-center dropend dropdown-toggle " data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <div class="avatar avatar-md2">
                        @php
                        $idPosisi = auth()->user()->posisi->id_posisi;
                        $gambar = $idPosisi == 1 ? 'Admin' : 'Pengawas';
                        @endphp
                        <img src='{{ asset("img/$gambar.png") }}' alt="Avatar">
                    </div>
                    <div class="text">
                        <h6 class="user-dropdown-name">{{ ucwords(auth()->user()->name) }}</h6>
                        <p class="user-dropdown-status text-sm text-muted">
                            {{ ucwords(auth()->user()->posisi->nm_posisi) }}
                        </p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="topbarUserDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
                    </li>
                    <li>
                        <form id="myForm" method="post" action="{{ route('logout') }}">
                            @csrf
                        </form>
                        <a class="dropdown-item" href="#"
                            onclick="document.getElementById('myForm').submit();">Logout</a>
                    </li>
                </ul>
            </div>

            <!-- Burger button responsive -->
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </div>
    </div>
</div>