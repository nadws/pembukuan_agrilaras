<div class="row">
    <div class="col-lg-12">
        <h5>Pembelian Pakan</h5>
        {{-- <div class="row mb-2">
            <div class="col-lg-3">
                <h6>Stok Pakan</h6>
            </div>
            <div class="col-lg-9">
                <a href="{{ route('history_perencanaan_pakan', ['kategori' => 'pakan']) }}"
                    class="btn btn-primary btn-sm float-end"><i class="fas fa-history"></i> History <span
                        class="badge bg-danger">{{ empty($total_pakan->total) ? '0' : $total_pakan->total }}</span></a>
            </div>
            <div class="col-lg-12">
                <br>
            </div>
            <div class="col-lg-7">

            </div>
            <div class="col-lg-5">
                <input id="pencarianPakan" placeholder="Pencarian" type="text" class="form-control">
            </div>
        </div>
        <table class="table table-bordered table-hover" id="tablePakan">
            <thead>
                <tr>
                    <th class="dhead">Nama Pakan</th>
                    <th class="dhead" style="text-align: right">Stok</th>
                    <th class="dhead" style="text-align: center">Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pakan as $p)
                    @if ($p->pcs_debit - $p->pcs_kredit < 1)
                        @php continue; @endphp
                    @endif
                    <tr>
                        <td><a href="#" onclick="event.preventDefault();" class="history_stok"
                                id_pakan="{{ $p->id_pakan }}">{{ ucwords(strtolower($p->nm_produk)) }}
                            </a>
                        </td>
                        <td style=" text-align: right">
                            {{ number_format($p->pcs_debit - $p->pcs_kredit, 0) }}
                        </td>
                        <td style="text-align: center">{{ $p->nm_satuan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br> --}}
        <div class="row">
            <div class="col-lg-3">

            </div>
            <div class="col-lg-5">
                <input id="pencarianPakan" placeholder="Pencarian" type="text" class="form-control">
            </div>
            <div class="col-lg-4">
                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#tambahdatahargapakan"
                    class="btn btn-primary btn-sm btn-block">Tambah Data</a>
            </div>
        </div>

        <form action="{{ route('save_stok_pakan') }}" method="post" id="save_hrga_pakan">
            @csrf


            <div class="modal fade" id="tambahdatahargapakan" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Tambah Harga Pakan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <label for="">Tanggal</label>
                                    <input type="date" name="tgl[]" id="" class="form-control">
                                </div>
                                <div class="col-lg-2">
                                    <label for="">Pakan</label>
                                    <select name="id_pakan[]" id="" class="form-control">
                                        <option value="">-Pilih Pakan-</option>
                                        @foreach ($pakan_table as $p)
                                            <option value="{{ $p->id_produk }}">{{ $p->nm_produk }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <label for="">Kg</label>
                                    <input type="text" name="sak[]" class="form-control">
                                </div>
                                <div class="col-lg-2">
                                    <label for="">total rp</label>
                                    <input type="text" name="total_rp[]" class="form-control">
                                </div>
                                <div class="col-lg-2">
                                    <label for="">rp lain-lain</label>
                                    <input type="text" name="rp_lain[]" class="form-control">
                                </div>
                                <div class="col-lg-1">
                                    <label for="">Aksi</label>
                                    <button type="button" class="btn btn-sm btn-success tambah_hrga_pakan"><i
                                            class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <div id="tbh_baris_hrga_pakan"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary button-save">Simpan</button>
                            <button class="float-end btn btn-primary button-save-modal-loading" type="button" disabled
                                hidden>
                                <span class="spinner-border spinner-border-sm " role="status"
                                    aria-hidden="true"></span>
                                Loading...
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <br>
        <table class="table table-bordered table-hover" id="tablePakan">
            <thead>
                <tr>
                    <th class="dhead">Nama Pakan</th>
                    <th class="dhead">Tanggal</th>
                    <th class="dhead" style="text-align: right">Ttl Kg</th>
                    <th class="dhead" style="text-align: right">Total Rp</th>
                    <th class="dhead" style="text-align: right">Rp lain-lain</th>
                    <th class="dhead" style="text-align: center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hrga_pakan as $p)
                    <tr>
                        <td>{{ $p->nm_produk }}</td>
                        <td>{{ tanggal($p->tgl) }}</td>
                        <td style=" text-align: right">
                            {{ number_format($p->ttl_gr, 0) }}
                        </td>
                        <td style=" text-align: right">
                            {{ number_format($p->ttl_rp, 0) }}
                        </td>
                        <td style=" text-align: right">
                            {{ number_format($p->rp_lain, 0) }}
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="btn btn-sm btn-warning edit_hrga_pakan"
                                data-bs-toggle="modal" data-bs-target="#editdatahargapakan"
                                id_harga_pakan="{{ $p->id_harga_pakan }}"><i class="fas fa-edit"></i></a>

                            <a href="{{ route('hapus_stok_pakan', ['id_harga_pakan' => $p->id_harga_pakan]) }}"
                                onclick="alert('Anda yakin ingin menghapus data ini ?')"
                                class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></a>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        <form action="{{ route('edit_stok_pakan') }}" method="post" id="edit_hrga_pakan">
            @csrf


            <div class="modal fade" id="editdatahargapakan" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Edit Harga Pakan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <div id="load_edit_harga_pakan"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary button-save"
                                data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary button-save">Simpan</button>
                            <button class="float-end btn btn-primary button-save-modal-loading" type="button"
                                disabled hidden>
                                <span class="spinner-border spinner-border-sm " role="status"
                                    aria-hidden="true"></span>
                                Loading...
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>


    </div>

    {{-- <div class="col-lg-3">
        <div class="row mb-2">

            <div class="col-lg-4">
                <h6>Stok Vitamin</h6>
            </div>
            <div class="col-lg-8">
                <a href="{{ route('history_perencanaan_pakan', ['kategori' => 'vitamin']) }}"
                    class="btn btn-primary btn-sm float-end"><i class="fas fa-history"></i> History <span
                        class="badge bg-danger">{{ empty($total_vitamin->total) ? '0' : $total_vitamin->total }}</span></a>
            </div>
            <div class="col-lg-12">
                <br>
            </div>
            <div class="col-lg-7">

            </div>
            <div class="col-lg-5">
                <input id="pencarianVitamin" placeholder="Pencarian" type="text" class="form-control">
            </div>
        </div>
        <table class="table table-bordered table-hover" id="table-vitamin">
            <thead>
                <tr>
                    <th class="dhead">Nama Vitamin</th>
                    <th class="dhead" style="text-align: right">Stok</th>
                    <th class="dhead" style="text-align: center">Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vitamin as $p)
                    @if ($p->pcs_debit - $p->pcs_kredit < 1)
                        @php continue; @endphp
                    @endif
                    <tr>
                        <td><a href="#" onclick="event.preventDefault();" class="history_stok"
                                id_pakan="{{ $p->id_pakan }}">{{ $p->nm_produk }}</a></td>
                        <td style="text-align: right">{{ number_format($p->pcs_debit - $p->pcs_kredit, 0) }}</td>
                        <td style="text-align: center">{{ $p->nm_satuan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-lg-4">
        <table class="table table-bordered" width="100%">
            <tr>
                <th style="text-align: center">
                    <h6>Rak Telur<br>{{ tanggal(date('Y-m-d')) }}</h6>
                </th>
            </tr>
            <tr>
                <th style="text-align: center; height: 60px;">
                    <h6>{{ number_format($stok_rak->saldo, 0) }} Rak</h6>
                </th>
            </tr>
            <tr>

                <th style="text-align: center" colspan="2">
                    <a href="{{ route('rak.history') }}" class="btn btn-primary btn-sm float-center"><i
                            class="fas fa-history"></i> History Opn<span
                            class="badge bg-danger">{{ empty($total_rak->total) ? '0' : $total_rak->total }}</span></a>

                </th>
            </tr>

        </table>
        <table class="table table-bordered" width="100%">
            <tr>
                <th style="text-align: center" colspan="4">
                    <h6>Pakan & Vitamin<br>{{ tanggal($tgl) }}</h6>
                    <br>
                    <button class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"
                        data-bs-target="#viewnew">View</button>
                </th>
            </tr>
            <tr>
                <th class="dhead">Nama</th>
                <th class="dhead">Kategori</th>
                <th class="dhead text-end">Qty</th>
                <th class="dhead text-end">Total Rp</th>
            </tr>
            <tbody>
                @foreach ($pengeluaran_pakan as $p)
                    <tr>
                        <td>{{ $p->nm_produk }}</td>
                        <td>{{ $p->kategori }}</td>
                        <td class="text-end">{{ number_format($p->qty, 1) }} {{ $p->nm_satuan }}</td>
                        <td class="text-end">{{ number_format($p->ttl_rp, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>


        </table>

        <form id="view_baru_pakan">
            <x-theme.modal btnSave='Y' title="View pakan dan obat" size="moda-md" idModal="viewnew">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">Tanggal</label>
                        <input type="date" name="tgl" class="form-control tgl_view_baru">
                    </div>
                </div>

            </x-theme.modal>
        </form>


    </div> --}}
</div>
