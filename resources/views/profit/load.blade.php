<style>
    .dhead {
        background-color: #435EBE !important;
        color: white;
    }
</style>
<section class="row">
    @php
    $totalPendapatan = 0;
    $totalBiaya = 0;
    $totalBiaya2 = 0;
    $totalLaba = 0;
    $totalbkn = 0;

    function getAkun($id_kategori, $tgl1, $tgl2, $jenis)
    {
    $jenis = $jenis == 1 ? 'b.kredit' : 'b.debit';

    return DB::select("SELECT a.id_akun,a.nm_akun, b.kredit, b.debit, c.debit as debit_saldo , c.kredit as kredit_saldo
    FROM akun as a
    left join (
    SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
    FROM jurnal as b
    WHERE b.id_buku not in(5,13) and b.kredit != 0 and b.tgl between '$tgl1' and '$tgl2' and b.penutup = 'T'
    group by b.id_akun
    ) as b on b.id_akun = a.id_akun

    left JOIN (
    SELECT c.id_akun , sum(c.debit) as debit, sum(c.kredit) as kredit
    FROM jurnal_saldo as c
    where c.tgl BETWEEN '$tgl1' and '$tgl2'
    group by c.id_akun
    ) as c on c.id_akun = a.id_akun

    where a.id_klasifikasi = '4';");
    }

    foreach ($subKategori1 as $d) {
    foreach (getAkun($d->id, $tgl1, $tgl2, 1) as $a) {
    $totalPendapatan += $a->kredit + $a->kredit_saldo;
    }
    }

    foreach ($biaya_murni as $a) {
    $totalBiaya += $a->debit + $a->debit_saldo;
    }

    foreach ($biayaGantung as $d) {
    $totalBiaya2 += $d->debit + $d->debit_saldo;
    }
    foreach ($biaya_bkn_keluar as $d) {
    $totalbkn += $d->debit;
    }

    @endphp
    <table class="table table-bordered" x-data="{
        open1: false,
        open2: false,
        open22: false,
        open23: false,
        open24: false,
        open25: false,
        open26: false,
    }">
        <tr>
            <th class="dhead"><a class="uraian text-white" href="#" data-bs-toggle="modal" jenis="1"
                    data-bs-target="#tambah-uraian">Uraian</a> </th>
            <th class="dhead" style="text-align: right">Rupiah</th>
            <th class="dhead" style="text-align: right">Budget</th>
        </tr>
        @foreach ($subKategori1 as $d)
        <tr>
            <th colspan="3">
                <a href="#" class="klikModal" id_kategori="{{ $d->id }}">{{ ucwords($d->sub_kategori) }}</a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open1 = ! open1">Buka <i
                        class="fas fa-caret-down"></i></button>
            </th>
        </tr>
        @foreach (getAkun($d->id, $tgl1, $tgl2, 1) as $a)
        <tr class="detail-row" data-id="{{ $d->id }}" x-transition x-show="open1">
            <td style="padding-left: 20px"><a target="_blank"
                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $a->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{
                    ucwords(strtolower($a->nm_akun)) }}</a>
            </td>
            <td style="text-align: right">Rp. {{ number_format($a->kredit + $a->kredit_saldo, 1) }}</td>
            <td class="text-end">Rp {{$a->id_akun == 26 ? number_format($estimasi_telur->estimasi,0) : ''}}</td>
        </tr>
        @endforeach
        @endforeach

        <tr>
            <td class="fw-bold" style="border-bottom: 1px solid black;">Total Pendapatan</td>
            <td class="fw-bold" align="right" style="border-bottom: 1px solid black;">
                Rp. {{ number_format($totalPendapatan, 0) }}</td>
            <td class="fw-bold" align="right" style="border-bottom: 1px solid black;">Rp.
                {{number_format($estimasi_telur->estimasi,0)}}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th class="dhead"><a class="uraian text-white">Biaya - Biaya</a> </th>
            <th class="dhead" style="text-align: right">Rupiah</th>
            <th class="dhead" style="text-align: right">Budget</th>
        </tr>
        @foreach ($subKategori2 as $d)
        <tr>
            <th colspan="3"><a href="#" class="klikModal" id_kategori="{{ $d->id }}">{{ ucwords($d->sub_kategori) }}</a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open2 = ! open2">Buka <i
                        class="fas fa-caret-down"></i></button>

            </th>
        </tr>
        @foreach ($biaya_murni as $a)
        <tr x-transition x-show="open2">
            <td style="padding-left: 20px"><a target="_blank"
                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $a->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{
                    ucwords(strtolower($a->nm_akun)) }}</a>
            </td>
            <td style="text-align: right">Rp {{ number_format($a->debit + $a->debit_saldo, 1) }}</td>
            <td style="text-align: right">Rp 0</td>
        </tr>
        @endforeach
        @endforeach

        <tr>
            <td class="fw-bold" style="border-bottom: 1px solid black;">Total Biaya-biaya</td>
            <td class="fw-bold" align="right" style="border-bottom: 1px solid black;">Rp
                {{ number_format($totalBiaya, 1) }}</td>
            <td class="fw-bold" align="right" style="border-bottom: 1px solid black;">Rp 0</td>
        </tr>
        <tr>
            <td class="fw-bold">TOTAL LABA KOTOR</td>
            <td class="fw-bold" align="right">Rp {{ number_format($totalPendapatan - $totalBiaya, 0) }}</td>
            <td class="fw-bold" align="right">Rp 0</td>
        </tr>
        <tr>
            <th colspan="3"><a href="#" class="klikModal" id_kategori="5">Biaya Penyesuaian</a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open24 = ! open24">Buka <i
                        class="fas fa-caret-down"></i></button>
            </th>
        </tr>
        @php
        $ttlEbdiba = 0;
        @endphp
        @foreach ($biaya_penyesuaian as $d)
        @php
        $ttlEbdiba += $d->debit + $d->debit_saldo ?? 0;
        @endphp
        <tr x-show="open24">
            <td style="padding-left: 20px"><a target="_blank"
                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $d->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{
                    ucwords(strtolower($d->nm_akun)) }}</a>
            </td>
            <td align="right">Rp {{ number_format($d->debit + $d->debit_saldo ?? 0, 0) }}</td>
            <td align="right">Rp 0</td>
        </tr>
        @endforeach
        <tr>
            <td class="fw-bold">TOTAL BIAYA PENYESUAIAN</td>
            <td class="fw-bold" align="right">Rp {{ number_format($ttlEbdiba, 0) }}</td>
            <td class="fw-bold" align="right">Rp 0</td>
        </tr>
        <tr>
            <td class="fw-bold">TOTAL LABA BERSIH</td>
            <td class="fw-bold" align="right">Rp {{ number_format($totalPendapatan - $totalBiaya - $ttlEbdiba, 0) }}
            </td>
            <td class="fw-bold" align="right">Rp 0 </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>

        {{-- biaya di benarkan --}}
        <tr>
            <th class="dhead"><a class="uraian text-white" href="#" data-bs-toggle="modal" jenis="3"
                    data-bs-target="#tambah-uraian">Biaya dan uang keluar</a> <button
                    class="btn btn-primary btn-sm btn-buka float-end" style="border: 1px solid white"
                    @click="open26 = ! open26">Buka <i class="fas fa-caret-down"></i></button></th>
            <th colspan="2" class="dhead" style="text-align: right">Rp {{number_format($totalBiaya + $totalBiaya2 -
                $totalbkn,0)}}
            </th>
        </tr>

        @foreach ($subKategori3 as $no => $d)
        <tr x-transition x-show="open26">
            <th colspan="3"><a href="#" class="klikModal" id_kategori="4">{{ $d->sub_kategori }}</a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open22 = ! open22">Buka <i
                        class="fas fa-caret-down"></i></button>

            </th>
        </tr>
        @php
        $ttl = 0;
        @endphp
        @foreach ($biaya_murni as $a)
        @php
        $ttl += $a->debit + $a->debit_saldo;
        @endphp
        <tr x-transition x-show="open22">
            <td colspan="2" style="padding-left: 20px"><a target="_blank"
                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $a->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{
                    ucwords(strtolower($a->nm_akun)) }}</a>
            </td>
            <td style="text-align: right">Rp. {{ number_format($a->debit + + $a->debit_saldo, 1) }}</td>
        </tr>
        @endforeach
        <tr x-transition x-show="open26">
            <th colspan="2" style="padding-left: 20px">Total Biaya</th>
            <th style="text-align: right">Rp. {{ number_format($ttl, 0) }}</th>
        </tr>
        @endforeach
        <tr x-transition x-show="open26">
            <th colspan="3"><a href="#" class="klikModal" id_kategori="{{ $d->id }}">Uang
                    Keluar</a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open23 = ! open23">Buka <i
                        class="fas fa-caret-down"></i></button>

            </th>
        </tr>
        @php
        $ttl2 = 0;
        @endphp
        @foreach ($biayaGantung as $a)
        @php
        $ttl2 += $a->debit + $a->debit_saldo;
        @endphp
        <tr x-transition x-show="open23">
            <td colspan="2" style="padding-left: 20px"><a target="_blank"
                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $a->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{
                    ucwords(strtolower($a->nm_akun)) }}</a>
            </td>
            <td style="text-align: right">Rp. {{ number_format($a->debit + $a->debit_saldo, 1) }}</td>
        </tr>
        @endforeach
        <tr x-transition x-show="open26">
            <th colspan="2" style="padding-left: 20px">Total Uang Keluar</th>
            <th style="text-align: right">Rp. {{ number_format($ttl2, 0) }}</th>
        </tr>
        <tr x-transition x-show="open26">
            <td colspan="2" class="fw-bold" style="border-bottom: 1px solid black;padding-left: 20px">Total Total</td>
            <td class="fw-bold" align="right" style="border-bottom: 1px solid black;"> Rp.
                {{ number_format($totalBiaya2 + $totalBiaya, 1) }}</td>
        </tr>
        <tr x-transition x-show="open26">
            <th colspan="3"><a href="#" class="klikModal" id_kategori="4">Biaya Bukan Uang keluar</a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open25 = ! open25">Buka <i
                        class="fas fa-caret-down"></i></button>

            </th>
        </tr>
        @php
        $ttl_bkn_klr = 0;
        @endphp
        @foreach ($biaya_bkn_keluar as $a)
        @php
        $ttl_bkn_klr += $a->debit;
        @endphp
        <tr x-transition x-show="open25">
            <td colspan="2" style="padding-left: 20px"><a target="_blank"
                    href="{{ route('summary_buku_besar.detail', ['id_akun' => $a->id_akun, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}">{{
                    ucwords(strtolower($a->nm_akun)) }}</a>
            </td>
            <td style="text-align: right">Rp. {{ number_format($a->debit, 1) }}</td>
        </tr>
        @endforeach
        <tr x-transition x-show="open26">
            <th colspan="2" style="padding-left: 20px">Total Bukan Uang Keluar </th>
            <th style="text-align: right">Rp. {{ number_format($ttl_bkn_klr, 0) }} (-)</th>
        </tr>
        <tr x-transition x-show="open26">
            <th colspan="2" style="padding-left: 20px">Total Uang Keluar</th>
            <th style="text-align: right">Rp. {{ number_format($totalBiaya2 + $totalBiaya - $ttl_bkn_klr, 0) }}</th>
        </tr>
    </table>


</section>