<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_suplier') && ! Schema::hasColumn('tb_suplier', 'kategori')) {
            Schema::table('tb_suplier', function (Blueprint $table) {
                $table->string('kategori', 150)->nullable()->after('id_suplier');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_suplier') && Schema::hasColumn('tb_suplier', 'kategori')) {
            Schema::table('tb_suplier', function (Blueprint $table) {
                $table->dropColumn('kategori');
            });
        }
    }
};
