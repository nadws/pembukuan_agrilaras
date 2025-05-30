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

                                    $tgl_awal = new DateTime("$tahun-$b->bulan-01");
                                    $tgl_akhir = new DateTime(date('Y-m-t', strtotime("$tahun-$b->bulan-01")));
                                    $checkIn = new DateTime($k->chick_in);
                                    $diffInDays = $tgl_awal->diff($checkIn)->days;
                                    $umurawal = ceil($diffInDays / 7);
                                    $diffInDays2 = $tgl_akhir->diff($checkIn)->days;
                                    $umursampai = ceil($diffInDays2 / 7);

                                @endphp
                                <td>{{ $umurawal }} - {{ $umursampai }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td colspan="2">Jumlah Ayam</td>
                            <td>Ayam (L)</td>

                            @php
                                $populasi = $k->populasi; // populasi awal, sesuaikan jika diambil dari $k->populasi
                            @endphp
                            @foreach ($bulan as $b)
                                @php
                                    // kurangi populasi karena mati dan dijual (1.2% total)
                                    $populasi -= $populasi * 0.012;
                                @endphp
                                <td class="text-end">
                                    {{ number_format($populasi, 0) }}
                                </td>
                            @endforeach


                        </tr>

                        <tr>
                            <td class="text-end">{{ number_format($k->populasi, 0) }}</td>
                            <td>Ekor</td>
                            <td>Ayam (D)</td>
                            @php
                                $populasi = $k->populasi;
                            @endphp
                            @foreach ($bulan as $b)
                                @php
                                    $ayam_mati = $populasi * 0.01; // 1% dari populasi bulan sebelumnya
                                    $populasi -= $populasi * 0.012; // Update populasi setelah dikurangi 1.2%
                                @endphp
                                <td class="text-end">{{ number_format($ayam_mati, 0) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>Ayam (C)</td>
                            @php
                                $populasi = $k->populasi;
                            @endphp
                            @foreach ($bulan as $b)
                                @php
                                    $ayam_jual = $populasi * 0.002; // 1% dari populasi bulan sebelumnya
                                    $populasi -= $populasi * 0.012; // Update populasi setelah dikurangi 1.2%
                                @endphp
                                <td class="text-end">{{ number_format($ayam_jual, 0) }}</td>
                            @endforeach

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
