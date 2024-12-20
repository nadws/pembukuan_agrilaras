@php
    $file = 'Faktur.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=$file");
@endphp
<table class="table" border="1" style="white-space:nowrap;">
    <thead>
        <tr>
            <th></th>
            <th>FK</th>
            <th>KD_JENIS_TRANSAKSI</th>
            <th>FG_PENGGANTI</th>
            <th>NOMOR_FAKTUR</th>
            <th>MASA_PAJAK</th>
            <th>TAHUN_PAJAK</th>
            <th>TANGGAL_FAKTUR</th>
            <th>NPWP</th>
            <th>NAMA</th>
            <th>ALAMAT_LENGKAP</th>
            <th>JUMLAH_DPP</th>
            <th>JUMLAH_PPN</th>
            <th>JUMLAH_PPNBM</th>
            <th>ID_KETERANGAN_TAMBAHAN</th>
            <th>FG_UANG_MUKA</th>
            <th>UANG_MUKA_DPP</th>
            <th>UANG_MUKA_PPN</th>
            <th>UANG_MUKA_PPNBM</th>
            <th>REFERENSI</th>
            <th>KODE_DOKUMEN_PENDUKUNG</th>
        </tr>
        <tr>
            <th></th>
            <th>LT</th>
            <th>NPWP</th>
            <th>NAMA</th>
            <th>JALAN</th>
            <th>BLOK</th>
            <th>NOMOR</th>
            <th>RT</th>
            <th>RW</th>
            <th>KECAMATAN</th>
            <th>KELURAHAN</th>
            <th>KABUPATEN</th>
            <th>PROPINSI</th>
            <th>KODE_POS</th>
            <th>NOMOR_TELEPON</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th>OF</th>
            <th>KODE_OBJEK</th>
            <th>NAMA</th>
            <th>HARGA_SATUAN</th>
            <th>JUMLAH_BARANG</th>
            <th>HARGA_TOTAL</th>
            <th>DISKON</th>
            <th>DPP</th>
            <th>PPN</th>
            <th>TARIF_PPNBM</th>
            <th>PPNBM</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($nota as $no => $f): 
            if ($f->lokasi == 'mtd') {
                $faktur = DB::select("SELECT a.no_nota, a.nm_telur, a.pcs, a.kg as kg_jual, a.rp_satuan, a.ttl_rp as total_rp
                FROM (
                SELECT a.no_nota, b.nm_telur, a.pcs_pcs as pcs, a.pcs_pcs as kg, a.rp_pcs as rp_satuan, (a.pcs_pcs * a.rp_pcs) as ttl_rp
                FROM invoice_mtd as a
                left join telur_produk as b on b.id_produk_telur = a.id_produk
                where a.no_nota = '$f->no_nota' 

                UNION ALL

                SELECT a.no_nota, b.nm_telur, a.ikat as pcs, (a.kg_ikat - a.ikat) as kg, a.rp_ikat as rp_satuan, ((a.kg_ikat - a.ikat) * a.rp_ikat) as ttl_rp
                FROM invoice_mtd as a 
                left join telur_produk as b on b.id_produk_telur = a.id_produk    
                where a.no_nota = '$f->no_nota'

                UNION ALL

                SELECT a.no_nota, b.nm_telur, a.pcs_kg as pcs, a.kg_kg as kg, a.rp_kg as rp_satuan, (a.kg_kg * a.rp_kg) as ttl_rp
                FROM invoice_mtd as a 
                left join telur_produk as b on b.id_produk_telur = a.id_produk
                where a.no_nota = '$f->no_nota'
                ) as a
                where (a.pcs + a.kg + a.rp_satuan) != 0;");
            } else {
                $faktur =  DB::select("SELECT a.tipe,b.nm_telur, a.rp_satuan, if(a.tipe = 'kg',a.kg_jual, a.pcs) as kg_jual, a.total_rp
                FROM invoice_telur as a 
                LEFT JOIN telur_produk as b on b.id_produk_telur = a.id_produk
                WHERE a.no_nota = '$f->no_nota'");
            }
            
             
            ?>
        <tr>
            @php
                $nama = $f->nm_customer;
            @endphp
            <td><?= $no + 1 ?></td>
            <td>FK</td>
            <td>'08</td>
            <td>0</td>
            <td></td>
            <td><?= date('m', strtotime($f->tgl)) ?></td>
            <td><?= date('Y', strtotime($f->tgl)) ?></td>
            <td><?= $f->tgl ?></td>
            <td><?= empty($f->npwp) || $f->npwp == 'Null' ? "'000000000000000" : "$f->npwp" ?></td>
            <td>{{ empty($f->npwp) || $f->npwp == 'Null' ? (empty($f->ktp) ? "0#NIK#NAMA#$nama" : "$f->ktp#NIK#NAMA#$nama") : "$nama" }}
            </td>
            <td>{{ $f->alamat }}</td>
            <td><?= $f->total_rp ?></td>
            <td><?= round($f->total_rp * 0.11, 0) ?></td>
            <td>0</td>
            <td>1</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>{{ "'$f->ktp" }}</td>
            <td>0</td>
        </tr>
        <?php foreach($faktur as $g): ?>
        <tr>
            <td></td>
            <td>OF </td>
            <td></td>
            <td>TELUR <?= strtoupper($g->nm_telur) ?></td>
            <td><?= $g->rp_satuan ?></td>
            <td><?= $g->kg_jual ?></td>
            <td><?= $g->total_rp ?></td>
            <td>0</td>
            <td><?= $g->total_rp ?></td>
            <td><?= round($g->total_rp * 0.11, 0) ?></td>
            <td>0</td>
            <td>0</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php endforeach ?>
        <?php endforeach ?>
    </tbody>

</table>
