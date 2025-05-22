<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <h5>{{ $title }}</h5>
    </x-slot>

    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="dhead" colspan="2">Kandang</th>
                        <th class="dhead">Deskripsi</th>
                        @foreach ($bulan as $b)
                            <th class="dhead">{{ $b->nm_bulan }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kandang as $k)
                        <tr>
                            <td colspan="2">{{ $k->nm_kandang }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">Jumlah Ayam</td>
                            <td>Ayam (L)</td>
                        </tr>
                        <tr>
                            <td class="text-end">{{ number_format($k->populasi, 0) }}</td>
                            <td>Ekor</td>
                            <td>Ayam (D)</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>Ayam (C)</td>
                        </tr>
                    @endforeach

                </tbody>

            </table>


        </section>
        {{-- tambah customer --}}

    </x-slot>
    @section('scripts')
        <script>
            $(document).ready(function() {
                edit('edit', 'id_customer', 'customer/edit', 'load-edit')
            });
        </script>
    @endsection
</x-theme.app>
