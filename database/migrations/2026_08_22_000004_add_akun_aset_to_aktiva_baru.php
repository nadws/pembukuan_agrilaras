<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('aktiva_pembukuan_baru', function(Blueprint $t){$t->unsignedInteger('id_akun_aset')->nullable()->after('id_kelompok');}); } public function down(): void {Schema::table('aktiva_pembukuan_baru',fn(Blueprint $t)=>$t->dropColumn('id_akun_aset'));}};
