<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('faktur_pembelian') && ! Schema::hasColumn('faktur_pembelian', 'biaya_lain')) {
            Schema::table('faktur_pembelian', function (Blueprint $table) {
                $table->json('biaya_lain')->nullable()->after('diskon_total');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('faktur_pembelian') && Schema::hasColumn('faktur_pembelian', 'biaya_lain')) {
            Schema::table('faktur_pembelian', fn (Blueprint $table) => $table->dropColumn('biaya_lain'));
        }
    }
};
