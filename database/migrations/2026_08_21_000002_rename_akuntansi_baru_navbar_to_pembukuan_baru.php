<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('navbar')
            ->where('route', 'akuntansi_baru')
            ->update([
                'nama' => 'Pembukuan Baru',
                'isi' => "['akuntansi_baru', 'master.akun-perkiraan.index', 'master.akun-perkiraan.store', 'master.akun-perkiraan.update', 'master.akun-perkiraan.toggle', 'master.akun-perkiraan.import.preview', 'master.akun-perkiraan.import.confirm', 'pembukuan-baru.jurnal-umum.index', 'pembukuan-baru.jurnal-umum.create', 'pembukuan-baru.jurnal-umum.store', 'pembukuan-baru.jurnal-umum.biaya.create', 'pembukuan-baru.jurnal-umum.biaya.store', 'pembukuan-baru.jurnal-umum.aktiva-gantung.create', 'pembukuan-baru.jurnal-umum.aktiva-gantung.store', 'jurnal-perkiraan.laba-rugi', 'jurnal-perkiraan.laba-rugi.export', 'jurnal-perkiraan.detail-akun']",
            ]);
    }

    public function down(): void
    {
        DB::table('navbar')
            ->where('route', 'akuntansi_baru')
            ->update(['nama' => 'Akuntansi Baru']);
    }
};
