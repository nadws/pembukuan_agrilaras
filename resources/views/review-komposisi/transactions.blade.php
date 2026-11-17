<x-theme.app title="Transaksi Sumber Laba-Rugi" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader"><div class="d-flex justify-content-between flex-wrap gap-2"><div><h5>{{ $account->kode_perkiraan }} · {{ $account->nama }}</h5><small>{{ $start->translatedFormat('F Y') }} · jurnal dari impor aktif</small></div><a class="btn btn-outline-primary btn-sm" href="{{ route('review-komposisi.index', ['periode' => $start->format('Y-m')]) }}">Kembali ke Review</a></div></x-slot>
    <x-slot name="cardBody">
        @php
            $rp = fn ($value) => 'Rp '.number_format($value, 2, ',', '.');
            $isIncome = in_array($account->tipe_akun, ['REVE', 'OINC'], true);
            $net = $isIncome ? $totals->kredit - $totals->debit : $totals->debit - $totals->kredit;
        @endphp
        <div class="alert alert-light border">Total seluruh transaksi periode: Debit {{ $rp($totals->debit) }} · Kredit {{ $rp($totals->kredit) }}<br>Nilai akun ({{ $isIncome ? 'kredit − debit' : 'debit − kredit' }}): <strong>{{ $rp($net) }}</strong></div>
        <div class="table-responsive"><table class="table table-striped"><thead><tr><th>Tanggal</th><th>Nomor transaksi</th><th>Deskripsi</th><th>File impor</th><th class="text-end">Debit</th><th class="text-end">Kredit</th></tr></thead><tbody>
            @forelse($rows as $row)
                <tr><td>{{ $row->tanggal }}</td><td>{{ $row->nomor_transaksi }}</td><td>{{ $row->deskripsi }}</td><td>{{ $row->nama_file }}</td><td class="text-end text-nowrap">{{ $rp($row->debit) }}</td><td class="text-end text-nowrap">{{ $rp($row->kredit) }}</td></tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody></table></div>{{ $rows->links() }}
    </x-slot>
</x-theme.app>
