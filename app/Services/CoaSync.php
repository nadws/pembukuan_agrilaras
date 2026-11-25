<?php

namespace App\Services;

use App\Models\AkunPerkiraan;

/**
 * Applies one COA change from the main application to akun_perkiraan.
 * Accounts are matched by kode_perkiraan. A deleted account is switched off, never erased, because
 * journals of this application may still point to it.
 */
class CoaSync
{
    /**
     * How the main application's account groups are written in tipe_akun.
     * Adjust the values here if this application uses other codes for them.
     */
    private const TIPE_AKUN = [
        'asset' => 'ASET',
        'liability' => 'LIAB',
        'equity' => 'EQTY',
        'revenue' => 'REVE',
        'expense' => 'EXPS',
    ];

    /**
     * @param  array<string, mixed>  $account  One account as the main application sends it (code, name, category, ...).
     */
    public function apply(array $account, string $action): void
    {
        $code = (string) $account['code'];

        if ($action === 'deleted') {
            AkunPerkiraan::query()->where('kode_perkiraan', $code)->update(['aktif' => false]);

            return;
        }

        AkunPerkiraan::query()->updateOrCreate(
            ['kode_perkiraan' => $code],
            [
                'tipe_akun' => self::TIPE_AKUN[$account['category']] ?? 'ASET',
                'nama' => (string) $account['name'],
                'catatan' => $account['notes'] ?? null,
                'aktif' => (bool) ($account['is_active'] ?? true) && empty($account['deleted_at']),
            ],
        );
    }

    /**
     * Point the account to its parent once both exist. Run it after a batch so parents that arrive later are found.
     *
     * @param  array<string, mixed>  $account
     */
    public function linkParent(array $account): void
    {
        $parentCode = $account['parent_code'] ?? null;

        $parentId = $parentCode === null ? null : AkunPerkiraan::query()
            ->where('kode_perkiraan', $parentCode)
            ->value('id_akun_perkiraan');

        AkunPerkiraan::query()
            ->where('kode_perkiraan', $account['code'])
            ->update(['id_akun_induk' => $parentId]);
    }
}
