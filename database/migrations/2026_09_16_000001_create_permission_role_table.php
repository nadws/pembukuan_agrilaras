<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id('id_permission_role');
            $table->unsignedBigInteger('posisi_id');
            $table->unsignedBigInteger('id_permission_button');
            $table->timestamps();

            $table->unique(['posisi_id', 'id_permission_button'], 'uniq_posisi_button');
            $table->index('posisi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
