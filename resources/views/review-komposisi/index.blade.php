<x-theme.app title="Review Komposisi Pendapatan" sizeCard="12" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><h5 class="mb-0">Komposisi Pendapatan Usaha</h5><small class="text-muted">Halaman review · seluruh usaha · HPP sesuai akun terpilih</small></div>
            <a href="{{ route('jurnal-perkiraan.laba-rugi', ['bulan_dari' => $start->month, 'tahun_dari' => $start->year, 'bulan_sampai' => $start->month, 'tahun_sampai' => $start->year]) }}" class="btn btn-outline-primary btn-sm">Lihat Laba-Rugi</a>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        @php
            $rp = fn ($value) => 'Rp '.number_format($value, 2, ',', '.');
            $nonFeedAccounts = $accounts->where('kode_perkiraan', '!=', '5101-04');
            $chosenAccounts = $nonFeedAccounts->filter(fn ($account) => in_array((int) $account->id_akun_perkiraan, $selected, true));
            $remainingAccounts = $nonFeedAccounts->reject(fn ($account) => in_array((int) $account->id_akun_perkiraan, $selected, true));
            $detailGroups = [
                'chosen' => ['title' => 'HPP SDM & Energy', 'accounts' => $chosenAccounts, 'total' => $utilities],
            ];
        @endphp
        <style>
            .composition-panel {border:1px solid #e1e7f2;border-radius:14px;padding:24px;background:#fff;height:100%}
            .composition-total {font-size:26px;font-weight:700;color:#18366f}
            .composition-dot {display:inline-block;width:12px;height:12px;border-radius:50%;margin-right:8px}
            .composition-account-list {max-height:360px;overflow:auto}
            .composition-row {padding:15px 0;border-bottom:1px solid #edf1f7}
        </style>
        <form method="GET" class="d-flex align-items-end flex-wrap gap-2 mb-4">
            <div><label for="periode" class="form-label">Periode bulanan</label><input type="month" class="form-control" name="periode" id="periode" value="{{ $start->format('Y-m') }}" required></div>
            <button class="btn btn-primary">Tampilkan</button>
        </form>
        <div class="alert alert-light border">{{ $start->translatedFormat('F Y') }} · Pendapatan mencakup penjualan telur, ayam, umum, dan pendapatan di luar usaha. HPP SDM & Energy mengikuti akun produksi terpilih. Keuntungan memakai laba bersih setelah seluruh biaya dari laporan laba-rugi; pilihan gir tidak mengubah keuntungan. Internet tidak masuk default HPP produksi. Periode berjalan belum final.</div>
        <div class="row g-3">
            <div class="col-lg-7"><div class="composition-panel">
                <h6>Komposisi Pendapatan</h6>
                @if($chartReady)
                    <div id="compositionChart"></div>
                @else
                    <div class="alert alert-warning my-4">Donat tidak ditampilkan: pendapatan kosong atau ada komponen negatif. Nilai asli tetap tampil di ringkasan; rugi tidak diubah menjadi laba.</div>
                @endif
                <div class="text-center text-muted small">Persentase donat dihitung dari total tiga komponen yang ditampilkan, bukan total pendapatan. Biaya di luar HPP produksi tidak menjadi irisan.</div>
            </div></div>
            <div class="col-lg-5"><div class="composition-panel">
                <small class="text-muted">TOTAL PENDAPATAN</small><div class="composition-total">{{ $rp($income) }}</div>
                <div class="small text-muted mb-3">Pendapatan usaha {{ $rp($revenue) }}<br>Pendapatan di luar usaha {{ $rp($otherIncome) }}</div>
                @foreach([['HPP Pakan', $feed, '#4472c4'], ['HPP SDM & Energy', $utilities, '#ed7d31'], [$profit < 0 ? 'Kerugian' : 'Keuntungan', $profit, '#a5a5a5']] as [$label, $amount, $color])
                    <div class="composition-row d-flex justify-content-between gap-2">
                        <span><span class="composition-dot" style="background:{{ $color }}"></span>
                            @if($label === 'HPP SDM & Energy')
                                <button type="button" id="compositionAllDetails" class="btn btn-link p-0 text-start" data-bs-toggle="modal" data-bs-target="#compositionDetail-chosen">{{ $label }} <i class="fas fa-list-ul ms-1" aria-hidden="true"></i></button>
                                <button class="btn btn-outline-primary btn-sm ms-1" type="button" data-bs-toggle="modal" data-bs-target="#compositionSettings" title="Atur akun HPP SDM & Energy"><i class="fas fa-cog"></i></button>
                            @elseif($label === 'Keuntungan' || $label === 'Kerugian')
                                <button type="button" id="compositionProfitDetails" class="btn btn-link p-0 text-start" data-bs-toggle="modal" data-bs-target="#compositionProfit">{{ $label }} <i class="fas fa-list-ul ms-1" aria-hidden="true"></i></button>
                            @else
                                {{ $label }}
                            @endif
                        </span>
                        <div class="text-end">
                            @if($label === 'HPP SDM & Energy')
                                <button type="button" class="btn btn-link p-0 fw-bold" data-bs-toggle="modal" data-bs-target="#compositionDetail-chosen">{{ $rp($amount) }}</button>
                            @elseif($label === 'Keuntungan' || $label === 'Kerugian')
                                <button type="button" class="btn btn-link p-0 fw-bold" data-bs-toggle="modal" data-bs-target="#compositionProfit">{{ $rp($amount) }}</button>
                            @else
                                <strong>{{ $rp($amount) }}</strong>
                            @endif
                            <small class="d-block text-muted">{{ $chartReady ? number_format($amount / $chartTotal * 100, 2, ',', '.').'%' : '—' }}</small>
                        </div>
                    </div>
                @endforeach
                <small class="d-block text-muted mt-3">{{ $chosenAccounts->count() }} akun HPP SDM & Energy dipilih. Klik nama atau nominal untuk detail akun; klik gir untuk menambah atau mengeluarkan akun.</small>
                <div class="border-top mt-3 pt-3 small text-muted"><span class="badge {{ abs($reconciliation) < 0.01 ? 'bg-success' : 'bg-danger' }}">{{ abs($reconciliation) < 0.01 ? 'Cocok dengan laba-rugi' : 'Periksa selisih rincian' }}</span><br>Biaya di luar HPP terpilih: {{ $rp($outsideCosts) }}<br>Pendapatan − seluruh biaya = laba bersih. Klik keuntungan untuk pembuktian.</div>
            </div></div>
        </div>
        <details class="mt-4"><summary class="mb-2">Lihat status perhitungan akun</summary><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Kode</th><th>Akun</th><th>Kelompok</th><th class="text-end">Nilai</th></tr></thead><tbody>
            @foreach($accounts as $account)
                @if((float) $account->nilai != 0)
                    <tr><td><a href="{{ route('review-komposisi.transactions', ['akun' => $account->id_akun_perkiraan, 'periode' => $start->format('Y-m')]) }}">{{ $account->kode_perkiraan }}</a></td><td>{{ $account->nama }}</td><td>{{ $account->kode_perkiraan === '5101-04' ? 'HPP Pakan' : (in_array((int) $account->id_akun_perkiraan, $selected, true) ? 'HPP SDM & Energy' : 'Di luar HPP; tetap mengurangi laba') }}</td><td class="text-end">{{ $rp($account->nilai) }}</td></tr>
                @endif
            @endforeach
        </tbody></table></div></details>
        @foreach($detailGroups as $key => $group)
            <div class="modal fade" id="compositionDetail-{{ $key }}" tabindex="-1" aria-labelledby="compositionDetailTitle-{{ $key }}" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
                <div class="modal-header"><div><h5 class="modal-title" id="compositionDetailTitle-{{ $key }}">{{ $group['title'] }}</h5><small class="text-muted">{{ $start->translatedFormat('F Y') }} · debit dikurangi kredit · impor aktif</small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                <div class="modal-body"><div class="table-responsive"><table class="table table-striped align-middle mb-0"><thead><tr><th>Kode</th><th>Akun</th><th>Kelompok</th><th class="text-end">Nilai</th></tr></thead><tbody>
                    @forelse($group['accounts'] as $account)
                        <tr><td><a href="{{ route('review-komposisi.transactions', ['akun' => $account->id_akun_perkiraan, 'periode' => $start->format('Y-m')]) }}">{{ $account->kode_perkiraan }}</a></td><td>{{ $account->nama }}</td><td><span class="badge {{ in_array((int) $account->id_akun_perkiraan, $selected, true) ? 'bg-primary' : 'bg-secondary' }}">{{ in_array((int) $account->id_akun_perkiraan, $selected, true) ? 'Dihitung' : 'Tidak dihitung' }}</span></td><td class="text-end text-nowrap">{{ $rp($account->nilai) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada akun dalam kelompok ini.</td></tr>
                    @endforelse
                </tbody></table></div><small class="d-block text-muted mt-3">Akun bernilai nol tetap ditampilkan agar pilihan dapat diperiksa. Hanya akun terpilih yang masuk HPP SDM & Energy.</small></div>
                <div class="modal-footer justify-content-between"><strong>Total {{ $rp($group['total']) }}</strong><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
            </div></div></div>
        @endforeach
        <div class="modal fade" id="compositionProfit" tabindex="-1" aria-labelledby="compositionProfitTitle" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><div><h5 class="modal-title" id="compositionProfitTitle">Pembuktian Laba Bersih</h5><small class="text-muted">{{ $start->translatedFormat('F Y') }} · seluruh usaha · hanya impor aktif</small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div class="alert alert-light border">Pendapatan {{ $rp($income) }} − seluruh biaya {{ $rp($totalCosts) }} = {{ $rp($income - $totalCosts) }}<br>Laba bersih laporan: <strong>{{ $rp($reportProfit) }}</strong> · Selisih pembuktian: <strong>{{ $rp($reconciliation) }}</strong></div>
                <div class="d-flex flex-wrap gap-3 mb-3 align-items-center"><input class="form-control" style="max-width:360px" type="search" id="profitAccountSearch" placeholder="Cari kode atau nama akun" aria-label="Cari akun pembuktian"><label><input type="checkbox" id="profitShowZero"> Tampilkan akun bernilai nol</label></div>
                <small class="d-block text-muted mb-3">Filter hanya mengatur tampilan rincian. Semua akun tetap dihitung. Klik kode akun untuk transaksi dan file impor sumber.</small>
                <div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Kode</th><th>Akun</th><th>Peran</th><th class="text-end">Nilai</th></tr></thead><tbody>
                    @foreach($incomeAccounts->concat($accounts) as $account)
                        <tr class="profit-account-row" data-zero="{{ (float) $account->nilai == 0 ? '1' : '0' }}" @if((float) $account->nilai == 0) hidden @endif><td><a href="{{ route('review-komposisi.transactions', ['akun' => $account->id_akun_perkiraan, 'periode' => $start->format('Y-m')]) }}">{{ $account->kode_perkiraan }}</a></td><td>{{ $account->nama }}</td><td>{{ in_array($account->tipe_akun, ['REVE', 'OINC'], true) ? 'Pendapatan (+)' : 'Biaya (−)' }}</td><td class="text-end text-nowrap">{{ $rp($account->nilai) }}</td></tr>
                    @endforeach
                </tbody><tfoot><tr><th colspan="3">Laba bersih laporan</th><th class="text-end text-nowrap">{{ $rp($reportProfit) }}</th></tr></tfoot></table></div>
            </div><div class="modal-footer"><a class="btn btn-outline-primary" href="{{ route('jurnal-perkiraan.laba-rugi', ['bulan_dari' => $start->month, 'tahun_dari' => $start->year, 'bulan_sampai' => $start->month, 'tahun_sampai' => $start->year]) }}">Bandingkan Laporan Laba-Rugi</a><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
        </div></div></div>
        <div class="modal fade" id="compositionSettings" tabindex="-1" aria-labelledby="compositionSettingsTitle" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content">
            <form method="POST" action="{{ route('review-komposisi.settings') }}">@csrf<input type="hidden" name="periode" value="{{ $start->format('Y-m') }}">
                <div class="modal-header"><h5 class="modal-title" id="compositionSettingsTitle">Atur Akun HPP SDM & Energy</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                <div class="modal-body"><p class="text-muted small">Centang akun untuk memasukkannya ke HPP SDM & Energy. Hapus centang untuk mengeluarkan akun dari HPP produksi. Akun tersebut tetap mengurangi laba bersih laporan. HPP Pakan dihitung terpisah. Pilihan tersimpan selama session pengguna.</p>
                    <div class="border rounded bg-light p-3 mb-3"><strong>Akun terpilih (<span id="compositionSelectedCount">{{ $chosenAccounts->count() }}</span>)</strong><div id="compositionSelectedSummary" class="d-flex flex-wrap gap-2 mt-2" aria-live="polite"></div><small class="d-block text-muted mt-2">Ringkasan mengikuti centang saat ini. Klik Terapkan untuk menyimpan.</small></div>
                    <input type="search" class="form-control mb-3" id="compositionSearch" placeholder="Cari kode atau nama akun" aria-label="Cari akun">
                    <div class="composition-account-list">
                        @foreach($chosenAccounts->concat($remainingAccounts) as $account)
                            <label class="d-flex justify-content-between align-items-center gap-2 border-bottom py-2 composition-option"><span><input type="checkbox" name="akun[]" value="{{ $account->id_akun_perkiraan }}" data-account-label="{{ $account->kode_perkiraan }} · {{ $account->nama }}" @checked(in_array((int) $account->id_akun_perkiraan, $selected, true))> {{ $account->kode_perkiraan }} · {{ $account->nama }}</span><small class="text-nowrap">{{ $rp($account->nilai) }}</small></label>
                        @endforeach
                    </div>
                </div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Terapkan</button></div>
            </form>
        </div></div></div>
    </x-slot>
    @section('scripts')
        <script src="{{ asset('theme/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const filterProfitAccounts = function () {
                    const query = document.getElementById('profitAccountSearch').value.toLowerCase().trim();
                    const showZero = document.getElementById('profitShowZero').checked;
                    document.querySelectorAll('.profit-account-row').forEach(function (row) {
                        row.hidden = (!showZero && row.dataset.zero === '1') || !row.textContent.toLowerCase().includes(query);
                    });
                };
                document.getElementById('profitAccountSearch').addEventListener('input', filterProfitAccounts);
                document.getElementById('profitShowZero').addEventListener('change', filterProfitAccounts);
                const accountCheckboxes = document.querySelectorAll('.composition-option input[type="checkbox"]');
                const refreshSelected = function () {
                    const summary = document.getElementById('compositionSelectedSummary');
                    summary.replaceChildren();
                    const checked = Array.from(accountCheckboxes).filter(input => input.checked);
                    document.getElementById('compositionSelectedCount').textContent = checked.length;
                    checked.forEach(function (input) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-primary text-wrap text-start';
                        badge.textContent = input.dataset.accountLabel;
                        summary.appendChild(badge);
                    });
                    if (!checked.length) summary.textContent = 'Belum ada akun dipilih.';
                };
                accountCheckboxes.forEach(input => input.addEventListener('change', refreshSelected));
                refreshSelected();
                document.getElementById('compositionSearch').addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    document.querySelectorAll('.composition-option').forEach(function (row) {
                        const visible = row.textContent.toLowerCase().includes(query);
                        row.classList.toggle('d-none', !visible);
                        row.classList.toggle('d-flex', visible);
                    });
                });
                @if($chartReady)
                    const formatRp = value => 'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 2}).format(value);
                    new ApexCharts(document.getElementById('compositionChart'), {
                        chart: {type: 'donut', height: 380, events: {dataPointSelection: function (event, chart, config) {
                            if (config.dataPointIndex === 1) document.getElementById('compositionAllDetails').click();
                            if (config.dataPointIndex === 2) document.getElementById('compositionProfitDetails').click();
                        }}},
                        series: @json([$feed, $utilities, $profit]),
                        labels: ['HPP Pakan', 'HPP SDM & Energy', 'Keuntungan'],
                        colors: ['#4472c4', '#ed7d31', '#a5a5a5'],
                        dataLabels: {formatter: value => value.toFixed(2) + '%'},
                        plotOptions: {pie: {donut: {size: '68%', labels: {show: true, value: {formatter: value => formatRp(Number(value))}, total: {show: true, label: 'Total 3 komponen', formatter: () => formatRp(@json($chartTotal))}}}}},
                        tooltip: {y: {formatter: formatRp}},
                        legend: {position: 'bottom'},
                        responsive: [{breakpoint: 576, options: {chart: {height: 310}}}]
                    }).render();
                @endif
            });
        </script>
    @endsection
</x-theme.app>
