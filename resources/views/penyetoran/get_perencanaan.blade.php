<style>
    .dhead {
        background-color: #435EBE !important;
        color: white;
    }
</style>
<div class="row">
    <div class="col-lg-12">
        <h6>Nota Setor : {{ $no_nota }}</h6>
    </div>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
        <tr>
            <th class="dhead" width="5">#</th>
            <th class="dhead">Tanggal</th>
            <th class="dhead">No Nota</th>
            <th class="dhead">Pembayaran</th>
            <th class="dhead">Keterangan</th>
            <th class="dhead" style="text-align: right">Total Rp</th>
        </tr>
        </tr>
    </thead>
    <tbody>
        @php
            $total = 0;
        @endphp
        @foreach ($invoice as $no => $i)
            @php
                $total += $i->nominal;
            @endphp
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ date(strtotime('d-m-Y', $i->tgl)) }}</td>
                <td>{{ $i->no_nota_jurnal }}</td>
                <td>{{ ucwords(strtolower($i->nm_akun)) }}</td>
                <td>{{ $i->ket }}</td>
                <td align="right">Rp {{ number_format($i->nominal, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" style="text-align: center">Total</th>
            <th style="text-align: right">Rp {{ number_format($total, 0) }}</th>
        </tr>
    </tfoot>
</table>
<div class="row">
    <div class="col-lg-3">
        <label for="">Tanggal</label>
        <input type="date" class="form-control" name="tgl" value="{{ date('Y-m-d') }}">
    </div>
    <div class="col-lg-3">
        <label for="">Pilih Akun Debit</label>
        <Select class="select" name="id_akun">
            <option value="">-Pilih Akun-</option>
            @foreach ($akun as $a)
                <option value="{{ $a->id_akun }}">{{ $a->nm_akun }}</option>
            @endforeach
        </Select>
    </div>
    <div class="col-lg-3">
        <label for="">Keterangan</label>
        <input type="text" class="form-control" name="ket">
    </div>
    <div class="col-lg-3">
        <label for="">Total Setor</label>
        <input type="text" class="form-control " style="text-align: right"
            value="Rp {{ number_format($total, 0, ',', '.') }}" readonly>

        <input type="hidden" value="{{ $total }}" name="total_setor">
        <input type="hidden" value="{{ $invo->id_akun }}" name="id_akun_kredit">
        <input type="hidden" value="{{ $no_nota }}" name="no_nota">
    </div>
</div>
