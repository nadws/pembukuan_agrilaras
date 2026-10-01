<table>
    <tr>
        <td colspan="10"><strong>Riwayat Pelunasan Piutang {{ ucfirst($jenis) }} ({{ $awal }} s/d {{ $akhir }})</strong></td>
    </tr>
    <tr><td colspan="10"></td></tr>
    <tr>
        <th>No</th>
        <th>Tgl Bayar</th>
        <th>Voucher</th>
        <th>Customer</th>
        <th>Akun Pembayaran</th>
        <th>No Nota</th>
        <th>Jumlah Bayar</th>
        <th>Nilai Dilunasi</th>
        <th>Jenis Selisih</th>
        <th>Selisih</th>
    </tr>
    @foreach ($rows as $no => $r)
        <tr>
            <td>{{ $no + 1 }}</td>
            <td>{{ date('d-m-Y', strtotime($r->tanggal_bayar)) }}</td>
            <td>{{ $r->voucher }}</td>
            <td>{{ $r->nm_customer ?? '-' }}</td>
            <td>{{ trim(($r->kode_perkiraan ?? '') . ' - ' . ($r->nama_akun ?? ''), ' -') ?: '-' }}</td>
            <td>{{ $r->no_nota }}</td>
            <td align="right">{{ $r->jumlah_bayar }}</td>
            <td align="right">{{ $r->nilai_piutang_dilunasi }}</td>
            <td>{{ $r->jenis_selisih === 'tidak' ? '-' : ucfirst($r->jenis_selisih) }}</td>
            <td align="right">{{ $r->selisih_pembayaran }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="6"><strong>Total</strong></td>
        <td align="right"><strong>{{ $rows->sum('jumlah_bayar') }}</strong></td>
        <td align="right"><strong>{{ $rows->sum('nilai_piutang_dilunasi') }}</strong></td>
        <td></td>
        <td align="right"><strong>{{ $rows->sum('selisih_pembayaran') }}</strong></td>
    </tr>
</table>
