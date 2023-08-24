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
        
            return DB::select("SELECT c.nm_akun, b.kredit, b.debit
            FROM akunprofit as a
            left join (
            SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
            FROM jurnal as b
            WHERE b.id_buku not in('1','5') and $jenis != 0 and b.penutup = 'Y' and b.tgl between '$tgl1' and '$tgl2'
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
    <table class="table table-bordered">
        <tr>
            <th class="dhead"><a class="uraian text-white" href="#" data-bs-toggle="modal" jenis="1"
                    data-bs-target="#tambah-uraian">Uraian</a> </th>
            <th colspan="2" class="dhead" style="text-align: right">Rupiah
            </th>
        </tr>
        @foreach ($subKategori1 as $d)
            <tr>
                <th colspan="2"><a href="#" class="klikModal"
                        id_kategori="{{ $d->id }}">{{ ucwords($d->sub_kategori) }}</a>
                </th>
            </tr>
            @foreach (getAkun($d->id, $tgl1, $tgl2, 1) as $a)
                <tr>
                    <td colspan="2" style="padding-left: 20px">{{ ucwords(strtolower($a->nm_akun)) }}</td>
                    <td style="text-align: right">Rp. {{ number_format($a->kredit, 2) }}</td>
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
                <th colspan="2"><a href="#" class="klikModal"
                        id_kategori="{{ $d->id }}">{{ ucwords($d->sub_kategori) }}</a>
                </th>
            </tr>
            @foreach (getAkun($d->id, $tgl1, $tgl2, 2) as $a)
                <tr>
                    <td colspan="2" style="padding-left: 20px">{{ ucwords(strtolower($a->nm_akun)) }}</td>
                    <td style="text-align: right">Rp. {{ number_format($a->debit, 2) }}</td>
                </tr>
            @endforeach
        @endforeach

        <tr>
            <td colspan="2" class="fw-bold" style="border-bottom: 1px solid black;">Total Biaya-biaya</td>
            <td class="fw-bold" align="right" style="border-bottom: 1px solid black;">
                {{ number_format($totalBiaya, 0) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="fw-bold">TOTAL LABA BERSIH</td>
            <td class="fw-bold" align="right">Rp.{{ number_format($totalPendapatan - $totalBiaya, 0) }}</td>
        </tr>
    </table>


</section>
