<x-theme.app title="{{ $title }}" table="Y" sizeCard="12">
    <x-slot name="cardHeader">
        <h6 class="float-start">Aktiva</h6>
        <div class="row justify-content-end">
            <div class="col-lg-6">
                @if (!empty($create))
                    <x-theme.button modal="T" href="{{ route('aktiva.add') }}" icon="fa-plus" addClass="float-end"
                        teks="Buat Baru" />
                @endif
                <a href="{{ route('aktiva.import.template') }}" class="btn btn-outline-success btn-sm float-end me-2"><i class="fas fa-download"></i> Format Import</a>
                <button type="button" class="btn btn-success btn-sm float-end me-2" data-bs-toggle="modal" data-bs-target="#importAktiva">
                    <i class="fas fa-file-import me-1"></i> Import Data
                </button>
                @if (!empty($print))
                    <x-theme.button modal="Y" idModal="view" icon="fa-print" addClass="float-end" teks="Print" />
                @endif
                {{--
                <x-theme.button modal="T" href="{{ route('print_aktiva') }}" icon="fa-print" addClass="float-end"
                    teks="Print" /> --}}
                <x-theme.akses :halaman="$halaman" route="aktiva" />

            </div>
        </div>
    </x-slot>
    <x-slot name="cardBody">
        @if ($errors->has('file_aktiva'))
            <div class="alert alert-danger" style="white-space: pre-line">{{ $errors->first('file_aktiva') }}</div>
        @endif
        <div class="alert alert-info py-2">Aktiva manual dan aktiva dari jurnal pembalik ditampilkan dalam satu daftar.</div>
        <section class="row">
            <table class="table table-hover" id="table1">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>Tanggal Perolehan</th>
                        <th>Nama</th>
                        <th>Kelompok</th>
                        <th class="text-end">Nilai Perolehan</th>
                        <th class="text-end">Penysutan Perbulan</th>
                        <th class="text-end">Akumulasi Penyusutan</th>
                        <th class="text-end">Nilai Buku</th>
                        <th>Sisa Umur</th>
                        {{-- <th>Aksi</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aktiva as $no => $a)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td>{{ date('d-m-Y', strtotime($a->tgl)) }}</td>
                            <td>{{ $a->nm_aktiva }}</td>
                            <td>{{ $a->nm_kelompok }}</td>
                            <td align="right">Rp {{ number_format($a->h_perolehan, 0) }}</td>
                            <td align="right">Rp {{ number_format($a->biaya_depresiasi, 0) }}</td>
                            <td align="right">Rp {{ number_format($a->beban, 0) }}</td>
                            <td align="right">Rp {{ number_format($a->h_perolehan - $a->beban, 0) }}</td>
                            <td>{{ !empty($a->sisa_umur_bulan) ? $a->sisa_umur_bulan . ' bulan' : '-' }}</td>
                            {{-- <td>
                                <div class="btn-group" role="group">
                                    <span class="btn btn-sm" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v text-primary"></i>
                                    </span>
                                    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                        @php
                                            $emptyKondisi = [$edit, $delete, $detail];
                                        @endphp
                                        <x-theme.dropdown_kosong :emptyKondisi="$emptyKondisi" />

                                        @if (!empty($edit))
                                            <li><a class="dropdown-item text-primary edit_akun" href=""><i
                                                        class="me-2 fas fa-pen"></i>Edit</a>
                                            </li>
                                        @endif

                                        @if (!empty($delete))
                                            <li>
                                                <a class="dropdown-item  text-danger delete_nota" no_nota=""
                                                    href="#" data-bs-toggle="modal" data-bs-target="#delete"><i
                                                        class="me-2 fas fa-trash"></i>Delete
                                                </a>
                                            </li>
                                        @endif

                                        @if (!empty($detail))
                                            <li><a class="dropdown-item  text-info detail_nota" href="#"
                                                    no_nota="" href="#" data-bs-toggle="modal"
                                                    data-bs-target="#detail"><i
                                                        class="me-2 fas fa-search"></i>Detail</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td> --}}
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </section>

        <div class="modal fade" id="importAktiva" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('aktiva.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Import Data Aktiva</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Pilih file CSV atau Excel</label>
                            <input type="file" name="file_aktiva" class="form-control" accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted d-block mt-2">Maksimal 5 MB. Gunakan susunan kolom dari Format Import terbaru.</small>
                            <a href="{{ route('aktiva.import.template') }}" class="btn btn-link px-0 mt-2">
                                <i class="fas fa-download me-1"></i> Unduh Format Import
                            </a>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-file-import me-1"></i> Import Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <form action="{{ route('print_aktiva') }}" method="get">
            <x-theme.modal title="Pilih Tahun" idModal="view">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">Tahun</label>
                        <select name="tahun" id="selectView">
                            @foreach ($tahun as $t)
                                <option value="{{ $t->tgl }}">{{ $t->tahun }}</option>
                            @endforeach
                        </select>

                    </div>
                </div>

            </x-theme.modal>
        </form>






    </x-slot>
    @section('scripts')
        <script>
            $(document).ready(function() {


            });
        </script>
    @endsection
</x-theme.app>
