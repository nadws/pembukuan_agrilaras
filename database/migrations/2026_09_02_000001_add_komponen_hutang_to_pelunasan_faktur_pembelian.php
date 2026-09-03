<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pelunasan_faktur_pembelian')
            && ! Schema::hasColumn('pelunasan_faktur_pembelian', 'komponen_hutang')) {
            Schema::table('pelunasan_faktur_pembelian', function (Blueprint $table) {
                $table->string('komponen_hutang', 20)->nullable()->after('faktur_pembelian_id');
                $table->index(
                    ['faktur_pembelian_id', 'komponen_hutang'],
                    'pelunasan_faktur_komponen_index'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pelunasan_faktur_pembelian')
            && Schema::hasColumn('pelunasan_faktur_pembelian', 'komponen_hutang')) {
            Schema::table('pelunasan_faktur_pembelian', function (Blueprint $table) {
                $table->dropIndex('pelunasan_faktur_komponen_index');
                $table->dropColumn('komponen_hutang');
            });
        }
    }
};
