<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImporJurnalPerkiraan extends Model
{
    protected $table = 'impor_jurnal_perkiraan';

    protected $primaryKey = 'id_impor_jurnal_perkiraan';

    protected $guarded = [];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'dibatalkan_pada' => 'datetime',
        'total_debit' => 'decimal:12',
        'total_kredit' => 'decimal:12',
    ];

    public function detail(): HasMany
    {
        return $this->hasMany(JurnalPerkiraan::class, 'id_impor_jurnal_perkiraan', 'id_impor_jurnal_perkiraan');
    }
}
