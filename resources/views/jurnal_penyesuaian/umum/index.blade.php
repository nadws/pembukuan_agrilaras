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
        <form action="{{ route('penyesuaian.save_umum') }}" method="post" class="save_jurnal">
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
                            <a class="nav-link {{ request()->route()->getName() == 'penyesuaian.umum'? ($kategori == 'pakan'? 'active': ''): '' }}"
                                href="{{ route('penyesuaian.umum', ['kategori' => 'pakan']) }}">Pakan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->getName() == 'penyesuaian.umum'? ($kategori == 'vitamin'? 'active': ''): '' }}"
                                href="{{ route('penyesuaian.umum', ['kategori' => 'vitamin']) }}">Vitamin</a>
                        </li>
                    </ul>

                </div>
                <div class="col-lg-12">
                    <hr style="border: 2px solid #435EBE">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-2">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="dhead">Bulan</th>
                                <th class="dhead">No Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="text" class="form-control"
                                        value="{{ date('F Y', strtotime($tgl_pakan)) }}" readonly>
                                    <input type="hidden" name="tgl" value="{{ $tgl_pakan }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="no_nota"
                                        value="JP-{{ $nota }}" readonly>
                                </td>
                            </tr>
                        </tbody>
                    </table>


                </div>
                <div class="col-lg-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="dhead">Akun Debit</th>
                                <th class="dhead">Debit</th>
                                <th class="dhead">Akun Kredit</th>
                                <th class="dhead">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                              
                                <td>
                                    <input type="hidden" name="id_akun_debit" value="{{ $akun_biaya }}">
                                    <select id="" class="select2_add" disabled>
                                        @foreach ($akun as $a)
                                            <option value="{{ $a->id_akun }}"
                                                {{ $a->id_akun == $akun_biaya ? 'SELECTED' : '' }}>
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
                                    <input type="hidden" name="id_akun_kredit" value="{{ $akun_kredit }}">
                                    <select name="" id="" class="select2_add" disabled>
                                        @foreach ($akun as $a)
                                            <option value="{{ $a->id_akun }}"
                                                {{ $a->id_akun == $akun_kredit ? 'SELECTED' : '' }}>
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
                    <table class="float-end">
                        <td>Pencarian :</td>
                        <td><input type="text" autofocus id="pencarian" class="form-control mb-2 float-end"></td>
                    </table>
                    <table class="table table-striped table-bordered" id="tblSearch">
                        <thead>

                            <tr>
                                <th class="dhead" width="15%">Nama Pakan</th>
                                <th class="dhead text-end" width="10%">Stok Program</th>
                                <th class="dhead text-end" width="10%">Stok Aktual</th>
                                <th class="dhead " width="5%">Satuan</th>
                                <th class="dhead text-end" width="10%">Selisih</th>
                                <th class="dhead text-end" width="10%">Rp Satuan</th>
                                <th class="dhead text-end" width="10%">Total Rp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pakan as $no => $p)
                                @if ($p->pcs + $p->pcs_sisa < 1)
                                    @php
                                        continue;
                                    @endphp
                                @endif
                                <tr>
                                    <td>{{ $p->nm_produk }}</td>
                                    <td align="right">
                                        {{ number_format($p->pcs + $p->pcs_sisa, 0) }}
                                        <input type="hidden" class="pcs{{ $no }}"
                                            value="{{ $p->pcs + $p->pcs_sisa }}">
                                        <input type="hidden" value="{{ $p->id_produk }}" name="id_pakan[]">
                                    </td>
                                    <td>
                                        <input type="text" value="{{ number_format($p->pcs + $p->pcs_sisa, 0) }}"
                                            class="form-control stok_aktual stok_aktual{{ $no }} text-end"
                                            value="0" count="{{ $no }}">
                                        <input type="hidden" class="stok_aktual_biasa{{ $no }}"
                                            value="0" value="{{ number_format($p->pcs + $p->pcs_sisa, 0) }}"
                                            name="stok_aktual[]">
                                    </td>
                                    <td>{{ $p->nm_satuan }}</td>
                                    <td class="text-end selisih{{ $no }}"></td>
                                    <td class="text-end">
                                        Rp.
                                        {{ number_format(($p->ttl_rp + $p->ttl_rp_sisa) / ($p->pcs + $p->pcs_sisa), 0) }}
                                        <input type="hidden" name="rp_satuan[]"
                                            class="rp_satuan{{ $no }}"
                                            value="{{ ($p->ttl_rp + $p->ttl_rp_sisa) / ($p->pcs + $p->pcs_sisa) }}">
                                        <input type="hidden" class="ttl_rp ttl_rp{{ $no }}"
                                            name="" id="" value="0">
                                    </td>
                                    <td class="text-end total_rp{{ $no }}"></td>
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
            pencarian('pencarian', 'tblSearch')
            $(document).ready(function() {
                $(document).on("keyup", ".stok_aktual", function() {
                    var count = $(this).attr('count');

                    var input = $(this).val();
                    input = input.replace(/[^\d\,]/g, "");
                    input = input.replace(".", ",");
                    input = input.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

                    if (input === "") {
                        $(this).val("");
                        $('.stok_aktual_biasa' + count).val(0)
                    } else {
                        $(this).val(input);
                        input = input.replaceAll(".", "");
                        input2 = input.replace(",", ".");
                        $('.stok_aktual_biasa' + count).val(input2)

                    }


                    var stok_aktual = parseFloat(input2);
                    var pcs = $(".pcs" + count).val();
                    var rp_satuan = $(".rp_satuan" + count).val();

                    var selisih = parseFloat(pcs) - parseFloat(stok_aktual);
                    var total_rp = parseFloat(selisih) * parseFloat(rp_satuan);
                    console.log(`${total_rp} : sel = ${selisih} : rpsa = ${rp_satuan}`)
                    var selisih_total = selisih.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    var total_rp_total = total_rp.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    // Menghilangkan simbol "Rp" dari string
                    selisih_total = selisih_total.replace("Rp", "");

                    $(".selisih" + count).text(selisih_total);
                    $(".total_rp" + count).text(total_rp_total);
                    $(".ttl_rp" + count).val(total_rp);


                    var total_debit = 0;
                    $(".ttl_rp").each(function() {
                        total_debit += parseFloat($(this).val());
                    });

                    var totalRupiah = total_debit.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                    });

                    $('.total').val(totalRupiah);
                    $('.total_biasa').val(total_debit);


                });
                aksiBtn("form");

            });
        </script>
    @endsection
</x-theme.app>
