<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h5 class="mb-1">Stok Opname Barang Umum</h5><small class="text-muted">Bandingkan stok sistem dengan jumlah barang yang benar-benar ada.</small></div><div class="d-flex gap-2"><a href="{{ route('gudang-persediaan.barang-umum') }}" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i> Kembali ke Stok</a><a href="{{ route('barang-umum.index') }}" class="btn btn-outline-primary"><i class="fas fa-boxes me-1"></i> Isi Stok Awal</a></div></div></x-slot>
    <x-slot name="cardBody">
        <style>.opname-info{border:1px solid #dbe5fb;border-radius:12px;background:#f5f8ff;padding:14px 16px;color:#44536f}.opname-table-wrap{overflow-x:auto;border:1px solid #e1e6f0;border-radius:12px}.opname-table{min-width:1050px;margin:0}.opname-table thead th{padding:12px;background:#304f9e;color:#fff;font-size:12px;white-space:nowrap}.opname-table td{padding:11px;vertical-align:middle}.opname-table .form-control{min-width:125px;border-color:#ced8eb}.difference-ok{color:#198754}.difference-minus{color:#dc3545}.difference-plus{color:#0d6efd}</style>
        @if (isset($errors) && $errors->any())<div class="alert alert-danger"><strong>Opname belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>@endif
        <div class="opname-info mb-3"><i class="fas fa-info-circle me-1"></i> Stok sistem dihitung dari <strong>stok awal + pembelian umum + hasil opname sebelumnya</strong>. Produk tanpa saldo disembunyikan agar tabel ringkas, tetapi bisa ditampilkan untuk menambahkan stok awal.</div>
        <div class="d-flex justify-content-end mb-3">
            @if($tampilkanKosong)
                <a href="{{ route('gudang-persediaan.barang-umum.opname') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-eye-slash me-1"></i> Sembunyikan stok kosong</a>
            @else
                <a href="{{ route('gudang-persediaan.barang-umum.opname', ['tampilkan_kosong' => 1]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i> Tampilkan stok kosong / tambah stok</a>
            @endif
        </div>
        <form method="POST" action="{{ route('pembukuan-baru.jurnal-penyesuaian.stok-opname.store') }}">@csrf
            <div class="row mb-3"><div class="col-md-4 col-lg-3"><label class="form-label fw-bold">Tanggal Opname</label><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal',date('Y-m-d')) }}" required></div></div>
            <div class="opname-table-wrap"><table class="table table-hover opname-table"><thead><tr><th>Barang</th><th>Satuan</th><th class="text-end">Stok Sistem</th><th class="text-end">Harga Satuan</th><th>Stok Fisik</th><th class="text-end">Selisih</th><th class="text-end">Nilai Selisih</th></tr></thead><tbody>
            @forelse($items as $i)
                @php $qty=(float)$i->qty_masuk; $harga=abs($qty)>0.000001?(float)$i->nilai_masuk/$qty:0; @endphp
                <tr class="opname-row"><td class="fw-semibold">{{ $i->nama_produk }}<input type="hidden" name="id_produk[]" value="{{ $i->id_produk }}"><input type="hidden" name="nama_produk[]" value="{{ $i->nama_produk }}"></td><td>{{ $i->satuan ?: '-' }}</td><td class="text-end">{{ number_format($qty,3,',','.') }}<input type="hidden" class="system-qty" name="qty_sistem[]" value="{{ $qty }}"></td><td><input type="number" step="0.01" min="0" name="nilai_satuan[]" class="form-control unit-price text-end" value="{{ old('nilai_satuan.'.$loop->index,$harga) }}" title="Isi harga satuan jika menambahkan stok baru"></td><td><input type="number" step="0.001" min="0" name="qty_fisik[]" class="form-control physical-qty" value="{{ old('qty_fisik.'.$loop->index,$qty) }}" required></td><td class="text-end fw-bold difference difference-ok">0</td><td class="text-end difference-value">Rp 0</td></tr>
            @empty
                <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-box-open fa-2x d-block mb-2"></i>Belum ada saldo barang umum.<br><a href="{{ route('barang-umum.index') }}" class="btn btn-sm btn-primary mt-3">Isi Stok Awal Barang</a></td></tr>
            @endforelse
            </tbody></table></div>
            @if($items->isNotEmpty())<div class="d-flex justify-content-end mt-3"><button class="btn btn-primary px-4" type="submit" onclick="return confirm('Simpan stok fisik sebagai saldo terbaru dan buat jurnal nilai selisih?')"><i class="fas fa-save me-1"></i> Simpan Opname &amp; Buat Jurnal</button></div>@endif
        </form>
        <script>document.addEventListener('DOMContentLoaded',function(){const rupiah=new Intl.NumberFormat('id-ID',{maximumFractionDigits:0});document.querySelectorAll('.opname-row').forEach(function(row){const hitung=function(){const sistem=Number(row.querySelector('.system-qty').value)||0,fisik=Number(row.querySelector('.physical-qty').value)||0,harga=Number(row.querySelector('.unit-price').value)||0,selisih=fisik-sistem,out=row.querySelector('.difference');out.textContent=selisih.toLocaleString('id-ID',{maximumFractionDigits:3});out.className='text-end fw-bold difference '+(selisih<0?'difference-minus':(selisih>0?'difference-plus':'difference-ok'));row.querySelector('.difference-value').textContent='Rp '+rupiah.format(Math.abs(selisih)*harga)};hitung();row.querySelector('.physical-qty').addEventListener('input',hitung);row.querySelector('.unit-price').addEventListener('input',hitung)})});</script>
    </x-slot>
</x-theme.app>
