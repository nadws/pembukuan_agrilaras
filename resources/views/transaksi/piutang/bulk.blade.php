<x-theme.app title="Bulk Pelunasan Piutang" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h5 class="mb-1">Bulk Pelunasan Per Tanggal</h5><small class="text-muted">Pilih tanggal, centang nota yang ingin dibayar, pilih akun, lalu simpan.</small></div>
            <a href="{{ route('transaksi.piutang.index', ['jenis' => $jenis]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali ke Piutang</a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <style>
            .bulk-filter,.bulk-table-wrap{border:1px solid #dce3f2;border-radius:12px}
            .bulk-filter{padding:16px;margin-bottom:14px;background:#f5f7fc}
            .bulk-filter .form-label{margin-bottom:5px;color:#536078;font-size:12px;font-weight:700}
            .bulk-filter .form-control,.bulk-filter .form-select{min-height:40px;border-color:#dce3f2;border-radius:8px}
            .bulk-table-wrap{overflow-x:auto}
            .bulk-table{min-width:800px;margin-bottom:0}
            .bulk-table thead th{padding:12px;background:#29468f;color:#fff;font-size:12px;white-space:nowrap}
            .bulk-total{color:#a12a35;font-size:20px;font-weight:800}
            .bulk-table tbody tr{cursor:pointer;transition:background 0.15s}
            .bulk-table tbody tr:hover{background:#eef2f9}
            .bulk-table tbody tr.row-checked{background:#d4ecff}
            .bulk-alert{background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px 16px;font-size:13px;color:#664d03;margin-bottom:14px}
        </style>
        @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <form method="GET" action="{{ route('transaksi.piutang.bulk') }}" class="bulk-filter" id="filterForm">
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label">Jenis</label>
                    <select name="jenis" class="form-select" onchange="this.form.submit()">
                        <option value="telur" @selected($jenis === 'telur')>Piutang Telur</option>
                        <option value="ayam" @selected($jenis === 'ayam')>Piutang Ayam</option>
                        <option value="umum" @selected($jenis === 'umum')>Piutang Umum</option>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $tanggal ?? date('Y-m-d')) }}" required></div>
                <div class="col-md-3"><label class="form-label">&nbsp;</label><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan Nota</button></div>
            </div>
        </form>
        @if(count($items) > 0)
        @php $adaCicilan = false; foreach($items as $n) { if($n->paid > 0) { $adaCicilan = true; break; } } @endphp
        @if($adaCicilan)
        <div class="bulk-alert">
            <strong>Perhatian:</strong> Jika dalam satu tanggal ada nota yang sudah pernah dibayar (cicilan) dan yang belum, pilih akun sesuai jenis pembayarannya. Misal: nota yang dibayar cash → pilih "kas penjualan telur", nota yang dibayar transfer → pilih akun bank lain. Jika hanya satu jenis pembayaran (misal semua cash), tinggal pilih satu akun dan centang semua nota.
        </div>
        @endif
        <form method="POST" action="{{ route('transaksi.piutang.bulk.store') }}" id="formBulk">
            @csrf
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <div class="bulk-filter mb-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4"><label class="form-label">Dibayar melalui akun</label>
                        <select name="id_akun_pembayaran" class="form-select piutang-account" required>
                            <option value="">Pilih kas atau bank</option>
                            @foreach($akunPembayaran as $akun)
                                <option value="{{ $akun->id_akun_perkiraan }}" @selected(old('id_akun_pembayaran') == $akun->id_akun_perkiraan)>{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Tanggal Bayar</label><input type="date" name="tanggal_bayar" class="form-control" value="{{ old('tanggal_bayar', $tanggal ?? date('Y-m-d')) }}" required></div>
                    <div class="col-md-4 text-md-end"><div class="small text-muted">Total bayar</div><div class="bulk-total" id="bulk-payment-total">Rp 0</div></div>
                </div>
            </div>
            <div class="bulk-table-wrap">
                <table class="table table-hover align-middle bulk-table" id="bulkTable">
                    <thead><tr>
                        <th style="width:40px"><input type="checkbox" id="selectAll" title="Pilih semua"></th>
                        <th>No</th><th>Tanggal</th><th>No Nota</th><th>Customer</th>@if($jenis === 'telur')<th>Tipe</th>@else<th class="text-end">Qty</th>@endif<th class="text-end">Nilai Item</th><th class="text-end">Sisa Nota</th><th style="min-width:170px">Bayar Sekarang</th><th style="min-width:260px">Penyelesaian</th>
                    </tr></thead>
                    <tbody>
                        @foreach($items as $i => $item)
                            <tr data-nota="{{ $item->no_nota }}" data-sisa="{{ $item->sisa }}">
                                <td><input type="checkbox" class="form-check-input nota-check" data-nota="{{ $item->no_nota }}" name="nota[]" value="{{ $item->no_nota }}"></td>
                                <td>{{ $loop->iteration }}</td><td>{{ tanggal($item->tgl) }}</td>
                                <td class="fw-semibold">{{ $item->no_nota }}</td>
                                <td>{{ $item->nm_customer }}</td>
                                @if($jenis === 'telur')
                                    <td>{{ strtoupper($item->tipe ?? '-') }}</td>
                                @else
                                    <td class="text-end">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
                                @endif
                                <td class="text-end">Rp {{ number_format($item->nilai_item, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($item->sisa, 0, ',', '.') }}</td>
                                <td>
                                    @if($item->is_first)
                                        <input type="number" name="jumlah_bayar[]" class="form-control payment-amount text-end" data-nota="{{ $item->no_nota }}" data-outstanding="{{ $item->sisa }}" value="{{ (int) $item->sisa }}" min="1" step="1" placeholder="Nominal diterima" required disabled>
                                    @else
                                        <small class="text-muted d-block text-center">—</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_first)
                                        <select name="jenis_selisih[]" class="form-select difference-type mb-1" data-nota="{{ $item->no_nota }}" disabled>
                                            <option value="tidak">Tanpa selisih / cicilan</option>
                                            <option value="lebih">Lebih bayar — lunaskan</option>
                                            <option value="kurang">Kurang bayar — lunaskan</option>
                                        </select>
                                        <small class="difference-info text-muted d-block" data-nota="{{ $item->no_nota }}">Centang untuk melunasi / mengedit nominal</small>
                                    @else
                                        <small class="text-muted d-block text-center">—</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted small">{{ count($items) }} baris | Total sisa: Rp {{ number_format($totalSisa, 0, ',', '.') }}</span>
                <button type="submit" class="btn btn-success" id="btnBulkBayar" disabled><i class="fas fa-check-circle me-1"></i> Bulk Pelunasan</button>
            </div>
        </form>
        @else
        <div class="alert alert-info">Tidak ada nota belum lunas untuk tanggal {{ tanggal($tanggal ?? date('Y-m-d')) }}.</div>
        @endif
        <script>
            (function() {
                var table = document.getElementById('bulkTable');
                if (!table) return;
                var checks = Array.prototype.slice.call(document.querySelectorAll('.nota-check'));
                var selectAll = document.getElementById('selectAll');
                var button = document.getElementById('btnBulkBayar');
                var totalEl = document.getElementById('bulk-payment-total');
                var accountSelect = document.querySelector('.piutang-account');

                function fmt(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

                function getUniqueCheckedNotas() {
                    var notas = [];
                    checks.forEach(function(cb) {
                        if (cb.checked) {
                            var nota = cb.getAttribute('data-nota');
                            if (notas.indexOf(nota) === -1) notas.push(nota);
                        }
                    });
                    return notas;
                }

                function syncNotaState(noNota, isChecked) {
                    checks.forEach(function(cb) {
                        if (cb.getAttribute('data-nota') === noNota) {
                            cb.checked = isChecked;
                            var row = cb.closest('tr');
                            if (isChecked) row.classList.add('row-checked');
                            else row.classList.remove('row-checked');
                        }
                    });

                    var amountInput = document.querySelector('.payment-amount[data-nota="' + CSS.escape(noNota) + '"]');
                    var typeSelect = document.querySelector('.difference-type[data-nota="' + CSS.escape(noNota) + '"]');
                    var diffInfo = document.querySelector('.difference-info[data-nota="' + CSS.escape(noNota) + '"]');

                    if (amountInput) amountInput.disabled = !isChecked;
                    if (typeSelect) typeSelect.disabled = !isChecked;

                    if (diffInfo && amountInput && typeSelect) {
                        if (!isChecked) {
                            diffInfo.textContent = 'Centang untuk melunasi / mengedit nominal';
                        } else {
                            var paid = parseFloat(amountInput.value) || 0;
                            var outstanding = parseFloat(amountInput.dataset.outstanding) || 0;
                            var type = typeSelect.value;
                            var diff = type === 'lebih' ? Math.max(0, paid - outstanding) : (type === 'kurang' ? Math.max(0, outstanding - paid) : 0);
                            var status = 'Nota tetap terbuka';
                            if (type === 'tidak' && paid === outstanding) status = 'Nota akan lunas';
                            if (type === 'tidak' && paid > outstanding) status = 'Pilih Lebih bayar';
                            if (type === 'lebih') status = paid > outstanding ? 'Nota akan lunas' : 'Nominal harus melebihi sisa';
                            if (type === 'kurang') status = paid > 0 && paid < outstanding ? 'Nota akan lunas' : 'Nominal harus di bawah sisa';
                            diffInfo.textContent = 'Selisih: Rp ' + Math.round(diff).toLocaleString('id-ID') + ' · ' + status;
                        }
                    }
                }

                function getTotal() {
                    var uniqueNotas = getUniqueCheckedNotas();
                    var total = 0;
                    uniqueNotas.forEach(function(noNota) {
                        var amountInput = document.querySelector('.payment-amount[data-nota="' + CSS.escape(noNota) + '"]');
                        if (amountInput) {
                            total += parseFloat(amountInput.value) || 0;
                        }
                    });
                    return total;
                }

                function updateTotal() {
                    var total = getTotal();
                    totalEl.textContent = fmt(total);
                    button.disabled = total <= 0;
                    button.innerHTML = total > 0
                        ? '<i class="fas fa-check-circle me-1"></i> Bayar ' + fmt(total)
                        : '<i class="fas fa-check-circle me-1"></i> Bulk Pelunasan';
                }

                function syncSelectAll() {
                    var allChecked = checks.length > 0 && checks.every(function(cb) { return cb.checked; });
                    var someChecked = checks.some(function(cb) { return cb.checked; });
                    selectAll.checked = allChecked;
                    selectAll.indeterminate = someChecked && !allChecked;
                }

                checks.forEach(function(cb) {
                    var noNota = cb.getAttribute('data-nota');
                    cb.addEventListener('change', function() {
                        syncNotaState(noNota, this.checked);
                        updateTotal();
                        syncSelectAll();
                    });

                    var amountInput = document.querySelector('.payment-amount[data-nota="' + CSS.escape(noNota) + '"]');
                    var typeSelect = document.querySelector('.difference-type[data-nota="' + CSS.escape(noNota) + '"]');

                    if (amountInput && !amountInput.dataset.hasListener) {
                        amountInput.dataset.hasListener = "true";
                        amountInput.addEventListener('input', function() {
                            syncNotaState(noNota, cb.checked);
                            updateTotal();
                        });
                    }
                    if (typeSelect && !typeSelect.dataset.hasListener) {
                        typeSelect.dataset.hasListener = "true";
                        typeSelect.addEventListener('change', function() {
                            syncNotaState(noNota, cb.checked);
                            updateTotal();
                        });
                    }
                });

                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        var isChecked = selectAll.checked;
                        checks.forEach(function(cb) {
                            var noNota = cb.getAttribute('data-nota');
                            syncNotaState(noNota, isChecked);
                        });
                        updateTotal();
                    });
                }

                Array.prototype.forEach.call(table.querySelectorAll('tbody tr'), function(row) {
                    row.addEventListener('click', function(e) {
                        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'OPTION' || e.target.tagName === 'A' || e.target.tagName === 'BUTTON') return;
                        var cb = this.querySelector('.nota-check');
                        if (cb) {
                            var noNota = cb.getAttribute('data-nota');
                            syncNotaState(noNota, !cb.checked);
                            updateTotal();
                            syncSelectAll();
                        }
                    });
                });

                if (window.jQuery && $.fn.select2 && accountSelect) {
                    if (accountSelect.classList.contains('select2-hidden-accessible')) $(accountSelect).select2('destroy');
                    $(accountSelect).select2({ width: '100%', dropdownParent: $('.bulk-filter') });
                }

                updateTotal();
                syncSelectAll();
            })();
        </script>
    </x-slot>
</x-theme.app>
