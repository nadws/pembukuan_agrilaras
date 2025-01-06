<h6>Kandang : {{ $kandang->nm_kandang }}</h6>
<br>
<div class="table-responsive">

    <table class="table table-bordered" id="table_pullet">
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th class="text-end">Death</th>
                <th class="text-end">Culling</th>
                <th class="text-end">Afkir</th>
                <th class="text-end">Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($populasi as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ tanggal($item->tgl) }}</td>
                    <td class="text-end">{{ $item->death }}</td>
                    <td class="text-end">{{ $item->jual }}</td>
                    <td class="text-end">{{ $item->afkir }}</td>
                    <td class="text-end">
                        {{ number_format(($item->rupiah / $item->stok_awal) * ($item->death + $item->jual + $item->afkir), 0) }}
                    </td>
                </tr>
            @endforeach

        </tbody>

    </table>
</div>
