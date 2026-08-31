<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('pembelian_pullet', function(Blueprint $t){$t->unsignedBigInteger('id_suplier')->nullable()->after('tanggal');}); } public function down(): void { Schema::table('pembelian_pullet',fn(Blueprint $t)=>$t->dropColumn('id_suplier')); } };
