<?php

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('penjualan_agl', 'status')) {
            \Illuminate\Support\Facades\Schema::table('penjualan_agl', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->enum('status', ['paid', 'unpaid'])->default('paid')->after('lokasi');
            });
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('penjualan_agl', 'status')) {
            \Illuminate\Support\Facades\Schema::table('penjualan_agl', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
