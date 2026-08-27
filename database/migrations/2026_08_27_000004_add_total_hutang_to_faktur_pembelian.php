<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('faktur_pembelian') || Schema::hasColumn('faktur_pembelian', 'total_hutang')) {
            return;
        }

        Schema::table('faktur_pembelian', function (Blueprint $table) {
            $table->decimal('total_hutang', 20, 2)->default(0)->after('total_harga');
        });

        DB::table('faktur_pembelian')
            ->where(function ($query) {
                $query->where('metode_pembayaran', 'hutang')
                    ->orWhereNull('metode_pembayaran');
            })
            ->update(['total_hutang' => DB::raw('total_harga')]);
    }

    public function down(): void
    {
        if (Schema::hasTable('faktur_pembelian') && Schema::hasColumn('faktur_pembelian', 'total_hutang')) {
            Schema::table('faktur_pembelian', function (Blueprint $table) {
                $table->dropColumn('total_hutang');
            });
        }
    }
};
