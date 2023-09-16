<table class="table table-hover" id="table">
    <thead>
        <tr>
            <th>#</th>
            <th>Tanggal</th>
            <th>No Nota</th>
            <th>Customer</th>
            <th>Pengantar</th>
            <th>Pcs</th>
            <th>Kg</th>
            <th>Ikat</th>
            <th>Kg Jual</th>
            <th>Total Rp</th>
            <th>Tipe Penjualan</th>
            <th>Status</th>
            <th>Tgl Setor</th>
            <th>Akun Setor</th>
            <th>No Setor</th>
            <th>Admin</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoice as $no => $i)
        <tr>
            <td>{{ $no + 1 }}</td>
            <td>{{ tanggal($i->tgl) }}</td>
            <td>{{ $i->no_nota }}</td>
            <td>
                @if ($i->lokasi == 'alpa')
                {{ $i->nm_customer }}{{$i->urutan_customer}}
                @else
                {{ $i->customer }}
                @endif

            </td>
            <td>{{$i->driver}}</td>
            <td>{{$i->pcs}}</td>
            <td>{{number_format($i->kg,1)}}</td>
            <td>{{number_format($i->pcs / 180,1)}}</td>
            <td>{{number_format($i->kg_jual,1)}}</td>
            <td>{{ number_format($i->total_rp,0) }}</td>
            <td>{{ $i->tipe }}</td>
            <td>{{$i->kredit - $i->debit == 0 ? 'Paid' : 'Unpaid'}}</td>
            <td>{{empty($i->nota_setor) ? '-' : (empty($i->akun_setor) ? tanggal($i->tgl_stor_kosong) :
                tanggal($i->tgl_setor))}}</td>
            <td>{{empty($i->nota_setor) ? '-' : $i->nota_setor}}</td>
            <td>{{empty($i->nota_setor) ? '-' : (empty($i->akun_setor) ? 'BCA' : $i->akun_setor)}}</td>
            <td>{{$i->admin}}</td>
        </tr>
        @endforeach
    </tbody>
</table>