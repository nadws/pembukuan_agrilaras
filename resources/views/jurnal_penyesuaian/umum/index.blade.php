<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}</h6>
            </div>
            <div class="col-lg-6">

            </div>

        </div>
    </x-slot>
    <x-slot name="cardBody">
        <form action="{{ route('penyesuaian.save_peralatan') }}" method="post" class="save_jurnal">
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
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->getName() == 'penyesuaian.umum'? 'active': '' }}"
                                href="{{ route('penyesuaian.umum') }}">Umum</a>
                        </li>
                    </ul>

                </div>
                <div class="col-lg-12">
                    <hr style="border: 2px solid #435EBE">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="dhead" width="17%">Bulan</th>
                                <th class="dhead" width="13%">No Nota</th>
                                <th class="dhead">Akun Debit</th>
                                <th class="dhead">Debit</th>
                                <th class="dhead">Akun Kredit</th>
                                <th class="dhead">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="text" class="form-control"
                                        value="{{ date('F Y', strtotime($tgl_pakan)) }}" readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="no_nota" value="JP-{{ $nota }}"
                                        readonly>
                                </td>
                                <td>
                                    <input type="hidden" name="id_akun_debit" value="45">
                                    <select id="" class="select2_add" disabled>
                                        @foreach ($akun as $a)
                                        <option value="{{ $a->id_akun }}" {{ $a->id_akun == '45' ? 'SELECTED' : '' }}>
                                            {{ $a->nm_akun }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control text-end total" readonly value="Rp. 0">
                                    <input type="hidden" class="total_biasa" name="debit_kredit" value="0">
                                </td>
                                <td>
                                    <input type="hidden" name="id_akun_kredit" value="1">
                                    <select name="" id="" class="select2_add" disabled>
                                        @foreach ($akun as $a)
                                        <option value="{{ $a->id_akun }}" {{ $a->id_akun == '1' ? 'SELECTED' : '' }}>
                                            {{ $a->nm_akun }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control text-end total" readonly value="Rp. 0">
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <div class="col-lg-12">
                    <hr style="border: 1px solid #435EBE">
                </div>

                <div class="col-lg-12">
                    <table class="table table-striped table-bordered">
                        <thead>

                            <tr>
                                <th class="dhead" width="15%">Nama Pakan</th>
                                <th class="dhead text-end" width="10%">Stok Program</th>
                                <th class="dhead text-end" width="10%">Stok Aktual</th>
                                <th class="dhead text-end" width="10%">Selisih</th>
                                <th class="dhead text-end" width="10%">Rp Satuan</th>
                                <th class="dhead text-end" width="10%">Total Rp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pakan as $no => $p)
                            <tr>
                                <td>{{$p->nm_produk}}</td>
                                <td align="right">{{ number_format($rp_satuan->pcs,0) }}</td>
                                <td><input type="text" class="form-control stok_aktual text-end" value="0"
                                        count="{{$no}}"></td>
                                <td class="text-end selisih{{$no}}"></td>
                                <td class="text-end">{{ number_format($rp_satuan->ttl_rp / $rp_satuan->pcs,0) }}</td>
                                <td class="text-end"></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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