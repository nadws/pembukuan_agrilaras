<table width="100%" cellpadding="10px">
    <tr>
        <th style="background-color: white;" width="10%">Tanggal</th>
        <th style="background-color: white;" width="2%">:</th>
        <th style="background-color: white;">{{date('d-m-Y',strtotime($head_invoice->tgl))}}</th>
        <th style="background-color: white;" width="10%">No Nota</th>
        <th style="background-color: white;" width="2%">:</th>
        <th style="background-color: white;">{{$head_invoice->no_nota}}</th>
    </tr>
    <tr>
        <th style="background-color: white;" width="10%">Customer</th>
        <th style="background-color: white;" width="2%">:</th>
        <th style="background-color: white;">{{$head_invoice->nm_customer}}</th>
        <th style="background-color: white;" width="10%">Admin</th>
        <th style="background-color: white;" width="2%">:</th>
        <th style="background-color: white;">{{$head_invoice->admin}}</th>
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
            <th class="dhead">Produk</th>
            <th class="dhead" style="text-align: right">Pcs</th>
            <th class="dhead" style="text-align: right">Kg</th>
            <th class="dhead" style="text-align: right">Ikat</th>
            <th class="dhead" style="text-align: right">Kg Jual</th>
            <th class="dhead" style="text-align: right">Rp Satuan</th>
            <th class="dhead" style="text-align: right">Total Rp</th>
        </tr>
    </thead>
    <tbody>
        @php
        $total = 0;
        @endphp
        @foreach ($invoice as $no => $a)
        @php
        $total += $a->total_rp;
        @endphp
        <tr>
            <td>{{$no + 1}}</td>
            <td>{{$a->nm_telur}}</td>
            <td align="right">{{number_format($a->pcs,0)}}</td>
            <td align="right">{{number_format($a->kg,2)}}</td>
            <td align="right">{{number_format($a->pcs / 180,2)}}</td>
            <td align="right">{{number_format($a->kg_jual,2)}}</td>
            <td align="right">Rp {{number_format($a->rp_satuan,0)}}</td>
            <td align="right">Rp {{number_format($a->total_rp,0)}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <td align="center" class="fw-bold" colspan="7">Total</td>
        <td align="right" class="fw-bold">Rp {{number_format($total,0)}}</td>
    </tfoot>
</table>