@php
    $filename = 'export_perlengkapan.xls';

    // Mengatur header untuk file Excel
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=\"$filename\"");

@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Export</title>
</head>

<body>
    <table border="1">
        <thead>
            <tr>
                <th>Nama Peralatan</th>
                <th>Tanggal</th>
                <th>Kelompok</th>
                <th>Umur</th>
                <th>Harga Perolehan</th>
                <th>Biaya Depresiasi</th>
                <th>Nila Buku</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($peralatan as $p)
                <tr>
                    <td>{{ $p->nm_aktiva }}</td>
                    <td>{{ $p->tgl }}</td>
                    <td>{{ $p->nm_kelompok }}</td>
                    <td>{{ $p->umur }} {{ $p->periode }}</td>
                    <td>{{ $p->h_perolehan }}</td>
                    <td>{{ $p->biaya_depresiasi }}</td>
                    <td>{{ $p->h_perolehan - $p->ttl_depresiasi }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
