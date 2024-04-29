@php
    $ttl = 0;
    foreach ($depaktiva as $a) {
        $ttl += $a->b_penyusutan;
    }
@endphp
<table class="table table-bordered" id="table_detail_akt">
    <thead>
        <tr>
            <th class="dhead">No</th>
            <th class="dhead">Nama aktiva</th>
            <th class="dhead">Tangal Perolehan</th>
            <th class="dhead">Nama kelompok</th>
            <th class="dhead text-end">Umur</th>
            <th class="dhead text-end">Biaya depresiasi <br> {{ number_format($ttl, 0) }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($depaktiva as $no => $d)
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ $d->nm_aktiva }}</td>
                <td>{{ tanggal($d->tgl_perolehan) }}</td>
                <td>{{ $d->nm_kelompok }}</td>
                <td class="text-end">{{ $d->umur }} tahun</td>
                <td class="text-end">{{ number_format($d->b_penyusutan, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
