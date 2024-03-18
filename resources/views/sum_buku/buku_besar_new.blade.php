<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }}: {{ tanggal($tgl1) }}~{{ tanggal($tgl2) }}</h6>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('controlflow') }}" class="btn btn-primary float-end"><i class="fas fa-home"></i></a>
                <x-theme.btn_filter />
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            @php
                $ttldebit = 0;
                $ttlkredit = 0;
            @endphp
            @foreach ($buku as $b)
                @php
                    $ttldebit += $b->debit + $b->debit_saldo;
                    $ttlkredit += $b->kredit + $b->kredit_saldo;
                @endphp
            @endforeach

            <table class="table table-hover table-striped" id="table1">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Kode Akun</th>
                        <th width="200px">Akun</th>
                        <th style="text-align: right">Debit ({{ number_format($ttldebit, 0) }})</th>
                        <th style="text-align: right">Kredit ({{ number_format($ttlkredit, 0) }})</th>
                        <th style="text-align: right">Saldo ({{ number_format($ttldebit - $ttlkredit, 0) }})</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($buku as $no => $b)
                        <tr>
                            <td class="fw-bold">{{ $no + 1 }}</td>
                            <td class="fw-bold">{{ $b->nm_subklasifikasi }}</td>
                            <td class="fw-bold">
                                <i style="cursor: pointer"
                                    class="fas fa-sort-down text-primary fa-2x showDetail showDetail{{ $b->id_klasifikasi }}"
                                    id_klasifikasi="{{ $b->id_klasifikasi }}" tgl1="{{ $tgl1 }}"
                                    tgl2="{{ $tgl2 }}">
                                </i>
                                <i style="cursor: pointer; display: none;"
                                    class="fas fa-sort-up text-primary fa-2x hideDetail hideDetail{{ $b->id_klasifikasi }}"
                                    id_klasifikasi="{{ $b->id_klasifikasi }}"></i>
                            </td>
                            <td class="text-end fw-bold">{{ number_format($b->debit + $b->debit_saldo, 0) }}</td>
                            <td class="text-end fw-bold">{{ number_format($b->kredit + $b->kredit_saldo, 0) }}</td>
                            <td class="text-end fw-bold">
                                {{ number_format($b->debit + $b->debit_saldo - ($b->kredit + $b->kredit_saldo), 0) }}
                            </td>
                        </tr>
                <tbody class="loadDetail{{ $b->id_klasifikasi }}"></tbody>
                @endforeach

                </tbody>
            </table>
        </section>


    </x-slot>
    @section('scripts')
        <script>
            $(document).ready(function() {
                $('.showDetail').click(function(e) {
                    e.preventDefault();
                    var id_klasifikasi = $(this).attr('id_klasifikasi');
                    var tgl1 = $(this).attr('tgl1');
                    var tgl2 = $(this).attr('tgl2');

                    $.ajax({
                        type: "get",
                        url: "{{ route('summary_buku_besar.loadDetail') }}",
                        data: {
                            id_klasifikasi: id_klasifikasi,
                            tgl1: tgl1,
                            tgl2: tgl2
                        },
                        success: function(response) {
                            $('.loadDetail' + id_klasifikasi).html(response);
                            $('.showDetail' + id_klasifikasi).hide();
                            $('.hideDetail' + id_klasifikasi).show();
                        }
                    });
                });
                $('.hideDetail').click(function(e) {
                    e.preventDefault();
                    var id_klasifikasi = $(this).attr('id_klasifikasi');
                    $('.loadDetail' + id_klasifikasi).html('');
                    $('.showDetail' + id_klasifikasi).show();
                    $('.hideDetail' + id_klasifikasi).hide();

                });
            });
        </script>
    @endsection

</x-theme.app>
