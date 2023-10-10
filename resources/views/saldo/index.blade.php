<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <h6 class="float-start mt-1">{{ $title }} : {{ tanggal($tgl_saldo->tgl ?? '2023-01-01') }}</h6>
    </x-slot>

    <x-slot name="cardBody">
        <form id="tambah_saldo">
            @csrf
            <section class="row">
                <div class="col-lg-8"></div>
                <div class="col-lg-4 mb-2">
                    <table class="float-end">
                        <td>Search :</td>
                        <td><input type="text" id="pencarian" class="form-control float-end"></td>
                    </table>


                </div>
                <table class="table table-hover" id="tablealdi">
                    <thead>
                        <tr>
                            <th width="5">#</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th style="text-align: right">Debit</th>
                            <th style="text-align: right">Kredit</th>
                            {{-- <th style="text-align: right">Saldo</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $t_debit = 0;
                            $t_kredit = 0;
                        @endphp
                        @foreach ($akun as $no => $a)
                            @php
                                $t_debit += $a->debit;
                                $t_kredit += $a->kredit;
                            @endphp
                            <tr>
                                <td>{{ $no + 1 }}</td>
                                <td>{{ $a->kode_akun }} </td>
                                <td>
                                    {{ ucwords(strtolower($a->nm_akun)) }}
                                    <input type="hidden" name="id_akun[]" value="{{ $a->id_akun }}">
                                </td>
                                <td>
                                    <input type="text"
                                        class="form-control text-end rp-nohide  rp-nohide{{ $no + 1 }}"
                                        count="{{ $no + 1 }}"
                                        value="Rp. {{ number_format($a->debit, 2, ',', '.') }}"
                                        {{ $a->penutup == 'Y' ? 'disabled' : '' }}>
                                    <input type="hidden" name="debit[]"
                                        class="form-control text-end rp-hide rp-hide{{ $no + 1 }}"
                                        value="{{ empty($a->debit) ? '0' : $a->debit }}">
                                </td>
                                <td>
                                    <input type="text"
                                        class="form-control text-end rp-nohides rp-nohides{{ $no + 1 }}"
                                        count="{{ $no + 1 }}"
                                        value="Rp. {{ number_format($a->kredit, 2, ',', '.') }}"
                                        {{ $a->penutup == 'Y' ? 'disabled' : '' }}>
                                    <input type="hidden" name="kredit[]"
                                        class="form-control text-end rp-hides rp-hides{{ $no + 1 }}"
                                        value="{{ empty($a->kredit) ? '0' : $a->kredit }}">
                                </td>
                                {{-- <td>
                                {{number_format($a->debit - $a->kredit,0)}}
                            </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th></th>
                            <th></th>
                            <th class="text-end">
                                <p class="totalDebit">Rp. {{ number_format($t_debit, 2, ',', '.') }}</p>
                            </th>
                            <th class="text-end">
                                <p class="totalKredit">Rp. {{ number_format($t_kredit, 2, ',', '.') }}
                                </p>
                                {{-- <p class="totalKredit">Rp. {{ number_format($t_debit - $t_kredit, 2, ',', '.') }}
                                </p> --}}
                            </th>
                            <input type="text" style="display: none" class="totalDebithide"
                                value="{{ empty($t_debit) ? '0' : $t_debit }}">
                            <input type="text" style="display: none" class="totalKredithide"
                                value="{{ empty($t_kredit) ? '0' : $t_kredit }}">
                        </tr>
                    </tfoot>
                </table>
            </section>

            <div class="modal fade" id="myModal" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg-max" role="document">
                    <div class="modal-content ">
                        <div class="modal-header bg-costume">
                            <h5 class="modal-title" id="exampleModalLabel">Saldo Awal</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h5>Silahkan konfirmasi untuk menerbitkan saldo awal dengan kondisi di bawah ini:</h5>
                            <br>
                            <p>
                                Total debit dan kredit harus sama. Total selisih berjumlah <span
                                    class="selisih text-danger"></span>
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
    </x-slot>
    <x-slot name="cardFooter">
        <button type="submit" class="float-end btn btn-primary button-save">Simpan</button>
        <button class="float-end btn btn-primary btn_save_loading" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm " role="status" aria-hidden="true"></span>
            Loading...
        </button>
        </form>
    </x-slot>
    @section('scripts')
        <script>
            convertRpKoma('rp-nohide', 'rp-hide', 'rp-hides', 'totalDebit')
            convertRpKoma('rp-nohides', 'rp-hides', 'rp-hide', 'totalKredit')
            // convertRp('rp-nohides', 'rp-hides','totalKredit','totalKredithide')

            $(document).ready(function() {
                pencarian('pencarian', 'tablealdi')
                $(document).on('submit', '#tambah_saldo', function(event) {
                    event.preventDefault();

                    var debit = $(".totalDebithide").val();
                    var kredit = $(".totalKredithide").val();
                    var total = parseFloat(debit) - parseFloat(kredit);
                    var save = $("#tambah_saldo").serialize();
                    // if (debit == kredit) {
                    $.ajax({
                        url: "/saveSaldo?" + save,
                        type: 'get',
                        success: function(data) {
                            window.location = "/saldo_awal";
                        }
                    });
                    window.location = "/saldo_awal";
                    // } else {
                    //     var number_total = total.toFixed(0).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
                    //     var rupiah_total = "Rp. " + number_total;
                    //     $('.selisih').text(rupiah_total);
                    //     $('#myModal').modal('show')
                    // }



                });

            });
        </script>
    @endsection
</x-theme.app>
