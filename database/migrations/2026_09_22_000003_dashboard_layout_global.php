<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Baris global memakai user_id NULL (satu baris, dibagikan semua akun).
        DB::statement('ALTER TABLE `dashboard_layout` MODIFY `user_id` BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::table('dashboard_layout')->whereNull('user_id')->delete();
        DB::statement('ALTER TABLE `dashboard_layout` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
    }
};
