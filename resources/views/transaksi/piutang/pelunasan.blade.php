<x-theme.app title="Pelunasan Piutang" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h5 class="mb-1">Pelunasan Piutang {{ ucfirst($jenis) }}</h5><small class="text-muted">Periksa nota yang dipilih, lalu simpan pembayarannya.</small></div>
            <a href="{{ route('transaksi.piutang.index', ['jenis' => $jenis]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali ke Piutang</a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>.settle-box,.settle-table-wrap{border:1px solid #dce3f2;border-radius:12px}.settle-box{padding:16px;background:#f5f7fc}.settle-box .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}.settle-box .form-control,.settle-box .form-select{min-height:40px;border-color:#dce3f2;border-radius:8px}.settle-box .select2-container{width:100%!important}.settle-box .select2-selection--single{height:40px!important;border-color:#dce3f2!important;border-radius:8px!important}.settle-box .select2-selection__rendered{line-height:40px!important}.settle-box .select2-selection__arrow{height:40px!important}.settle-table-wrap{overflow-x:auto}.settle-table{min-width:720px;margin-bottom:0}.settle-table thead th{padding:12px;background:#29468f;color:#fff;font-size:12px;white-space:nowrap}.settle-total{color:#a12a35;font-size:20px;font-weight:800}</style>
        <form method="POST" action="{{ route('transaksi.piutang.pelunasan.store') }}">
            @csrf
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <div class="settle-box mb-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3"><label class="form-label">Tanggal pembayaran</label><input type="date" name="tanggal_bayar" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                    <div class="col-md-6"><label class="form-label">Dibayar melalui akun</label><select name="id_akun_pembayaran" class="form-select select2 piutang-account" required><option value="">Pilih kas atau bank</option>@foreach($akunPembayaran as $akun)<option value="{{ $akun->id_akun_perkiraan }}">{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>@endforeach</select></div>
                    <div class="col-md-3 text-md-end"><div class="small text-muted">Total masuk kas/bank</div><div class="settle-total" id="payment-total">Rp 0</div></div>
                </div>
            </div>
            <div class="settle-table-wrap">
                <table class="table table-hover align-middle settle-table">
                    <thead><tr><th>No</th><th>Tanggal</th><th>No Nota</th><th>Customer</th>@if($jenis === 'telur')<th>Tipe</th>@else<th class="text-end">Qty</th>@endif<th class="text-end">Nilai Nota</th><th class="text-end">Sudah Dibayar</th><th style="min-width:180px">Bayar Sekarang</th><th style="min-width:280px">Penyelesaian</th></tr></thead>
                    <tbody>
                        @foreach($noteSummaries as $noNota => $summary)
                            @php $item = $summary->item; $items = $summary->items; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td><td>{{ tanggal($item->tgl) }}</td>
                                <td class="fw-semibold">{{ $noNota }}<input type="hidden" name="nota[]" value="{{ $noNota }}"></td>
                                <td>{{ $item->nm_customer ?? '-' }}</td>
                                @if($jenis === 'telur')<td>{{ strtoupper($item->tipe) }}</td>@else<td class="text-end">{{ number_format($items->sum('qty'), 0, ',', '.') }}</td>@endif
                                <td class="text-end">Rp {{ number_format($summary->invoice_total, 0, ',', '.') }}</td>
                                <td class="text-end"><span class="d-block">Rp {{ number_format($summary->paid, 0, ',', '.') }}</span><small class="text-muted">Sisa Rp {{ number_format($summary->outstanding, 0, ',', '.') }}</small></td>
                                <td><input type="number" name="jumlah_bayar[]" class="form-control payment-amount text-end" data-outstanding="{{ $summary->outstanding }}" value="{{ old('jumlah_bayar.'.$loop->index) }}" min="1" step="1" placeholder="Nominal diterima" required></td>
                                <td>
                                    <select name="jenis_selisih[]" class="form-select difference-type mb-1">
                                        <option value="tidak" @selected(old('jenis_selisih.'.$loop->index, 'tidak') === 'tidak')>Tanpa selisih / cicilan</option>
                                        <option value="lebih" @selected(old('jenis_selisih.'.$loop->index) === 'lebih')>Lebih bayar — lunaskan</option>
                                        <option value="kurang" @selected(old('jenis_selisih.'.$loop->index) === 'kurang')>Kurang bayar — lunaskan</option>
                                    </select>
                                    <small class="difference-info text-muted">Selisih: Rp 0 · Nota tetap terbuka jika masih bersisa</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3"><button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-1"></i> Simpan Pelunasan</button></div>
        </form>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && $.fn.select2) {
                    const account = $('.piutang-account');
                    if (account.hasClass('select2-hidden-accessible')) account.select2('destroy');
                    account.select2({ width: '100%', dropdownParent: $('.settle-box') });
                }
                const amounts = document.querySelectorAll('.payment-amount');
                const differenceTypes = document.querySelectorAll('.difference-type');
                const differenceInfos = document.querySelectorAll('.difference-info');
                const total = document.getElementById('payment-total');
                const refreshTotal = () => {
                    let value = 0;
                    amounts.forEach((input, index) => {
                        value += Number(input.value) || 0;
                        const paid = Number(input.value) || 0;
                        const outstanding = Number(input.dataset.outstanding) || 0;
                        const type = differenceTypes[index].value;
                        const difference = type === 'lebih' ? Math.max(0, paid - outstanding) : (type === 'kurang' ? Math.max(0, outstanding - paid) : 0);
                        let status = 'Nota tetap terbuka';
                        if (type === 'tidak' && paid === outstanding) status = 'Nota akan lunas';
                        if (type === 'tidak' && paid > outstanding) status = 'Pilih Lebih bayar';
                        if (type === 'lebih') status = paid > outstanding ? 'Nota akan lunas' : 'Nominal harus melebihi sisa';
                        if (type === 'kurang') status = paid > 0 && paid < outstanding ? 'Nota akan lunas' : 'Nominal harus di bawah sisa';
                        differenceInfos[index].textContent = 'Selisih: Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(difference) + ' · ' + status;
                    });
                    total.textContent = 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value);
                };
                amounts.forEach(input => input.addEventListener('input', refreshTotal));
                differenceTypes.forEach(input => input.addEventListener('change', refreshTotal));
                refreshTotal();
            });
        </script>
    </x-slot>
</x-theme.app>
