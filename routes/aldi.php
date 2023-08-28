<?php

use App\Http\Controllers\AksesController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\BarangDaganganController;
use App\Http\Controllers\CashflowController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardKandangController;
use App\Http\Controllers\DataKandangController;
use App\Http\Controllers\JualController;
use App\Http\Controllers\JurnalPenyesuaianController;
use App\Http\Controllers\OpnameController;
use App\Http\Controllers\PenjualanUmumController;
use App\Http\Controllers\PenutupController;
use App\Http\Controllers\PenyetoranController;
use App\Http\Controllers\PeralatanController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\PoController;
use App\Http\Controllers\ProfitController;
use App\Http\Controllers\StokTelurMtdController;
use App\Http\Controllers\SuplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/403', function () {
        view('error.403');
    })->name('403');

    Route::controller(PoController::class)
        ->prefix('po')
        ->name('po.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/add', 'add')->name('add');
            Route::get('/load_view_add', 'load_view_add')->name('load_view_add');
            Route::get('/tbh_baris', 'tbh_baris')->name('tbh_baris');
            Route::post('/', 'create')->name('create');
            Route::get('/{gudang_id}', 'index')->name('detail');
            Route::get('/edit/{id_produk}', 'edit_load')->name('edit_load');
            Route::post('/edit', 'edit')->name('edit');
            Route::get('/delete/{id_produk}', 'delete')->name('delete');
        });

    Route::controller(OpnameController::class)
        ->prefix('opname')
        ->name('opname.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/add', 'add')->name('add');
            Route::get('/add/{gudang_id}', 'add')->name('add_detail');
            Route::get('/delete/{no_nota}', 'delete')->name('delete');
            Route::post('/', 'save')->name('save');
            Route::get('/cetak', 'cetak')->name('cetak');
            Route::get('/edit/{no_nota}', 'edit')->name('edit');
            Route::post('/update', 'update')->name('update');
            Route::get('/detail/{no_nota}', 'detail')->name('detail');
            Route::get('/{gudang_id}', 'index')->name('detail');
        });

    Route::controller(BahanBakuController::class)
        ->prefix('bahan_baku')
        ->name('bahan_baku.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/create', 'create')->name('create');
            Route::get('/stok_masuk', 'stokMasuk')->name('stok_masuk');
            Route::get('/add', 'add')->name('stok_masuk_add');
            Route::get('/opname', 'opname')->name('opname');
            Route::get('/delete', 'delete')->name('delete');
            Route::post('/edit', 'edit')->name('edit');
            Route::post('/store', 'store')->name('store');
            Route::get('/load_menu', 'load_menu')->name('load_menu');
            Route::get('/tbh_baris', 'tbh_baris')->name('tbh_baris');
            Route::get('/opname', 'opname')->name('opname');
            Route::get('/opname/add', 'opname_add')->name('opname.add');
            Route::post('/opname/add', 'opname_store')->name('opname.save');
            Route::get('/opname/add/{gudang_id}', 'opname_add')->name('opname.add_detail');
            Route::get('/opname/cetak/{no_nota}', 'opname_cetak')->name('opname.cetak');
            Route::get('/opname/detail/{gudang_id}', 'opname_detail')->name('opname.detail');
            Route::get('/opname/{gudang_id}', 'opname')->name('opname.detail');
            Route::get('/stok_masuk/{gudang_id}', 'stokMasuk')->name('stok_masuk_segment');
            // Route::get('/stok_masuk/edit/{id_produk}', 'edit_load')->name('edit_load');
            Route::get('/{gudang_id}', 'index')->name('detail');
            Route::get('/edit/{id_produk}', 'edit_load')->name('edit_load');
        });

    Route::controller(BarangDaganganController::class)
        ->prefix('barang_dagangan')
        ->name('barang_dagangan.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/create', 'create')->name('create');
            Route::post('/edit', 'edit')->name('edit');
            Route::get('/stok_masuk', 'stokMasuk')->name('stok_masuk');
            Route::get('/add', 'add')->name('add');
            Route::get('/load_menu', 'load_menu')->name('load_menu');
            Route::get('/tbh_baris', 'tbh_baris')->name('tbh_baris');
            Route::get('/get_stok_sebelumnya', 'get_stok_sebelumnya')->name('get_stok_sebelumnya');
            Route::post('/store', 'store')->name('store');
            Route::get('/delete', 'delete')->name('delete');
            Route::get('/opname', 'opname')->name('opname');
            Route::get('/opname/add', 'opname_add')->name('opname.add');
            Route::post('/opname/add', 'opname_store')->name('opname.save');
            Route::post('/opname/update', 'opname_update')->name('opname.update');
            Route::get('/opname/add/{gudang_id}', 'opname_add')->name('opname.add_detail');
            Route::get('/opname/delete/{gudang_id}', 'opname_delete')->name('opname.delete');
            Route::get('/opname/edit/{no_nota}', 'opname_edit')->name('opname.edit');
            Route::get('/opname/cetak/{no_nota}', 'opname_cetak')->name('opname.cetak');
            Route::get('/opname/detail/{gudang_id}', 'opname_detail')->name('opname.detail');
            Route::get('/opname/{gudang_id}', 'opname')->name('opname.detail');
            Route::get('/{gudang_id}', 'index')->name('detail');
            Route::get('/stok_masuk/{gudang_id}', 'stokMasuk')->name('stok_masuk_segment');
            Route::get('/stok_masuk/edit/{id_produk}', 'detail')->name('edit_load');
        });

    Route::controller(PenutupController::class)
        ->prefix('penutup')
        ->name('penutup.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/saldo', 'saldo')->name('saldo');
            Route::post('/edit_akun', 'edit_akun')->name('edit_akun');
            Route::get('/akun', 'akun')->name('akun');
            Route::get('/history', 'history')->name('history');
        });

    Route::controller(SuplierController::class)
        ->prefix('suplier')
        ->name('suplier.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'create')->name('create');
            Route::post('/update', 'update')->name('update');
            Route::get('/delete/{id_suplier}', 'delete')->name('delete');
            Route::get('/edit/{id_suplier}', 'edit')->name('edit');
        });

    Route::controller(CustomerController::class)
        ->prefix('customer')
        ->name('customer.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'create')->name('create');
            Route::post('/update', 'update')->name('update');
            Route::get('/delete/{id_customer}', 'delete')->name('delete');
            Route::get('/edit/{id_customer}', 'edit')->name('edit');
        });

    Route::controller(JurnalPenyesuaianController::class)
        ->prefix('penyesuaian')
        ->name('penyesuaian.')
        ->group(function () {
            Route::get('/', 'jurnal')->name('index');
            Route::get('/aktiva', 'index')->name('aktiva');
            Route::post('/aktiva', 'save_aktiva')->name('save_aktiva');

            Route::get('/peralatan', 'peralatan')->name('peralatan');
            Route::post('/save_peralatan', 'save_peralatan')->name('save_peralatan');

            Route::get('/atk', 'atk')->name('atk');
            Route::get('/atk/{gudang_id}', 'atk')->name('atk_gudang');
            Route::post('/save_atk', 'save_atk')->name('save_atk');

            Route::get('/umum', 'umum')->name('umum');
            Route::post('/save_umum', 'save_umum')->name('save_umum');
            // Route::get('/atk/{gudang_id}', 'atk')->name('atk_gudang');
        });

    Route::controller(UserController::class)
        ->prefix('user')
        ->name('user.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'create')->name('create');
            Route::get('/edit', 'edit')->name('edit');
            Route::post('/edit', 'update')->name('update');
            Route::get('/delete', 'delete')->name('delete');
        });

    Route::controller(AksesController::class)
        ->prefix('akses')
        ->name('akses.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/navbar', 'detail_edit')->name('navbar');
            Route::get('/{id}', 'detail')->name('detail');
            Route::get('/{id}', 'navbar_delete')->name('navbar_delete');
            Route::get('/detail/{id}', 'detail_get')->name('detail_get');
            Route::post('/', 'save')->name('save');
            Route::post('/add_menu', 'addMenu')->name('add_menu');
            Route::post('/edit_menu', 'editMenu')->name('edit_menu');
        });

    Route::controller(JualController::class)
        ->prefix('jual')
        ->name('jual.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/aldiexport', 'aldiexport')->name('aldiexport');
            Route::get('/bayar', 'bayar')->name('bayar');
            Route::get('/add', 'add')->name('add');
            Route::get('/tbh_baris', 'tbh_baris')->name('tbh_baris');
            Route::get('/get_kredit_pi', 'get_kredit_pi')->name('get_kredit_pi');
            Route::get('/tbh_add', 'tbh_add')->name('tbh_add');
            Route::get('/export', 'export')->name('export');
            Route::get('/edit', 'edit')->name('edit');
            Route::get('/edit_pembayaran', 'edit_pembayaran')->name('edit_pembayaran');
            Route::post('/edit_save_penjualan', 'edit_save_penjualan')->name('edit_save_penjualan');
            Route::post('/edit_save_pembayaran', 'edit_save_pembayaran')->name('edit_save_pembayaran');
            Route::post('/piutang', 'piutang')->name('piutang');
            Route::post('/', 'create')->name('create');
            Route::get('/delete', 'delete')->name('delete');
        });

    Route::controller(ProfitController::class)
        ->prefix('profit')
        ->name('profit.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/load', 'load')->name('load');
            Route::get('/add', 'add')->name('add');
            Route::get('/modal', 'modal')->name('modal');
            Route::get('/delete', 'delete')->name('delete');
            Route::get('/print', 'print')->name('print');
            Route::get('/view_akun', 'view_akun')->name('view_akun');
            Route::get('/load_uraian', 'load_uraian')->name('load_uraian');
            Route::get('/count_sisa', 'count_sisa')->name('count_sisa');
            Route::get('/save_subkategori', 'save_subkategori')->name('save_subkategori');
            Route::get('/delete_subkategori', 'delete_subkategori')->name('delete_subkategori');
            Route::get('/update', 'update')->name('update');
        });

    Route::controller(PeralatanController::class)
        ->prefix('peralatan')
        ->name('peralatan.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/add', 'add')->name('add');
            Route::post('/save_kelompok', 'save_kelompok')->name('save_kelompok');
            Route::get('/delete_kelompok', 'delete_kelompok')->name('delete_kelompok');
            Route::get('/delete_peralatan', 'delete_peralatan')->name('delete_peralatan');
            Route::get('/edit_kelompok', 'edit_kelompok')->name('edit_kelompok');
            Route::get('/load_edit', 'load_edit')->name('load_edit');
            Route::get('/load_menu_add_aktiva', 'load_menu_add_aktiva')->name('load_menu_add_aktiva');
            Route::get('/tambah_baris_aktiva', 'tambah_baris_aktiva')->name('tambah_baris_aktiva');
            Route::get('/nota_jurnal/{no_nota}/{kategori?}/{print?}', 'nota_jurnal')->name('nota_jurnal');
            Route::post('/save_jurnal', 'save_jurnal')->name('save_jurnal');
            Route::get('/load_aktiva', 'load_aktiva')->name('load_aktiva');
            Route::get('/get_data_kelompok', 'get_data_kelompok')->name('get_data_kelompok');
            Route::post('/save_aktiva', 'save_aktiva')->name('save_aktiva');
        });

    Route::controller(PenjualanUmumController::class)
        ->prefix('penjualan2')
        ->name('penjualan2.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/add', 'add')->name('add');
            Route::post('/add', 'store')->name('store');
            Route::get('/tbh_add', 'tbh_add')->name('tbh_add');
            Route::get('/tbh_pembayaran', 'tbh_pembayaran')->name('tbh_pembayaran');
            Route::get('/delete', 'delete')->name('delete');
            Route::get('/edit', 'edit')->name('edit');
            Route::post('/edit', 'update')->name('update');
            Route::get('/print', 'print')->name('print');
            Route::get('/detail/{no_nota}', 'detail')->name('detail');
        });

    Route::controller(PenjualanUmumController::class)
        ->prefix('penjualan2')
        ->name('penjualan2.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/add', 'add')->name('add');
            Route::post('/add', 'store')->name('store');
            Route::get('/tbh_add', 'tbh_add')->name('tbh_add');
            Route::get('/tbh_pembayaran', 'tbh_pembayaran')->name('tbh_pembayaran');
            Route::get('/delete', 'delete')->name('delete');
            Route::get('/edit', 'edit')->name('edit');
            Route::post('/edit', 'update')->name('update');
            Route::get('/print', 'print')->name('print');
            Route::get('/detail/{no_nota}', 'detail')->name('detail');
        });

    Route::controller(PiutangController::class)
        ->prefix('piutang')
        ->name('piutang.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit', 'edit')->name('edit');
            Route::get('/bayar', 'bayar')->name('bayar');
            Route::get('/tbh_baris', 'tbh_baris')->name('tbh_baris');
            Route::post('/create', 'create')->name('create');
            Route::get('/export', 'export')->name('export');
            Route::get('/get_kredit_pi', 'get_kredit_pi')->name('get_kredit_pi');
            Route::get('/edit_pembayaran', 'edit_pembayaran')->name('edit_pembayaran');
            Route::post('/edit_save_pembayaran', 'edit_save_pembayaran')->name('edit_save_pembayaran');
            Route::get('/detail/{no_nota}', 'detail')->name('detail');
        });
    Route::controller(PenyetoranController::class)
        ->prefix('penyetoran')
        ->name('penyetoran.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/perencanaan', 'perencanaan')->name('perencanaan');
            Route::post('/perencanaan', 'save_perencanaan')->name('save_perencanaan');
            Route::get('/bayar', 'bayar')->name('bayar');
            Route::get('/load_perencanaan', 'load_perencanaan')->name('load_perencanaan');
            Route::get('/load_history', 'load_history')->name('load_history');
            Route::get('/print', 'print')->name('print');
            Route::get('/print_setor', 'print_setor')->name('print_setor');
            Route::get('/export', 'export')->name('export');
            Route::post('/hapus_setor', 'hapus_setor')->name('hapus_setor');
            Route::get('/edit/{nota}', 'edit')->name('edit');
            Route::get('/kembali/{nota}', 'kembali')->name('kembali');
            Route::post('/edit', 'save_setor')->name('save_setor');
            Route::get('/delete', 'delete')->name('delete');
        });
    Route::controller(DataKandangController::class)
        ->prefix('data_kandang')
        ->name('data_kandang.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/delete', 'delete')->name('delete');
            Route::get('/edit/{id}', 'edit')->name('edit');
        });

    Route::controller(StokTelurMtdController::class)
        ->prefix('stok_telur_mtd')
        ->name('stok_telur_mtd.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/add', 'add')->name('add');
        });

    Route::controller(DashboardKandangController::class)
        ->prefix('dashboard_kandang')
        ->name('dashboard_kandang.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/tambah_telur', 'tambah_telur')->name('tambah_telur');
            Route::get('/load_telur/{id_kandang}', 'load_telur')->name('load_telur');
            Route::get('/load_populasi/{id_kandang}', 'load_populasi')->name('load_populasi');
            Route::post('/tambah_populasi', 'tambah_populasi')->name('tambah_populasi');
            Route::post('/tambah_karung', 'tambah_karung')->name('tambah_karung');

            // perencanaan
            Route::get('/load_perencanaan/{id_kandang}', 'load_perencanaan')->name('load_perencanaan');
            Route::post('/tambah_perencanaan', 'tambah_perencanaan')->name('tambah_perencanaan');
            Route::get('/load_pakan_perencanaan', 'load_pakan_perencanaan')->name('load_pakan_perencanaan');
            Route::get('/save_tambah_pakan', 'save_tambah_pakan')->name('save_tambah_pakan');
            Route::get('/tbh_pakan', 'tbh_pakan')->name('tbh_pakan');
            Route::get('/get_stok_pakan', 'get_stok_pakan')->name('get_stok_pakan');


            // penjualan martadah
            Route::get('/penjualan_telur', 'penjualan_telur')->name('penjualan_telur');
            Route::get('/add_penjualan_telur', 'add_penjualan_telur')->name('add_penjualan_telur');
            Route::get('/edit_telur', 'edit_telur')->name('edit_telur');
            Route::post('/save_penjualan_telur', 'save_penjualan_telur')->name('save_penjualan_telur');
            Route::post('/save_edit_telur', 'save_edit_telur')->name('save_edit_telur');

            // transfer stok
            Route::get('/transfer_stok', 'transfer_stok')->name('transfer_stok');
            Route::get('/add_transfer_stok', 'add_transfer_stok')->name('add_transfer_stok');
            Route::post('/save_transfer', 'save_transfer')->name('save_transfer');

            // penjualan umum
            Route::get('/penjualan_umum', 'penjualan_umum')->name('penjualan_umum');
            Route::get('/tbh_add', 'tbh_add')->name('tbh_add');
            Route::get('/get_stok', 'get_stok')->name('get_stok');
            Route::get('/edit_penjualan', 'edit_penjualan')->name('edit_penjualan');
            Route::get('/add_penjualan_umum', 'add_penjualan_umum')->name('add_penjualan_umum');
            Route::get('/detail/{urutan}', 'detail')->name('detail');
            Route::get('/load_detail_nota/{urutan}', 'load_detail_nota')->name('load_detail_nota');
            Route::post('/update_penjualan', 'update_penjualan')->name('update_penjualan');
            Route::post('/save_penjualan_umum', 'save_penjualan_umum')->name('save_penjualan_umum');
        });
});
