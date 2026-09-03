<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetorKas extends Model
{
    protected $table = 'setoran_kas';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nomor_setoran',
        'tanggal_setoran',
        'akun_tujuan_id',
        'nominal_total',
        'keterangan',
        'nomor_referensi',
        'id_impor_jurnal_perkiraan',
    ];

    protected $casts = [
        'tanggal_setoran' => 'date',
        'nominal_total' => 'decimal:2',
    ];

    public function akunTujuan()
    {
        return $this->belongsTo(AkunPerkiraan::class, 'akun_tujuan_id', 'id_akun_perkiraan');
    }

    public function detail()
    {
        return $this->hasMany(SetorKasDetail::class, 'setoran_kas_id');
    }
}
