<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home"
            aria-selected="true">Laba Rugi</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile"
            aria-selected="false">Buku besar</a>
    </li>
</ul>
<br>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
        <table class="table table-bordered">
            <tr>
                <th class="text-center">Kandang</th>
                <th class="text-center">{{ $kandang->nm_kandang }}</th>
                <th class="text-center">Rata-rata Penjualan</th>
            </tr>
            <tr>
                <td>Ayam Awal</td>
                <td class="text-end">{{ number_format($kandang->stok_awal, 0) }}</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td>Total Telur</td>
                <td class="text-end">{{ number_format($total_telur, 0) }}</td>
                <td class="text-end">{{ number_format($rata_rata_telur, 0) }}</td>
            </tr>
            <tr>
                <th colspan="3">Ayam</th>
            </tr>
            <tr>
                <td>Mati</td>
                <td class="text-end">{{ number_format($populasi->mati, 0) }}</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td>Culling</td>
                <td class="text-end">{{ number_format($populasi->jual + $populasi->afkir, 0) }}</td>
                <td class="text-end">{{ number_format($rata_rata_ayam, 0) }}</td>
            </tr>
            <tr>
                <th colspan="3">Pendapatan</th>
            </tr>
            <tr>
                <td>Jual Telur</td>
                <td class="text-end">{{ number_format($total_telur * $rata_rata_telur, 0) }}</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td>Jual Ayam</td>
                <td class="text-end">{{ number_format(($populasi->jual + $populasi->afkir) * $rata_rata_ayam, 0) }}</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <th>Total Pendapatan</th>
                <th class="text-end">
                    {{ number_format($total_telur * $rata_rata_telur + ($populasi->jual + $populasi->afkir) * $rata_rata_ayam, 0) }}
                </th>
                <th></th>
            </tr>
            <tr>
                <th colspan="3">Biaya</th>
            </tr>
            <tr>
                <td>Pakan</td>
                <td class="text-end">{{ number_format($biaya_pakan_program, 0) }}</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td>Vitamin</td>
                <td class="text-end">{{ number_format($biaya_vitamin, 0) }}</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td>Vaksin</td>
                <td class="text-end">{{ number_format($vaksin, 0) }}</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td>Beli Pullet</td>
                <td class="text-end">{{ number_format($kandang->rupiah, 0) }}</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td>Rak Telur</td>
                <td class="text-end" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="{{ number_format($pcs_telur, 0) }}">
                    {{ number_format($rak_telur * 820, 0) }}</td>
                <td class="text-end">820</td>
            </tr>
            <tr>
                <td>Biaya operasional</td>
                <td class="text-end" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Total Periode Ayam : {{ number_format($total, 0) }} | stok_awal : {{ number_format($stok_awal, 0) }}">
                    {{ number_format($biaya_operasional, 0) }}</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <th>Total Biaya</th>
                <th class="text-end">
                    {{ number_format($biaya_pakan_program + $biaya_vitamin + $vaksin + $kandang->rupiah + $rak_telur * 820 + $biaya_operasional, 0) }}
                </th>
                <th class="text-end"></th>
            </tr>
            <tr>
                <th>Pendapatan Biaya</th>
                <th class="text-end">
                    @php
                        $pendapatan =
                            $total_telur * $rata_rata_telur + ($populasi->jual + $populasi->afkir) * $rata_rata_ayam;

                        $biaya =
                            $biaya_pakan_program +
                            $biaya_vitamin +
                            $vaksin +
                            $kandang->rupiah +
                            $rak_telur * 820 +
                            $biaya_operasional;
                    @endphp
                    {{ number_format($pendapatan - $biaya, 0) }}
                </th>
                <th class="text-end"></th>
            </tr>

        </table>
    </div>
    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <div class="row">

            <div class="col-lg-6">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Akun</th>
                            <th>Debit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = 0;
                        @endphp
                        @foreach ($jurnal_periode_detail as $j)
                            @php
                                $total += $j->debit;
                            @endphp
                            <tr>
                                <td>{{ $j->nm_akun }}</td>
                                <td class="text-end">{{ number_format($j->debit, 0) }}
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th class="text-end">{{ number_format($total, 0) }}
                        </tr>
                        <tr>
                            <th>Total Biaya Jurnal Accurate</th>
                            <th class="text-end">{{ number_format($operasional_acc, 0) }}
                        </tr>
                        <tr>
                            <th>Grand Total</th>
                            <th class="text-end">{{ number_format($total + $operasional_acc, 0) }}
                        </tr>
                    </tfoot>

                </table>
            </div>
            <div class="col-lg-6">
                <table class="table table-bordered table-responsive">
                    <thead>
                        <tr>
                            <th>Kandang</th>
                            <th>Stok Awal</th>
                            <th>Mati</th>
                            <th>Jual</th>
                            <th>Afkir</th>
                            <th>Rupiah</th>
                            <th>Operasional</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($populasi_periode as $p)
                            <tr>
                                <td
                                    class="{{ $kandang->nm_kandang == $p->nm_kandang ? 'bg-warning text-white' : '' }}">
                                    {{ $p->nm_kandang }}</td>
                                <td
                                    class="text-end {{ $kandang->nm_kandang == $p->nm_kandang ? 'bg-warning text-white' : '' }}">
                                    {{ number_format($p->stok_awal, 0) }}</td>
                                <td
                                    class="text-end {{ $kandang->nm_kandang == $p->nm_kandang ? 'bg-warning text-white' : '' }}">
                                    {{ number_format($p->mati, 0) }}</td>
                                <td
                                    class="text-end {{ $kandang->nm_kandang == $p->nm_kandang ? 'bg-warning text-white' : '' }}">
                                    {{ number_format($p->jual, 0) }}</td>
                                <td
                                    class="text-end {{ $kandang->nm_kandang == $p->nm_kandang ? 'bg-warning text-white' : '' }}">
                                    {{ number_format($p->afkir, 0) }}</td>
                                <td
                                    class="text-end {{ $kandang->nm_kandang == $p->nm_kandang ? 'bg-warning text-white' : '' }}">
                                    {{ number_format($p->rupiah, 0) }}</td>

                                <td
                                    class="text-end {{ $kandang->nm_kandang == $p->nm_kandang ? 'bg-warning text-white' : '' }}">
                                    {{ number_format((($total + $operasional_acc) / sumBk($populasi_periode, 'stok_awal')) * $p->stok_awal, 0) }}
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th class="text-end">{{ number_format(sumBk($populasi_periode, 'stok_awal'), 0) }}</th>
                            <th class="text-end">{{ number_format(sumBk($populasi_periode, 'mati'), 0) }}</th>
                            <th class="text-end">{{ number_format(sumBk($populasi_periode, 'jual'), 0) }}</th>
                            <th class="text-end">{{ number_format(sumBk($populasi_periode, 'afkir'), 0) }}</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

</div>
