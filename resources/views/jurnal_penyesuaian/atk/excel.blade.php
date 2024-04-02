@php
    $filename = 'export_atk_opname.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=\"$filename\"");

@endphp

<table class="table table-bordered" border="1">
    <thead>
        <tr>
            <th class="dhead" width="15%">Tanggal Perolehan</th>
            <th class="dhead" width="20%">Barang</th>
            <th class="dhead">Stok Program</th>
            <th class="dhead text-end" width="13%">Harga Satuan</th>
            <th class="dhead text-end" width="13%">Total</th>
            <th class="dhead">Stok Aktual</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total = 0;
        @endphp
        @foreach ($atk as $no => $d)
            @php

                $sisa = $d->debit - $d->kredit;
                $rp_satuan = $d->rp_satuan;
                $ttl = $rp_satuan * $d->debit;
                $total += $rp_satuan * $d->debit;
            @endphp
            <input type="hidden" name="id_produk[]" value="{{ $d->id_produk }}">
            <input type="hidden" name="sisa[]" class="sisa{{ $no }}" value="{{ $sisa }}">
            <input type="hidden" name="rp_satuan[]" class="rp_satuan{{ $no }}" value="{{ $rp_satuan }}">
            <input type="hidden" name="gudang_id[]" value="{{ $d->gudang_id }}">
            <input type="hidden" name="ttl[]" value="{{ $ttl }}" class="ttl{{ $no }}">


            <tr>
                <td>{{ $d->tgl1 }}</td>
                <td>{{ ucwords($d->nm_produk) }}</td>
                <td align="right">{{ $sisa }}</td>
                <td align="right">Rp. {{ number_format($rp_satuan, 0) }}</td>
                <td align="right">Rp. {{ number_format($ttl, 0) }}</td>
                <td align="right"></td>

            </tr>
        @endforeach
    </tbody>
</table>
