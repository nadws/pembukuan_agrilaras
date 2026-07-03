<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        body {
            background: #f6f8fc;
            color: #263238;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        .navbar {
            background: #fff;
            border-bottom: 1px solid #dfe6f3;
        }

        .doc-shell {
            max-width: 1180px;
        }

        .doc-title {
            font-size: 22px;
            font-weight: 700;
        }

        .doc-subtitle {
            color: #607085;
        }

        .section-card {
            background: #fff;
            border: 1px solid #dfe6f3;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            background: #435EBE;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            padding: 12px 16px;
        }

        .formula {
            background: #f2f7ff;
            border: 1px solid #d8e5ff;
            border-radius: 6px;
            color: #12356d;
            display: inline-block;
            font-size: 13px;
            line-height: 1.45;
            padding: 6px 8px;
            white-space: normal;
        }

        .table td,
        .table th {
            vertical-align: top;
        }

        .table thead th {
            color: #435EBE;
            font-size: 12px;
            text-transform: uppercase;
        }

        @media print {
            .navbar,
            .nav,
            .btn {
                display: none !important;
            }

            body {
                background: #fff;
            }

            .section-card {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="container-fluid doc-shell py-2">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('laporan_layer') }}">
                <img src="/assets/login/img/agri_laras2.png" alt="Agri Laras" width="38" height="38">
                <span class="fw-bold">Laporan Layer</span>
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">Print</button>
        </div>
    </nav>

    <main class="container-fluid doc-shell py-3">
        <div class="row align-items-end mb-3">
            <div class="col-lg-8">
                <div class="doc-title">{{ $title }}</div>
                <div class="doc-subtitle">
                    Panduan membaca angka laporan layer dengan bahasa sederhana.
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <ul class="nav nav-pills nav-fill">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('laporan_layer', request()->only('tgl')) }}">Laporan layer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('dokumentasi_laporan_layer', request()->only('tgl')) }}">Dokumentasi rumus</a>
                    </li>
                </ul>
            </div>
        </div>

        @foreach ($sections as $section)
            <section class="section-card mb-3">
                <div class="section-header">{{ $section['title'] }}</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 22%">Nama</th>
                                <th style="width: 43%">Cara menghitung</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($section['items'] as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item['name'] }}</td>
                                    <td><span class="formula">{{ $item['formula'] }}</span></td>
                                    <td>{{ $item['note'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</body>

</html>
