<table class="table table-hover" id="table">
    <thead>
        <tr>
            <th>#</th>
            <th>Tanggal</th>
            <th>No Nota</th>
            <th>Tgl Bayar</th>
            <th>Pembayaran</th>
            <th>No Setor</th>
            <th>Customer</th>
            <th>Total Rp</th>
            <th>Tipe Jual</th>
            <th>Admin</th>
            <th>Pengantar</th>
            {{-- <th>Metode</th> --}}
            {{-- <th>Lokasi</th> --}}
            <th>Status</th>
            <th>Pcs</th>
            <th>Kg</th>
            <th>Ikat</th>
            <th>Kg Jual</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoice as $no => $i)
        <tr>
            <td>{{ $no + 1 }}</td>
            <td>{{ tanggal($i->tgl) }}</td>
            <td>{{ $i->no_nota }}</td>
            <td>{{ $i->tgl_bayar }}</td>
            <td>{{ $i->akun_setor }}</td>
            <td>{{ $i->nota_setor }}</td>
            <td>{{ $i->nm_customer }}{{ $i->urutan_customer }}</td>
            <td align="right">{{ $i->kredit }}</td>
            <td>{{ $i->tipe }}</td>
            <td>{{ ucwords($i->admin) }}</td>
            <td>{{ ucwords($i->driver) }}</td>
            {{-- <td>{{ $i->status == 'paid' ? 'Tunai' : 'Piutang' }}</td>
            <td>{{ $i->lokasi }}</td> --}}
            <td>
                {{$i->kredit - $i->debit == 0 ? 'Paid' : 'Unpaid'}}
            </td>
            <td>{{ $i->pcs }}</td>
            <td>{{ $i->kg }}</td>
            <td>{{ $i->pcs / 180 }}</td>
            <td>{{ $i->kg_jual }}</td>

        </tr>
        @endforeach
    </tbody>
</table>