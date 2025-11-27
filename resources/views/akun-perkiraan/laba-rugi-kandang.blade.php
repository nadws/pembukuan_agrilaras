<table class="table table-bordered">
    <tr>
        <th class="text-center">Kandang</th>
        <th class="text-center">{{ $kandang->nm_kandang }}</th>
        <th class="text-center">Rata-rata Penjualan</th>
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
        <td class="text-end">{{ number_format($rak_telur, 0) }}</td>
        <td class="text-end"></td>
    </tr>
    <tr>
        <td>Biaya operasional</td>
        <td class="text-end"></td>
        <td class="text-end"></td>
    </tr>
    <tr>
        <th>Total Biaya</th>
        <th class="text-end"></th>
        <th class="text-end"></th>
    </tr>

</table>
