<table width="100%" cellpadding="10px">
    <tr>
        <th style="background-color: white;" width="10%">Tanggal</th>
        <th style="background-color: white;" width="2%">:</th>
        <th style="background-color: white;">{{ tanggal($head_jurnal->tgl) }}</th>
        <th style="background-color: white;" width="10%">No Nota</th>
        <th style="background-color: white;" width="2%">:</th>
        <th style="background-color: white;">{{ $head_jurnal->kode . '-' . $head_jurnal->urutan }}</th>
    </tr>
    <tr>
        <th style="background-color: white;" width="10%">Customer</th>
        <th style="background-color: white;" width="2%">:</th>
        <th style="background-color: white;">{{ $head_jurnal->nm_customer }}</th>
        <th style="background-color: white;" width="10%">Driver</th>
        <th style="background-color: white;" width="2%">:</th>
        <th style="background-color: white;">{{ $head_jurnal->driver }}</th>
    </tr>
</table>
<a href="{{ route('penjualan2.print', ['urutan' => $head_jurnal->urutan]) }}" class="btn btn-sm btn-primary float-end"
    target="_blank"><i class="fas fa-print"></i> Print</a>
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
            <th width="15%" class="dhead">Qty</th>
            <th width="15%" class="dhead" style="text-align: right">Rp Satuan</th>
            <th width="15%" class="dhead" style="text-align: right">Total Rp</th>
            <th width="15%" class="dhead">Admin</th>
        </tr>
    </thead>
    <tbody>
        
        @foreach ($produk as $no => $a)
        <tr>
            <td>{{ $no + 1 }}</td>
            <td>{{ $a->nm_produk }}</td>
            <td>{{ $a->qty }}</td>
            <td align="right">{{ number_format($a->rp_satuan, 0) }}</td>
            <td align="right">{{ number_format($a->total_rp, 0) }}</td>
            <td>{{ $a->admin }}</td>
        </tr>
        @endforeach
    </tbody>
</table>