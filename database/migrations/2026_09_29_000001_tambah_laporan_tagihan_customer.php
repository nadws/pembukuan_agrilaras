<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permission = DB::table('permission')->where('url', 'laporan.tagihan-customer')->first();
        $permissionId = $permission?->id_permission ?? DB::table('permission')->insertGetId([
            'nm_permission' => 'Laporan Tagihan Customer',
            'url' => 'laporan.tagihan-customer',
            'id_induk' => 76,
        ]);

        $buttonIds = [];
        foreach (['Buka Halaman', 'Export Excel'] as $nama) {
            $button = DB::table('permission_button')
                ->where('permission_id', $permissionId)
                ->where('nm_permission_button', $nama)
                ->first();
            $buttonIds[] = $button?->id_permission_button ?? DB::table('permission_button')->insertGetId([
                'permission_id' => $permissionId,
                'nm_permission_button' => $nama,
                'jenis' => 'read',
            ]);
        }

        foreach ([1, 2, 3] as $posisiId) {
            foreach ($buttonIds as $buttonId) {
                DB::table('permission_role')->updateOrInsert(
                    ['posisi_id' => $posisiId, 'id_permission_button' => $buttonId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        $permission = DB::table('permission')->where('url', 'laporan.tagihan-customer')->first();
        if (! $permission) {
            return;
        }

        $buttonIds = DB::table('permission_button')
            ->where('permission_id', $permission->id_permission)
            ->pluck('id_permission_button');
        DB::table('permission_role')->whereIn('id_permission_button', $buttonIds)->delete();
        DB::table('permission_button')->where('permission_id', $permission->id_permission)->delete();
        DB::table('permission')->where('id_permission', $permission->id_permission)->delete();
    }
};
