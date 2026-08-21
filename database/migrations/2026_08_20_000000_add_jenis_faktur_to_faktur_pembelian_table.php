<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('faktur_pembelian', 'jenis_faktur')) {
            Schema::table('faktur_pembelian', function (Blueprint $table) {
                $table->string('jenis_faktur', 20)->default('pakan')->after('no_faktur');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('faktur_pembelian', 'jenis_faktur')) {
            Schema::table('faktur_pembelian', function (Blueprint $table) {
                $table->dropColumn('jenis_faktur');
            });
        }
    }
};
