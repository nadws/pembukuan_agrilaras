<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('impor_jurnal_perkiraan')
            && ! Schema::hasColumn('impor_jurnal_perkiraan', 'sumber_data')) {
            Schema::table('impor_jurnal_perkiraan', function (Blueprint $table) {
                $table->string('sumber_data', 20)->default('sistem')->after('nama_file')->index();
            });

            DB::table('impor_jurnal_perkiraan')
                ->whereRaw("LOWER(nama_file) LIKE '%.xlsx'")
                ->orWhereRaw("LOWER(nama_file) LIKE '%.xls'")
                ->orWhereRaw("LOWER(nama_file) LIKE '%.csv'")
                ->update(['sumber_data' => 'impor']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('impor_jurnal_perkiraan')
            && Schema::hasColumn('impor_jurnal_perkiraan', 'sumber_data')) {
            Schema::table('impor_jurnal_perkiraan', function (Blueprint $table) {
                $table->dropIndex(['sumber_data']);
                $table->dropColumn('sumber_data');
            });
        }
    }
};
