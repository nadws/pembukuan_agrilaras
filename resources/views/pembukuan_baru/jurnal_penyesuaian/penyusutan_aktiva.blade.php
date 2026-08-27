<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h5 class="mb-1" style="color: #0f172a !important;">Penyusutan Aktiva</h5>
                <small class="text-muted">Pilih aktiva yang akan disusutkan untuk satu periode atau catat aktiva yang rusak (write-off).</small>
            </div>
            <div>
                <a href="{{ route('aktiva') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-building me-1"></i> Daftar Aktiva
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .penyusutan-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; }
            .penyusutan-card .label { font-size: 12px; color: #64748b !important; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
            .penyusutan-card .value { font-size: 18px; color: #0f172a !important; font-weight: 700; }
            
            .table-penyusutan { font-size: 13px; width: 100%; border-collapse: separate; border-spacing: 0; background: #ffffff !important; }
            .table-penyusutan thead th {
                background: #304f9e !important;
                color: #ffffff !important;
                font-weight: 600;
                font-size: 12.5px;
                padding: 10px 12px;
                border: none;
                white-space: nowrap;
            }
            .table-penyusutan tbody td {
                padding: 10px 12px;
                vertical-align: middle;
                border-bottom: 1px solid #eef2f6;
                color: #0f172a !important;
                background-color: #ffffff !important;
            }
            .table-penyusutan tbody tr:hover td {
                background-color: #f1f5f9 !important;
            }
            .table-penyusutan tbody tr.row-selesai td {
                background-color: #fafafa !important;
            }
            .nama-aktiva-text {
                color: #0f172a !important;
                font-weight: 700 !important;
                font-size: 13.5px !important;
                display: block;
            }
            .btn-write-off {
                font-size: 11px;
                padding: 3px 8px;
                border-radius: 4px;
            }
            .nav-pills .nav-link { color: #435EBE !important; background: #edf2fc; border: 1px solid #dce4f2; font-weight: 600; margin-right: 8px; }
            .nav-pills .nav-link.active { color: #ffffff !important; background: #435EBE !important; border-color: #435EBE; }
        </style>

        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('aktiva') }}">
                    <i class="fas fa-building me-1"></i> Daftar Aktiva
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva', ['tanggal' => $tanggal]) }}">
                    <i class="fas fa-chart-line me-1"></i> Penyusutan Aktiva
                </a>
            </li>
        </ul>

        @if (session('sukses'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter & Summary Bar --}}
        <div class="row g-3 align-items-stretch mb-3">
            <div class="col-lg-4 col-md-5">
                <form method="GET" action="{{ route('pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva') }}" class="penyusutan-card h-100 d-flex flex-column justify-content-between">
                    <div class="label mb-1">Pilih Periode Penyesuaian</div>
                    <div class="input-group input-group-sm mt-1">
                        <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" required>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Tampilkan</button>
                    </div>
                </form>
            </div>
            @php
                $tersedia = $aktiva->filter(fn ($a) => !$a->id_penyusutan_periode && $a->akun_biaya && $a->nominal_periode > 0);
            @endphp
            <div class="col-lg-4 col-md-3 col-6">
                <div class="penyusutan-card h-100">
                    <div class="label">Aktiva Siap Disusutkan</div>
                    <div class="value mt-1 text-primary">{{ $tersedia->count() }} <span class="fs-6 fw-normal text-muted">dari {{ $aktiva->count() }} aktiva</span></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-6">
                <div class="penyusutan-card h-100">
                    <div class="label">Total Penyusutan Dipilih</div>
                    <div class="value mt-1 text-success" id="totalDipilih">Rp {{ number_format($tersedia->sum('nominal_periode'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva.store') }}" id="formPenyusutan">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <div class="table-responsive border rounded bg-white">
                <table class="table-penyusutan">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input type="checkbox" id="pilihSemua" {{ $tersedia->isNotEmpty() ? 'checked' : '' }}>
                            </th>
                            <th style="min-width: 170px;">Nama Aktiva</th>
                            <th style="min-width: 160px;">Akun Aset (Kredit)</th>
                            <th style="min-width: 160px;">Akun Biaya (Debit)</th>
                            <th class="text-end" style="min-width: 140px;">Nilai Buku</th>
                            <th class="text-center" style="min-width: 100px;">Umur &amp; Sisa</th>
                            <th class="text-end" style="min-width: 130px;">Penyusutan</th>
                            <th class="text-center" style="min-width: 90px;">Status</th>
                            <th class="text-center" style="min-width: 70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aktiva as $a)
                            @php
                                $dapatDipilih = !$a->id_penyusutan_periode && $a->akun_biaya && $a->nominal_periode > 0;
                                $umurTahun = !empty($a->umur_aktiva_bulan) ? intdiv((int) $a->umur_aktiva_bulan, 12) : 0;
                                $umurBulan = !empty($a->umur_aktiva_bulan) ? (int) $a->umur_aktiva_bulan % 12 : 0;
                            @endphp
                            <tr class="{{ $a->id_penyusutan_periode ? 'row-selesai' : '' }}">
                                <td class="text-center">
                                    <input type="checkbox" class="pilih-aktiva" name="id_aktiva[]" value="{{ $a->id }}" data-nominal="{{ $a->nominal_periode }}" {{ $dapatDipilih ? 'checked' : 'disabled' }}>
                                </td>
                                <td>
                                    <span class="nama-aktiva-text" style="color: #0f172a !important; font-weight: 700 !important;">{{ $a->nm_aktiva }}</span>
                                    <small class="text-muted" style="color: #64748b !important; font-size: 11.5px;">Perolehan: {{ date('d/m/Y', strtotime($a->tgl)) }}</small>
                                </td>
                                <td>
                                    <span style="color: #2563eb !important; font-weight: 600; font-size: 12px; display: block;">{{ $a->kode_perkiraan ?: '-' }}</span>
                                    <small class="text-truncate d-block" style="color: #475569 !important; font-size: 11.5px; max-width: 170px;">{{ $a->nama_akun_aset ?: 'Belum dipilih' }}</small>
                                </td>
                                <td>
                                    @if ($a->akun_biaya)
                                        <span class="text-truncate d-block" style="color: #334155 !important; font-size: 12px; max-width: 180px;">{{ $a->akun_biaya }}</span>
                                    @else
                                        <span class="small" style="color: #dc2626 !important; font-weight: 600;"><i class="fas fa-exclamation-triangle me-1"></i>Belum lengkap</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div style="color: #0f172a !important; font-weight: 700; font-size: 13px;">Rp {{ number_format($a->nilai_buku, 0, ',', '.') }}</div>
                                    <small style="color: #64748b !important; font-size: 11px;">Awal: Rp {{ number_format($a->h_perolehan, 0, ',', '.') }}</small>
                                </td>
                                <td class="text-center">
                                    <div style="color: #0f172a !important; font-weight: 600; font-size: 12.5px;">{{ $umurTahun > 0 ? $umurTahun . ' th ' : '' }}{{ $umurBulan > 0 ? $umurBulan . ' bln' : ($umurTahun < 1 ? '-' : '') }}</div>
                                    <small style="color: #64748b !important; font-size: 11px;">Sisa: {{ $a->sisa_periode }} bln</small>
                                </td>
                                <td class="text-end">
                                    <div style="color: #2563eb !important; font-weight: 700; font-size: 13.5px;">Rp {{ number_format($a->nominal_periode, 0, ',', '.') }}</div>
                                    <small style="color: #64748b !important; font-size: 11px;">/ bulan</small>
                                </td>
                                <td class="text-center">
                                    @if ($a->id_penyusutan_periode)
                                        <span class="badge bg-success" style="color: #ffffff !important; font-size: 11px;">Sudah Disusutkan</span>
                                    @elseif (!$a->akun_biaya)
                                        <span class="badge bg-warning text-dark" style="color: #1e293b !important; font-size: 11px;">Akun Biaya Kosong</span>
                                    @else
                                        <span class="badge bg-primary" style="color: #ffffff !important; font-size: 11px;">Siap</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($a->nilai_buku > 0)
                                        <button type="button" class="btn btn-outline-danger btn-write-off"
                                            data-id="{{ $a->id }}"
                                            data-nama="{{ $a->nm_aktiva }}"
                                            data-perolehan="{{ $a->h_perolehan }}"
                                            data-akumulasi="{{ $a->akumulasi_penyusutan }}"
                                            data-nilai-buku="{{ $a->nilai_buku }}"
                                            title="Hapus / Write-Off jika barang rusak">
                                            <i class="fas fa-trash-alt me-1"></i> Rusak
                                        </button>
                                    @else
                                        <span class="text-muted" style="color: #94a3b8 !important;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-5" style="color: #64748b !important;">Tidak ada aktiva dengan nilai buku yang masih tersisa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($tersedia->isNotEmpty())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted" style="color: #64748b !important;"><i class="fas fa-info-circle me-1"></i> Centang aktiva yang ingin disusutkan, lalu klik tombol simpan.</small>
                    <button class="btn btn-primary px-4" type="submit" id="simpanPenyusutan">
                        <i class="fas fa-save me-1"></i> Simpan &amp; Buat Jurnal Penyusutan
                    </button>
                </div>
            @endif
        </form>

        {{-- Modal Write-Off Aktiva Rusak --}}
        <div class="modal fade" id="modalWriteOff" tabindex="-1" aria-labelledby="modalWriteOffLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva.write-off') }}">
                        @csrf
                        <input type="hidden" name="id_aktiva" id="wo_id_aktiva">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title text-white" id="modalWriteOffLabel"><i class="fas fa-exclamation-triangle me-2"></i>Penghapusan / Write-Off Aktiva Rusak</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning py-2 mb-3">
                                <small>Sisa nilai buku aktiva akan langsung diakui sebagai <strong>Beban Kerugian Disposisi Aset</strong> dan saldo aset menjadi Rp 0.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Aktiva</label>
                                <input type="text" class="form-control bg-light" id="wo_nama_aktiva" readonly>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Nilai Perolehan</label>
                                    <input type="text" class="form-control bg-light" id="wo_h_perolehan" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Akumulasi Penyusutan</label>
                                    <input type="text" class="form-control bg-light" id="wo_akumulasi" readonly>
                                </div>
                            </div>
                            <div class="mb-3 p-3 bg-light border rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-danger">Sisa Nilai Buku (Kerugian):</span>
                                    <strong class="fs-5 text-danger" id="wo_nilai_buku">Rp 0</strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Penghapusan</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Akun Beban Kerugian</label>
                                <select name="id_akun_beban" class="form-select" required>
                                    @foreach ($akunBebanDisposisi as $akun)
                                        <option value="{{ $akun->id_akun_perkiraan }}" @selected($akun->kode_perkiraan === '720006' || str_contains(strtolower($akun->nama), 'disposisi'))>
                                            {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan / Alasan Kerusakan</label>
                                <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Rusak total dan tidak dapat diperbaiki">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i> Konfirmasi Hapus &amp; Jurnal Kerugian</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

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

                var formatRp = function(val) {
                    return 'Rp ' + Math.round(Number(val) || 0).toLocaleString('id-ID');
                };

                $(document).on('click', '.btn-write-off', function() {
                    var id = $(this).data('id');
                    var nama = $(this).data('nama');
                    var perolehan = $(this).data('perolehan');
                    var akumulasi = $(this).data('akumulasi');
                    var nilaiBuku = $(this).data('nilai-buku');

                    $('#wo_id_aktiva').val(id);
                    $('#wo_nama_aktiva').val(nama);
                    $('#wo_h_perolehan').val(formatRp(perolehan));
                    $('#wo_akumulasi').val(formatRp(akumulasi));
                    $('#wo_nilai_buku').text(formatRp(nilaiBuku));

                    var modal = new bootstrap.Modal(document.getElementById('modalWriteOff'));
                    modal.show();
                });
            });
        </script>
    @endsection
</x-theme.app>
