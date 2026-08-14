<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AkunPerkiraan extends Model
{
    use HasFactory;

    protected $table = 'akun_perkiraan';

    protected $primaryKey = 'id_akun_perkiraan';

    protected $fillable = [
        'tipe_akun',
        'kode_perkiraan',
        'nama',
        'id_akun_induk',
        'cabang_saldo',
        'catatan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function akunInduk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_akun_induk', 'id_akun_perkiraan');
    }

    public function akunAnak(): HasMany
    {
        return $this->hasMany(self::class, 'id_akun_induk', 'id_akun_perkiraan')
            ->orderBy('kode_perkiraan');
    }
}
