<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1"><i class="fas fa-exchange-alt me-2 text-primary"></i>{{ $title }}</h5>
                <small class="text-muted">Membalik / mengkapitalisasi saldo akumulasi Aktiva Gantung menjadi Aset Tetap</small>
            </div>
            <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembalik-aktiva-gantung']) }}"
                class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Jurnal
            </a>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .pembalik-card {
                border: 1px solid #dce3f2;
                border-radius: 12px;
                background: #fff;
                overflow: hidden;
            }

            .pembalik-card-header {
                padding: 14px 16px;
                border-bottom: 1px solid #dce3f2;
                background: #f5f7fc;
                color: #1d3167;
                font-weight: 800;
            }

            .pembalik-card-body {
                padding: 16px;
            }

            .form-label-custom {
                color: #536078;
                font-size: 12px;
                font-weight: 700;
                margin-bottom: 4px;
            }

            .info-box-aset {
                border: 1px solid #c7d7f7;
                background: #f0f5ff;
                border-radius: 10px;
                padding: 14px;
            }

            .info-box-aset .saldo-value {
                font-size: 20px;
                font-weight: 800;
                color: #1d3167;
            }

            .journal-preview-card {
                border: 1px solid #d1e7dd;
                background: #f8fdfa;
                border-radius: 10px;
                padding: 14px;
            }

            .journal-preview-card .preview-total {
                font-size: 18px;
                font-weight: 800;
                color: #0f5132;
            }
        </style>

        <form action="{{ route('pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                {{-- Kolom Kiri: Informasi Transaksi & Aset --}}
                <div class="col-lg-6">
                    <div class="pembalik-card mb-3">
                        <div class="pembalik-card-header">
                            <i class="fas fa-file-invoice me-1"></i> Data Transaksi & Aset Asal
                        </div>
                        <div class="pembalik-card-body">
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label-custom" for="tanggal">Tanggal Transaksi</label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal"
                                        value="{{ old('tanggal', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label-custom" for="nomor_transaksi">No Transaksi</label>
                                    <input type="text" class="form-control" id="nomor_transaksi" name="nomor_transaksi"
                                        value="{{ old('nomor_transaksi', $noTransaksi) }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom" for="aktiva_gantung_id">Pilih Aset Aktiva Gantung Asal</label>
                                <select class="form-select select2-aset" id="aktiva_gantung_id" name="aktiva_gantung_id" required>
                                    <option value="">-- Pilih Aset Gantung --</option>
                                    @foreach ($asetGantung as $ag)
                                        <option value="{{ $ag->id }}"
                                            data-nama="{{ $ag->nama_aset }}"
                                            data-kode="{{ $ag->kode }}"
                                            data-saldo="{{ $ag->total_terkumpul }}"
                                            data-transaksi="{{ $ag->jumlah_transaksi }}"
                                            data-status="{{ $ag->status }}"
                                            @selected(old('aktiva_gantung_id') == $ag->id)>
                                            {{ $ag->kode }} - {{ $ag->nama_aset }} (Terkumpul: Rp {{ number_format($ag->total_terkumpul, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="infoAsetContainer" class="info-box-aset mb-3" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small">Total Biaya Terkumpul:</span>
                                    <span class="badge bg-primary" id="infoStatusAset">Gantung</span>
                                </div>
                                <div class="saldo-value mb-1" id="infoSaldoAset">Rp 0</div>
                                <div class="text-muted small" id="infoDetailAset">0 riwayat transaksi pengeluaran</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom" for="id_akun_aktiva_gantung">Akun Aktiva Gantung yang Dikredit</label>
                                <select class="form-select select2-akun" id="id_akun_aktiva_gantung" name="id_akun_aktiva_gantung" required>
                                    @foreach ($akunAktivaGantung as $akun)
                                        <option value="{{ $akun->id_akun_perkiraan }}"
                                            @selected(old('id_akun_aktiva_gantung', $akunAktivaGantungDefault->id_akun_perkiraan ?? null) == $akun->id_akun_perkiraan)>
                                            {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Akun penampung biaya pembangunan yang akan dikurangi/dinolkan saldonya.</small>
                            </div>

                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="status_aset_gantung"
                                    name="status_aset_gantung" value="selesai" checked>
                                <label class="form-check-label fw-bold text-dark" for="status_aset_gantung">
                                    Tandai status master aset gantung menjadi <span class="badge bg-success">Selesai</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Aset Tetap Tujuan & Kapitalisasi --}}
                <div class="col-lg-6">
                    <div class="pembalik-card mb-3">
                        <div class="pembalik-card-header">
                            <i class="fas fa-building me-1"></i> Akun Aset Tetap & Nominal
                        </div>
                        <div class="pembalik-card-body">
                            <div class="mb-3">
                                <label class="form-label-custom" for="id_akun_aset">Akun Aset Tetap Tujuan (Debit)</label>
                                <select class="form-select select2-akun" id="id_akun_aset" name="id_akun_aset" required>
                                    <option value="">-- Pilih Akun Aset Tetap --</option>
                                    @foreach ($akunAset as $akun)
                                        <option value="{{ $akun->id_akun_perkiraan }}"
                                            data-nama="{{ $akun->nama }}"
                                            @selected(old('id_akun_aset') == $akun->id_akun_perkiraan)>
                                            {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Akun aset tetap perusahaan (Bangunan, Peralatan, Kendaraan, Mesin, dll).</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom" for="nominal">Nominal Pembalikan / Kapitalisasi (Rp)</label>
                                <input type="number" step="any" min="0.01" class="form-control form-control-lg fw-bold"
                                    id="nominal" name="nominal" value="{{ old('nominal', 0) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom" for="keterangan">Keterangan / Catatan Transaksi</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2"
                                    placeholder="Contoh: Pembalikan aktiva gantung Kandang Ayam A menjadi aset tetap Bangunan">{{ old('keterangan') }}</textarea>
                            </div>

                            {{-- Pilihan daftarkan ke master aktiva tetap untuk depresiasi --}}
                            <div class="p-3 border rounded bg-light mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="simpan_ke_master_aktiva"
                                        name="simpan_ke_master_aktiva" value="1" @checked(old('simpan_ke_master_aktiva') == '1')>
                                    <label class="form-check-label fw-bold text-dark" for="simpan_ke_master_aktiva">
                                        Daftarkan juga ke Master Aset Tetap (Penyusutan / Depresiasi)
                                    </label>
                                </div>
                                <div id="kelompokAktivaWrap" style="display: none;">
                                    <label class="form-label-custom" for="id_kelompok_aktiva">Kelompok Golongan Aktiva</label>
                                    <select class="form-select" id="id_kelompok_aktiva" name="id_kelompok_aktiva">
                                        <option value="">-- Pilih Kelompok Aktiva --</option>
                                        @foreach ($kelompokAktiva as $kel)
                                            <option value="{{ $kel->id_kelompok }}"
                                                data-umur="{{ $kel->umur }}"
                                                data-tarif="{{ $kel->tarif * 100 }}"
                                                @selected(old('id_kelompok_aktiva') == $kel->id_kelompok)>
                                                {{ $kel->nm_kelompok }} (Masa Manfaat: {{ $kel->umur }} Thn, Tarif: {{ $kel->tarif * 100 }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">Akan otomatis dihitung beban penyusutan tahunan / bulanan.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Jurnal Preview --}}
            <div class="journal-preview-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-success"><i class="fas fa-check-circle me-1"></i> Preview Jurnal Umum Otomatis</strong>
                    <span class="badge bg-success">Seimbang (Balance)</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-white mb-2">
                        <thead class="table-light">
                            <tr>
                                <th>Posisi</th>
                                <th>Akun Perkiraan</th>
                                <th>Keterangan</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-info">DEBIT</span></td>
                                <td id="previewAkunDebit" class="fw-bold">-</td>
                                <td id="previewKetDebit">-</td>
                                <td class="text-end fw-bold text-success" id="previewNominalDebit">Rp 0</td>
                                <td class="text-end text-muted">-</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">KREDIT</span></td>
                                <td id="previewAkunKredit" class="fw-bold">-</td>
                                <td id="previewKetKredit">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end fw-bold text-danger" id="previewNominalKredit">Rp 0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembalik-aktiva-gantung']) }}"
                    class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary px-4" id="btnSubmit">
                    <i class="fas fa-save me-1"></i> Simpan Pembalik Aktiva
                </button>
            </div>
        </form>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                const asetSelect = document.getElementById('aktiva_gantung_id');
                const akunAsetSelect = document.getElementById('id_akun_aset');
                const akunGantungSelect = document.getElementById('id_akun_aktiva_gantung');
                const nominalInput = document.getElementById('nominal');
                const keteranganInput = document.getElementById('keterangan');
                const simpanAktivaCheck = document.getElementById('simpan_ke_master_aktiva');
                const kelompokAktivaWrap = document.getElementById('kelompokAktivaWrap');

                const infoContainer = document.getElementById('infoAsetContainer');
                const infoSaldo = document.getElementById('infoSaldoAset');
                const infoStatus = document.getElementById('infoStatusAset');
                const infoDetail = document.getElementById('infoDetailAset');

                const previewAkunDebit = document.getElementById('previewAkunDebit');
                const previewKetDebit = document.getElementById('previewKetDebit');
                const previewNominalDebit = document.getElementById('previewNominalDebit');

                const previewAkunKredit = document.getElementById('previewAkunKredit');
                const previewKetKredit = document.getElementById('previewKetKredit');
                const previewNominalKredit = document.getElementById('previewNominalKredit');

                function formatRupiah(val) {
                    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
                }

                function updatePreview() {
                    const asetOpt = asetSelect.options[asetSelect.selectedIndex];
                    const akunAsetOpt = akunAsetSelect.options[akunAsetSelect.selectedIndex];
                    const akunGantungOpt = akunGantungSelect.options[akunGantungSelect.selectedIndex];
                    const nominalVal = Number(nominalInput.value || 0);
                    const ket = keteranganInput.value.trim();

                    // Info box aset gantung
                    if (asetOpt && asetOpt.value) {
                        const saldo = Number(asetOpt.dataset.saldo || 0);
                        const status = asetOpt.dataset.status || 'gantung';
                        const transaksi = asetOpt.dataset.transaksi || '0';

                        infoContainer.style.display = 'block';
                        infoSaldo.textContent = formatRupiah(saldo);
                        infoStatus.textContent = status.toUpperCase();
                        infoStatus.className = 'badge ' + (status === 'gantung' ? 'bg-warning text-dark' : 'bg-success');
                        infoDetail.textContent = `${transaksi} riwayat transaksi pengeluaran tercatat`;
                    } else {
                        infoContainer.style.display = 'none';
                    }

                    // Live journal preview
                    previewAkunDebit.textContent = akunAsetOpt && akunAsetOpt.value ? akunAsetOpt.text : '(Pilih Akun Aset Tetap)';
                    previewAkunKredit.textContent = akunGantungOpt && akunGantungOpt.value ? akunGantungOpt.text : '(Pilih Akun Aktiva Gantung)';

                    const defaultKetDebit = asetOpt && asetOpt.value && akunAsetOpt && akunAsetOpt.value
                        ? `Pembalikan aktiva gantung ${asetOpt.dataset.nama} ke aset ${akunAsetOpt.dataset.nama || ''}`
                        : 'Pembalikan aktiva gantung';

                    const defaultKetKredit = asetOpt && asetOpt.value
                        ? `Pembalikan aktiva gantung ${asetOpt.dataset.nama}`
                        : 'Pembalikan aktiva gantung';

                    previewKetDebit.textContent = ket || defaultKetDebit;
                    previewKetKredit.textContent = ket || defaultKetKredit;

                    previewNominalDebit.textContent = formatRupiah(nominalVal);
                    previewNominalKredit.textContent = formatRupiah(nominalVal);
                }

                asetSelect.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    if (opt && opt.value) {
                        const saldo = Number(opt.dataset.saldo || 0);
                        nominalInput.value = saldo;

                        if (!keteranganInput.value) {
                            keteranganInput.value = `Pembalikan aktiva gantung ${opt.dataset.nama} menjadi aset tetap`;
                        }
                    }
                    updatePreview();
                });

                akunAsetSelect.addEventListener('change', updatePreview);
                akunGantungSelect.addEventListener('change', updatePreview);
                nominalInput.addEventListener('input', updatePreview);
                keteranganInput.addEventListener('input', updatePreview);

                simpanAktivaCheck.addEventListener('change', function() {
                    kelompokAktivaWrap.style.display = this.checked ? 'block' : 'none';
                    document.getElementById('id_kelompok_aktiva').required = this.checked;
                });

                if (simpanAktivaCheck.checked) {
                    kelompokAktivaWrap.style.display = 'block';
                    document.getElementById('id_kelompok_aktiva').required = true;
                }

                if (asetSelect.value) {
                    updatePreview();
                }
            })();
        </script>
    @endsection
</x-theme.app>
