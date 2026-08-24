<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('faktur_pembelian', 'diskon_total')) {
            Schema::table('faktur_pembelian', function (Blueprint $table) {
                $table->decimal('diskon_total', 15, 2)->default(0)->after('total_harga');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('faktur_pembelian', 'diskon_total')) {
            Schema::table('faktur_pembelian', function (Blueprint $table) {
                $table->dropColumn('diskon_total');
            });
        }
    }
};
