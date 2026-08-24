<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1"><i class="fas fa-edit me-2 text-warning"></i>{{ $title }}</h5>
                <small class="text-muted">Edit transaksi pembalik / kapitalisasi Aktiva Gantung: <strong>{{ $nomor_transaksi }}</strong></small>
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

            .journal-preview-card {
                border: 1px solid #d1e7dd;
                background: #f8fdfa;
                border-radius: 10px;
                padding: 14px;
            }
        </style>

        <form action="{{ route('pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.update', $nomor_transaksi) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Kolom Kiri: Informasi Transaksi & Akun Aktiva Gantung --}}
                <div class="col-lg-6">
                    <div class="pembalik-card mb-3">
                        <div class="pembalik-card-header">
                            <i class="fas fa-file-invoice me-1"></i> Data Transaksi & Akun Asal
                        </div>
                        <div class="pembalik-card-body">
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label-custom" for="tanggal">Tanggal Transaksi</label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal"
                                        value="{{ old('tanggal', $tanggal) }}" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label-custom" for="nomor_transaksi">No Transaksi</label>
                                    <input type="text" class="form-control" id="nomor_transaksi" name="nomor_transaksi"
                                        value="{{ old('nomor_transaksi', $nomor_transaksi) }}" readonly required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom" for="id_akun_aktiva_gantung">Akun Aktiva Gantung yang Dikredit</label>
                                <select class="form-select select2-akun" id="id_akun_aktiva_gantung" name="id_akun_aktiva_gantung" required>
                                    @foreach ($akunAktivaGantung as $akun)
                                        <option value="{{ $akun->id_akun_perkiraan }}"
                                            @selected(old('id_akun_aktiva_gantung', $id_akun_aktiva_gantung) == $akun->id_akun_perkiraan)>
                                            {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Akun penampung biaya pembangunan yang dikreditkan.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Akun Aset Tetap & Nominal --}}
                <div class="col-lg-6">
                    <div class="pembalik-card mb-3">
                        <div class="pembalik-card-header">
                            <i class="fas fa-building me-1"></i> Akun Aset Tetap & Nominal
                        </div>
                        <div class="pembalik-card-body">
                            <div class="mb-3">
                                <label class="form-label-custom" for="id_akun_aset">Akun Aset Tetap Tujuan (Debit)</label>
                                <select class="form-select select2-akun" id="id_akun_aset" name="id_akun_aset" required>
                                    @foreach ($akunAset as $akun)
                                        <option value="{{ $akun->id_akun_perkiraan }}"
                                            data-nama="{{ $akun->nama }}"
                                            @selected(old('id_akun_aset', $id_akun_aset) == $akun->id_akun_perkiraan)>
                                            {{ $akun->kode_perkiraan }} - {{ $akun->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Akun aset tetap perusahaan (Bangunan, Peralatan, Kendaraan, Mesin, dll).</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom" for="nominal">Nominal Pembalikan / Kapitalisasi (Rp)</label>
                                <input type="number" step="any" min="0.01" class="form-control form-control-lg fw-bold"
                                    id="nominal" name="nominal" value="{{ old('nominal', $nominal) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom" for="keterangan">Keterangan / Catatan Transaksi</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2">{{ old('keterangan', $keterangan) }}</textarea>
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
                    <i class="fas fa-save me-1"></i> Perbarui Pembalik Aktiva
                </button>
            </div>
        </form>
    </x-slot>

    @section('scripts')
        <script>
            (function() {
                const akunAsetSelect = document.getElementById('id_akun_aset');
                const akunGantungSelect = document.getElementById('id_akun_aktiva_gantung');
                const nominalInput = document.getElementById('nominal');
                const keteranganInput = document.getElementById('keterangan');

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
                    const akunAsetOpt = akunAsetSelect.options[akunAsetSelect.selectedIndex];
                    const akunGantungOpt = akunGantungSelect.options[akunGantungSelect.selectedIndex];
                    const nominalVal = Number(nominalInput.value || 0);
                    const ket = keteranganInput.value.trim();

                    previewAkunDebit.textContent = akunAsetOpt && akunAsetOpt.value ? akunAsetOpt.text : '(Pilih Akun Aset Tetap)';
                    previewAkunKredit.textContent = akunGantungOpt && akunGantungOpt.value ? akunGantungOpt.text : '(Pilih Akun Aktiva Gantung)';

                    previewKetDebit.textContent = ket || 'Pembalikan aktiva gantung';
                    previewKetKredit.textContent = ket || 'Pembalikan aktiva gantung';

                    previewNominalDebit.textContent = formatRupiah(nominalVal);
                    previewNominalKredit.textContent = formatRupiah(nominalVal);
                }

                akunAsetSelect.addEventListener('change', updatePreview);
                akunGantungSelect.addEventListener('change', updatePreview);
                nominalInput.addEventListener('input', updatePreview);
                keteranganInput.addEventListener('input', updatePreview);

                updatePreview();
            })();
        </script>
    @endsection
</x-theme.app>
