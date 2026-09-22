<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permission = DB::table('permission')->where('url', 'laporan.faktur-pajak')->first();
        if (! $permission) {
            $id = DB::table('permission')->insertGetId([
                'nm_permission' => 'Laporan Faktur Pajak',
                'url' => 'laporan.faktur-pajak',
                'id_induk' => 76,
            ]);
        } else {
            $id = $permission->id_permission;
        }

        $buttons = [];
        foreach (['Buka Halaman', 'Export Excel'] as $nama) {
            $button = DB::table('permission_button')
                ->where('permission_id', $id)
                ->where('nm_permission_button', $nama)
                ->first();
            if (! $button) {
                $buttons[] = DB::table('permission_button')->insertGetId([
                    'permission_id' => $id,
                    'nm_permission_button' => $nama,
                    'jenis' => 'read',
                ]);
            } else {
                $buttons[] = $button->id_permission_button;
            }
        }

        foreach ([1, 2, 3] as $posisiId) {
            foreach ($buttons as $buttonId) {
                $ada = DB::table('permission_role')
                    ->where('posisi_id', $posisiId)
                    ->where('id_permission_button', $buttonId)
                    ->exists();
                if (! $ada) {
                    DB::table('permission_role')->insert([
                        'posisi_id' => $posisiId,
                        'id_permission_button' => $buttonId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $navbar = DB::table('navbar')->where('route', 'laporan')->first();
        if ($navbar && ! str_contains((string) $navbar->isi, 'laporan.faktur-pajak')) {
            DB::table('navbar')->where('route', 'laporan')->update([
                'isi' => rtrim((string) $navbar->isi, ']') . ", 'laporan.faktur-pajak']",
            ]);
        }
    }

    public function down(): void
    {
        $permission = DB::table('permission')->where('url', 'laporan.faktur-pajak')->first();
        if ($permission) {
            $buttonIds = DB::table('permission_button')
                ->where('permission_id', $permission->id_permission)
                ->pluck('id_permission_button');
            DB::table('permission_role')->whereIn('id_permission_button', $buttonIds)->delete();
            DB::table('permission_button')->where('permission_id', $permission->id_permission)->delete();
            DB::table('permission')->where('id_permission', $permission->id_permission)->delete();
        }

        $navbar = DB::table('navbar')->where('route', 'laporan')->first();
        if ($navbar) {
            DB::table('navbar')->where('route', 'laporan')->update([
                'isi' => str_replace(", 'laporan.faktur-pajak']", ']', (string) $navbar->isi),
            ]);
        }
    }
};
