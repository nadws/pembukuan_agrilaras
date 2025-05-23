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
                            <td colspan="2">
                                {{ $k->nm_kandang }}
                            </td>
                            <td></td>

                            @foreach ($bulan as $b)
                                @php

                                    $tgl_awal = "$tahun-$b->bulan-01";
                                    $tgl_akhir = date('Y-m-t', strtotime($tgl_awal));

                                    $umurawal = \Carbon\Carbon::parse($k->chick_in)->diffInWeeks(
                                        \Carbon\Carbon::parse('2025-04-26'),
                                    );
                                    $umursampai = \Carbon\Carbon::parse($k->chick_in)->diffInWeeks(
                                        \Carbon\Carbon::parse($tgl_akhir),
                                    );
                                @endphp
                                <td>{{ $umurawal }} - {{ $umursampai }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td colspan="2">Jumlah Ayam</td>
                            <td>Ayam (L)</td>
                            <td class="text-end">
                                {{ number_format($k->populasi - $k->populasi * 0.01 - $k->populasi * 0.002, 0) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end">{{ number_format($k->populasi, 0) }}</td>
                            <td>Ekor</td>
                            <td>Ayam (D)</td>
                            <td class="text-end">{{ number_format($k->populasi * 0.01, 0) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>Ayam (C)</td>
                            <td class="text-end">{{ number_format($k->populasi * 0.002, 0) }}</td>
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
