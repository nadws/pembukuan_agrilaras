<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;

class FakturPembelianDetail extends Model
{
    protected $table = 'faktur_pembelian_detail';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function produk()
    {
        return $this->belongsTo(ProdukPerencanaan::class, 'pakan_id', 'id_produk');
    }

    public function produkUmum()
    {
        return $this->belongsTo(Produk::class, 'pakan_id', 'id_produk');
    }

    public function akunPembayaran()
    {
        return $this->belongsTo(AkunPerkiraan::class, 'id_akun_pembayaran', 'id_akun_perkiraan');
    }
}
