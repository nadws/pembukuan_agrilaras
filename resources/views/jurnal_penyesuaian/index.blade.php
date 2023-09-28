<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }} </h6>
            </div>
            <div class="col-lg-6">

            </div>

        </div>
    </x-slot>
    <x-slot name="cardBody">
        <form action="{{ route('save_penyesuaian_aktiva') }}" method="post" class="save_jurnal">
            @csrf
            <div class="row mb-4">
                <div class="col-lg-12">
                    <ul class="nav nav-pills float-start">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->getName() == 'penyesuaian.aktiva'? 'active': '' }}"
                                aria-current="page" href="{{ route('penyesuaian.aktiva') }}">Aktiva</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->getName() == 'penyesuaian.peralatan'? 'active': '' }}"
                                href="{{ route('penyesuaian.peralatan') }}">Peralatan</a>

                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->getName() == 'penyesuaian.atk'? 'active': '' }}"
                                href="{{ route('penyesuaian.atk') }}">Atk</a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link {{ request()->route()->getName() == 'penyesuaian.umum'? ($kategori == 'pakan'? 'active': ''): '' }}"
                                href="{{ route('penyesuaian.umum', ['kategori' => 'pakan']) }}">Pakan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->getName() == 'penyesuaian.umum'? ($kategori == 'vitamin'? 'active': ''): '' }}"
                                href="{{ route('penyesuaian.umum', ['kategori' => 'vitamin']) }}">Vitamin</a>
                        </li> --}}
                    </ul>

                </div>
                <div class="col-lg-12">
                    <hr style="border: 2px solid #435EBE">
                </div>
            </div>
            <section class="row">
                <div class="col-lg-12">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="dhead" width="15%">Bulan</th>
                                <th class="dhead" width="13%">No Nota</th>
                                <th class="dhead">Akun Debit</th>
                                <th class="dhead">Debit</th>
                                <th class="dhead">Akun Kredit</th>
                                <th class="dhead">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $total = 0;
                            @endphp
                            @foreach ($aktiva as $a)
                            @php
                            $total += $a->biaya_depresiasi;
                            @endphp
                            @endforeach
                            <tr>
                                <td>
                                    <input type="text" class="form-control" value="{{ date('F Y', strtotime($tgl)) }}"
                                        readonly>
                                    <input type="hidden" class="form-control" name="tgl"
                                        value="{{ date('Y-m-d', strtotime($tgl)) }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control" readonly name="no_nota"
                                        value="JP-{{ $nota }}">

                                </td>
                                <td>
                                    <input type="hidden" name="id_akun_debit" value="51">
                                    {{ ucwords($akunDebit->nm_akun) }}
                                    {{-- <select id="" class="select2_add" disabled>
                                        @foreach ($akun as $a)
                                        <option value="{{ $a->id_akun }}" {{ $a->id_akun == '51' ? 'SELECTED' : '' }}>
                                            {{ $a->nm_akun }}
                                        </option>
                                        @endforeach
                                    </select> --}}
                                </td>
                                <td>
                                    <input type="text" readonly class="text-end form-control"
                                        value="Rp {{ number_format($total, 2, ',', '.') }}">
                                </td>
                                <input type="hidden" class="total_biasa" name="debit_kredit"
                                    value="{{ round($total, 2) }}">
                                <td>
                                    {{ ucwords($akunDebit->nm_akun) }}

                                    <input type="hidden" name="id_akun_kredit" value="52">
                                    {{-- <select name="" id="" class="select2_add" disabled>
                                        @foreach ($akun as $a)
                                        <option value="{{ $a->id_akun }}" {{ $a->id_akun == '52' ? 'SELECTED' : '' }}>
                                            {{ $a->nm_akun }}
                                        </option>
                                        @endforeach
                                    </select> --}}
                                </td>
                                <td>
                                    <input type="text" readonly class="text-end form-control"
                                        value="Rp {{ number_format($total, 2, ',', '.') }}">
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <div class="col-lg-12">
                    <hr style="border: 1px solid #435EBE">
                </div>
                <div class="col-lg-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="dhead" width="20%">Tanggal Perolehan</th>
                                <th class="dhead" width="20%">Nama Aktiva</th>
                                <th class="dhead" width="20%">Harga Perolehan</th>
                                <th class="dhead" width="20%">Nilai Buku</th>
                                <th class="dhead" width="20%">Beban Penyusutan <br> (<span style="font-size: 13.5px"
                                        class="text-warning text-sm">Barang rusak/hilang bebankan sesuia nilai
                                        buku</span>)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($aktiva as $no => $a)
                            @if (round($a->h_perolehan - $a->beban, 0) <= '0' ) @php continue; @endphp @else @endif <tr>
                                <td>{{ date('d-m-Y', strtotime($a->tgl)) }}</td>
                                <td>{{ $a->nm_aktiva }}</td>
                                <td>{{ number_format($a->h_perolehan, 0) }}</td>
                                <td>{{ number_format($a->h_perolehan - $a->beban, 0) }} </td>
                                <td>
                                    <input type="text" class="form-control beban beban{{ $no + 1 }}"
                                        count="{{ $no + 1 }}"
                                        value="Rp {{ number_format($a->biaya_depresiasi, 2, ',', '.') }}">

                                    <input type="hidden" name="b_penyusutan[]"
                                        class="beban_biasa beban_biasa{{ $no + 1 }}"
                                        value="{{ round($a->biaya_depresiasi, 2) }}">
                                    <input type="hidden" name="id_aktiva[]" value="{{ $a->id_aktiva }}">
                                </td>
                                </tr>
                                @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary button-save">Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        <a href="{{ route('penyesuaian.aktiva') }}" class="float-end btn btn-outline-primary me-2">Batal</a>
        {{-- <a href="{{route('jurnal')}}" class="float-end btn btn-outline-primary me-2">Batal</a> --}}
        </form>
    </x-slot>
    @section('scripts')
    <script>
        $(document).ready(function() {
                $(document).on("keyup", ".beban", function() {
                    var count = $(this).attr("count");
                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.beban_biasa' + count).val(0)
                    } else {
                        $(this).val("Rp " + input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.beban_biasa' + count).val(input2)

                    }
                    var total_debit = 0;
                    $(".beban_biasa").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var totalRupiah = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    console.log(totalRupiah);
                    var debit = $(".total").val(totalRupiah);
                    var debit_biasa = $(".total_biasa").val(total_debit);
                });
                aksiBtn("form");

            });
    </script>
    @endsection
</x-theme.app>