<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalAccurate extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode_akun',
        'bulan',
        'tahun',
        'total_biaya',
        'id_kandang',
    ];
}
