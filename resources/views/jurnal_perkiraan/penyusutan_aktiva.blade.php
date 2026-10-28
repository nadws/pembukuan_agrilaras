<x-theme.app title="{{ $title }}" table="Y" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><h5 class="mb-0">{{ $akun->nama }}</h5><small>Daftar aktiva penyumbang biaya penyusutan</small></div>
            <a href="{{ route('jurnal-perkiraan.laba-rugi', ['bulan_dari' => $tanggalAwal->month, 'tahun_dari' => $tanggalAwal->year, 'bulan_sampai' => $tanggalAkhir->month, 'tahun_sampai' => $tanggalAkhir->year]) }}" class="btn btn-light btn-sm">Kembali ke Laba/Rugi</a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <div class="mb-3 text-muted">Periode: {{ $tanggalAwal->translatedFormat('d F Y') }} s/d {{ $tanggalAkhir->translatedFormat('d F Y') }}</div>
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead><tr><th>Nama Aktiva</th><th>Tanggal Input Penyusutan</th><th class="text-end">Nilai Perolehan</th><th class="text-end">Penyusutan/Bulan</th><th class="text-end">Akumulasi</th><th class="text-end">Nilai Buku</th><th class="text-end">Penyusutan Periode</th></tr></thead>
                <tbody>
                    @forelse ($aktiva as $a)
                        <tr>
                            <td><strong>{{ $a->nm_aktiva }}</strong><small class="d-block text-muted">{{ $a->nama_akun_aset ?: 'Akun aset tidak tersedia' }}</small></td>
                            <td>{{ $a->tanggal_input ?: '-' }}</td>
                            <td class="text-end">Rp {{ number_format($a->h_perolehan, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($a->biaya_depresiasi, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($a->akumulasi_penyusutan, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format(max(0, (float) $a->h_perolehan - (float) $a->akumulasi_penyusutan), 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($a->nominal_periode, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada aktiva pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if ($aktiva->isNotEmpty())
                    <tfoot><tr class="fw-bold"><td colspan="6">Total Penyusutan Periode</td><td class="text-end">Rp {{ number_format($aktiva->sum('nominal_periode'), 0, ',', '.') }}</td></tr></tfoot>
                @endif
            </table>
        </div>
    </x-slot>
</x-theme.app>
