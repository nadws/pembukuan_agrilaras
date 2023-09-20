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

    function getAkun($id_kategori, $tgl1, $tgl2, $jenis)
    {
    $jenis = $jenis == 1 ? 'b.kredit' : 'b.debit';

    return DB::select("SELECT b.id_akun,c.nm_akun, b.kredit, b.debit
    FROM akunprofit as a
    left join (
    SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
    FROM jurnal as b
    WHERE b.id_buku not in('1','5') and $jenis != 0 and b.tgl between '$tgl1' and '$tgl2'
    group by b.id_akun
    ) as b on b.id_akun = a.id_akun
    left join akun as c on c.id_akun = a.id_akun
    where a.kategori = '$id_kategori';");
    }

    $biaya_murni = DB::select("SELECT b.id_akun,c.nm_akun, b.kredit, b.debit
    FROM akunprofit as a
    left join (
    SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
    FROM jurnal as b
    WHERE b.id_buku in ('2','12','10') and b.debit != 0 and b.tgl between '$tgl1' and '$tgl2'
    group by b.id_akun
    ) as b on b.id_akun = a.id_akun
    left join akun as c on c.id_akun = a.id_akun
    where a.kategori = '4';");

    $biayaGantung = DB::select("SELECT a.nm_akun, b.debit, b.kredit
    FROM akun as a
    left join (
    SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit, c.akunvs
    FROM jurnal as b
    left join (
    SELECT c.no_nota, c.id_akun as akunvs
    FROM jurnal as c
    WHERE c.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '6')
    group by c.no_nota
    ) as c on c.no_nota = b.no_nota
    where b.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_buku in ('2','12','10') and c.akunvs is not null
    group by b.id_akun
    ) as b on b.id_akun = a.id_akun
    where a.id_akun in (SELECT t.id_akun FROM akunprofit as t where t.kategori = '9'); ");
    foreach ($subKategori1 as $d) {
    foreach (getAkun($d->id, $tgl1, $tgl2, 1) as $a) {
    $totalPendapatan += $a->kredit;
    }
    }

    foreach ($biaya_murni as $a) {
    $totalBiaya += $a->debit;
    }

    foreach ($biayaGantung as $d) {
    $totalBiaya2 += $d->debit;
    }

    @endphp
    <table class="table table-bordered" x-data="{
        open1: false,
        open2: false,
        open22: false,
        open23: false,
    }">
        <tr>
            <th class="dhead"><a class="uraian text-white" href="#" data-bs-toggle="modal" jenis="1"
                    data-bs-target="#tambah-uraian">Uraian</a> </th>
            <th colspan="2" class="dhead" style="text-align: right">Rupiah
            </th>
        </tr>
        @foreach ($subKategori1 as $d)
        <tr>
            <th colspan="3"><a href="#" class="klikModal" id_kategori="{{ $d->id }}">{{ ucwords($d->sub_kategori) }}
                </a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open1 = ! open1">Buka <i
                        class="fas fa-caret-down"></i></button>
            </th>
        </tr>
        @foreach (getAkun($d->id, $tgl1, $tgl2, 1) as $a)
        <tr class="detail-row" data-id="{{ $d->id }}" x-transition x-show="open1">
            <td colspan="2" style="padding-left: 20px">{{ ucwords(strtolower($a->nm_akun)) }}</td>
            <td style="text-align: right">Rp. {{ number_format($a->kredit, 1) }}</td>
        </tr>
        @endforeach
        @endforeach

        <tr>
            <td colspan="2" class="fw-bold" style="border-bottom: 1px solid black;">Total Pendapatan</td>
            <td class="fw-bold" align="right" style="border-bottom: 1px solid black;">
                Rp. {{ number_format($totalPendapatan, 0) }}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th class="dhead"><a class="uraian text-white" href="#" data-bs-toggle="modal" jenis="2"
                    data-bs-target="#tambah-uraian">Biaya - Biaya</a> </th>
            <th colspan="2" class="dhead" style="text-align: right">Rupiah
            </th>
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
            <td colspan="2" style="padding-left: 20px">{{ ucwords(strtolower($a->nm_akun)) }}</td>
            <td style="text-align: right">Rp. {{ number_format($a->debit, 1) }}</td>
        </tr>
        @endforeach
        @endforeach

        <tr>
            <td colspan="2" class="fw-bold" style="border-bottom: 1px solid black;">Total Biaya-biaya</td>
            <td class="fw-bold" align="right" style="border-bottom: 1px solid black;">
                {{ number_format($totalBiaya, 1) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="fw-bold">TOTAL LABA KOTOR</td>
            <td class="fw-bold" align="right">Rp.{{ number_format($totalPendapatan - $totalBiaya, 0) }}</td>
        </tr>

        @php
        $akunPenyesuaian = '51,58, 91,92,93,70,54,47,77';

        $ebdiba = DB::select("SELECT a.nm_akun,b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
        FROM jurnal as b
        LEFT JOIN akun as a ON b.id_akun = a.id_akun
        WHERE b.id_buku not in('1','5') and b.debit != 0 and b.tgl between '$tgl1' and '$tgl2' AND b.id_akun in
        ($akunPenyesuaian)
        group by b.id_akun;");



        $ttlEbdiba = 0;
        @endphp
        @foreach ($ebdiba as $d)
        @php
        $ttlEbdiba += $d->debit;
        @endphp
        <tr>
            <td colspan="2" class="fw-bold">{{ ucwords($d->nm_akun) }}</td>
            <td class="fw-bold" align="right">Rp.{{ number_format($d->debit, 0) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="2" class="fw-bold">TOTAL PENYESUAIAN</td>
            <td class="fw-bold" align="right">Rp.{{ number_format($ttlEbdiba, 0) }}
            </td>
        </tr>
        <tr>
            <td colspan="2" class="fw-bold">TOTAL LABA BERSIH</td>
            <td class="fw-bold" align="right">Rp.{{ number_format($totalPendapatan - $totalBiaya - $ttlEbdiba, 0) }}
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>

        {{-- biaya di benarkan --}}
        <tr>
            <th class="dhead"><a class="uraian text-white" href="#" data-bs-toggle="modal" jenis="3"
                    data-bs-target="#tambah-uraian">Biaya dan uang keluar</a> </th>
            <th colspan="2" class="dhead" style="text-align: right">Rupiah
            </th>
        </tr>

        @foreach ($subKategori3 as $no => $d)
        <tr>
            <th colspan="3"><a href="#" class="klikModal" id_kategori="{{ $d->id }}">{{$d->sub_kategori}}</a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open22 = ! open22">Buka <i
                        class="fas fa-caret-down"></i></button>

            </th>
        </tr>
        @php
        $ttl=0;
        @endphp
        @foreach ($biaya_murni as $a)
        @php
        $ttl+= $a->debit;
        @endphp
        <tr x-transition x-show="open22">
            <td colspan="2" style="padding-left: 20px">{{ ucwords(strtolower($a->nm_akun)) }}</td>
            <td style="text-align: right">Rp. {{ number_format($a->debit, 1) }}</td>
        </tr>
        @endforeach
        <tr>
            <th colspan="2" style="padding-left: 20px">Total Biaya</th>
            <th style="text-align: right">Rp. {{number_format($ttl,0)}}</th>
        </tr>
        @endforeach
        <tr>
            <th colspan="3"><a href="#" class="klikModal" id_kategori="{{ $d->id }}">Uang Keluar</a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open23 = ! open23">Buka <i
                        class="fas fa-caret-down"></i></button>

            </th>
        </tr>
        @php
        $ttl2=0;
        @endphp
        @foreach ($biayaGantung as $a)
        @php
        $ttl2+=$a->debit;
        @endphp
        <tr x-transition x-show="open23">
            <td colspan="2" style="padding-left: 20px">{{ ucwords(strtolower($a->nm_akun)) }}</td>
            <td style="text-align: right">Rp. {{ number_format($a->debit, 1) }}</td>
        </tr>
        @endforeach
        <tr>
            <th colspan="2" style="padding-left: 20px">Total Uang Keluar</th>
            <th style="text-align: right">Rp. {{number_format($ttl2,0)}}</th>
        </tr>
        <tr>
            <th colspan="3" style="padding-left: 20px">&nbsp;</th>
        </tr>
        <tr>
            <td colspan="2" class="fw-bold" style="border-bottom: 1px solid black;">Total Total</td>
            <td class="fw-bold" align="right" style="border-bottom: 1px solid black;">
                {{ number_format($totalBiaya2 + $totalBiaya, 1) }}</td>
        </tr>
    </table>


</section>