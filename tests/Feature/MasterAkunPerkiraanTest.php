<?php

namespace Tests\Feature;

use App\Models\AkunPerkiraan;
use App\Models\User;
use App\Services\ImportAkunPerkiraanService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MasterAkunPerkiraanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('akun_perkiraan');
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
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
    }

    public function test_route_requires_authentication(): void
    {
        $this->get(route('master.akun-perkiraan.index'))->assertRedirect('/login');
    }

    public function test_user_can_create_update_and_toggle_account(): void
    {
        $user = User::factory()->create();
        $parent = AkunPerkiraan::create([
            'tipe_akun' => 'BANK',
            'kode_perkiraan' => '1101',
            'nama' => 'Kas & Bank',
        ]);

        $this->actingAs($user)->post(route('master.akun-perkiraan.store'), [
            'tipe_akun' => 'BANK',
            'kode_perkiraan' => '110101',
            'nama' => 'Kas Kecil',
            'id_akun_induk' => $parent->getKey(),
        ])->assertSessionHasNoErrors();

        $account = AkunPerkiraan::where('kode_perkiraan', '110101')->firstOrFail();
        $this->actingAs($user)->put(route('master.akun-perkiraan.update', $account), [
            'tipe_akun' => 'BANK',
            'kode_perkiraan' => '110101',
            'nama' => 'Kas Kecil Proyek',
            'id_akun_induk' => $parent->getKey(),
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->patch(route('master.akun-perkiraan.toggle', $account))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('akun_perkiraan', [
            'kode_perkiraan' => '110101',
            'nama' => 'Kas Kecil Proyek',
            'aktif' => false,
        ]);
    }

    public function test_duplicate_and_circular_parent_are_rejected(): void
    {
        $user = User::factory()->create();
        $parent = AkunPerkiraan::create(['tipe_akun' => 'BANK', 'kode_perkiraan' => '1101', 'nama' => 'Induk']);
        $child = AkunPerkiraan::create([
            'tipe_akun' => 'BANK', 'kode_perkiraan' => '110101', 'nama' => 'Anak', 'id_akun_induk' => $parent->getKey(),
        ]);

        $this->actingAs($user)->post(route('master.akun-perkiraan.store'), [
            'tipe_akun' => 'BANK', 'kode_perkiraan' => '1101', 'nama' => 'Duplikat',
        ])->assertSessionHasErrors('kode_perkiraan');

        $this->actingAs($user)->put(route('master.akun-perkiraan.update', $parent), [
            'tipe_akun' => 'BANK', 'kode_perkiraan' => '1101', 'nama' => 'Induk', 'id_akun_induk' => $child->getKey(),
        ])->assertSessionHasErrors('id_akun_induk');
    }

    public function test_imports_201_rows_and_reimport_does_not_duplicate(): void
    {
        $file = $this->makeWorkbook();
        $service = app(ImportAkunPerkiraanService::class);
        $rows = $service->preview($file);

        $this->assertCount(201, $rows);
        $this->assertCount(27, collect($rows)->whereNull('kode_akun_induk'));
        $service->simpan($rows);
        $service->simpan($rows);

        $this->assertDatabaseCount('akun_perkiraan', 201);
        $this->assertSame(174, AkunPerkiraan::whereNotNull('id_akun_induk')->count());
    }

    private function makeWorkbook(): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([['No. ', 'Tipe Akun', 'Kode Perkiraan', 'Nama', 'Akun Induk', 'Cabang Saldo', 'Catatan']]);
        $row = 2;

        for ($root = 1; $root <= 27; $root++) {
            $code = (string) (1000 + $root);
            $sheet->fromArray([[$root, 'BANK', $code, "Induk {$root}", null, null, null]], null, "A{$row}");
            $row++;
        }

        for ($child = 1; $child <= 174; $child++) {
            $parent = (string) (1001 + (($child - 1) % 27));
            $code = $parent.str_pad((string) $child, 3, '0', STR_PAD_LEFT);
            $sheet->fromArray([[$row - 1, 'BANK', $code, "Anak {$child}", $parent, null, null]], null, "A{$row}");
            $row++;
        }

        $path = tempnam(sys_get_temp_dir(), 'akun-perkiraan-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'akun-perkiraan.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
