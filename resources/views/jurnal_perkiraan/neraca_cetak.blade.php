<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        @page { size: A4 landscape; margin: 11mm 10mm 13mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f8; color: #111827; font-family: Arial, Helvetica, sans-serif; }
        .print-actions { display: flex; justify-content: flex-end; gap: 8px; max-width: 277mm; margin: 16px auto 8px; }
        .print-actions button, .print-actions a { padding: 9px 15px; border: 1px solid #3156aa; border-radius: 6px; background: #3156aa; color: #fff; font-size: 13px; text-decoration: none; cursor: pointer; }
        .print-actions a { background: #fff; color: #3156aa; }
        .sheet { width: 277mm; min-height: 190mm; margin: 0 auto 18px; padding: 10mm; background: #fff; box-shadow: 0 5px 24px rgba(31, 53, 101, .13); }
        .report-header { margin-bottom: 7mm; text-align: center; }
        .report-header h1 { margin: 0 0 1.5mm; font-size: 16pt; letter-spacing: .2px; }
        .report-header h2 { margin: 0 0 1.5mm; font-size: 13pt; }
        .report-header p { margin: 0; font-size: 9pt; }
        .balance-print { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 7.8pt; }
        .balance-print col.description { width: 32%; }
        .balance-print col.amount { width: 18%; }
        .balance-print thead { display: table-header-group; }
        .balance-print tr { break-inside: avoid; page-break-inside: avoid; }
        .balance-print th { padding: 2.4mm 2mm; border: 1px solid #6f7c91; background: #dbe5f5; color: #152f61; font-size: 8.5pt; }
        .balance-print th:nth-child(2), .balance-print th:nth-child(4),
        .balance-print td:nth-child(2), .balance-print td:nth-child(4) { text-align: right; }
        .balance-print td { height: 6.3mm; padding: 1.3mm 2mm; border-right: 1px solid #c8ced8; border-bottom: 1px solid #d9dde5; vertical-align: middle; }
        .balance-print td:first-child, .balance-print th:first-child { border-left: 1px solid #6f7c91; }
        .balance-print td:nth-child(3), .balance-print th:nth-child(3) { border-left: 2px solid #6f7c91; }
        .balance-print tbody tr:last-child td { border-bottom: 1px solid #6f7c91; }
        .amount { white-space: nowrap; font-variant-numeric: tabular-nums; }
        .code { display: inline-block; min-width: 24mm; color: #64748b; font-family: Consolas, monospace; }
        .fw { font-weight: 700; }
        .section { background: #e7edf6; color: #19376f; font-weight: 800; }
        .subsection { background: #f3f5f8; color: #3b4b65; font-weight: 700; }
        .subtotal { border-top: 1px solid #7b8799 !important; font-weight: 700; }
        .total { border-top: 1.5px solid #344d7b !important; background: #edf2f9; color: #18366f; font-weight: 800; }
        .grand { border-top: 2px solid #18366f !important; border-bottom: 3px double #18366f !important; background: #dce6f6; color: #102e66; font-weight: 900; }
        .blank { border-bottom-color: transparent !important; background: #fff !important; }
        .balance-check { display: flex; justify-content: space-between; margin-top: 5mm; padding-top: 3mm; border-top: 1px solid #6f7c91; font-size: 8.5pt; font-weight: 700; }
        .footnote { margin: 3mm 0 0; color: #586579; font-size: 7pt; }
        @media print {
            html, body { width: 100%; margin: 0; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-actions { display: none !important; }
            .sheet { width: 100%; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
@php
    $formatNumber = function ($value) {
        $number = (float) $value;
        return ($number < 0 ? '(' : '') . 'Rp ' . number_format(abs($number), 0, ',', '.') . ($number < 0 ? ')' : '');
    };
    $line = fn ($label, $value = null, $class = '', $depth = 0, $code = null) => compact('label', 'value', 'class', 'depth', 'code');
    $appendAccounts = function (&$target, $rows, $multiplier = 1) use ($line) {
        foreach ($rows as $row) {
            $target[] = $line(
                $row['nama'],
                bcmul($row['value'], (string) $multiplier, 12),
                $row['has_children'] ? 'fw' : '',
                $row['depth'],
                $row['kode']
            );
        }
    };

    $left = [];
    $left[] = $line('ASET LANCAR', null, 'section');
    $left[] = $line('Kas dan Bank', null, 'subsection');
    $appendAccounts($left, $result['cashRows']);
    $left[] = $line('Jumlah Kas dan Bank', $result['cash'], 'subtotal');
    $left[] = $line('Piutang dan Uang Muka', null, 'subsection');
    $appendAccounts($left, $result['receivableRows']);
    $left[] = $line('Jumlah Piutang dan Uang Muka', $result['receivable'], 'subtotal');
    $left[] = $line('Persediaan', null, 'subsection');
    $appendAccounts($left, $result['inventoryRows']);
    $left[] = $line('Jumlah Persediaan', $result['inventory'], 'subtotal');
    $left[] = $line('Aset Lancar Lainnya', null, 'subsection');
    $appendAccounts($left, $result['otherCurrentRows']);
    $left[] = $line('Jumlah Aset Lancar Lainnya', $result['otherCurrent'], 'subtotal');
    $left[] = $line('JUMLAH ASET LANCAR', $result['currentAssets'], 'total');
    $left[] = $line('ASET TETAP', null, 'section');
    $appendAccounts($left, $result['fixedAssetRows']);
    $left[] = $line('Jumlah Harga Perolehan', $result['fixedAssets'], 'subtotal');
    $left[] = $line('Akumulasi Penyusutan', null, 'subsection');
    $appendAccounts($left, $result['depreciationRows'], -1);
    $left[] = $line('Jumlah Akumulasi Penyusutan', bcmul($result['accumulatedDepreciation'], '-1', 12), 'subtotal');
    $left[] = $line('JUMLAH ASET TETAP NETO', $result['netFixedAssets'], 'total');
    $left[] = $line('TOTAL ASET', $result['totalAssets'], 'grand');

    $right = [];
    $right[] = $line('KEWAJIBAN JANGKA PENDEK', null, 'section');
    $right[] = $line('Hutang Usaha', null, 'subsection');
    $appendAccounts($right, $result['payableRows']);
    $right[] = $line('Jumlah Hutang Usaha', $result['payable'], 'subtotal');
    $right[] = $line('Kewajiban Lancar Lainnya', null, 'subsection');
    $appendAccounts($right, $result['otherCurrentLiabilityRows']);
    $right[] = $line('Jumlah Kewajiban Lancar Lainnya', $result['otherCurrentLiability'], 'subtotal');
    $right[] = $line('JUMLAH KEWAJIBAN JANGKA PENDEK', $result['currentLiabilities'], 'total');
    $right[] = $line('KEWAJIBAN JANGKA PANJANG', null, 'section');
    $appendAccounts($right, $result['longTermLiabilityRows']);
    $right[] = $line('JUMLAH KEWAJIBAN JANGKA PANJANG', $result['longTermLiabilities'], 'total');
    $right[] = $line('TOTAL KEWAJIBAN', $result['totalLiabilities'], 'total');
    $right[] = $line('EKUITAS', null, 'section');
    $appendAccounts($right, $result['equityRows']);
    $right[] = $line('Laba/Rugi Berjalan', $result['currentProfit']);
    $right[] = $line('JUMLAH EKUITAS', $result['totalEquity'], 'total');
    $right[] = $line('TOTAL KEWAJIBAN DAN EKUITAS', $result['liabilitiesAndEquity'], 'grand');
    $rowCount = max(count($left), count($right));
@endphp

<div class="print-actions">
    <a href="{{ route('jurnal-perkiraan.neraca', ['tanggal' => $reportDate->toDateString()]) }}">Kembali</a>
    <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>

<main class="sheet">
    <header class="report-header">
        <h1>CV AGRI LARAS</h1>
        <h2>LAPORAN NERACA</h2>
        <p>Posisi per {{ $reportDate->translatedFormat('d F Y') }}</p>
    </header>

    <table class="balance-print">
        <colgroup>
            <col class="description"><col class="amount"><col class="description"><col class="amount">
        </colgroup>
        <thead>
            <tr><th>ASET</th><th>NILAI (IDR)</th><th>KEWAJIBAN DAN EKUITAS</th><th>NILAI (IDR)</th></tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $rowCount; $i++)
                @php $l = $left[$i] ?? null; $r = $right[$i] ?? null; @endphp
                <tr>
                    @foreach ([$l, $r] as $item)
                        @if ($item)
                            <td class="{{ $item['class'] }}" style="padding-left: {{ 2 + ($item['depth'] * 4) }}mm">
                                @if ($item['code'])<span class="code">{{ $item['code'] }}</span>@endif{{ $item['label'] }}
                            </td>
                            <td class="amount {{ $item['class'] }}">{{ $item['value'] !== null ? $formatNumber($item['value']) : '' }}</td>
                        @else
                            <td class="blank"></td><td class="blank"></td>
                        @endif
                    @endforeach
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="balance-check">
        <span>{{ abs((float) $result['difference']) <= 1 ? 'NERACA SEIMBANG' : 'NERACA BELUM SEIMBANG' }}</span>
        <span>Selisih: {{ $formatNumber($result['difference']) }}</span>
    </div>
    <p class="footnote">Disusun dari jurnal perkiraan pada batch berstatus aktif sampai tanggal laporan.</p>
</main>
</body>
</html>
