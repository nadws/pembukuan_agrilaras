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

    foreach ($subKategori1 as $d) {
    foreach (getAkun($d->id, $tgl1, $tgl2, 1) as $a) {
    $totalPendapatan += $a->kredit;
    }
    }
    foreach ($subKategori2 as $d) {
    foreach (getAkun($d->id, $tgl1, $tgl2, 2) as $a) {
    $totalBiaya += $a->debit;
    }
    }

    @endphp
    <table class="table table-bordered" x-data="{
        open1:false,
        open2:false,
    }">
        <tr>
            <th class="dhead"><a class="uraian text-white" href="#" data-bs-toggle="modal" jenis="1"
                    data-bs-target="#tambah-uraian">Uraian</a> </th>
            <th colspan="2" class="dhead" style="text-align: right">Rupiah
            </th>
        </tr>
        @foreach ($subKategori1 as $d)
        <tr>
            <th colspan="2"><a href="#" class="klikModal" id_kategori="{{ $d->id }}">{{ ucwords($d->sub_kategori) }}
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
            <th colspan="2"><a href="#" class="klikModal" id_kategori="{{ $d->id }}">{{ ucwords($d->sub_kategori) }}</a>
                <button class="btn btn-primary btn-sm btn-buka float-end" @click="open2 = ! open2">Buka <i
                        class="fas fa-caret-down"></i></button>

            </th>
        </tr>
        @foreach (getAkun($d->id, $tgl1, $tgl2, 2) as $a)
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
        $ebdiba = DB::select("SELECT a.nm_akun,b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
        FROM jurnal as b
        LEFT JOIN akun as a ON b.id_akun = a.id_akun
        WHERE b.id_buku not in('1','5') and b.debit != 0 and b.tgl between '$tgl1' and '$tgl2' AND b.id_akun in (51,58)
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
            <td colspan="2" class="fw-bold">TOTAL LABA BERSIH</td>
            <td class="fw-bold" align="right">Rp.{{ number_format($totalPendapatan - $totalBiaya - $ttlEbdiba, 0) }}
            </td>
        </tr>
    </table>


</section>