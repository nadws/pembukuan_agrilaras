<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $routes = [
        'akuntansi_baru',

        'master.akun-perkiraan.index',
        'master.akun-perkiraan.store',
        'master.akun-perkiraan.update',
        'master.akun-perkiraan.toggle',
        'master.akun-perkiraan.export',
        'master.akun-perkiraan.template',
        'master.akun-perkiraan.import.preview',
        'master.akun-perkiraan.import.confirm',

        'pembukuan-baru.jurnal-umum.index',
        'pembukuan-baru.jurnal-umum.create',
        'pembukuan-baru.jurnal-umum.store',
        'pembukuan-baru.jurnal-umum.manual.edit',
        'pembukuan-baru.jurnal-umum.manual.update',
        'pembukuan-baru.jurnal-umum.manual.destroy',
        'pembukuan-baru.jurnal-umum.biaya.create',
        'pembukuan-baru.jurnal-umum.biaya.store',
        'pembukuan-baru.jurnal-umum.biaya.edit',
        'pembukuan-baru.jurnal-umum.biaya.update',
        'pembukuan-baru.jurnal-umum.biaya.destroy',
        'pembukuan-baru.jurnal-umum.pembelian-umum.create',
        'pembukuan-baru.jurnal-umum.pembelian-umum.store',
        'pembukuan-baru.jurnal-umum.pembelian-umum.edit',
        'pembukuan-baru.jurnal-umum.pembelian-umum.update',
        'pembukuan-baru.jurnal-umum.pembelian-umum.destroy',
        'pembukuan-baru.jurnal-umum.aktiva-gantung.create',
        'pembukuan-baru.jurnal-umum.aktiva-gantung.store',
        'pembukuan-baru.jurnal-umum.aktiva-gantung.aset.update',
        'pembukuan-baru.jurnal-umum.aktiva-gantung.aset.destroy',
        'pembukuan-baru.jurnal-umum.aktiva-gantung.transaksi.edit',
        'pembukuan-baru.jurnal-umum.aktiva-gantung.transaksi.update',
        'pembukuan-baru.jurnal-umum.aktiva-gantung.transaksi.destroy',
        'pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.create',
        'pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.store',
        'pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.edit',
        'pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.update',
        'pembukuan-baru.jurnal-umum.pembalik-aktiva-gantung.destroy',

        'aktiva',
        'aktiva.add',
        'aktiva.import',
        'aktiva.import.template',
        'save_aktiva',
        'load_aktiva',
        'tambah_baris_aktiva',
        'print_aktiva',

        'pembukuan-baru.jurnal-penyesuaian.index',
        'pembukuan-baru.jurnal-penyesuaian.stok-opname',
        'pembukuan-baru.jurnal-penyesuaian.stok-opname.store',
        'pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva',
        'pembukuan-baru.jurnal-penyesuaian.penyusutan-aktiva.store',
        'barang-umum.index',

        'pembukuan-baru.buku-besar.index',
        'pembukuan-baru.buku-besar.export',
        'pembukuan-baru.buku-besar.detail',
        'pembukuan-baru.buku-besar.detail.export',

        'jurnal-perkiraan.index',
        'jurnal-perkiraan.pratinjau',
        'jurnal-perkiraan.simpan',
        'jurnal-perkiraan.batalkan',
        'jurnal-perkiraan.template',
        'jurnal-perkiraan.detail-batch',
        'jurnal-perkiraan.detail-akun',
        'jurnal-perkiraan.laba-rugi',
        'jurnal-perkiraan.laba-rugi.export',
    ];

    public function up(): void
    {
        $navbar = DB::table('navbar')->where('route', 'akuntansi_baru')->first();
        if (! $navbar) return;

        preg_match_all("/'([^']+)'/", (string) $navbar->isi, $matches);
        $routes = array_values(array_unique(array_merge($matches[1] ?? [], $this->routes)));

        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)
            ->update(['isi' => "['" . implode("', '", $routes) . "']"]);
    }

    public function down(): void
    {
        $navbar = DB::table('navbar')->where('route', 'akuntansi_baru')->first();
        if (! $navbar) return;

        preg_match_all("/'([^']+)'/", (string) $navbar->isi, $matches);
        $routes = array_values(array_diff($matches[1] ?? [], $this->routes));

        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)
            ->update(['isi' => "['" . implode("', '", $routes) . "']"]);
    }
};
