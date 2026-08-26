<div class="journal-summary">
    <div class="journal-summary-box"><div class="label">Jumlah detail</div><div class="value">{{ number_format($ringkasan->jumlah_detail ?? 0, 0, ',', '.') }}</div></div>
    <div class="journal-summary-box"><div class="label">Total debit</div><div class="value">Rp {{ number_format($ringkasan->total_debit ?? 0, 0, ',', '.') }}</div></div>
    <div class="journal-summary-box"><div class="label">Total kredit</div><div class="value">Rp {{ number_format($ringkasan->total_kredit ?? 0, 0, ',', '.') }}</div></div>
</div>
<div class="journal-table-wrap">
    <table class="table table-hover align-middle journal-table">
        <thead><tr><th width="55">No</th><th>Tanggal</th><th>No Transaksi</th><th>Tipe</th><th class="text-end">Detail</th><th class="text-end">Debit</th><th class="text-end">Kredit</th><th width="90" class="text-center">Aksi</th></tr></thead>
        <tbody>
        @forelse ($jurnal as $nomor => $item)
            @php $detailRows = $detail[$item->nomor_transaksi] ?? collect(); $detailId = 'detail-penyesuaian-' . md5($item->nomor_transaksi); @endphp
            <tr class="journal-master-row" data-detail-target="{{ $detailId }}">
                <td>{{ $jurnal->firstItem() + $nomor }}</td><td>{{ tanggal($item->tanggal) }}</td><td>{{ $item->nomor_transaksi }}</td><td>{{ $item->tipe_transaksi }}</td>
                <td class="text-end">{{ number_format($item->jumlah_detail, 0, ',', '.') }} baris</td><td class="text-end">Rp {{ number_format($item->total_debit, 0, ',', '.') }}</td><td class="text-end">Rp {{ number_format($item->total_kredit, 0, ',', '.') }}</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary btn-toggle-detail" data-detail-target="{{ $detailId }}" title="Detail"><i class="fas fa-eye"></i></button></td>
            </tr>
            <tr class="journal-detail-row" id="{{ $detailId }}"><td colspan="8"><div class="journal-detail-box"><div class="table-responsive"><table class="table table-sm table-bordered align-middle journal-detail-table"><thead><tr><th width="45">No</th><th>No. Transaksi</th><th>Akun</th><th>Keterangan</th><th class="text-end">Debit</th><th class="text-end">Kredit</th></tr></thead><tbody>
                @foreach ($detailRows as $detailNo => $detailItem)<tr><td>{{ $detailNo + 1 }}</td><td>{{ $detailItem->nomor_transaksi }}</td><td>{{ $detailItem->kode_perkiraan }} - {{ $detailItem->nama_akun }}</td><td>{{ $detailItem->deskripsi ?: '-' }}</td><td class="text-end">Rp {{ number_format($detailItem->debit, 0, ',', '.') }}</td><td class="text-end">Rp {{ number_format($detailItem->kredit, 0, ',', '.') }}</td></tr>@endforeach
            </tbody></table></div></div></td></tr>
        @empty
            <tr><td colspan="8" class="empty-journal"><strong class="d-block mb-1">Belum ada jurnal penyesuaian</strong><span>Jurnal stok opname dan penyusutan aktiva akan muncul di sini.</span></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@if ($jurnal->hasPages())<div class="mt-3">{{ $jurnal->links() }}</div>@endif
