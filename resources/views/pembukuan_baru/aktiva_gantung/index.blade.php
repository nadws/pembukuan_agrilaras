<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader">
        <div>
            <h5 class="mb-1">Aktiva Gantung</h5>
            <small class="text-muted">Daftar aktiva yang masih dalam proses pengerjaan.</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'aktiva-gantung']) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-receipt me-1"></i> Transaksi Berjalan
            </a>
            <a href="{{ route('pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-exchange-alt me-1"></i> Pembalikan Aktiva
            </a>
            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#formSaldoAwal">
                <i class="fas fa-plus me-1"></i> Input Saldo Awal
            </button>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        <style>
            .saldo-awal-box{border:1px solid #dce3f2;border-radius:12px;background:#f7f9fc;padding:18px;margin-bottom:20px}
            .saldo-awal-table{min-width:1050px}.saldo-awal-table th{font-size:12px;white-space:nowrap;color:#52627a}
            .saldo-awal-table .select2-container{width:100%!important}.saldo-awal-table .select2-selection--single{height:38px!important;border-color:#dce3f2!important}.saldo-awal-table .select2-selection__rendered{line-height:36px!important}.saldo-awal-table .select2-selection__arrow{height:36px!important}
            .asset-summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:16px}
            .asset-summary>div{border:1px solid #dce3f2;border-radius:10px;padding:13px;background:#f7f9fc}
            .asset-summary span{display:block;color:#647089;font-size:12px;font-weight:700}.asset-summary strong{color:#1d3167;font-size:19px}
            @media(max-width:767px){.asset-summary{grid-template-columns:1fr}}
        </style>

        @if ($errors->any())
            <div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><div>{{ $errors->first() }}</div></div>
        @endif

        <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="formSaldoAwal">
            <form method="POST" action="{{ route('pembukuan-baru.aktiva-gantung.saldo-awal.store') }}" class="saldo-awal-box">
                @csrf
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div><h6 class="mb-1">Input Saldo Awal Aktiva Gantung</h6><small class="text-muted">Hanya membuat data aktiva gantung. Tidak membuat jurnal baru karena jurnal perkiraan sudah diimpor.</small></div>
                    <div style="min-width:190px"><label class="form-label fw-semibold">Tanggal saldo</label><input type="date" class="form-control" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required></div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle saldo-awal-table mb-2">
                        <thead><tr><th>Nama Aktiva Gantung</th><th>Akun Aktiva Gantung</th><th>Saldo Awal</th><th>Keterangan</th><th width="55">Aksi</th></tr></thead>
                        <tbody id="saldoAwalRows"></tbody>
                    </table>
                </div>
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="tambahBaris"><i class="fas fa-plus me-1"></i> Tambah Baris</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Saldo Awal Tanpa Jurnal</button>
                </div>
            </form>
        </div>

        <div class="asset-summary">
            <div><span>Jumlah Aktiva Gantung</span><strong>{{ number_format($aktivaGantung->total(), 0, ',', '.') }}</strong></div>
            <div><span>Total Saldo Terkumpul</span><strong>Rp {{ number_format($totalSaldo, 0, ',', '.') }}</strong></div>
        </div>

        <div class="table-responsive border rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary"><tr><th>No</th><th>Kode</th><th>Nama Aktiva Gantung</th><th>Keterangan</th><th>Status</th><th class="text-end">Saldo Terkumpul</th></tr></thead>
                <tbody>
                    @forelse ($aktivaGantung as $no => $item)
                        <tr><td>{{ $aktivaGantung->firstItem() + $no }}</td><td>{{ $item->kode }}</td><td><strong>{{ $item->nama_aset }}</strong></td><td>{{ $item->keterangan ?: '-' }}</td><td><span class="badge bg-{{ $item->status === 'gantung' ? 'warning' : 'success' }}">{{ ucfirst($item->status) }}</span></td><td class="text-end">Rp {{ number_format($item->total_saldo, 0, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada data aktiva gantung.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($aktivaGantung->hasPages())<div class="mt-3">{{ $aktivaGantung->links('pagination::bootstrap-5') }}</div>@endif

        <template id="saldoAwalTemplate">
            <tr>
                <td><input class="form-control" data-name="nama_aset" placeholder="Contoh: Kandang I" required></td>
                <td><select class="form-select select-akun-gantung" data-name="id_akun_aktiva_gantung" data-placeholder="Cari kode atau nama akun" required><option value="">Cari kode atau nama akun</option>@foreach($akunAktivaGantung as $akun)<option value="{{ $akun->id_akun_perkiraan }}">{{ $akun->kode_perkiraan }} - {{ $akun->nama }}</option>@endforeach</select></td>
                <td><input type="number" min="0.01" step="0.01" class="form-control text-end" data-name="jumlah" placeholder="0" required></td>
                <td><input class="form-control" data-name="keterangan" placeholder="Opsional"></td>
                <td><button type="button" class="btn btn-outline-danger btn-hapus-row"><i class="fas fa-trash"></i></button></td>
            </tr>
        </template>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const body = document.getElementById('saldoAwalRows');
                const template = document.getElementById('saldoAwalTemplate');
                function reindex() { body.querySelectorAll('tr').forEach((row, i) => row.querySelectorAll('[data-name]').forEach(el => el.name = `detail[${i}][${el.dataset.name}]`)); }
                function initAccountSearch(row) {
                    if (!window.jQuery || !jQuery.fn.select2) return;
                    jQuery(row).find('.select-akun-gantung').select2({
                        width: '100%',
                        placeholder: 'Cari kode atau nama akun',
                        allowClear: true,
                        dropdownParent: jQuery('.saldo-awal-box')
                    });
                }
                function addRow() { body.appendChild(template.content.cloneNode(true)); const row = body.lastElementChild; reindex(); initAccountSearch(row); }
                document.getElementById('tambahBaris').addEventListener('click', addRow);
                body.addEventListener('click', e => { const button = e.target.closest('.btn-hapus-row'); if (!button) return; button.closest('tr').remove(); if (!body.children.length) addRow(); reindex(); });
                addRow();
            });
        </script>
    </x-slot>
</x-theme.app>
