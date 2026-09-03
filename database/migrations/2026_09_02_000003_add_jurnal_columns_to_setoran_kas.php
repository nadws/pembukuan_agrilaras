<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('setoran_kas', function (Blueprint $table) {
            if (!Schema::hasColumn('setoran_kas', 'nomor_setoran')) {
                $table->string('nomor_setoran', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('setoran_kas', 'id_impor_jurnal_perkiraan')) {
                $table->unsignedBigInteger('id_impor_jurnal_perkiraan')->nullable()->after('nomor_referensi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setoran_kas', function (Blueprint $table) {
            if (Schema::hasColumn('setoran_kas', 'nomor_setoran')) {
                $table->dropColumn('nomor_setoran');
            }
            if (Schema::hasColumn('setoran_kas', 'id_impor_jurnal_perkiraan')) {
                $table->dropColumn('id_impor_jurnal_perkiraan');
            }
        });
    }
};
