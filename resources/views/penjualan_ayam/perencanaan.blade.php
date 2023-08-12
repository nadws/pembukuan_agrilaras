<x-theme.app title="{{ $title }}" table="Y" sizeCard="11">

    <x-slot name="cardHeader">
        <div class="row ">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }} </h6>
            </div>
        </div>

    </x-slot>


    <x-slot name="cardBody">
        <style>
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #000000;
                line-height: 36px;
                /* font-size: 12px; */
                width: 170px;

            }
        </style>
        <style>
            .dhead {
                background-color: #435EBE !important;
                color: white;
            }
        </style>
        <form action="{{ route('penjualan_ayam.save_perencanaan') }}" method="post" class="save_jurnal">
            @csrf

            <section class="row">
                {{-- <div class="col-lg-2 col-6">
                    <label for="">Tanggal</label>
                    <input type="date" class="form-control" name="tgl" value="{{date('Y-m-d')}}">
                </div>
                <div class="col-lg-2 col-6">
                    <label for="">No Nota</label>
                    <input type="text" class="form-control nota_bk" name="no_nota" value="PA-{{$nota}}" readonly>
                </div>
                <div class="col-lg-12">
                    <hr style="border: 1px solid black">
                </div> --}}
                <div class="col-lg-12">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="dhead">Tanggal</th>
                                <th class="dhead">No Nota</th>
                                <th class="dhead">Pembayaran</th>
                                <th class="dhead">Keterangan</th>
                                <th class="dhead" style="text-align: right">Total Rp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $total = 0;
                            @endphp
                            @foreach ($id_jurnal as $no => $n)
                            @php
                            $invoice = DB::selectOne("SELECT a.id_jurnal, a.id_akun, a.tgl, a.no_nota, b.nm_akun, a.ket,
                            a.debit
                            FROM jurnal as a
                            left join akun as b on b.id_akun = a.id_akun
                            where a.id_buku = '6' and a.id_jurnal = '$n' and a.setor ='T' and a.debit != '0'
                            group by a.no_nota
                            order by a.tgl ASC
                            ");
                            $total += $invoice->debit;
                            $id_akun = $invoice->id_akun;
                            @endphp
                            <tr>
                                <td>{{tanggal($invoice->tgl)}}</td>
                                <td>{{$invoice->no_nota}}</td>
                                <td>{{$invoice->nm_akun}}</td>
                                <td>{{$invoice->ket}}</td>
                                <td align="right">{{number_format($invoice->debit,0)}}</td>
                            </tr>
                            <input type="hidden" name="id_jurnal[]" value="{{$invoice->id_jurnal}}">
                            <input type="hidden" name="no_nota_jurnal[]" value="{{$invoice->no_nota}}">
                            <input type="hidden" name="nominal[]" value="{{$invoice->debit}}">
                            <input type="hidden" name="id_akun_pem[]" value="{{$invoice->id_akun}}">

                            @endforeach


                        </tbody>
                        <tfoot>
                            <th colspan="4">Total</th>
                            <th style="text-align: right">Rp {{number_format($total,0)}}</th>
                        </tfoot>


                    </table>

                </div>


            </section>
            <div class="row">
                <div class="col-lg-3">
                    <label for="">Tanggal</label>
                    <input type="date" class="form-control" name="tgl" value="{{date('Y-m-d')}}">
                    <input type="hidden" class="form-control" name="id_akun1" value="{{$id_akun}}">
                </div>
                <div class="col-lg-3">
                    <label for="">Pilih Akun Debit</label>
                    <Select class="select2_add" name="id_akun" required>
                        <option value="">-Pilih Akun-</option> 
                        @foreach ($akun as $a)
                        <option value="{{$a->id_akun}}">{{$a->nm_akun}}</option>
                        @endforeach
                    </Select>
                </div>
                <div class="col-lg-3">
                    <label for="">Keterangan</label>
                    <input type="text" class="form-control" name="ket" value="PA-{{$nota}}">
                    <input type="hidden" name="no_nota" value="PA-{{$nota}}">
                </div>
                <div class="col-lg-3">
                    <label for="">Total Setor</label>
                    <input type="text" class="form-control " style="text-align: right"
                        value="Rp {{number_format($total,0,',','.')}}" readonly>

                    <input type="hidden" value="{{$total}}" name="total_setor">
                    <input type="hidden" value="3" name="id_akun_kredit">
                    <input type="hidden" value="PA-{{$nota}}" name="no_nota">
                </div>
            </div>
    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary button-save">Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        <a href="{{ route('penjualan_ayam.penyetoran') }}" class="float-end btn btn-outline-primary me-2">Batal</a>
        </form>
    </x-slot>



    @section('scripts')
    <script>
        $(document).ready(function () {
            $("form").on("keypress", function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    return false;
                }
            });
            aksiBtn("form");
        });
    </script>

    @endsection
</x-theme.app>