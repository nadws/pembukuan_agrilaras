<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stok extends Model
{
  use HasFactory;
  public $timestamps = false;
  protected $primaryKey = 'id_stok_produk';
  protected $table = 'tb_stok_produk';
  protected $guarded = [];

  public function produk()
  {
    return $this->belongsTo(Produk::class, 'id_produk');
  }

  public function satuan()
  {
    return $this->belongsTo(Produk::class, 'id_produk');
  }

  public function gudang()
  {
    return $this->belongsTo(Gudang::class, 'gudang_id');
  }

  public static function getStatus($no_nota)
  {
    return self::where('no_nota', $no_nota)->first();
  }

  public static function getStokMasuk($id_produk)
  {
    return DB::selectOne("SELECT a.rp_satuan, a.debit, a.id_produk,a.jml_sebelumnya,a.jml_sesudahnya,b.nm_produk,d.nm_satuan, c.ttlDebit, c.ttlKredit, a.id_stok_produk 
            FROM `tb_stok_produk` as a
            LEFT JOIN tb_produk as b ON a.id_produk = b.id_produk
            LEFT JOIN tb_satuan as d ON b.satuan_id = d.id_satuan
            LEFT JOIN (
                SELECT b.id_produk, sum(b.debit) as ttlDebit, sum(b.kredit) as ttlKredit
            FROM tb_stok_produk as b WHERE b.jenis = 'selesai' AND b.id_produk = '$id_produk' GROUP BY b.id_produk
            ) as c ON c.id_produk = a.id_produk WHERE a.id_produk = '$id_produk'
            ");
  }

  public static function getProduk($kategori_id, $gudang_id = null, $kontrol = null)
  {
    $plusQuery = !empty($gudang_id) ? "AND a.gudang_id = '$gudang_id'" : '';
    $plusKontrol = !empty($kontrol) ? "AND a.kontrol_stok = 'Y'" : '';

    return DB::select("SELECT 
        a.id_produk, 
        a.kd_produk, 
        a.gudang_id, 
        a.nm_produk, 
        e.nm_satuan,
        a.admin,
        f.debit,
        f.kredit,
        f.tgl as tgl1 
      FROM 
        tb_produk as a 
        left join tb_satuan as e on e.id_satuan = a.satuan_id 
        LEFT join (
          SELECT 
            max(b.tgl) as tgl, 
            b.id_produk, 
            b.urutan, 
            SUM(b.debit) as debit, 
            sum(b.kredit) as kredit 
          FROM 
            tb_stok_produk as b 
          where 
            b.jenis = 'selesai'
          group by 
            b.id_produk
        ) as f on f.id_produk = a.id_produk 
      WHERE a.kategori_id = '$kategori_id'
        $plusKontrol $plusQuery ");
  }

  public static function getCetak($no_nota)
  {
    return DB::select("SELECT a.jml_sesudahnya,b.nm_produk,a.jml_sebelumnya, a.debit, a.kredit, a.selisih, c.rp, c.ttl FROM `tb_stok_produk` as a
      LEFT JOIN tb_produk as b ON a.id_produk = b.id_produk
      LEFT JOIN (
          SELECT a.id_produk, sum(a.debit - a.kredit) as ttl, sum(a.rp_satuan) as rp FROM `tb_stok_produk` as a
          WHERE a.status = 'masuk'
      GROUP BY a.id_produk
      ) as c ON c.id_produk = a.id_produk
      WHERE a.no_nota = '$no_nota';");
  }
}
