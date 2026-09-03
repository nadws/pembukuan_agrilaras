<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetorKasDetail extends Model
{
    protected $table = 'setoran_kas_detail';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'setoran_kas_id',
        'jurnal_perkiraan_id',
        'akun_sumber_id',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function setorKas()
    {
        return $this->belongsTo(SetorKas::class, 'setoran_kas_id');
    }

    public function jurnalPerkiraan()
    {
        return $this->belongsTo(JurnalPerkiraan::class, 'jurnal_perkiraan_id', 'id_jurnal_perkiraan');
    }

    public function akunSumber()
    {
        return $this->belongsTo(AkunPerkiraan::class, 'akun_sumber_id', 'id_akun_perkiraan');
    }
}
