<table class="table table-stripped">
    <thead>
        <tr>
            <th class="dhead">Tanggal</th>
            <th class="dhead">Nota</th>
            <th class="dhead">Akun Pembayaran</th>
            <th class="dhead">Rupiah</th>
            <th class="text-center dhead">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($perencanaan as $no => $d)
        <tr>
            <td>{{ tanggal($d->tgl) }}</td>
            <td>{{ $d->nota_setor }}</td>
            <td>{{ $d->nm_akun }}</td>
            <td>Rp. {{number_format($d->nominal,0)}}</td>
            <td align="center">
                <a id_produk="{{ $d->nota_setor }}" class="btn btn-sm btn-primary edit" href="#"><i
                        class="fas fa-eye"></i>
                </a>
                <a href="{{ route('penyetoran.print', ['nota_setor' => $d->nota_setor]) }}"
                    class="btn btn-sm btn-primary"><i class="fas fa-print"></i></a>
                <a onclick="return confirm('Yakin dihapus ?')"
                    href="{{ route('penyetoran.delete', ['nota_setor' => $d->nota_setor]) }}"
                    class="btn btn-sm btn-danger delete_nota"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>