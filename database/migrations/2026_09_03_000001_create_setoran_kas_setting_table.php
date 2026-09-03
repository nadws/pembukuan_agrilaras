<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setoran_kas_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->nullable()->index();
            $table->json('akun_sumber_ids');
            $table->timestamps();

            $table->unique('id_user', 'setoran_kas_setting_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setoran_kas_setting');
    }
};
