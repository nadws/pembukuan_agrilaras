<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small>ACCURATE Accounting System Report</small>
                <h5 class="mb-0">{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</h5>
                <small>{{ $tanggalAwal }} s.d. {{ $tanggalAkhir }}</small>
            </div>
            <div class="d-flex gap-2">
            <a href="{{ route('jurnal-perkiraan.detail-akun.export', ['akun_perkiraan' => $akun->id_akun_perkiraan, 'tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir]) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
            <a href="{{ route('jurnal-perkiraan.laba-rugi', [
                'bulan_dari' => \Carbon\Carbon::parse($tanggalAwal)->month,
                'tahun_dari' => \Carbon\Carbon::parse($tanggalAwal)->year,
                'bulan_sampai' => \Carbon\Carbon::parse($tanggalAkhir)->month,
                'tahun_sampai' => \Carbon\Carbon::parse($tanggalAkhir)->year,
            ]) }}" class="btn btn-light btn-sm">Kembali</a></div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        @php
            $formatNumber = fn ($value) => number_format((float) $value, 2, ',', '.');
        @endphp
        <form method="get" class="row g-2 align-items-end mb-4">
            <div class="col-md-4"><label class="form-label">Tanggal Dari</label><input type="date" name="tanggal_awal" value="{{ $tanggalAwal }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Tanggal Sampai</label><input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir }}" class="form-control"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan</button></div>
        </form>
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card border h-100 p-3"><small>Jumlah Detail</small><strong class="fs-5">{{ number_format($detail->count()) }}</strong></div></div>
            <div class="col-md-3"><div class="card border h-100 p-3"><small>Total Debit</small><strong class="fs-5 text-end">{{ $formatNumber($totalDebit) }}</strong></div></div>
            <div class="col-md-3"><div class="card border h-100 p-3"><small>Total Kredit</small><strong class="fs-5 text-end">{{ $formatNumber($totalKredit) }}</strong></div></div>
            <div class="col-md-3"><div class="card border-primary h-100 p-3"><small>Nilai Laporan</small><strong class="fs-5 text-end text-primary">{{ $formatNumber($nilaiLaporan) }}</strong></div></div>
        </div>
        @if ($jumlahAkun > 1)<div class="alert alert-info py-2">Nilai mencakup {{ number_format($jumlahAkun) }} akun dalam hierarki {{ $akun->nama }}.</div>@endif
        <div class="table-responsive"><table class="table table-hover" id="table">
            <thead><tr><th>Tanggal</th><th>No. Transaksi</th><th>Tipe</th><th>Kode Akun</th><th>Nama Akun</th><th>Deskripsi</th><th>File Import</th><th>Debit</th><th>Kredit</th></tr></thead>
            <tbody>@foreach ($detail as $item)<tr>
                <td>{{ $item->tanggal->format('d-m-Y') }}</td><td>{{ $item->nomor_transaksi }}</td><td>{{ $item->tipe_transaksi ?: '-' }}</td><td>{{ $item->akun->kode_perkiraan }}</td><td>{{ $item->akun->nama }}</td><td>{{ $item->deskripsi }}</td><td>{{ $item->impor->nama_file }}</td>
                <td class="text-end">{{ number_format((float) $item->debit, 2, ',', '.') }}</td><td class="text-end">{{ number_format((float) $item->kredit, 2, ',', '.') }}</td>
            </tr>@endforeach</tbody>
            <tfoot><tr class="fw-bold">
                <td colspan="7" class="text-end">TOTAL</td>
                <td class="text-end">{{ $formatNumber($totalDebit) }}</td>
                <td class="text-end">{{ $formatNumber($totalKredit) }}</td>
            </tr></tfoot>
        </table></div>
    </x-slot>
</x-theme.app>
