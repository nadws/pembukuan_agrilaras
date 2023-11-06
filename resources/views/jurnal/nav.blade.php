<ul class="nav nav-pills float-start">
    <li class="nav-item">
        <a class="nav-link {{ $id_buku == '2' ? 'active' : '' }}" aria-current="page"
            href="{{ route('jurnal', ['id_buku' => '2', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'period' => 'costume']) }}">Biaya</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $id_buku == '12' ? 'active' : '' }}" aria-current="page"
            href="{{ route('jurnal', ['id_buku' => '12', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'period' => 'costume']) }}">
            Pengeluaran Aktiva Gantung</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $id_buku == '13' ? 'active' : '' }}" aria-current="page"
            href="{{ route('jurnal', ['id_buku' => '13', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'period' => 'costume']) }}">
            Pembalikan Aktiva Gantung</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $id_buku == '6' ? 'active' : '' }}"
            href="{{ route('jurnal', ['id_buku' => '6', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'period' => 'costume']) }}">Penjualan</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $id_buku == '7' ? 'active' : '' }}"
            href="{{ route('jurnal', ['id_buku' => '7', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'period' => 'costume']) }}">Kas
            & Bank</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $id_buku == '10' ? 'active' : '' }}"
            href="{{ route('jurnal', ['id_buku' => '10', 'kategori' => 'peralatan', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'period' => 'costume']) }}">Pembelian
            Umum</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $id_buku == '14' ? 'active' : '' }}"
            href="{{ route('jurnal', ['id_buku' => '14', 'kategori' => 'peralatan', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'period' => 'costume']) }}">Hutang</a>
    </li>
</ul>
