<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('gudang-persediaan.index') }}" class="btn btn-sm {{ request()->routeIs('gudang-persediaan.index') ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="fas fa-boxes me-1"></i> Stok Gudang
    </a>
    <a href="{{ route('gudang-persediaan.opname') }}" class="btn btn-sm {{ request()->routeIs('gudang-persediaan.opname') ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="fas fa-clipboard-check me-1"></i> Stok Opname
    </a>
    <a href="{{ route('gudang-persediaan.riwayat') }}" class="btn btn-sm {{ request()->routeIs('gudang-persediaan.riwayat') ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="fas fa-history me-1"></i> Riwayat Opname
    </a>
</div>
