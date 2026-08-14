<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalPerkiraan extends Model
{
    protected $table = 'jurnal_perkiraan';

    protected $primaryKey = 'id_jurnal_perkiraan';

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
        'debit' => 'decimal:12',
        'kredit' => 'decimal:12',
    ];

    public function impor(): BelongsTo
    {
        return $this->belongsTo(ImporJurnalPerkiraan::class, 'id_impor_jurnal_perkiraan', 'id_impor_jurnal_perkiraan');
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(AkunPerkiraan::class, 'id_akun_perkiraan', 'id_akun_perkiraan');
    }
}
