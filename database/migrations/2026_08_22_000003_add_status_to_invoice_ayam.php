<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoice_ayam ADD status ENUM('paid', 'unpaid') NOT NULL DEFAULT 'paid' AFTER lokasi");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE invoice_ayam DROP COLUMN status');
    }
};
