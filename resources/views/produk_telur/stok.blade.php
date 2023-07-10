<div class="row">
    <div class="col-lg-8">


        <div class="row">
            <div class="col-lg-5">
                <h6>Penjualan martadah</h6> <br>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="dhead" width="5">#</th>
                    <th class="dhead">Tipe Penjualan</th>
                    <th class="dhead" style="text-align: right">Total Rp</th>
                    <th class="dhead" style="text-align: right">Yang Sudah Diterima</th>
                    <th class="dhead" style="text-align: right">Sisa</th>
                    <th class="dhead" style="text-align: center">Aksi</th>
                </tr>
            </thead>
            <tbody style="border-color: #435EBE; ">
                <tr>
                    <td>1</td>
                    <td>Penjualan Telur</td>
                    <td align="right">Rp {{number_format($penjualan_cek_mtd->ttl_rp +
                        $penjualan_blmcek_mtd->ttl_rp,0)}}</td>
                    <td align="right">Rp {{number_format($penjualan_cek_mtd->ttl_rp,0)}}</td>
                    <td align="right">Rp {{number_format($penjualan_blmcek_mtd->ttl_rp,0)}}</td>
                    <td align="center">
                        <a href="{{route('penjualan_martadah_cek',['lokasi' => 'mtd'])}}"
                            class="btn btn-primary btn-sm"><i class="fas fa-history"></i>
                            History
                            <span class="badge bg-danger">{{empty($penjualan_blmcek_mtd->jumlah) ? '0' :
                                $penjualan_blmcek_mtd->jumlah}}</span>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Penjualan Umum</td>
                    <td align="right">Rp {{number_format($penjualan_umum_mtd->ttl_rp +
                        $penjualan_umum_blmcek_mtd->ttl_rp ,0)}}</td>
                    <td align="right">Rp {{number_format($penjualan_umum_mtd->ttl_rp,0)}}</td>
                    <td align="right">Rp {{number_format($penjualan_umum_blmcek_mtd->ttl_rp,0)}}</td>
                    <td align="center">
                        <a href="{{route('penjualan_umum_cek')}}" class="btn btn-primary btn-sm">
                            <i class="fas fa-history"></i> History
                            <span class="badge bg-danger">{{empty($penjualan_umum_blmcek_mtd->jumlah) ? '0' :
                                $penjualan_umum_blmcek_mtd->jumlah}}</span>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Opname Telur Martadah</td>
                    <td align="right">Rp {{number_format($opname_cek_mtd->ttl_rp +
                        $opname_blmcek_mtd->ttl_rp,0)}}</td>
                    <td align="right">Rp {{number_format($opname_cek_mtd->ttl_rp,0)}}</td>
                    <td align="right">Rp {{number_format($opname_blmcek_mtd->ttl_rp,0)}}</td>
                    <td align="center">
                        <a href="{{route('bukukan_opname_martadah')}}" class="btn btn-primary btn-sm"><i
                                class="fas fa-history"></i>
                            History
                            <span class="badge bg-danger">{{empty($opname_blmcek_mtd->jumlah) ? '0' :
                                $opname_blmcek_mtd->jumlah}}</span>
                        </a>
                    </td>
                </tr>

            </tbody>
        </table>

    </div>

    <div class="col-lg-4">
        <div class="row mb-2">
            <div class="col-lg-6">
                <h6>Stok Pakan</h6>
            </div>
            <div class="col-lg-6">
                <button type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Opname"
                    class="btn btn-primary btn-sm float-end opnme_pakan me-2"><i class="fas fa-tasks"></i>

                </button>

            </div>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="dhead">Nama Pakan</th>
                    <th class="dhead" style="text-align: right">Stok</th>
                    <th class="dhead" style="text-align: center">Satuan</th>
                </tr>
            </thead>
            <tbody style="border-color: #435EBE;">
                @foreach ($pakan as $p)
                <tr>
                    <td><a href="#" onclick="event.preventDefault();" class="history_stok"
                            id_pakan="{{$p->id_pakan}}">{{ucwords(strtolower($p->nm_produk))}}
                        </a>
                    </td>
                    <td style=" text-align: right">
                        {{$p->pcs_debit - $p->pcs_kredit}}
                    </td>
                    <td style="text-align: center">{{$p->nm_satuan}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>


    </div>
    <div class="col-lg-4">
        <div class="row mb-2">

            <div class="col-lg-6">
                <h6>Stok Vitamin</h6>
            </div>
            <div class="col-lg-6">
                <button data-bs-toggle="tooltip" data-bs-placement="top" title="Opname" type="button"
                    class="btn btn-primary btn-sm float-end opnme_vitamin"><i class="fas fa-tasks"></i>
                </button>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="dhead">Nama Vitamin</th>
                    <th class="dhead" style="text-align: right">Stok</th>
                    <th class="dhead" style="text-align: center">Satuan</th>
                </tr>
            </thead>
            <tbody style="border-color: #435EBE; ">
                @foreach ($vitamin as $p)
                <tr>
                    <td><a href="#" onclick="event.preventDefault();" class="history_stok"
                            id_pakan="{{$p->id_pakan}}">{{$p->nm_produk}}</a></td>
                    <td style="text-align: right">{{$p->pcs_debit - $p->pcs_kredit}}</td>
                    <td style="text-align: center">{{$p->nm_satuan}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>