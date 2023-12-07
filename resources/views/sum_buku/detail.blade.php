<x-theme.app title="{{ $title }} " table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="row justify-content-end">
            <div class="col-lg-6">
                <h6 class="float-start mt-1">{{ $title }} : {{ ucwords(strtolower($nm_akun->nm_akun)) }}
                    ({{ tanggal($tgl1) }}~{{ tanggal($tgl2) }})</h6>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('summary_buku_besar.export_detail', ['id_akun' => $id_akun, 'id_klasifikasi' => $id_klasifikasi, 'tgl1' => $tgl1, 'tgl2' => $tgl2]) }}"
                    class="float-end btn   btn-success me-2"><i class="fas fa-file-excel"></i> Export</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-hover table-striped" id="table1">
                <thead>
                    @php
                        $ttlDebit = 0;
                        $ttlKredit = 0;
                        $ttlSaldo = 0;

                        foreach ($detail as $d) {
                            $ttlDebit += $d->debit;
                            $ttlKredit += $d->kredit;
                            $ttlSaldo += $d->debit - $d->kredit;
                        }
                    @endphp
                    <tr>
                        <th width="5">#</th>
                        <th style="white-space: nowrap;">No Urut JU</th>
                        <th style="white-space: nowrap;">No Urut Akun</th>
                        <th>Tanggal</th>
                        <th>Nama Akun Lawan</th>
                        <th>Sub Akun</th>
                        <th>Keterangan</th>
                        <th style="text-align: right">Debit <br> ({{ number_format($ttlDebit, 2) }})</th>
                        <th style="text-align: right">Kredit <br> ({{ number_format($ttlKredit, 2) }})</th>
                        <th style="text-align: right">Saldo <br> ({{ number_format($ttlSaldo, 2) }})</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $saldo = 0;
                    @endphp
                    @foreach ($detail as $n => $d)
                        @php
                            $saldo += $d->debit - $d->kredit;
                        @endphp
                        <tr>
                            <td>{{ $n + 1 }}</td>
                            <td>{{ $d->no_nota }}</td>
                            <td>{{ $d->no_cfm }}</td>
                            <td class="nowrap">{{ tanggal($d->tgl) }}</td>
                            <td>{{ $d->saldo == 'Y' ? 'Saldo Awal' : ucwords(strtolower($d->nm_akun)) }}</td>
                            <td>{{ $d->nm_post }}</td>
                            <td>{{ $d->ket }}</td>
                            <td style="text-align: right">{{ number_format($d->debit, 2) }}</td>
                            <td style="text-align: right">{{ number_format($d->kredit, 2) }}</td>
                            <td style="text-align: right">{{ number_format($saldo, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </section>

    </x-slot>

</x-theme.app>
