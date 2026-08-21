<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FakturPembelianDetail extends Model
{
    protected $table = 'faktur_pembelian_detail';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function produk()
    {
        return $this->belongsTo(ProdukPerencanaan::class, 'pakan_id', 'id_produk');
    }
}
