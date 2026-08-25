<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader">
        <div><h5 class="mb-1">Penyusutan Aktiva</h5><small class="text-muted">Pilih aktiva yang akan disusutkan untuk satu periode.</small></div>
        <div class="btn-group">
            <a class="btn btn-outline-primary" href="{{ route('pembukuan-baru.jurnal-penyesuaian.stok-opname') }}">Stok Opname</a>
            <a class="btn btn-primary" href="{{ route('pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva', ['tanggal' => $tanggal]) }}">Penyusutan Aktiva</a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <div class="alert alert-info py-2">Penyusutan per bulan = <strong>nilai perolehan ÷ umur aktiva</strong>. Pada periode terakhir, nominal otomatis dibatasi sebesar nilai buku yang tersisa.</div>

        <form method="GET" action="{{ route('pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva') }}" class="periode-filter mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-4 col-lg-3"><label class="form-label fw-semibold">Tanggal Penyesuaian</label><input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" required></div>
                <div class="col-auto"><button type="submit" class="btn btn-outline-primary"><i class="fas fa-search me-1"></i> Tampilkan Periode</button></div>
            </div>
        </form>

        @php
            $tersedia = $aktiva->filter(fn ($a) => !$a->id_penyusutan_periode && $a->akun_biaya && $a->nominal_periode > 0);
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="summary-box"><span>Periode</span><strong>{{ date('m-Y', strtotime($tanggal)) }}</strong></div></div>
            <div class="col-md-4"><div class="summary-box"><span>Aktiva Tersedia</span><strong>{{ $tersedia->count() }} aktiva</strong></div></div>
            <div class="col-md-4"><div class="summary-box"><span>Total Dipilih</span><strong id="totalDipilih">Rp {{ number_format($tersedia->sum('nominal_periode'), 0, ',', '.') }}</strong></div></div>
        </div>

        <form method="POST" action="{{ route('pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva.store') }}" id="formPenyusutan">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <div class="table-responsive penyusutan-table-wrap">
                <table class="table table-hover align-middle mb-0 penyusutan-table">
                    <thead><tr><th class="text-center"><input type="checkbox" id="pilihSemua" {{ $tersedia->isNotEmpty() ? 'checked' : '' }}></th><th>Aktiva</th><th>Akun Aset (Kredit)</th><th>Akun Biaya (Debit)</th><th class="text-end">Nilai Perolehan</th><th class="text-end">Nilai Buku</th><th>Umur Aktiva</th><th class="text-end">Penyusutan Periode Ini</th><th class="text-center">Sisa Periode</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($aktiva as $a)
                            @php
                                $dapatDipilih = !$a->id_penyusutan_periode && $a->akun_biaya && $a->nominal_periode > 0;
                                $umurTahun = !empty($a->umur_aktiva_bulan) ? intdiv((int) $a->umur_aktiva_bulan, 12) : 0;
                                $umurBulan = !empty($a->umur_aktiva_bulan) ? (int) $a->umur_aktiva_bulan % 12 : 0;
                            @endphp
                            <tr class="{{ $a->id_penyusutan_periode ? 'table-light text-muted' : '' }}">
                                <td class="text-center"><input type="checkbox" class="pilih-aktiva" name="id_aktiva[]" value="{{ $a->id }}" data-nominal="{{ $a->nominal_periode }}" {{ $dapatDipilih ? 'checked' : 'disabled' }}></td>
                                <td><div class="fw-semibold text-dark">{{ $a->nm_aktiva }}</div><small class="text-muted">Perolehan {{ date('d-m-Y', strtotime($a->tgl)) }}</small></td>
                                <td><span class="account-code">{{ $a->kode_perkiraan ?: '-' }}</span><div>{{ $a->nama_akun_aset ?: 'Akun belum dipilih' }}</div></td>
                                <td>@if ($a->akun_biaya) {{ $a->akun_biaya }} @else <span class="text-danger">Akun biaya belum tersedia</span> @endif</td>
                                <td class="text-end text-nowrap">Rp {{ number_format($a->h_perolehan, 0, ',', '.') }}</td>
                                <td class="text-end text-nowrap fw-semibold">Rp {{ number_format($a->nilai_buku, 0, ',', '.') }}</td>
                                <td class="text-nowrap">{{ $umurTahun > 0 ? $umurTahun . ' tahun' : '' }}{{ $umurTahun > 0 && $umurBulan > 0 ? ' ' : '' }}{{ $umurBulan > 0 ? $umurBulan . ' bulan' : ($umurTahun < 1 ? '-' : '') }}</td>
                                <td class="text-end text-nowrap fw-semibold text-primary">Rp {{ number_format($a->nominal_periode, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $a->sisa_periode }} bulan</td>
                                <td>@if ($a->id_penyusutan_periode)<span class="badge bg-success">Sudah diproses</span>@elseif (!$a->akun_biaya)<span class="badge bg-danger">Akun belum lengkap</span>@else<span class="badge bg-primary">Siap</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-5">Tidak ada aktiva dengan nilai buku yang masih tersisa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($tersedia->isNotEmpty())
                <div class="d-flex justify-content-end mt-3"><button class="btn btn-primary px-4" type="submit" id="simpanPenyusutan"><i class="fas fa-save me-1"></i> Simpan &amp; Buat Jurnal</button></div>
            @endif
        </form>
    </x-slot>

    @section('styles')
        <style>
            .periode-filter,.summary-box{background:#f7f9fc;border:1px solid #dfe7f3;border-radius:10px;padding:14px 16px}.summary-box{display:flex;flex-direction:column;gap:5px;height:100%}.summary-box span{font-size:13px;color:#7183a2}.summary-box strong{font-size:18px;color:#17366f}.penyusutan-table-wrap{border:1px solid #e2e8f0;border-radius:10px}.penyusutan-table{min-width:1550px}.penyusutan-table thead th{background:#304f9e;color:#fff;white-space:nowrap;border:0;padding:12px 10px}.penyusutan-table td{padding:12px 10px}.account-code{font-weight:700;color:#304f9e}
        </style>
    @endsection

    @section('scripts')
        <script>
            $(function () {
                function hitungTotal() {
                    var total = 0;
                    $('.pilih-aktiva:checked').each(function () { total += Number($(this).data('nominal')) || 0; });
                    $('#totalDipilih').text(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(total));
                    $('#simpanPenyusutan').prop('disabled', total <= 0);
                }
                $('#pilihSemua').on('change', function () { $('.pilih-aktiva:not(:disabled)').prop('checked', this.checked); hitungTotal(); });
                $(document).on('change', '.pilih-aktiva', function () { var semua = $('.pilih-aktiva:not(:disabled)').length; $('#pilihSemua').prop('checked', semua > 0 && $('.pilih-aktiva:not(:disabled):checked').length === semua); hitungTotal(); });
                hitungTotal();
            });
        </script>
    @endsection
</x-theme.app>
