<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FakturModel extends Model
{
    use HasFactory;
    protected $table = 'faktur_pembelian';
    protected $guarded = ['id'];
    protected $casts = [
        'biaya_lain' => 'array',
    ];
    const UPDATED_AT = null;

    public function supplier()
    {
        return $this->belongsTo(Suplier::class, 'supplier_id', 'id_suplier');
    }

    public function detail()
    {
        return $this->hasMany(FakturPembelianDetail::class, 'faktur_pembelian_id');
    }
}
