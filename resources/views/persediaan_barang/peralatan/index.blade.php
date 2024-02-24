<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <h6>{{ $title }}</h6>
        <div class="row justify-content-end">
            <div class="col-lg-6">
                @if (!empty($create))
                    <x-theme.button modal="T" href="{{ route('peralatan.add') }}" icon="fa-plus" addClass="float-end"
                        teks="Buat Baru" />
                @endif
                <a href="{{ route('peralatan.export') }}" class="btn me-2 btn-primary float-end"><i
                        class="fas fa-print"></i>
                    Print</a>
                <x-theme.akses :halaman="$halaman" route="peralatan.index" />

            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-hover" id="table1">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Tanggal Perolehan</th>
                        <th>Nama</th>
                        <th>Kelompok</th>
                        <th>Nilai Perolehan</th>
                        <th>Penysutan Perbulan</th>
                        <th>Akumulasi Penyusutan</th>
                        <th>Nilai Buku</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($peralatan as $no => $a)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ date('d-m-Y', strtotime($a->tgl)) }}</td>
                            <td>{{ $a->nm_aktiva }}</td>
                            <td>{{ $a->nm_kelompok }}</td>
                            <td align="right">Rp {{ number_format($a->h_perolehan, 0) }}</td>
                            <td align="right">Rp {{ number_format($a->biaya_depresiasi, 0) }}</td>
                            <td align="right">Rp {{ number_format($a->beban, 0) }}</td>
                            <td align="right">Rp {{ number_format($a->h_perolehan - $a->beban, 0) }}</td>

                        </tr>
                    @endforeach
                </tbody>

            </table>
        </section>
        <x-theme.btn_alert_delete route="peralatan.delete_peralatan" name="id_peralatan" :tgl1="$tgl1"
            :tgl2="$tgl2" :id_proyek="$id_proyek" />
    </x-slot>
    @section('scripts')
    @endsection
</x-theme.app>
