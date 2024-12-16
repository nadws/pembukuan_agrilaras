<table width="100%" cellpadding="10px">
    <tr>
        <th width="10%">Tanggal</th>
        <th width="2%">:</th>
        <th>{{ tanggal($detail->tgl) }}</th>
        <th width="10%">No Nota</th>
        <th width="2%">:</th>
        <th>{{ $detail->no_nota }}</th>
    </tr>
    <tr>
        <th width="10%">Gudang</th>
        <th width="2%">:</th>
        <th>{{ $detail->gudang->nm_gudang }}</th>
        <th width="10%">Keterangan</th>
        <th width="2%">:</th>
        <th>{{ $detail->ket }}</th>
    </tr>

</table>
<br>
<br>

<style>
    .dhead {
        background-color: #435EBE !important;
        color: white;
    }

    .dborder {
        border-color: #435EBE
    }
</style>
<table class="table table-hover table-bordered dborder">
    <thead>
        <tr>
            <th class="dhead" width="5">#</th>
            <th class="dhead">Nama Produk</th>
            <th width="15%" class="dhead" style="text-align: right">Stok Sebelumnya</th>
            <th width="15%" class="dhead" style="text-align: right">Stok Masuk</th>
            <th width="15%" class="dhead" style="text-align: right">Rp Satuan</th>
            <th width="15%" class="dhead" style="text-align: right">Total Rp</th>
        </tr>
    </thead>
    <tbody>
        @php
            $ttlDebit = 0;
            $ttlRpSatuan = 0;
            $ttlRp = 0;
        @endphp
        @foreach ($stok as $no => $d)
            @php
                $ttlDebit += $d->debit;
                $rpSatuan = $d->rp / $d->debit;
                $ttlRpSatuan += $rpSatuan;
                $ttlRp += $rpSatuan * $d->debit;
            @endphp
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ $d->nm_produk }}</td>
                <td align="right">{{ $d->jml_sebelumnya }}</td>
                <td align="right">{{ $d->debit }}</td>
                <td align="right">Rp. {{ number_format($rpSatuan, 0) }}</td>
                <td align="right">Rp. {{ number_format($rpSatuan * $d->debit, 0) }}</td>
            </tr>
        @endforeach
        <tr>
            <td></td>
            <th colspan="2">Total</th>
            <th style="text-align: right">{{ $ttlDebit }}</th>
            <th style="text-align: right">Rp. {{ number_format($ttlRpSatuan, 0) }}</th>
            <th style="text-align: right">Rp. {{ number_format($ttlRp, 0) }}</th>
        </tr>
    </tbody>

</table>
