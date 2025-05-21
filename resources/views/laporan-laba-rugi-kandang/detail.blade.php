<div class="row">
    <div class="col-lg-6 mb-4">
        <label for="">Pilih Kandang</label>
        <select name="" id="" class="form-control pilih-kandang">
            @foreach ($kandang as $k)
                <option value="{{ $k->kandang_id }}" @selected($kandang_id == $k->kandang_id)>{{ $k->nm_kandang }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-12">
        <table class="table ">

            {{-- <tr>
                    <th>Akun</th>
                </tr> --}}
            <tr>
                <th colspan="2" class="bg-secondary text-white fs-6">Pendapatan Operasional</th>
            </tr>
            @foreach ($pendapatan as $p)
                <tr>
                    <td>{{ $p->nama }}</td>
                    <td class="text-end">Rp. {{ number_format($p->ttl_rp, 0) }}</td>
                </tr>
            @endforeach
            <tr>
                <th class=" fs-6">Jumlah Pendapatan</th>
                <th class="text-end fs-6 text-nowrap">Rp. {{ number_format(sumbK($pendapatan, 'ttl_rp'), 0) }}</th>
            </tr>
            <tr>
                <th colspan="2" class="bg-secondary text-white fs-6">Biaya Pokok Penjualan</th>
            </tr>
            @foreach ($biaya_pokok as $p)
                <tr>
                    <td>{{ $p->nama }}</td>
                    <td class="text-end">Rp. {{ number_format($p->ttl_rp, 0) }}</td>
                </tr>
            @endforeach
            <tr>
                <th class=" fs-6">Jumlah Beban Pokok Penjualan</th>
                <th class="text-end fs-6 text-nowrap">Rp. {{ number_format(sumbK($biaya_pokok, 'ttl_rp'), 0) }}</th>
            </tr>
            <tr>
                <th class=" fs-6">LABA KOTOR</th>
                <th class="text-end fs-6 text-nowrap">Rp.
                    {{ number_format(sumbK($pendapatan, 'ttl_rp') - sumbK($biaya_pokok, 'ttl_rp'), 0) }}</th>
            </tr>
            <tr>
                <th colspan="2" class="bg-secondary text-white fs-6">Biaya Operasional</th>
            </tr>
            @foreach ($biaya_operasional as $p)
                <tr>
                    <td>{{ $p->nama }}</td>
                    <td class="text-end">Rp. {{ number_format($p->ttl_rp, 0) }}</td>
                </tr>
            @endforeach
            <tr>
                <th class=" fs-6">Jumlah Beban Operasional</th>
                <th class="text-end fs-6 text-nowrap">Rp. {{ number_format(sumbK($biaya_operasional, 'ttl_rp'), 0) }}
                </th>
            </tr>
            <tr>
                <th class=" fs-6">LABA/RUGI</th>
                <th class="text-end fs-6 text-nowrap">Rp.
                    {{ number_format(sumbK($pendapatan, 'ttl_rp') - sumbK($biaya_pokok, 'ttl_rp') - sumbK($biaya_operasional, 'ttl_rp'), 0) }}
                </th>
            </tr>




        </table>
    </div>
</div>
