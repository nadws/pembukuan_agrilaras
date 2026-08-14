<?php

namespace Tests\Feature;

use App\Models\AkunPerkiraan;
use App\Models\ImporJurnalPerkiraan;
use App\Models\JurnalPerkiraan;
use App\Models\User;
use App\Services\ImporJurnalPerkiraanService;
use App\Services\LaporanLabaRugiPerkiraanService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class JurnalPerkiraanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('navbar', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('urutan')->default(0);
            $table->string('isi')->nullable();
            $table->string('route')->nullable();
            $table->string('nama')->nullable();
        });
        Schema::create('akun_perkiraan', function (Blueprint $table) {
            $table->bigIncrements('id_akun_perkiraan');
            $table->string('tipe_akun', 20);
            $table->string('kode_perkiraan', 50)->unique();
            $table->string('nama');
            $table->unsignedBigInteger('id_akun_induk')->nullable();
            $table->string('cabang_saldo')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('impor_jurnal_perkiraan', function (Blueprint $table) {
            $table->bigIncrements('id_impor_jurnal_perkiraan');
            $table->string('nama_file');
            $table->char('hash_file', 64)->unique();
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->unsignedInteger('jumlah_transaksi');
            $table->unsignedInteger('jumlah_detail');
            $table->decimal('total_debit', 24, 12);
            $table->decimal('total_kredit', 24, 12);
            $table->string('status', 20)->default('aktif');
            $table->unsignedBigInteger('diimpor_oleh')->nullable();
            $table->timestamp('dibatalkan_pada')->nullable();
            $table->unsignedBigInteger('dibatalkan_oleh')->nullable();
            $table->timestamps();
        });
        Schema::create('jurnal_perkiraan', function (Blueprint $table) {
            $table->bigIncrements('id_jurnal_perkiraan');
            $table->unsignedBigInteger('id_impor_jurnal_perkiraan');
            $table->unsignedBigInteger('id_akun_perkiraan');
            $table->date('tanggal');
            $table->string('nomor_transaksi');
            $table->string('tipe_transaksi', 100)->nullable();
            $table->unsignedInteger('urutan_detail');
            $table->text('deskripsi')->nullable();
            $table->decimal('debit', 24, 12)->default(0);
            $table->decimal('kredit', 24, 12)->default(0);
            $table->timestamps();
        });
    }

    public function test_valid_file_with_zero_rows_can_be_previewed_and_saved(): void
    {
        $cash = $this->account('1101', 'Kas', 'BANK');
        $revenue = $this->account('4001', 'Pendapatan', 'REVE');
        $file = $this->workbook([
            ['2026-07-01', 'TRX-1', 'Penerimaan Penjualan', '1101', 'Kas', 'Penjualan', 100, 0],
            ['2026-07-01', 'TRX-1', 'Penerimaan Penjualan', '4001', 'Pendapatan', 'Penjualan', 0, 100],
            ['2026-07-02', 'TRX-2', 'Jurnal Umum', '1101', 'Kas', 'Baris nol', 0, 0],
            ['2026-07-02', 'TRX-2', 'Jurnal Umum', '4001', 'Pendapatan', 'Baris nol', 0, 0],
        ]);

        $service = app(ImporJurnalPerkiraanService::class);
        $preview = $service->pratinjau($file);
        $this->assertSame(2, $preview['jumlah_transaksi']);
        $this->assertSame(4, $preview['jumlah_detail']);
        $this->assertSame([], $preview['errors']);
        $batch = $service->simpan($preview, null);
        $this->assertDatabaseCount('jurnal_perkiraan', 4);
        $this->assertSame($cash->getKey(), JurnalPerkiraan::first()->id_akun_perkiraan);
        $this->assertSame('Penerimaan Penjualan', JurnalPerkiraan::first()->tipe_transaksi);
        $this->assertSame(['Jurnal Umum' => 1, 'Penerimaan Penjualan' => 1], $preview['ringkasan_tipe']);
        $this->assertSame('100.000000000000', $batch->total_debit);
        $this->assertNotNull($revenue);
    }

    public function test_duplicate_file_is_rejected(): void
    {
        $this->account('1101', 'Kas', 'BANK');
        $file = $this->workbook([
            ['2026-07-01', 'TRX-1', 'Jurnal Umum', '1101', 'Kas', null, 0, 0],
        ]);
        $service = app(ImporJurnalPerkiraanService::class);
        $preview = $service->pratinjau($file);
        $service->simpan($preview, null);

        $this->expectException(ValidationException::class);
        $service->pratinjau($file);
    }

    public function test_batch_detail_uses_server_side_pagination_and_search(): void
    {
        $user = User::factory()->create();
        $account = $this->account('1101', 'Kas', 'BANK');
        $batch = $this->batch('aktif', 'besar.xlsx');
        foreach (range(1, 60) as $number) {
            JurnalPerkiraan::create([
                'id_impor_jurnal_perkiraan' => $batch->getKey(),
                'id_akun_perkiraan' => $account->getKey(),
                'tanggal' => '2026-07-01',
                'nomor_transaksi' => sprintf('TRX-%03d', $number),
                'tipe_transaksi' => 'Jurnal Umum',
                'urutan_detail' => 1,
                'debit' => 0,
                'kredit' => 0,
            ]);
        }

        $this->actingAs($user)->get(route('jurnal-perkiraan.detail-batch', $batch))
            ->assertOk()
            ->assertSee('1–50 dari 60')
            ->assertSee('TRX-001')
            ->assertDontSee('TRX-060');

        $this->actingAs($user)->get(route('jurnal-perkiraan.detail-batch', [$batch, 'cari' => 'TRX-060']))
            ->assertOk()
            ->assertSee('TRX-060')
            ->assertSee('1–1 dari 1');
    }

    public function test_wrong_headers_are_rejected(): void
    {
        $book = new Spreadsheet();
        $book->getActiveSheet()->fromArray([['Tanggal', 'Kode Salah']]);
        $path = tempnam(sys_get_temp_dir(), 'jurnal-header-').'.xlsx';
        (new Xlsx($book))->save($path);

        $this->expectException(ValidationException::class);
        app(ImporJurnalPerkiraanService::class)->pratinjau(
            new UploadedFile($path, 'salah.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true)
        );
    }

    public function test_invalid_account_name_number_and_unbalanced_transaction_are_reported(): void
    {
        $this->account('1101', 'Kas', 'BANK');
        $preview = app(ImporJurnalPerkiraanService::class)->pratinjau($this->workbook([
            ['2026-07-01', 'TRX-1', 'Jurnal Umum', '1101', 'Nama Salah', null, 100, 0],
            ['2026-07-01', 'TRX-1', 'Jurnal Umum', '9999', 'Tidak Ada', null, 0, 50],
            ['2026-07-02', 'TRX-2', 'Jurnal Umum', '1101', 'Kas', null, -1, 0],
            ['2026-07-03', 'TRX-3', '', '1101', 'Kas', null, 0, 0],
            ['2026-07-04', 'TRX-4', 'Jurnal Umum', '1101', 'Kas', null, 10, 0],
        ]));

        $messages = collect($preview['errors'])->pluck('pesan')->implode(' ');
        $this->assertStringContainsString('Nama akun tidak cocok', $messages);
        $this->assertStringContainsString('Kode akun tidak ditemukan', $messages);
        $this->assertStringContainsString('Debit harus berupa angka nonnegatif', $messages);
        $this->assertStringContainsString('Tipe transaksi wajib diisi', $messages);
        $this->assertStringContainsString('tidak seimbang', $messages);
    }

    public function test_deleted_batch_is_removed_and_cancelled_batch_is_excluded_from_profit_and_loss(): void
    {
        $user = User::factory()->create();
        $revenue = $this->account('4001', 'Pendapatan', 'REVE');
        $active = $this->batch('aktif', 'active.xlsx');
        $cancelled = $this->batch('dibatalkan', 'cancelled.xlsx');
        $this->journal($active, $revenue, '100');
        $this->journal($cancelled, $revenue, '900');

        $report = app(LaporanLabaRugiPerkiraanService::class)->buat(
            Carbon::create(2026, 7, 1), Carbon::create(2026, 7, 1)
        );
        $this->assertSame('100.000000000000', $report['revenue']['2026-07']);

        $this->actingAs($user)->get(route('jurnal-perkiraan.laba-rugi', [
            'bulan_dari' => 7, 'tahun_dari' => 2026, 'bulan_sampai' => 7, 'tahun_sampai' => 2026,
        ]))->assertOk()
            ->assertSee('Laba/Rugi (Multi Periode)')
            ->assertSee('Juli 2026 (IDR)')
            ->assertSee('laporan-scroll', false)
            ->assertSee('Cari Nama Akun')
            ->assertSee('account-row', false)
            ->assertSee(route('jurnal-perkiraan.detail-akun', [
                'akun_perkiraan' => $revenue,
                'tanggal_awal' => '2026-07-01',
                'tanggal_akhir' => '2026-07-31',
            ]));

        $this->actingAs($user)->get(route('jurnal-perkiraan.detail-akun', [
            'akun_perkiraan' => $revenue,
            'tanggal_awal' => '2026-07-01',
            'tanggal_akhir' => '2026-07-31',
        ]))->assertOk()
            ->assertSee('Total Debit')
            ->assertSee('Total Kredit')
            ->assertSee('Nilai Laporan')
            ->assertSee('100,00');

        $this->actingAs($user)->patch(route('jurnal-perkiraan.batalkan', $active))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('impor_jurnal_perkiraan', ['id_impor_jurnal_perkiraan' => $active->getKey()]);
        $this->assertDatabaseMissing('jurnal_perkiraan', ['id_impor_jurnal_perkiraan' => $active->getKey()]);
        $this->assertDatabaseCount('jurnal_perkiraan', 1);
    }

    private function account(string $code, string $name, string $type): AkunPerkiraan
    {
        return AkunPerkiraan::create(['kode_perkiraan' => $code, 'nama' => $name, 'tipe_akun' => $type]);
    }

    private function batch(string $status, string $file): ImporJurnalPerkiraan
    {
        return ImporJurnalPerkiraan::create([
            'nama_file' => $file, 'hash_file' => hash('sha256', $file), 'periode_awal' => '2026-07-01',
            'periode_akhir' => '2026-07-31', 'jumlah_transaksi' => 1, 'jumlah_detail' => 1,
            'total_debit' => 0, 'total_kredit' => 0, 'status' => $status,
        ]);
    }

    private function journal(ImporJurnalPerkiraan $batch, AkunPerkiraan $account, string $credit): void
    {
        JurnalPerkiraan::create([
            'id_impor_jurnal_perkiraan' => $batch->getKey(), 'id_akun_perkiraan' => $account->getKey(),
            'tanggal' => '2026-07-01', 'nomor_transaksi' => 'TRX-'.$batch->getKey(), 'urutan_detail' => 1,
            'debit' => 0, 'kredit' => $credit,
        ]);
    }

    private function workbook(array $rows): UploadedFile
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->fromArray([['Tanggal', 'No. Transaksi', 'Tipe Transaksi', 'Kode Perkiraan', 'Nama Perkiraan', 'Deskripsi', 'Debit', 'Kredit']]);
        $sheet->fromArray($rows, null, 'A2');
        $path = tempnam(sys_get_temp_dir(), 'jurnal-perkiraan-').'.xlsx';
        (new Xlsx($book))->save($path);

        return new UploadedFile($path, 'jurnal.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
