<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukPerencanaan extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $primaryKey = 'id_produk';
    protected $table = 'tb_produk_perencanaan';
    protected $guarded = [];
}
