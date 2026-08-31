<?php

use App\Http\Controllers\AktivaController;
use App\Http\Controllers\AktivaGantungController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\AkunPerkiraanController;
use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\BarangUmumController;
use App\Http\Controllers\CashflowController;
use App\Http\Controllers\ControlflowController;
use App\Http\Controllers\CrudPermissionController;
use App\Http\Controllers\DokumentasiLaporanLayerController;
use App\Http\Controllers\DashboardJurnalPerkiraanController;
use App\Http\Controllers\FakturPenjualanController;
use App\Http\Controllers\FakturPembelianController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\JurnalPenyesuaianController;
use App\Http\Controllers\JurnalPerkiraanController;
use App\Http\Controllers\Laporan_layerController;
use App\Http\Controllers\NavbarController;
use App\Http\Controllers\NeracaController;
use App\Http\Controllers\OpnamemtdController;
use App\Http\Controllers\PembayaranBkController;
use App\Http\Controllers\PembelianBahanBakuController;
use App\Http\Controllers\PembukuanBaruJurnalUmumController;
use App\Http\Controllers\PembukuanBaruJurnalPenyesuaianController;
use App\Http\Controllers\PembukuanBaruBukuBesarController;
use App\Http\Controllers\Penjualan_martadah_alpaController;
use App\Http\Controllers\PenjualanUmumTransaksiController;
use App\Http\Controllers\Penjualan_umum_cekController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\Penyetoran_telurController;
use App\Http\Controllers\PiutangtelurController;
use App\Http\Controllers\PiutangTransaksiController;
use App\Http\Controllers\Produk_telurController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfitController;
use App\Http\Controllers\StokMasukController;
use App\Http\Controllers\ProyekController;
use App\Http\Controllers\Saldo;
use App\Http\Controllers\SaldoController;
use App\Http\Controllers\Stock_telurController;
use App\Http\Controllers\Stok_pakanController;
use App\Http\Controllers\Stok_telur_alpaController;
use App\Http\Controllers\ExportRecordingController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\Jurnal_aktivaController;
use App\Http\Controllers\LaporanLabarugiKandangController;
use App\Http\Controllers\LaporanStokPersediaanController;
use App\Http\Controllers\MedionController;
use App\Http\Controllers\MasterAkunPerkiraanController;
use App\Http\Controllers\PenjualanAyamController;
use App\Http\Controllers\PenjualanAyamTransaksiController;
use App\Http\Controllers\PenjualanTelurTransaksiController;
use App\Http\Controllers\Saldo_penutup;
use App\Http\Controllers\Stok_ayam;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('login');
});

Route::get('/template1', function () {
    return view('template-notable');
})->name('template1');
Route::get('/template2', function () {
    return view('template-table');
})->name('template2');




Route::get('/dashboard', [DashboardJurnalPerkiraanController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // 
    Route::controller(NavbarController::class)->group(function () {
        Route::get('/data_master', 'data_master')->name('data_master');
        Route::get('/buku_besar', 'buku_besar')->name('buku_besar');
        Route::get('/penjualan', 'penjualan')->name('penjualan');
        Route::get('/pembelian', 'pembelian')->name('pembelian');
        Route::get('/pembayaran', 'pembayaran')->name('pembayaran');
        Route::get('/persediaan_barang', 'persediaan_barang')->name('persediaan_barang');
        Route::get('/asset', 'asset')->name('asset');
        Route::get('/penjualan_umum', 'penjualan_umum')->name('penjualan_umum');
        Route::get('/testing', 'testing')->name('testing');
        Route::get('/penjualan_agl', 'penjualan_agl')->name('penjualan_agl');
        Route::get('/kandang', 'kandang')->name('kandang');
        Route::get('/accurate', 'accurate')->name('accurate');
        Route::get('/akuntansi-baru', 'akuntansi_baru')->name('akuntansi_baru');
        Route::get('/transaksi', 'transaksi')->name('transaksi');
        Route::get('/gudang-persediaan', 'gudang_persediaan')->name('gudang_persediaan');
        Route::get('/history-perencanaan', 'history_perencanaan')->name('history_perencanaan');
        Route::get('/penjualan-martadah', 'penjualan_martadah')->name('penjualan_martadah');
        Route::get('/penjualan-martadah/telur', 'penjualan_martadah_telur')->name('penjualan_martadah_telur');
        Route::get('/laporan', 'laporan')->name('laporan');
    });

    Route::controller(BarangUmumController::class)
        ->prefix('data-master/barang-umum')
        ->name('barang-umum.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::post('/stok-awal', 'storeStokAwal')->name('stok-awal.store');
            Route::post('/{idProduk}/stok-awal', 'storeStokAwal')->name('stok-awal');
            Route::put('/{idProduk}', 'update')->name('update');
            Route::delete('/{idProduk}', 'destroy')->name('destroy');
        });

    Route::controller(FakturPembelianController::class)
        ->prefix('transaksi/faktur-pembelian')
        ->name('transaksi.faktur-pembelian.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{faktur_pembelian}/detail', 'detail')->name('detail');
            Route::get('/{faktur_pembelian}/edit', 'edit')->name('edit');
            Route::delete('/{faktur_pembelian}', 'destroy')->name('destroy');
            Route::put('/{faktur_pembelian}', 'update')->name('update');
            Route::get('/{faktur_pembelian}/terima', 'terima')->name('terima');
            Route::post('/{faktur_pembelian}/terima', 'storeTerima')->name('terima.store');
        });

    Route::controller(FakturPembelianController::class)
        ->prefix('transaksi/penerimaan')
        ->name('transaksi.penerimaan.')
        ->group(function () {
            Route::get('/', 'penerimaanIndex')->name('index');
            Route::get('/terima', 'terimaBatch')->name('terima');
            Route::post('/terima', 'storeTerimaBatch')->name('terima.store');
        });

    Route::controller(FakturPembelianController::class)
        ->prefix('transaksi/buku-hutang')
        ->name('transaksi.buku-hutang.')
        ->group(function () {
            Route::get('/', 'bukuHutangIndex')->name('index');
            Route::get('/{faktur_pembelian}/pelunasan', 'pelunasan')->name('pelunasan');
            Route::post('/{faktur_pembelian}/pelunasan', 'storePelunasan')->name('pelunasan.store');
        });

    Route::controller(PembukuanBaruJurnalUmumController::class)
        ->prefix('pembukuan-baru/jurnal-umum')
        ->name('pembukuan-baru.jurnal-umum.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/manual/{id}/edit', 'editManual')->name('manual.edit');
            Route::put('/manual/{id}', 'updateManual')->name('manual.update');
            Route::delete('/manual/{id}', 'destroyManual')->name('manual.destroy');

            Route::get('/biaya/create', 'createBiaya')->name('biaya.create');
            Route::post('/biaya', 'storeBiaya')->name('biaya.store');
            Route::get('/biaya/{nomor_transaksi}/edit', 'editBiaya')->name('biaya.edit');
            Route::put('/biaya/{nomor_transaksi}', 'updateBiaya')->name('biaya.update');
            Route::delete('/biaya/{nomor_transaksi}', 'destroyBiaya')->name('biaya.destroy');

            Route::get('/aktiva-gantung/create', 'createAktivaGantung')->name('aktiva-gantung.create');
            Route::post('/aktiva-gantung', 'storeAktivaGantung')->name('aktiva-gantung.store');
            Route::get('/aktiva-gantung/transaksi/{nomor_transaksi}/edit', 'editAktivaGantungTransaksi')->name('aktiva-gantung.transaksi.edit');
            Route::put('/aktiva-gantung/transaksi/{nomor_transaksi}', 'updateAktivaGantungTransaksi')->name('aktiva-gantung.transaksi.update');
            Route::delete('/aktiva-gantung/transaksi/{nomor_transaksi}', 'destroyAktivaGantungTransaksi')->name('aktiva-gantung.transaksi.destroy');
            Route::put('/aktiva-gantung/aset/{id}', 'updateAktivaGantungAset')->name('aktiva-gantung.aset.update');
            Route::delete('/aktiva-gantung/aset/{id}', 'destroyAktivaGantungAset')->name('aktiva-gantung.aset.destroy');

            Route::get('/pembalik-aktiva-gantung/create', 'createPembalikAktivaGantung')->name('pembalik-aktiva-gantung.create');
            Route::post('/pembalik-aktiva-gantung', 'storePembalikAktivaGantung')->name('pembalik-aktiva-gantung.store');
            Route::get('/pembalik-aktiva-gantung/{nomor_transaksi}/edit', 'editPembalikAktivaGantung')->name('pembalik-aktiva-gantung.edit');
            Route::put('/pembalik-aktiva-gantung/{nomor_transaksi}', 'updatePembalikAktivaGantung')->name('pembalik-aktiva-gantung.update');
            Route::delete('/pembalik-aktiva-gantung/{nomor_transaksi}', 'destroyPembalikAktivaGantung')->name('pembalik-aktiva-gantung.destroy');

            Route::get('/pembelian-umum/create', 'createPembelianUmum')->name('pembelian-umum.create');
            Route::post('/pembelian-umum', 'storePembelianUmum')->name('pembelian-umum.store');
            Route::get('/pembelian-umum/{nomor_transaksi}/edit', 'editPembelianUmum')->name('pembelian-umum.edit');
            Route::put('/pembelian-umum/{nomor_transaksi}', 'updatePembelianUmum')->name('pembelian-umum.update');
            Route::delete('/pembelian-umum/{nomor_transaksi}', 'destroyPembelianUmum')->name('pembelian-umum.destroy');
        });

    Route::prefix('master/akun-perkiraan')->name('master.akun-perkiraan.')->controller(MasterAkunPerkiraanController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{akun_perkiraan_baru}', 'update')->name('update');
        Route::patch('/{akun_perkiraan_baru}/status', 'toggle')->name('toggle');
        Route::post('/import/preview', 'preview')->name('import.preview');
        Route::post('/import/confirm', 'import')->name('import.confirm');
        Route::get('/export/data', 'export')->name('export');
        Route::get('/export/template', 'template')->name('template');
    });
    Route::controller(PenjualanTelurTransaksiController::class)
        ->prefix('transaksi/penjualan-telur')
        ->name('transaksi.penjualan-telur.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{noNota}/detail', 'detail')->name('detail');
            Route::get('/{noNota}/edit', 'edit')->name('edit');
            Route::put('/{noNota}', 'update')->name('update');
            Route::delete('/{noNota}', 'destroy')->name('destroy');
        });
    Route::controller(PenjualanAyamTransaksiController::class)
        ->prefix('transaksi/penjualan-ayam')
        ->name('transaksi.penjualan-ayam.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{noNota}/detail', 'detail')->name('detail');
            Route::get('/{noNota}/edit', 'edit')->name('edit');
            Route::put('/{noNota}', 'update')->name('update');
            Route::delete('/{noNota}', 'destroy')->name('destroy');
        });
    Route::controller(PenjualanUmumTransaksiController::class)
        ->prefix('transaksi/penjualan-umum')
        ->name('transaksi.penjualan-umum.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{urutan}/detail', 'detail')->name('detail');
            Route::get('/{urutan}/edit', 'edit')->name('edit');
            Route::put('/{urutan}', 'update')->name('update');
            Route::delete('/{urutan}', 'destroy')->name('destroy');
        });
    Route::controller(PiutangTransaksiController::class)
        ->prefix('transaksi/piutang')
        ->name('transaksi.piutang.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/import-accurate', 'importAccurate')->name('import-accurate');
            Route::post('/import-accurate-ayam', 'importAccurateAyam')->name('import-accurate-ayam');
            Route::get('/pelunasan', 'pelunasan')->name('pelunasan');
            Route::post('/pelunasan', 'storePelunasan')->name('pelunasan.store');
        });

    Route::prefix('jurnal-perkiraan')->name('jurnal-perkiraan.')->controller(JurnalPerkiraanController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/pratinjau', 'pratinjau')->name('pratinjau');
        Route::post('/simpan', 'simpan')->name('simpan');
        Route::patch('/batch/{impor_jurnal_perkiraan}/batalkan', 'batalkan')->name('batalkan');
        Route::get('/batch/{impor_jurnal_perkiraan}', 'detailBatch')->name('detail-batch');
        Route::get('/template', 'template')->name('template');
        Route::get('/laporan/laba-rugi', 'labaRugi')->name('laba-rugi');
        Route::get('/laporan/laba-rugi/budget', 'budgetLabaRugi')->name('laba-rugi.budget');
        Route::post('/laporan/laba-rugi/budget', 'simpanBudgetLabaRugi')->name('laba-rugi.budget.simpan');
        Route::get('/laporan/laba-rugi/export', 'exportLabaRugi')->name('laba-rugi.export');
        Route::get('/laporan/laba-rugi/akun/{akun_perkiraan}', 'detailAkun')->name('detail-akun');
        Route::get('/laporan/arus-kas', 'arusKas')->name('arus-kas');
        Route::get('/laporan/arus-kas/detail', 'detailArusKas')->name('arus-kas-detail');
        Route::get('/laporan/neraca', 'neraca')->name('neraca');
        Route::get('/laporan/neraca/cetak', 'cetakNeraca')->name('neraca.cetak');
    });


    Route::controller(JurnalController::class)->group(function () {
        Route::get('/jurnal', 'index')->name('jurnal');
        Route::post('/jurnal-update', 'update')->name('jurnal.update');
        Route::get('/jurnal-delete', 'delete')->name('jurnal-delete');
        Route::get('/jurnal-add', 'add')->name('jurnal.add');
        Route::get('/load_menu', 'load_menu')->name('load_menu');
        Route::get('/tambah_baris_jurnal', 'tambah_baris_jurnal')->name('tambah_baris_jurnal');
        Route::get('/export_jurnal', 'export')->name('export_jurnal');
        Route::post('/save_jurnal', 'save_jurnal')->name('save_jurnal');
        Route::get('/edit_jurnal', 'edit')->name('edit_jurnal');
        Route::post('/edit_jurnal', 'edit_save')->name('edit_jurnal');
        Route::get('/detail_jurnal', 'detail_jurnal')->name('detail_jurnal');
        Route::post('/import_jurnal', 'import_jurnal')->name('import_jurnal');
        Route::get('/saldo_akun', 'saldo_akun')->name('saldo_akun');
        Route::get('/get_post', 'get_post')->name('get_post');
        Route::get('/get_proyek', 'get_proyek')->name('get_proyek');
        Route::get('/get_post2', 'get_post2')->name('get_post2');
        Route::get('/get_total_post', 'get_total_post')->name('get_total_post');
    });
    Route::controller(Jurnal_aktivaController::class)->group(function () {
        Route::get('/add_balik_aktiva', 'add_balik_aktiva')->name('add_balik_aktiva');
        Route::post('/save_jurnal_aktiva', 'save_jurnal')->name('save_jurnal_aktiva');
        Route::get('/Cek_aktiva', 'Cek_aktiva')->name('Cek_aktiva');
        Route::get('/get_post_pembalikan', 'get_post_pembalikan')->name('get_post_pembalikan');
        Route::post('/save_atk_pembalik', 'save_atk')->name('save_atk_pembalik');
        Route::post('/save_pullet', 'save_pullet')->name('save_pullet');
    });

    Route::controller(AkunController::class)->group(function () {
        Route::get('/akun', 'index')->name('akun');
        Route::post('/akun', 'create')->name('akun');
        Route::post('/akun-update', 'update')->name('akun.update');
        Route::get('/akun-delete', 'delete')->name('akun.delete');
        Route::get('/akun-sub', 'add_sub')->name('akun.add_sub');
        Route::get('/remove_sub', 'remove_sub')->name('akun.remove_sub');
        Route::get('/get_kode', 'get_kode')->name('get_kode');
        Route::get('/export_akun', 'export_akun')->name('export_akun');
        Route::post('/importAkun', 'importAkun')->name('importAkun');
        Route::get('/get_edit_akun/{id_akun}', 'get_edit_akun')->name('get_edit_akun');
        Route::get('/load_sub_akun/{id_akun}', 'load_sub_akun')->name('load_sub_akun');
        Route::get('/get_edit_sub/{id_akun}', 'get_edit_sub')->name('get_edit_sub');
        Route::get('/edit_sub', 'edit_sub')->name('edit_sub');
    });
    Route::controller(SaldoController::class)->group(function () {
        Route::get('/saldo_awal', 'index')->name('saldo_awal');
        Route::get('/saveSaldo', 'saveSaldo')->name('saveSaldo');
    });



    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    Route::controller(ProdukController::class)
        ->prefix('produk')
        ->name('produk.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'create')->name('create');
            Route::get('/delete', 'delete')->name('delete');
            Route::post('/edit', 'edit')->name('edit');
            Route::get('/{gudang_id}', 'index')->name('detail');
            Route::get('/edit/{id_produk}', 'edit_load')->name('edit_load');
        });

    Route::controller(StokMasukController::class)
        ->prefix('stok_masuk')
        ->name('stok_masuk.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/add', 'add')->name('add');
            Route::post('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/load', 'load_menu')->name('load_menu');
            Route::get('/tbh_baris', 'tbh_baris')->name('tbh_baris');
            Route::get('/get_stok_sebelumnya', 'get_stok_sebelumnya')->name('get_stok_sebelumnya');
            Route::get('/cetak', 'cetak')->name('cetak');
            Route::get('/{gudang_id}', 'index')->name('detail');
            Route::get('/delete/{no_nota}', 'delete')->name('delete');
            Route::get('/edit/{no_nota}', 'edit')->name('edit_load');
            Route::post('/edit', 'update')->name('edit');
        });



    Route::controller(GudangController::class)
        ->prefix('gudang')
        ->name('gudang.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'create')->name('create');
            Route::get('/edit/{id_gudang}', 'edit_load')->name('edit_load');
            Route::post('/edit', 'edit')->name('edit');
            Route::get('/delete/{id_gudang}', 'delete')->name('delete');
        });

    Route::controller(CrudPermissionController::class)
        ->prefix('permis')
        ->name('permis.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'create')->name('create');
            Route::get('/edit/{id_permis}', 'edit')->name('edit');
        });
    Route::controller(BukuBesarController::class)
        ->prefix('summary_buku_besar')
        ->name('summary_buku_besar.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/export_buku_besar', 'export_buku_besar')->name('export_buku_besar');
            Route::get('/detail', 'detail')->name('detail');
            Route::get('/export_detail', 'export_detail')->name('export_detail');
            Route::get('/export_detail_format', 'export_detail_format')->name('export_detail_format');
            Route::get('/buku_besar_new', 'buku_besar_new')->name('buku_besar_new');
            Route::get('/loadDetail', 'loadDetail')->name('loadDetail');
        });

    Route::controller(ProyekController::class)->group(function () {
        Route::get('/proyek', 'index')->name('proyek');
        Route::post('/proyek_add', 'add')->name('proyek_add');
        Route::get('/proyek_delete', 'delete')->name('proyek_delete');
        Route::get('/proyek_selesai', 'proyek_selesai')->name('proyek_selesai');
        Route::get('/get_proyek_selesai', 'get_proyek_selesai')->name('get_proyek_selesai');
    });
    Route::controller(FakturPenjualanController::class)->group(function () {
        Route::get('/faktur_penjualan', 'index')->name('faktur_penjualan');
    });

    Route::controller(ProfitController::class)->group(function () {
        Route::get('/profit', 'index')->name('profit');
        Route::get('/akunprofit', 'akunprofit')->name('akunprofit');
        Route::post('/seleksi_akun_profit', 'seleksi_akun_profit')->name('seleksi_akun_profit');
        Route::get('/profit_print', 'print')->name('profit_print');
        Route::get('/persen_pendapatan', 'persen_pendapatan')->name('persen_pendapatan');
        Route::post('/save_persen_pendapatan', 'save_persen_pendapatan')->name('save_persen_pendapatan');
        Route::get('/tambah_baris_budget_persen', 'tambah_baris_budget_persen')->name('tambah_baris_budget_persen');
        Route::post('/save_budget', 'save_budget')->name('save_budget');
        Route::get('/profit_setahun', 'profit_setahun')->name('profit_setahun');
        Route::get('/get_depresiasi', 'get_depresiasi')->name('get_depresiasi');
        Route::get('/getPopulasi', 'getPopulasi')->name('getPopulasi');
        // Route::get('/get_depresiasi_peralatan', 'get_depresiasi_peralatan')->name('get_depresiasi_peralatan');
    });

    Route::controller(AktivaController::class)->group(function () {
        Route::get('/aktiva', 'index')->name('aktiva');
        Route::get('/aktiva/import/template', 'templateImport')->name('aktiva.import.template');
        Route::post('/aktiva/import', 'import')->name('aktiva.import');
        Route::get('/aktiva.add', 'add')->name('aktiva.add');
        Route::get('/load_aktiva', 'load_aktiva')->name('load_aktiva');
        Route::get('/tambah_baris_aktiva', 'tambah_baris_aktiva')->name('tambah_baris_aktiva');
        Route::get('/get_data_kelompok', 'get_data_kelompok')->name('get_data_kelompok');
        Route::post('/save_aktiva', 'save_aktiva')->name('save_aktiva');
        Route::get('/print_aktiva', 'print')->name('print_aktiva');
    });

    Route::controller(JurnalPenyesuaianController::class)->group(function () {
        Route::get('/jurnal_penyesuaian', 'index')->name('jurnal_penyesuaian');
        Route::get('/jurnal_aktiva', 'jurnal')->name('jurnal_aktiva');
        Route::post('/save_penyesuaian_aktiva', 'save_penyesuaian_aktiva')->name('save_penyesuaian_aktiva');
    });
    Route::prefix('pembukuan-baru/jurnal-penyesuaian')->name('pembukuan-baru.jurnal-penyesuaian.')->controller(PembukuanBaruJurnalPenyesuaianController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        // URL lama tetap dipertahankan sebagai pintu masuk, tetapi proses opname
        // barang umum sekarang dilakukan dari modul Gudang.
        Route::get('/stok-opname', fn () => redirect()->route('gudang-persediaan.barang-umum'))->name('stok-opname');
        Route::post('/stok-opname', 'simpanStokOpname')->name('stok-opname.store');
        Route::get('/penyusutan-aktiva', 'penyusutanAktiva')->name('penyusutan-aktiva');
        Route::post('/penyusutan-aktiva', 'simpanPenyusutanGrouped')->name('penyusutan-aktiva.store');
        Route::post('/penyusutan-aktiva/write-off', 'writeOffAktiva')->name('penyusutan-aktiva.write-off');
    });
    Route::prefix('pembukuan-baru/aktiva-gantung')->name('pembukuan-baru.aktiva-gantung.')->controller(AktivaGantungController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/saldo-awal', 'storeSaldoAwal')->name('saldo-awal.store');
    });
    Route::prefix('pembukuan-baru/buku-besar')->name('pembukuan-baru.buku-besar.')->controller(PembukuanBaruBukuBesarController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export', 'export')->name('export');
        Route::get('/{id}', 'detail')->name('detail');
        Route::get('/{id}/export', 'exportDetail')->name('detail.export');
    });
    Route::controller(PembelianBahanBakuController::class)->group(function () {
        Route::get('/pembelian_bk', 'index')->name('pembelian_bk');
        Route::get('/pembelian_bk.add', 'add')->name('pembelian_bk.add');
        Route::get('/get_satuan_produk', 'get_satuan_produk')->name('get_satuan_produk');
        Route::get('/tambah_baris_bk', 'tambah_baris_bk')->name('tambah_baris_bk');
        Route::post('/save_pembelian_bk', 'save_pembelian_bk')->name('save_pembelian_bk');
        Route::get('/print_bk', 'print')->name('print_bk');
        Route::get('/delete_bk', 'delete_bk')->name('delete_bk');
        Route::get('/edit_pembelian_bk', 'edit_pembelian_bk')->name('edit_pembelian_bk');
        Route::post('/edit_pembelian_bk', 'edit_save')->name('edit_pembelian_bk');
        Route::post('/grading', 'grading')->name('grading');
        Route::post('/approve_invoice_bk', 'approve_invoice_bk')->name('approve_invoice_bk');
        Route::get('/get_grading', 'get_grading')->name('get_grading');
        Route::get('/get_grading2', 'get_grading2')->name('get_grading2');
        Route::get('/nota_invoice_bk', 'nota_invoice_bk')->name('nota_invoice_bk');
        Route::get('/export_bk', 'export_bk')->name('export_bk');
    });

    Route::controller(PembayaranBkController::class)->group(function () {
        Route::get('/pembayaranbk', 'index')->name('pembayaranbk');
        Route::get('/pembayaranbk.add', 'add')->name('pembayaranbk.add');
        Route::get('/pembayaranbk.tambah', 'tambah')->name('pembayaranbk.tambah');
        Route::post('/pembayaranbk.save_pembayaran', 'save_pembayaran')->name('pembayaranbk.save_pembayaran');
        Route::get('/pembayaranbk.edit', 'edit')->name('pembayaranbk.edit');
        Route::post('/pembayaranbk.save_edit', 'save_edit')->name('pembayaranbk.save_edit');
        Route::get('/get_kreditBK', 'get_kreditBK')->name('get_kreditBK');
        Route::get('/exportBayarbk', 'exportBayarbk')->name('exportBayarbk');
    });
    Route::controller(ControlflowController::class)->group(function () {
        Route::get('/controlflow', 'index')->name('controlflow');
        Route::get('/loadcontrolflow', 'loadcontrolflow')->name('loadcontrolflow');
        Route::get('/loadInputAkunCashflow', 'loadInputAkunCashflow')->name('loadInputAkunCashflow');
        Route::get('/save_kategoriCashcontrol', 'save_kategoriCashcontrol')->name('save_kategoriCashcontrol');
        Route::get('/edit_kategoriCashcontrol', 'edit_kategoriCashcontrol')->name('edit_kategoriCashcontrol');
        Route::get('/loadInputsub', 'loadInputsub')->name('loadInputsub');
        Route::get('/SaveSubAkunCashflow', 'SaveSubAkunCashflow')->name('SaveSubAkunCashflow');
        Route::get('/deleteSubAkunCashflow', 'deleteSubAkunCashflow')->name('deleteSubAkunCashflow');
        Route::get('/deleteAkunCashflow', 'deleteAkunCashflow')->name('deleteAkunCashflow');
        Route::get('/view_akun', 'view_akun')->name('view_akun');
        Route::get('/print_cashflow', 'print')->name('print_cashflow');

        Route::get('/akuncashflow', 'akuncashflow')->name('akuncashflow');
        Route::get('/akunuangditarik', 'akunuangditarik')->name('akunuangditarik');
        Route::get('/total_cash_flow', 'total_cash_flow')->name('total_cash_flow');
        Route::get('/total_cash_ibu', 'total_cash_ibu')->name('total_cash_ibu');
        Route::get('/total_cash_profit', 'total_cash_profit')->name('total_cash_profit');
        Route::post('/seleksi_cash_flow_ditarik', 'seleksi_cash_flow_ditarik')->name('seleksi_cash_flow_ditarik');
    });

    Route::controller(CashflowController::class)->group(function () {
        Route::get('/cashflow_ibu', 'index')->name('cashflow_ibu');
        Route::get('/loadInputKontrol', 'loadInputKontrol')->name('loadInputKontrol');
        Route::get('/save_akun_ibu', 'save_akun_ibu')->name('save_akun_ibu');
        Route::get('/delete_akun_ibu', 'delete_akun_ibu')->name('delete_akun_ibu');
        Route::get('/delete_akun_ibu', 'delete_akun_ibu')->name('delete_akun_ibu');
        Route::get('/edit_akun_ibu', 'edit_akun_ibu')->name('edit_akun_ibu');
        Route::post('/seleksi_akun_control_ditarik', 'seleksi_akun_control_ditarik')->name('seleksi_akun_control_ditarik');
        Route::get('/cashflow_setahun', 'cashflowsetahun')->name('cashflow_setahun');
        Route::get('/detail_proyek', 'detail_proyek')->name('detail_proyek');
        Route::get('/print_uang_ditarik', 'print_uang_ditarik')->name('print_uang_ditarik');
        Route::get('/export_uang_ditarik', 'export_uang_ditarik')->name('export_uang_ditarik');
    });
    Route::controller(NeracaController::class)->group(function () {
        Route::get('/neraca', 'index')->name('neraca');
        Route::get('/load_pasiva', 'load_pasiva')->name('load_pasiva');
        Route::get('/loadNeraca', 'loadneraca')->name('loadNeraca');
        Route::get('/loadinputSub_neraca', 'loadinputSub_neraca')->name('loadinputSub_neraca');
        Route::get('/view_akun_neraca', 'view_akun_neraca')->name('view_akun_neraca');
        Route::get('/saveSub_neraca', 'saveSub_neraca')->name('saveSub_neraca');
        Route::get('/loadinputAkun_neraca', 'loadinputAkun_neraca')->name('loadinputAkun_neraca');
        Route::get('/saveAkunNeraca', 'saveAkunNeraca')->name('saveAkunNeraca');
        Route::get('/delete_akun_neraca', 'delete_akun_neraca')->name('delete_akun_neraca');
        Route::get('/akun_neraca', 'akun_neraca')->name('akun_neraca');
        Route::get('/print_neraca', 'print_neraca')->name('print_neraca');
    });

    Route::controller(PenjualanController::class)->group(function () {
        Route::get('/penjualan_agrilaras', 'index')->name('penjualan_agrilaras');
        Route::get('/export_penjualan_telur/{tgl1}/{tgl2}', 'export_penjualan_telur')->name('export_penjualan_telur');
        Route::get('/tbh_invoice_telur', 'tbh_invoice_telur')->name('tbh_invoice_telur');
        Route::get('/loadkginvoice', 'loadkginvoice')->name('loadkginvoice');
        Route::get('/tambah_baris_kg', 'tambah_baris_kg')->name('tambah_baris_kg');
        Route::get('/tbh_pembayaran', 'tbh_pembayaran')->name('tbh_pembayaran');
        Route::post('/save_penjualan_telur', 'save_penjualan_telur')->name('save_penjualan_telur');
        Route::get('/detail_invoice_telur', 'detail_invoice_telur')->name('detail_invoice_telur');
        Route::get('/loadpcsinvoice', 'loadpcsinvoice')->name('loadpcsinvoice');
        Route::get('/tambah_baris_pcs', 'tambah_baris_pcs')->name('tambah_baris_pcs');
        Route::get('/edit_invoice_telur', 'edit_invoice_telur')->name('edit_invoice_telur');
        Route::get('/loadkginvoiceedit', 'loadkginvoiceedit')->name('loadkginvoiceedit');
        Route::post('/edit_penjualan_telur', 'edit_penjualan_telur')->name('edit_penjualan_telur');
        Route::get('/delete_invoice_telur', 'delete_invoice_telur')->name('delete_invoice_telur');
        Route::get('/loadpcsinvoiceedit', 'loadpcsinvoiceedit')->name('loadpcsinvoiceedit');
        Route::get('/export_faktur', 'export_faktur')->name('export_faktur');
    });

    Route::controller(Stock_telurController::class)->group(function () {
        Route::get('/stok_telur', 'index')->name('stok_telur');
        Route::get('/tbh_stok_telur', 'tbh_stok_telur')->name('tbh_stok_telur');
        Route::get('/load_menu_telur', 'load_menu_telur')->name('load_menu_telur');
        Route::get('/tbh_baris_telur', 'tbh_baris_telur')->name('tbh_baris_telur');
        Route::post('/save_stok_telur', 'save_stok_telur')->name('save_stok_telur');
        Route::get('/transfer_stok_telur', 'transfer_stok_telur')->name('transfer_stok_telur');
        Route::get('/load_transfer_telur', 'load_transfer_telur')->name('load_transfer_telur');
        Route::get('/tbh_baris_transfer', 'tbh_baris_transfer')->name('tbh_baris_transfer');
        Route::post('/save_transfer_stok_telur', 'save_transfer_stok_telur')->name('save_transfer_stok_telur');
        Route::get('/get_stok_telur', 'get_stok')->name('get_stok_telur');
        Route::get('/edit_telur', 'edit_telur')->name('edit_telur');
        Route::post('/save_edit_stok_telur', 'save_edit_stok_telur')->name('save_edit_stok_telur');
        Route::get('/delete_telur', 'delete_telur')->name('delete_telur');
    });
    Route::controller(Stok_telur_alpaController::class)->group(function () {
        Route::get('/stok_telur_alpa', 'index')->name('stok_telur_alpa');
        Route::get('/detail_stok_telur_alpa', 'detail_stok_telur_alpa')->name('detail_stok_telur_alpa');
        Route::get('/delete_transfer', 'delete_transfer')->name('delete_transfer');
    });

    Route::controller(PiutangtelurController::class)->group(function () {
        Route::get('/piutang_telur', 'index')->name('piutang_telur');
        Route::get('/bayar_piutang_telur', 'bayar_piutang_telur')->name('bayar_piutang_telur');
        Route::post('/save_bayar_piutang', 'save_bayar_piutang')->name('save_bayar_piutang');
        Route::get('/get_pembayaranpiutang_telur', 'get_pembayaranpiutang_telur')->name('get_pembayaranpiutang_telur');
        Route::get('/edit_pembayaran_piutang_telur', 'edit_piutang')->name('edit_pembayaran_piutang_telur');
        Route::post('/edit_bayar_piutang', 'edit_bayar_piutang')->name('edit_bayar_piutang');
    });
    Route::controller(Penyetoran_telurController::class)->group(function () {
        Route::get('/penyetoran_telur', 'index')->name('penyetoran_telur');
        Route::get('/perencanaan_setor_telur', 'perencanaan_setor_telur')->name('perencanaan_setor_telur');
        Route::post('/save_perencanaan_telur', 'save_perencanaan_telur')->name('save_perencanaan_telur');
        Route::get('/get_list_perencanaan', 'get_list_perencanaan')->name('get_list_perencanaan');
        Route::get('/get_perencanaan', 'get_perencanaan')->name('get_perencanaan');
        Route::post('/save_setoran', 'save_setoran')->name('save_setoran');
        Route::get('/print_setoran', 'print_setoran')->name('print_setoran');
        Route::get('/delete_perencanaan', 'delete_perencanaan')->name('delete_perencanaan');
        Route::get('/get_history_perencanaan', 'get_history_perencanaan')->name('get_history_perencanaan');
    });

    Route::controller(Produk_telurController::class)->group(function () {
        Route::get('/produk_telur', 'index')->name('produk_telur');
        Route::get('/CheckMartadah', 'CheckMartadah')->name('CheckMartadah');
        Route::get('/CheckAlpa', 'CheckAlpa')->name('CheckAlpa');
        Route::get('/HistoryMtd', 'HistoryMtd')->name('HistoryMtd');
        Route::get('/edit_telur_dashboard', 'edit_telur_dashboard')->name('edit_telur_dashboard');
        Route::get('/export_telur', 'export')->name('export_telur');
        Route::get('/HistoryAlpa', 'HistoryAlpa')->name('HistoryAlpa');
        Route::get('/get_edit_hrga_telur', 'get_edit_hrga_telur')->name('get_edit_hrga_telur');
        Route::get('/delete_harga_telur', 'delete_harga_telur')->name('delete_harga_telur');
        Route::post('/edit_harga_telur', 'edit_harga_telur')->name('edit_harga_telur');
        Route::post('/import_biaya', 'import_biaya')->name('import_biaya');
        Route::post('/import_biaya_hpp', 'import_biaya_hpp')->name('import_biaya_hpp');
        Route::post('/saveHargaTelur', 'saveHargaTelur')->name('saveHargaTelur');
    });
    Route::controller(Saldo_penutup::class)->group(function () {
        Route::get('/saldo_penutup', 'index')->name('saldo_penutup');
        Route::post('/saveSaldopenutup', 'saveSaldopenutup')->name('saveSaldopenutup');
    });

    Route::controller(PenjualanAyamController::class)
        ->prefix('penjualan_ayam')
        ->name('penjualan_ayam.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/cek', 'cek')->name('cek');
            Route::post('/save_cek', 'save_cek')->name('save_cek');
            Route::get('/penyetoran', 'penyetoran')->name('penyetoran');
            Route::get('/get_history_perencanaan', 'get_history_perencanaan')->name('get_history_perencanaan');
            Route::get('/print_setoran', 'print_setoran')->name('print_setoran');
            Route::get('/delete_perencanaan', 'delete_perencanaan')->name('delete_perencanaan');
            Route::get('/perencanaan_setor', 'perencanaan_setor')->name('perencanaan_setor');
            Route::get('/get_list_perencanaan', 'get_list_perencanaan')->name('get_list_perencanaan');
            Route::get('/get_perencanaan', 'get_perencanaan')->name('get_perencanaan');
            Route::post('/save_perencanaan', 'save_perencanaan')->name('save_perencanaan');
            Route::post('/save_setoran', 'save_setoran')->name('save_setoran');
        });

    Route::controller(Stok_pakanController::class)->group(function () {
        // History Alpa
        Route::get('/load_stok_pakan', 'load_stok_pakan')->name('load_stok_pakan');
        Route::get('/opname_pakan', 'opname_pakan')->name('opname_pakan');
        Route::get('/opnme_vitamin', 'opnme_vitamin')->name('opnme_vitamin');
        Route::post('/save_opname_pakan', 'save_opname_pakan')->name('save_opname_pakan');

        Route::get('/history_stok', 'history_stok')->name('history_stok');
        Route::get('/tambah_pakan', 'tambah_pakan')->name('tambah_pakan');
        Route::get('/tambah_vitamin', 'tambah_vitamin')->name('tambah_vitamin');
        Route::post('/save_tambah_pakan', 'save_tambah_pakan')->name('save_tambah_pakan');
        Route::get('/tambah_baris_stok', 'tambah_baris_stok')->name('tambah_baris_stok');
        Route::get('/tambah_baris_stok_vitamin', 'tambah_baris_stok_vitamin')->name('tambah_baris_stok_vitamin');

        Route::get('/history_perencanaan_pakan', 'history_perencanaan_pakan')->name('history_perencanaan_pakan');
        Route::get('/pembukuan_biaya_pv', 'pembukuan_biaya_pv')->name('pembukuan_biaya_pv');
        Route::post('/bukukan_pv', 'bukukan_pv')->name('bukukan_pv');
        Route::post('/save_stok_pakan', 'save_stok_pakan')->name('save_stok_pakan');
        Route::get('/tbh_stok_pakan', 'tbh_stok_pakan')->name('tbh_stok_pakan');
        Route::get('/get_edit_hrga_pakan', 'get_edit_hrga_pakan')->name('get_edit_hrga_pakan');
        Route::get('/hapus_stok_pakan', 'hapus_stok_pakan')->name('hapus_stok_pakan');
        Route::post('/edit_stok_pakan', 'edit_stok_pakan')->name('edit_stok_pakan');
    });

    Route::controller(Penjualan_martadah_alpaController::class)->group(function () {
        Route::get('/penjualan_martadah_cek', 'index')->name('penjualan_martadah_cek');
        Route::get('/detail_penjualan_mtd', 'detail_penjualan_mtd')->name('detail_penjualan_mtd');
        Route::get('/terima_invoice_mtd', 'terima_invoice_mtd')->name('terima_invoice_mtd');
        Route::get('/tbh_pembayaran_martadah', 'tbh_pembayaran_martadah')->name('tbh_pembayaran_martadah');
        Route::post('/save_terima_invoice', 'save_terima_invoice')->name('save_terima_invoice');
    });
    Route::controller(Stok_ayam::class)->group(function () {
        Route::get('/stok_ayam', 'index')->name('stok_ayam');
        Route::get('/history_ayam', 'history_ayam')->name('history_ayam');
        Route::get('/hapus_ayam', 'hapus_ayam')->name('hapus_ayam');
        Route::get('/piutang_ayam', 'piutang_ayam')->name('piutang_ayam');
        Route::get('/edit_ayam', 'edit_ayam')->name('edit_ayam');
        Route::get('/bayar_piutang_ayam', 'bayar_piutang')->name('bayar_piutang_ayam');
        Route::post('/save_penjualan_ayam', 'save_penjualan_ayam')->name('save_penjualan_ayam');
        Route::post('/edit_save_penjualan_ayam', 'edit_save_penjualan_ayam')->name('edit_save_penjualan_ayam');
        Route::post('/save_bayar_piutang_ayam', 'save_bayar_piutang')->name('save_bayar_piutang_ayam');
    });
    Route::controller(Penjualan_umum_cekController::class)->group(function () {
        Route::get('/penjualan_umum_cek', 'index')->name('penjualan_umum_cek');
        Route::get('/penyetoran_penjualan_umum', 'penyetoran')->name('penyetoran_penjualan_umum');
        Route::get('/penjualan_umum_perencanaan_setor', 'perencanaan_setor')->name('penjualan_umum_perencanaan_setor');
        Route::get('/penjualan_umum_get_history_perencanaan', 'get_history_perencanaan')->name('penjualan_umum_get_history_perencanaan');
        Route::get('/penjualan_umum_print_setoran', 'print_setoran')->name('penjualan_umum_print_setoran');
        Route::get('/penjualan_umum_delete_perencanaan', 'delete_perencanaan')->name('penjualan_umum_delete_perencanaan');
        Route::post('/penjualan_umum_save_perencanaan', 'save_perencanaan')->name('penjualan_umum_save_perencanaan');
        Route::get('/terima_invoice_umum_cek', 'terima_invoice_umum_cek')->name('terima_invoice_umum_cek');
        Route::get('/get_list_perencanaan_umum', 'get_list_perencanaan_umum')->name('get_list_perencanaan_umum');
        Route::get('/get_perencanaan_umum', 'get_perencanaan_umum')->name('get_perencanaan_umum');
        Route::post('/save_cek_umum_invoice', 'save_cek_umum_invoice')->name('save_cek_umum_invoice');
        Route::post('/save_setoran_umum', 'save_setoran_umum')->name('save_setoran_umum');
    });

    Route::controller(OpnamemtdController::class)->group(function () {
        Route::get('/opnamemtd', 'index')->name('opnamemtd');
        Route::get('/bayar_opname', 'bayar_opname')->name('bayar_opname');
        Route::post('/save_opname_telur_mtd', 'save_opname_telur_mtd')->name('save_opname_telur_mtd');
        Route::post('/save_bayar_opname', 'save_bayar_opname')->name('save_bayar_opname');
        Route::get('/bukukan_opname_martadah', 'bukukan_opname_martadah')->name('bukukan_opname_martadah');
        Route::get('/terima_opname', 'terima_opname')->name('terima_opname');
        Route::get('/history_opname_mtd', 'history_opname_mtd')->name('history_opname_mtd');
    });
});


Route::controller(Laporan_layerController::class)->group(function () {
    Route::get('/laporan_layer', 'index')->name('laporan_layer');
    Route::get('/laporan-layer/export', 'export')->name('laporan_layer.export');
    Route::get('/rumus_layer', 'rumus_layer')->name('rumus_layer');
    Route::get('/get_history_produk', 'get_history_produk')->name('get_history_produk');
    Route::get('/laporan-layer/hd-tiga-minggu', 'hdTigaMinggu')
        ->name('laporan_layer.hd_tiga_minggu');
});
Route::controller(DokumentasiLaporanLayerController::class)->group(function () {
    Route::get('/dokumentasi_laporan_layer', 'index')->name('dokumentasi_laporan_layer');
});
Route::controller(LaporanStokPersediaanController::class)->group(function () {
    Route::get('/laporan/stok-persediaan', 'index')->name('laporan.stok-persediaan');
    Route::get('/laporan/stok-persediaan/{produk}', 'detail')->name('laporan.stok-persediaan.detail');
});
Route::controller(MedionController::class)->group(function () {
    Route::get('/record_pullet', 'index')->name('record_pullet');
    Route::get('/export_pullet_medion', 'export')->name('export_pullet_medion');
});
Route::controller(ExportRecordingController::class)->group(function () {
    Route::get('/commercial_layer', 'commercial_layer')->name('commercial_layer');
});
Route::controller(AkunPerkiraanController::class)->group(function () {
    Route::get('/akun_perkiraan', 'index')->name('akun_perkiraan');
    Route::get('/akun_perkiraan.biaya', 'biaya')->name('akun_perkiraan.biaya');
    Route::get('/akun_perkiraan.biaya_hpp', 'biaya_hpp')->name('akun_perkiraan.biaya_hpp');
    Route::get('/akun_perkiraan.kandang', 'kandang')->name('akun_perkiraan.kandang');
    Route::get('/labaRugiKandang', 'labaRugiKandang')->name('labaRugiKandang');
    Route::get('/labaRugiKandang2', 'labaRugiKandang2')->name('labaRugiKandang2');
    Route::get('/laba-rugi-kandang2', 'labaRugiKandang2')->name('laba-rugi-kandang2');
    Route::get('/labaRugiKandang_view', 'getLabaRugiData')->name('labaRugiKandang_view');
    Route::post('/importHpp', 'importHpp')->name('importHpp');
    Route::post('/importBiaya', 'importBiaya')->name('importBiaya');
    Route::post('/importPenjualan', 'importPenjualan')->name('importPenjualan');
    Route::get('/accurate/callback', 'accurate')->name('accurate.callback');
    Route::get('/accurate/databases', 'getDatabases')->name('getDatabases');
    Route::post('/accurate/openDatabase', 'openDatabase')->name('openDatabase');
    Route::get('/accurate/getItems', 'getItems')->name('getItems');
    Route::get('/accurate/openDb', 'openDb')->name('openDb');
});
Route::controller(LaporanLabarugiKandangController::class)->group(function () {
    Route::get('/laporanlabakandang', 'index')->name('laporanlabakandang');
    Route::get('/saveLabaRugiKandang', 'saveLabaRugiKandang')->name('saveLabaRugiKandang');
    Route::get('/detailLabaRugiKandang', 'detailLabaRugiKandang')->name('detailLabaRugiKandang');
});

Route::controller(ForecastController::class)
    ->prefix('forecast')
    ->name('forecast.')
    ->group(function () {
        Route::get('/detailEggProduction', 'detailEggProduction')->name('detailEggProduction');
    });
