<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ $setorKas->nomor_setoran }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f7; color: #172554; font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        .toolbar { width: 210mm; max-width: calc(100% - 24px); margin: 14px auto; display: flex; justify-content: flex-end; gap: 8px; }
        .btn { border: 1px solid #3155a5; border-radius: 5px; padding: 9px 15px; background: #fff; color: #274690; cursor: pointer; text-decoration: none; font-weight: 700; }
        .btn-primary { background: #3155a5; color: #fff; }
        .sheet { width: 210mm; min-height: 297mm; margin: 0 auto 20px; padding: 15mm 14mm; background: #fff; box-shadow: 0 2px 14px rgba(15, 23, 42, .12); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #3155a5; padding-bottom: 12px; margin-bottom: 18px; }
        .company { font-size: 22px; font-weight: 800; letter-spacing: .5px; color: #17366f; }
        .company-sub { color: #64748b; margin-top: 4px; }
        .document-title { text-align: right; }
        .document-title h1 { margin: 0 0 5px; font-size: 20px; color: #17366f; }
        .document-number { font-weight: 700; color: #3155a5; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 28px; margin-bottom: 18px; }
        .info { border-bottom: 1px solid #dbe3ef; padding: 5px 0 8px; }
        .label { display: block; color: #64748b; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
        .value { color: #172554; font-weight: 700; }
        .total-box { background: #eef4ff; border: 1px solid #b8c8eb; border-radius: 7px; padding: 13px 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .total-label { color: #536987; font-weight: 700; }
        .total-value { color: #17366f; font-size: 21px; font-weight: 800; }
        h2 { margin: 20px 0 8px; font-size: 13px; color: #17366f; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 8px; background: #3155a5; color: #fff; font-size: 10px; text-align: left; }
        td { padding: 8px; border: 1px solid #dbe3ef; vertical-align: top; }
        tfoot td { background: #f4f7fb; font-weight: 800; }
        .right { text-align: right; }
        .center { text-align: center; }
        .muted { color: #64748b; }
        .signature { margin-top: 38px; display: flex; justify-content: flex-end; page-break-inside: avoid; }
        .signature-box { width: 210px; text-align: center; }
        .signature-space { height: 65px; }
        .signature-line { border-top: 1px solid #334155; padding-top: 5px; }
        tr { page-break-inside: avoid; }
        @page { size: A4 portrait; margin: 9mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { width: auto; min-height: auto; margin: 0; padding: 5mm; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('transaksi.setoran-kas.show', $setorKas) }}" class="btn">Kembali</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak</button>
    </div>

    <main class="sheet">
        <header class="header">
            <div>
                <div class="company">AGRI LARAS</div>
                <div class="company-sub">Bukti transaksi setoran kas/bank</div>
            </div>
            <div class="document-title">
                <h1>BUKTI SETORAN</h1>
                <div class="document-number">{{ $setorKas->nomor_setoran ?? 'SK-' . $setorKas->id }}</div>
            </div>
        </header>

        @php
            $akunSumberUnik = $setorKas->detail->map(function($d) {
                return ($d->akunSumber->kode_perkiraan ?? '') . ' - ' . ($d->akunSumber->nama ?? '');
            })->unique()->filter()->values();
        @endphp

        <section class="info-grid">
            <div class="info">
                <span class="label">Tanggal Setoran</span>
                <span class="value">{{ $setorKas->tanggal_setoran->format('d/m/Y') }}</span>
            </div>
            <div class="info">
                <span class="label">Nomor Referensi</span>
                <span class="value">{{ $setorKas->nomor_referensi ?: '-' }}</span>
            </div>
            <div class="info">
                <span class="label">Akun Tujuan</span>
                <span class="value">{{ $setorKas->akunTujuan->kode_perkiraan ?? '-' }} - {{ $setorKas->akunTujuan->nama ?? '-' }}</span>
            </div>
            <div class="info">
                <span class="label">Keterangan</span>
                <span class="value">{{ $setorKas->keterangan ?: '-' }}</span>
            </div>
            <div class="info">
                <span class="label">Akun Kas Sumber</span>
                <span class="value">{{ $akunSumberUnik->implode(', ') ?: '-' }}</span>
            </div>
            <div class="info"></div>
        </section>

        <div class="total-box">
            <span class="total-label">TOTAL SETORAN</span>
            <span class="total-value">Rp {{ number_format($setorKas->nominal_total, 0, ',', '.') }}</span>
        </div>

        <h2>Rincian Transaksi yang Disetorkan</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 25mm;">Tanggal</th>
                    <th style="width: 32mm;">No. Transaksi</th>
                    <th style="width: 42mm;">Customer</th>
                    <th>Keterangan</th>
                    <th style="width: 35mm;" class="right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($setorKas->detail as $detail)
                    <tr>
                        <td>{{ $detail->jurnalPerkiraan && $detail->jurnalPerkiraan->tanggal ? \Carbon\Carbon::parse($detail->jurnalPerkiraan->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $detail->jurnalPerkiraan->nomor_transaksi ?? '-' }}</td>
                        <td>{{ $detail->nama_customer ?? '-' }}</td>
                        <td>{{ $detail->jurnalPerkiraan->deskripsi ?? '-' }}</td>
                        <td class="right">Rp {{ number_format($detail->nominal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="center muted">Tidak ada rincian transaksi</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="right">Total</td>
                    <td class="right">Rp {{ number_format($setorKas->detail->sum('nominal'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        @if($jurnalHasil->isNotEmpty())
            <h2>Jurnal Perkiraan</h2>
            <table>
                <thead>
                    <tr>
                        <th>Akun</th>
                        <th>Keterangan</th>
                        <th style="width: 34mm;" class="right">Debit</th>
                        <th style="width: 34mm;" class="right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurnalHasil as $jurnal)
                        <tr>
                            <td>{{ $jurnal->kode_perkiraan }} - {{ $jurnal->nama_akun }}</td>
                            <td>{{ $jurnal->deskripsi ?: '-' }}</td>
                            <td class="right">{{ $jurnal->debit > 0 ? 'Rp ' . number_format($jurnal->debit, 0, ',', '.') : '-' }}</td>
                            <td class="right">{{ $jurnal->kredit > 0 ? 'Rp ' . number_format($jurnal->kredit, 0, ',', '.') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="signature">
            <div class="signature-box">
                <div>Dibuat oleh,</div>
                <div class="signature-space"></div>
                <div class="signature-line">( ................................ )</div>
            </div>
        </div>
    </main>

    <script>
        window.addEventListener('load', function () {
            if (new URLSearchParams(window.location.search).get('auto') === '1') {
                window.print();
            }
        });
    </script>
</body>
</html>
