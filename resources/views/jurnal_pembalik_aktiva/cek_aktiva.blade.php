<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">

    <x-slot name="cardHeader">
        <div class="row justify-content-end">

            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}</h6>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('controlflow') }}" class="btn btn-primary float-end"><i class="fas fa-home"></i></a>
            </div>

        </div>

    </x-slot>
    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #000000;
            line-height: 36px;
            /* font-size: 12px; */
            width: 150px;

        }
    </style>

    <x-slot name="cardBody">
        <form action="{{ route('save_jurnal_aktiva') }}" method="post" class="save_jurnal">
            @csrf
            <input type="hidden" name="id_buku" value="{{ $id_buku }}">
            <section class="row">
                <div class="col-lg-3">
                    <label for="">Tanggal</label>
                    <input type="date" class="form-control" name="tgl" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-lg-3">
                    <label for="">No Urut Jurnal Umum</label>
                    <input type="text" class="form-control" name="no_nota" value="JU-{{ $max }}" readonly>
                </div>
                {{-- @if ($id_buku == '12')
                <div class="col-lg-3">
                    <label for="">Proyek</label>
                    <select name="id_proyek" id="select2" class="proyek proyek_berjalan">

                    </select>
                </div>
                @endif --}}

                {{-- <div class="col-lg-3">
                    <label for="">Suplier</label>
                    <select name="id_suplier" class="select2suplier form-control">
                        <option value="">- Pilih Suplier -</option>
                        @foreach ($suplier as $p)
                        <option value="{{ $p->id_suplier }}">{{ $p->nm_suplier }}</option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="col-lg-12">
                    <hr style="border: 1px solid black">
                </div>
                <div class="col-lg-12">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th width="2%">#</th>
                                <th width="14%">Akun</th>
                                <th width="10%">Sub Akun</th>
                                <th width="18%">Keterangan</th>
                                <th width="12%" style="text-align: right;">Debit</th>
                                <th width="12%" style="text-align: right;">Kredit</th>
                                {{-- <th width="12%" style="text-align: right;">Saldo</th> --}}
                                {{-- <th width="5%">Aksi</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="baris1">
                                <td style="vertical-align: top;">
                                    {{-- <button type="button" data-bs-toggle="collapse" href=".join1"
                                        class="btn rounded-pill " count="1"><i class="fas fa-angle-down"></i>
                                    </button> --}}
                                </td>
                                <td style="vertical-align: top;">
                                    <input type="hidden" name="id_akun[]" value="{{$akun_gantung->id_akun}}">
                                    <input type="text" class="form-control" value="{{$akun_gantung->nm_akun}} "
                                        readonly>
                                    <div class="">
                                        <label for="" class="mt-2 ">Urutan Pengeluaran</label>
                                        <input type="text" class="form-control " name="no_urut[]">
                                    </div>

                                </td>
                                <td style="vertical-align: top;">
                                    <select name="id_post" id="" class="select2_add post_center post_center1" count="1">
                                        <option value="">-Pilih Post-</option>
                                        @foreach ($post as $p)
                                        <option value="{{$p->id_post_center}}">{{$p->nm_post}}</option>
                                        @endforeach

                                    </select>
                                </td>

                                <td style="vertical-align: top;">
                                    <input type="text" name="keterangan[]" class="form-control"
                                        style="vertical-align: top" placeholder="nama barang, qty, @rp">

                                </td>
                                <td style="vertical-align: top;">
                                    <input type="text" class="form-control debit_rupiah text-end" value="Rp 0" count="1"
                                        readonly>
                                    <input type="hidden" class="form-control debit_biasa debit_biasa1" value="0"
                                        name="debit[]">
                                    <p class="peringatan_debit1 mt-2 text-danger" hidden>Data yang dimasukkan salah
                                        harap cek kembali !!
                                    </p>
                                </td>
                                <td style="vertical-align: top;">
                                    <input type="text" class="form-control kredit_rupiah kredit_rupiah1 text-end"
                                        value="Rp 0" count="1" readonly>
                                    <input type="hidden" class="form-control kredit_biasa kredit_biasa1" value="0"
                                        name="kredit[]">
                                    <input type="hidden" class="form-control id_klasifikasi1" value="0"
                                        name="id_klasifikasi[]">
                                    <p class="peringatan1 mt-2 text-danger" hidden>Apakah anda yakin ingin memasukkan
                                        biaya disebelah kredit
                                    </p>
                                </td>
                                {{-- <td style="vertical-align: top;">
                                    <p class="saldo_akun1 text-end" style="font-size: 12px"></p>
                                </td> --}}
                                {{-- <td style="vertical-align: top;">
                                    <button type="button" class="btn rounded-pill remove_baris" count="1"><i
                                            class="fas fa-trash text-danger"></i>
                                    </button>
                                </td> --}}
                            </tr>


                            <tr class="baris2">
                                <td style="vertical-align: top;">
                                    {{-- <button type="button" data-bs-toggle="collapse" href=".join2"
                                        class="btn rounded-pill " count="1"><i class="fas fa-angle-down"></i>
                                    </button> --}}
                                </td>
                                <td style="vertical-align: top;">
                                    <input type="hidden" name="id_akun[]" value="{{$akun_aktiva->id_akun}}">
                                    <input type="text" class="form-control" value="{{$akun_aktiva->nm_akun}} " readonly>
                                    <div class="">
                                        <label for="" class="mt-2 ">Urutan Pengeluaran</label>
                                        <input type="text" class="form-control " name="no_urut[]">
                                    </div>
                                </td>
                                <td style="vertical-align: top;">
                                    {{-- <select name="id_post[]" id="" class="select2_add post2">

                                    </select> --}}
                                </td>


                                <td style="vertical-align: top;">
                                    <input type="text" name="keterangan[]" class="form-control"
                                        placeholder="nama barang, qty, @rp">

                                </td>
                                <td style="vertical-align: top;">
                                    <input type="text" class="form-control debit_rupiah2 debit_rupiah text-end"
                                        value="Rp 0" count="2" readonly>
                                    <input type="hidden" class="form-control debit_biasa debit_biasa2" value="0"
                                        name="debit[]">
                                    <p class="peringatan_debit2 mt-2 text-danger" hidden>Data yang dimasukkan salah
                                        harap cek kembali !!
                                    </p>

                                </td>
                                <td style="vertical-align: top;">
                                    <input type="text" class="form-control kredit_rupiah text-end" value="Rp 0"
                                        count="2" readonly>
                                    <input type="hidden" class="form-control kredit_biasa kredit_biasa2" value="0"
                                        name="kredit[]">
                                    <input type="hidden" class="form-control id_klasifikasi2" value="0"
                                        name="id_klasifikasi[]">
                                    <p class="peringatan2 mt-2 text-danger" hidden>Apakah anda yakin ingin memasukkan
                                        biaya disebelah kredit
                                    </p>
                                </td>
                                {{-- <td style="vertical-align: top;">
                                    <p class="saldo_akun2 text-end" style="font-size: 12px"></p>
                                </td> --}}
                                {{-- <td style="vertical-align: top;">
                                    <button type="button" class="btn rounded-pill remove_baris" count="2"><i
                                            class="fas fa-trash text-danger"></i>
                                    </button>
                                </td> --}}
                            </tr>
                        </tbody>



                    </table>
                </div>
                <div class="col-lg-6">

                </div>
                <div class="col-lg-6">
                    <hr style="border: 1px solid blue">
                    <table class="" width="100%">
                        <tr>
                            <td width="20%">Total</td>
                            <td width="40%" class="total" style="text-align: right;">Rp.0</td>
                            <td width="40%" class="total_kredit" style="text-align: right;">Rp.0</td>
                        </tr>
                        <tr>
                            <td class="cselisih" colspan="2">Selisih</td>
                            <td style="text-align: right;" class="selisih cselisih">Rp.0</td>
                        </tr>
                    </table>

                </div>
            </section>

    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary button-save" hidden>Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        <a href="{{ route('jurnal') }}" class="float-end btn btn-outline-primary me-2">Batal</a>
        </form>
    </x-slot>
</x-theme.app>