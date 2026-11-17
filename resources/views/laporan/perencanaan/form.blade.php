@php
    $titles = ['create'=>'Tambah Tertinggal', 'edit'=>'Koreksi', 'detail'=>'Detail'];
    $readonly = $mode === 'detail';
    $values = $readonly ? $form : array_replace($form, old());
@endphp
<x-theme.app title="{{ $titles[$mode] }} Perencanaan" cont="container-fluid">
    <x-slot name="cardHeader">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><h5 class="mb-0">{{ $titles[$mode] }} Perencanaan</h5><div class="d-flex gap-2">
            @if($readonly && $canUpdate)<a class="btn btn-warning btn-sm" href="{{ route('history_perencanaan_pakan.edit', ['tgl'=>$form['tgl'],'id_kandang'=>$form['id_kandang']]) }}">Koreksi</a>@endif
            <a class="btn btn-outline-primary btn-sm" href="{{ route('history_perencanaan_pakan') }}">Kembali</a>
        </div></div>
    </x-slot>
    <x-slot name="cardBody">
        @include('laporan.perencanaan.style')
        @include('laporan.perencanaan.messages')
        <div id="planning-context-message" class="alert alert-warning d-none"></div>
        <form id="planning-form" class="planning-form" method="post" action="{{ route($mode === 'edit' ? 'history_perencanaan_pakan.update' : 'history_perencanaan_pakan.store') }}">
            @csrf
            @if($mode === 'edit')@method('PUT')<input type="hidden" name="target" value="{{ old('target', $target) }}"><input type="hidden" name="snapshot" value="{{ old('snapshot', $snapshot) }}">@endif
            <fieldset @disabled($readonly)>
                <div class="row g-3">
                    <div class="col-lg-3"><label for="planning-date">Tanggal</label><input required type="date" id="planning-date" name="tgl" value="{{ $mode === 'create' ? $values['tgl'] : $form['tgl'] }}" class="form-control" @readonly($mode !== 'create')></div>
                    <div class="col-lg-3"><label for="planning-kandang">Kandang</label>
                        @if($mode !== 'create')<input type="hidden" name="id_kandang" value="{{ $form['id_kandang'] }}">@endif
                        <select required id="planning-kandang" @if($mode === 'create') name="id_kandang" @endif class="form-select planning-select2" @disabled($mode !== 'create')><option value="">- Pilih Kandang -</option>@foreach($kandang as $k)<option value="{{ $k->id_kandang }}" @selected((int)($mode === 'create' ? $values['id_kandang'] : $form['id_kandang']) === (int)$k->id_kandang)>{{ $k->nm_kandang }}</option>@endforeach</select>
                    </div>
                    <div class="col-lg-3"><label for="planning-box">Kg pakan/box</label><input required type="number" min="0.000001" step="any" id="planning-box" name="kg_pakan_box" value="{{ $values['kg_pakan_box'] }}" class="form-control"></div>
                </div>
                <hr>
                <h5>Pakan</h5>
                <div class="row g-3 mb-3">
                    <div class="col-lg-3"><label for="planning-population">Populasi</label><input readonly id="planning-population" value="{{ $population }}" class="form-control"></div>
                    <div class="col-lg-3"><label for="planning-per-bird">Gr Pakan / Ekor</label><input required type="number" min="0.000001" step="any" id="planning-per-bird" name="gr_pakan_ekor" value="{{ $values['gr_pakan_ekor'] }}" class="form-control"></div>
                    <div class="col-lg-2"><label>Kg/karung</label><input readonly id="planning-full-bag" class="form-control"></div>
                    <div class="col-lg-3"><label>Kg/karung sisa</label><input readonly id="planning-bag-rest" class="form-control"></div>
                </div>
                @foreach(['pakan'=>'Pakan','obat_pakan'=>'Obat/vit dengan campuran pakan','obat_air'=>'Obat/vit dengan campuran air','obat_ayam'=>'Obat/ekor ayam'] as $category=>$label)
                    @if($category !== 'pakan')<h5>{{ $label }}</h5>@endif
                    <div id="planning-{{ $category }}">
                        @foreach(($values[$category] ?: ($readonly ? [] : [[]])) as $index=>$row)
                            @include('laporan.perencanaan.row', compact('category','index','row'))
                        @endforeach
                    </div>
                    @if(!$readonly)<button type="button" class="btn btn-primary btn-sm mb-3 planning-add" data-category="{{ $category }}"><i class="fas fa-plus"></i> Tambah {{ $category === 'pakan' ? 'Pakan' : ($category === 'obat_pakan' ? 'Obat Pakan' : ($category === 'obat_air' ? 'Obat Air' : 'Obat Ayam')) }}</button>
                        <template id="template-{{ $category }}">@include('laporan.perencanaan.row', ['category'=>$category,'index'=>'__INDEX__','row'=>[]])</template>
                    @endif
                    @if($category === 'pakan')<div class="row mb-3"><div class="col-lg-3 offset-lg-7"><label>Total Pakan (Gr)</label><input readonly id="planning-total" class="form-control"></div></div>@endif
                @endforeach
            </fieldset>
            @if(!$readonly)<hr><button id="planning-save" class="btn btn-primary">Simpan {{ $titles[$mode] }}</button>@endif
        </form>
        @if($vaccines->isNotEmpty() || $otherMedicine->isNotEmpty())
            <h5 class="mt-4">Item lain</h5><div class="planning-table"><table class="table"><thead><tr><th>Kategori</th><th>Produk</th><th>Qty / Dosis</th><th>Keterangan</th></tr></thead><tbody>
                @foreach($vaccines as $v)<tr><td>Vaksin</td><td>{{ $v->nm_vaksin }}</td><td>{{ $v->qty }}</td><td>Rp {{ number_format($v->ttl_rp,2,',','.') }}</td></tr>@endforeach
                @foreach($otherMedicine as $m)<tr><td>{{ $m->kategori }}</td><td>{{ $products->firstWhere('id_produk',$m->id_produk)->nm_produk ?? $m->id_produk }}</td><td>{{ $m->dosis }}</td><td>{{ $m->ket }}</td></tr>@endforeach
            </tbody></table></div>
        @endif
        @if($readonly)
            <h5 class="mt-4">Mutasi Stok</h5><div class="planning-table"><table class="table"><thead><tr><th>Nota</th><th>Produk</th><th>Masuk</th><th>Pakai</th><th>Nilai</th><th>Check</th></tr></thead><tbody>@foreach($stockRows as $s)<tr><td>{{ $s->no_nota }}</td><td>{{ $products->firstWhere('id_produk',$s->id_pakan)->nm_produk ?? $s->id_pakan }}</td><td>{{ $s->pcs }}</td><td>{{ $s->pcs_kredit }}</td><td>{{ number_format($s->total_rp,2,',','.') }}</td><td>{{ $s->check }}</td></tr>@endforeach</tbody></table></div>
            <h5 class="mt-4">Jurnal PPH</h5><div class="planning-table"><table class="table"><thead><tr><th>Nomor</th><th>Deskripsi</th><th>Debit</th><th>Kredit</th></tr></thead><tbody>@forelse($journalRows as $j)<tr><td>{{ $j->nomor_transaksi }}</td><td>{{ $j->deskripsi }}</td><td>{{ number_format($j->debit,2,',','.') }}</td><td>{{ number_format($j->kredit,2,',','.') }}</td></tr>@empty<tr><td colspan="4">Belum ada jurnal PPH.</td></tr>@endforelse</tbody></table></div>
        @endif
        @include('laporan.perencanaan.script')
    </x-slot>
</x-theme.app>
