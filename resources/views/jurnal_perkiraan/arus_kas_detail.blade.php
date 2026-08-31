<x-theme.app title="{{ $title }}" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader"><div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><div><h5 class="mb-0">Detail Arus Kas</h5><small>{{ ($akunKasAccounts ?? collect($akunKas))->map(fn($a)=>$a->kode_perkiraan.' - '.$a->nama)->implode(' | ') }} → {{ $akunLawan->nama }}{{ $kategori ? ' - '.$kategori : '' }}</small></div><a href="{{ url()->previous() }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a></div></x-slot>
    <x-slot name="cardBody">
        @php $fmt=fn($value)=>'Rp '.number_format((float)$value,0,',','.'); @endphp
        <div class="mb-3"><div class="text-muted">Periode {{ Carbon\Carbon::parse($tanggalAwal)->translatedFormat('d F Y') }} s/d {{ Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}</div><h5 class="text-primary mt-2 mb-0">Total: {{ $fmt($total) }}</h5></div>
        <div class="table-responsive"><table class="table table-sm table-hover align-middle"><thead class="table-primary"><tr><th>Tanggal</th><th>No. Transaksi</th><th>Akun</th><th>Keterangan</th><th class="text-end">Debit</th><th class="text-end">Kredit</th><th class="text-end">Nilai</th></tr></thead><tbody>
            @forelse($rows as $row)<tr><td>{{ Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td><td>{{ $row->nomor_transaksi }}</td><td>{{ $row->akun->kode_perkiraan ?? '-' }} - {{ $row->akun->nama ?? '-' }}</td><td>{{ $row->deskripsi ?: '-' }}</td><td class="text-end">{{ $fmt($row->debit) }}</td><td class="text-end">{{ $fmt($row->kredit) }}</td><td class="text-end fw-semibold">{{ $fmt($row->nilai_detail) }}</td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi detail untuk nominal ini.</td></tr>@endforelse
        </tbody><tfoot><tr class="fw-bold"><td colspan="6" class="text-end">Total</td><td class="text-end">{{ $fmt($total) }}</td></tr></tfoot></table></div>
        <p class="small text-muted mb-0">Detail ini hanya menampilkan transaksi yang membentuk nominal pada laporan arus kas, bukan seluruh buku besar akun.</p>
    </x-slot>
</x-theme.app>
