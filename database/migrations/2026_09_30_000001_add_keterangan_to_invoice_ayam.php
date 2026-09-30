<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { if (!Schema::hasColumn('invoice_ayam','keterangan')) Schema::table('invoice_ayam', fn(Blueprint $t) => $t->text('keterangan')->nullable()->after('h_satuan')); }
    public function down(): void { if (Schema::hasColumn('invoice_ayam','keterangan')) Schema::table('invoice_ayam', fn(Blueprint $t) => $t->dropColumn('keterangan')); }
};
