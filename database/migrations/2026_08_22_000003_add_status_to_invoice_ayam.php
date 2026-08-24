<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoice_ayam', 'status')) {
            Schema::table('invoice_ayam', function (Blueprint $table) {
                $table->enum('status', ['paid', 'unpaid'])->default('paid')->after('lokasi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoice_ayam', 'status')) {
            Schema::table('invoice_ayam', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
