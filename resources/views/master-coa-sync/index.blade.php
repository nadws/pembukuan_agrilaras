<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <h5 class="float-start mt-1">{{ $title }}</h5>
        <form method="POST" action="{{ route('master.coa-sync.tarik') }}" class="float-end">
            @csrf
            <button type="submit" class="btn btn-success"><i class="fas fa-sync"></i> Tarik sekarang</button>
        </form>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-sm mb-4">
        <tr><th style="width: 260px">Alamat aplikasi pusat</th><td>{{ $apiUrl !== '' ? $apiUrl : 'Belum diisi (COA_API_URL)' }}</td></tr>
        <tr><th>Token API</th><td>{{ $hasToken ? 'Sudah diisi' : 'Belum diisi (COA_API_TOKEN)' }}</td></tr>
        <tr><th>Posisi sinkron terakhir</th><td>{{ $cursor ?? 'Belum pernah sinkron' }}</td></tr>
        <tr><th>Terakhir ditarik</th><td>{{ $lastSync ?? '-' }}</td></tr>
        <tr><th>Jumlah akun</th><td>{{ $totalAccounts }} (aktif: {{ $activeAccounts }})</td></tr>
    </table>

    <h6>10 akun terakhir diubah</h6>
    <table class="table table-sm">
        <thead><tr><th>Kode</th><th>Nama</th><th>Aktif</th><th>Terakhir diubah</th></tr></thead>
        <tbody>
            @forelse ($recent as $akun)
                <tr>
                    <td>{{ $akun->kode_perkiraan }}</td>
                    <td>{{ $akun->nama }}</td>
                    <td>{{ $akun->aktif ? 'Ya' : 'Tidak' }}</td>
                    <td>{{ $akun->updated_at?->format('d-m-Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Belum ada akun.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-theme.app>
