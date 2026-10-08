<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LaporanPerencanaanService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/** Requires an explicit database copy. Never uses RefreshDatabase or migrations. */
class LaporanPerencanaanTest extends TestCase
{
    private LaporanPerencanaanService $service;

    private string $date = '2099-01-01';

    private int $kandang = 990001;

    private bool $transactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('PERENCANAAN_TEST_DATABASE');
        if (! $database || ! preg_match('/^agrilaras_perencanaan_test_[a-zA-Z0-9_]+$/', $database)) {
            $this->markTestSkipped('Set PERENCANAAN_TEST_DATABASE ke database salinan khusus.');
        }
        $production = \Dotenv\Dotenv::parse(file_get_contents(base_path('.env')))['DB_DATABASE'] ?? '';
        $this->assertNotSame($production, $database);
        config(['database.default' => 'mysql', 'database.connections.mysql.database' => $database,
            'database.connections.mysql.url' => null, 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('mysql');
        $this->assertSame($database, DB::connection()->getDatabaseName());
        DB::beginTransaction();
        $this->transactionStarted = true;
        $this->service = app(LaporanPerencanaanService::class);
        $user = User::where('posisi_id', 1)->firstOrFail();
        $this->actingAs($user);
        DB::table('kandang')->insert(['id_kandang' => $this->kandang, 'nm_kandang' => 'Kandang Uji Salinan',
            'chick_in' => '2098-01-01', 'stok_awal' => 100, 'id_strain' => 1, 'selesai' => 'T', 'rupiah' => 0, 'id_post' => 0]);
        foreach (['pakan', 'obat_pakan', 'obat_air', 'obat_ayam', 'vaksin'] as $index => $category) {
            $id = 990101 + $index;
            DB::table('tb_produk_perencanaan')->insert(['id_produk' => $id, 'nm_produk' => 'Uji '.$category,
                'kategori' => $category, 'tgl' => '2098-01-01', 'admin' => 'uji salinan', 'dosis_satuan' => 1, 'campuran_satuan' => 1]);
            DB::table('stok_produk_perencanaan')->insert($this->stockRow($id, 'BELI-UJI', ['tgl' => '2098-12-31',
                'id_kandang' => 0, 'pcs' => 100000, 'total_rp' => $category === 'pakan' ? 200000 : 300000]));
        }
        foreach (['karung', 'pupuk'] as $type) {
            DB::table('stok_ayam')->insert(['tgl' => '2098-12-31', 'no_nota' => 'BELI-UJI', 'debit' => 10000,
                'kredit' => 0, 'id_gudang' => 1, 'admin' => 'uji salinan', 'transfer' => 'T', 'cek' => 'T', 'jenis' => $type]);
        }
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
        parent::tearDown();
    }

    private function stockRow(int $id, string $note, array $replace = []): array
    {
        return array_replace(['tgl' => $this->date, 'id_kandang' => $this->kandang, 'id_pakan' => $id,
            'pcs' => 0, 'pcs_kredit' => 0, 'total_rp' => 0, 'biaya_dll' => 0, 'admin' => 'uji salinan',
            'check' => 'T', 'cek_admin' => '', 'opname' => 'T', 'h_opname' => 'T', 'penyesuaian' => 'T', 'no_nota' => $note], $replace);
    }

    private function input(array $replace = []): array
    {
        return array_replace(['tgl' => $this->date, 'id_kandang' => $this->kandang, 'kg_pakan_box' => 10,
            'gr_pakan_ekor' => 100, 'pakan' => [['id_produk' => 990101, 'persen' => 100]],
            'obat_pakan' => [['id_produk' => 990102, 'dosis' => 2, 'campuran' => 10]],
            'obat_air' => [['id_produk' => 990103, 'dosis' => 3, 'campuran' => 5, 'waktu' => '08:30', 'cara_pemakaian' => 'Minum', 'ket' => 'Lengkap']],
            'obat_ayam' => [['id_produk' => 990104, 'dosis' => 0.01]]], $replace);
    }

    private function save(array $replace = [], bool $correction = false): bool
    {
        $input = $this->input($replace);
        if ($correction && ! isset($input['snapshot'])) {
            $input['snapshot'] = $this->service->snapshot($this->date, $this->kandang);
        }

        return $this->service->save($input, $this->date, $this->kandang, $correction, 'uji salinan');
    }

    private function scope(): array
    {
        return ['tgl' => $this->date, 'id_kandang' => $this->kandang];
    }

    private function state(): string
    {
        $data = [];
        foreach (['tb_pakan_perencanaan', 'tb_obat_perencanaan', 'tb_karung_perencanaan', 'stok_produk_perencanaan'] as $table) {
            $data[$table] = DB::table($table)->where($this->scope())->get()->all();
        }
        $data['byproducts'] = DB::table('stok_ayam')->where('tgl', $this->date)->get()->all();
        $data['journal'] = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $this->service->number($this->date, $this->kandang))->get()->all();

        return json_encode($data);
    }

    private function rejected(callable $operation, string $message): void
    {
        try {
            $operation();
            $this->fail('Simpan seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString($message, implode(' ', $exception->errors()['perencanaan']));
        }
    }

    public function test_server_calculates_all_quantities_and_balanced_pph(): void
    {
        $this->assertTrue($this->save(['populasi' => 999999, 'gr_pakan' => [999999], 'kg_karung' => 999999]));
        $this->assertEquals(10000, DB::table('tb_pakan_perencanaan')->where($this->scope())->sum('gr'));
        $usage = DB::table('stok_produk_perencanaan')->where($this->scope())->pluck('pcs_kredit', 'id_pakan');
        $this->assertEquals(2, $usage[990102]);
        $this->assertEquals(3, $usage[990103]);
        $this->assertEquals(1, $usage[990104]);
        $journal = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $this->service->number($this->date, $this->kandang))->get();
        $this->assertCount(4, $journal);
        $this->assertEquals(20018, $journal->sum('debit'));
        $this->assertEquals($journal->sum('debit'), $journal->sum('kredit'));
        $this->assertSame(['Pemakaian Pakan Harian'], $journal->pluck('tipe_transaksi')->unique()->all());
    }

    public function test_correction_preserves_other_items_check_and_uses_byproduct_deltas(): void
    {
        $this->save();
        DB::table('stok_produk_perencanaan')->where($this->scope())->update(['check' => 'Y', 'cek_admin' => 'admin lama']);
        $vaccineId = DB::table('tb_vaksin_perencanaan')->insertGetId($this->scope() + ['nm_vaksin' => 'Vaksin Lain', 'qty' => 5, 'ttl_rp' => 15, 'biaya_dll' => 1, 'admin' => 'uji']);
        $otherId = DB::table('stok_produk_perencanaan')->insertGetId($this->stockRow(990105, 'VAKSIN-UJI', ['pcs_kredit' => 5, 'total_rp' => 15]));
        $other = DB::table('stok_produk_perencanaan')->where('id_stok_telur', $otherId)->first();
        $oldNotes = DB::table('stok_ayam')->where('tgl', $this->date)->get()->keyBy('id_stok_ayam');
        $this->save(['gr_pakan_ekor' => 200, 'kg_pakan_box' => 12], true);
        $this->assertEquals(20000, DB::table('tb_pakan_perencanaan')->where($this->scope())->sum('gr'));
        $this->assertEquals($other, DB::table('stok_produk_perencanaan')->where('id_stok_telur', $otherId)->first());
        $this->assertTrue(DB::table('tb_vaksin_perencanaan')->where('id_vaksin', $vaccineId)->exists());
        $this->assertSame('Y', DB::table('stok_produk_perencanaan')->where($this->scope())->where('id_pakan', 990101)->value('check'));
        $this->assertSame('admin lama', DB::table('stok_produk_perencanaan')->where($this->scope())->where('id_pakan', 990101)->value('cek_admin'));
        foreach ($oldNotes as $id => $row) {
            $this->assertEquals($row, DB::table('stok_ayam')->where('id_stok_ayam', $id)->first());
        }
        $this->assertEquals(6, DB::table('stok_ayam')->where('tgl', $this->date)->where('jenis', 'pupuk')->sum(DB::raw('debit-kredit')));
        $this->assertEquals(-12, DB::table('stok_ayam')->where('tgl', $this->date)->where('jenis', 'karung')->sum(DB::raw('debit-kredit')));
        $count = DB::table('stok_ayam')->where('tgl', $this->date)->count();
        $this->save(['gr_pakan_ekor' => 200, 'kg_pakan_box' => 12], true);
        $this->assertSame($count, DB::table('stok_ayam')->where('tgl', $this->date)->count());
        $this->assertSame(4, DB::table('jurnal_perkiraan')->where('nomor_transaksi', $this->service->number($this->date, $this->kandang))->count());
    }

    public function test_duplicate_create_redirects_to_correction_without_writes(): void
    {
        $this->save();
        $before = $this->state();
        $this->post(route('history_perencanaan_pakan.store'), $this->input())->assertRedirect(route('history_perencanaan_pakan.edit', $this->scope()));
        $this->assertFalse($this->save());
        $this->assertSame($before, $this->state());
    }

    public function test_missing_account_rolls_back_detail_stock_byproducts_and_journal(): void
    {
        $this->save();
        $before = $this->state();
        DB::table('akun_perkiraan')->where('nama', 'Persediaan Pakan')->update(['aktif' => 0]);
        $this->rejected(fn () => $this->save(['gr_pakan_ekor' => 200, 'kg_pakan_box' => 12], true), 'Akun aktif');
        $this->assertSame($before, $this->state());
    }

    public function test_stale_form_and_changed_identity_are_rejected(): void
    {
        $this->save();
        $snapshot = $this->service->snapshot($this->date, $this->kandang);
        $this->save(['gr_pakan_ekor' => 150], true);
        $before = $this->state();
        $this->rejected(fn () => $this->save(['snapshot' => $snapshot], true), 'Data berubah');
        $input = $this->input(['target' => encrypt($this->scope()), 'snapshot' => $snapshot, 'tgl' => '2099-01-02']);
        $this->put(route('history_perencanaan_pakan.update'), $input)->assertStatus(422);
        $this->assertSame($before, $this->state());
    }

    public function test_bad_legacy_byproduct_baseline_is_blocked_without_reset(): void
    {
        $this->save();
        DB::table('stok_ayam')->where('tgl', $this->date)->where('jenis', 'pupuk')->delete();
        $before = $this->state();
        $this->rejected(fn () => $this->save(['gr_pakan_ekor' => 200], true), 'Saldo pupuk lama');
        $this->assertSame($before, $this->state());
    }

    public function test_insufficient_stock_future_balance_and_price_are_blocked(): void
    {
        DB::table('stok_produk_perencanaan')->where('id_pakan', 990101)->where('pcs', '>', 0)->update(['pcs' => 500]);
        $this->rejected(fn () => $this->save(), 'Stok tidak cukup');
        $this->assertFalse($this->service->exists($this->date, $this->kandang));
        DB::table('stok_produk_perencanaan')->where('id_pakan', 990101)->where('pcs', '>', 0)->update(['pcs' => 100000]);
        DB::table('stok_produk_perencanaan')->insert($this->stockRow(990101, 'FUTURE-UJI', ['tgl' => '2099-01-02', 'pcs_kredit' => 95000]));
        $this->rejected(fn () => $this->save(), '2099-01-02');
        DB::table('stok_produk_perencanaan')->where('no_nota', 'FUTURE-UJI')->delete();
        DB::table('stok_produk_perencanaan')->where('id_pakan', 990101)->update(['total_rp' => 0]);
        $this->rejected(fn () => $this->save(), 'Harga pembelian');
    }

    public function test_all_fields_render_filter_pagination_permissions_and_locked_identity(): void
    {
        $this->get(route('history_perencanaan_pakan.create'))->assertOk()->assertSee('Tambah Tertinggal');
        $this->getJson(route('history_perencanaan_pakan.context', $this->scope()))->assertOk()->assertJson(['populasi' => 100, 'exists' => false]);
        $this->save();
        DB::table('stok_produk_perencanaan')->where($this->scope())->update(['check' => 'Y']);
        $this->get(route('history_perencanaan_pakan', ['tgl1' => $this->date, 'tgl2' => $this->date, 'id_kandang' => $this->kandang]))
            ->assertOk()->assertSee('Kandang Uji Salinan')->assertSee('Tambah Tertinggal')->assertSee('Koreksi')
            ->assertSee('Tampilkan')->assertSee('Export Lengkap')->assertDontSee('correction-date')->assertDontSee('Buka Koreksi')->assertDontSee('cek_bayar')->assertDontSee('Bukukan');
        $response = $this->get(route('history_perencanaan_pakan.edit', $this->scope()))->assertOk();
        foreach (['Kg pakan/box', 'Populasi', 'Gr Pakan / Ekor', 'Kg/karung sisa', 'Campuran', 'Waktu', 'Cara Pemakaian', 'Keterangan', 'Tambah Obat Air', 'Tambah Pakan'] as $label) {
            $response->assertSee($label);
        }
        $response->assertSee('08:30')->assertSee('Lengkap')->assertSee('readonly', false)->assertSee('disabled', false);
        $this->get(route('history_perencanaan_pakan.detail', $this->scope()))->assertOk()->assertSee('Jurnal PPH');
        $button = DB::table('permission_button as b')->join('permission as p', 'p.id_permission', '=', 'b.permission_id')
            ->where('p.url', 'history_perencanaan_pakan')->where('b.jenis', 'create')->value('b.id_permission_button');
        DB::table('permission_role')->where('posisi_id', 1)->where('id_permission_button', $button)->delete();
        $this->get(route('history_perencanaan_pakan.edit', $this->scope()))->assertForbidden();
        $this->put(route('history_perencanaan_pakan.update'), [])->assertForbidden();
        $this->get(route('history_perencanaan_pakan', ['tgl1' => $this->date, 'tgl2' => $this->date]))->assertOk()->assertDontSee('>Koreksi<', false);
        $this->assertFalse(DB::table('permission_role')->where('posisi_id', 1)->where('id_permission_button', $button)->exists());
    }

    public function test_invalid_percent_category_and_population_are_rejected(): void
    {
        $this->rejected(fn () => $this->save(['pakan' => [['id_produk' => 990101, 'persen' => 90]]]), '100%');
        $this->rejected(fn () => $this->save(['pakan' => [['id_produk' => 990102, 'persen' => 100]]]), 'kategori pakan');
        DB::table('populasi')->insert(['id_kandang' => $this->kandang, 'tgl' => '2099-01-02', 'mati' => 100, 'jual' => 0, 'afkir' => 0, 'admin' => 'uji']);
        $this->assertEquals(100, $this->service->population($this->date, $this->kandang));
        $this->assertEquals(0, $this->service->population('2099-01-02', $this->kandang));
    }

    public function test_extreme_dose_is_rejected_before_mysql_can_clamp_values(): void
    {
        $this->rejected(fn () => $this->save(['obat_pakan' => [['id_produk' => 990102, 'dosis' => 1000000000, 'campuran' => 0.000001]]]), 'di luar batas penyimpanan');
        $this->assertFalse($this->service->exists($this->date, $this->kandang));
        $this->assertSame(0, DB::table('stok_ayam')->where('tgl', $this->date)->count());
    }

    public function test_history_read_is_side_effect_free_and_manual_posting_is_disabled(): void
    {
        $this->save();
        $before = $this->state();
        $this->get(route('history_perencanaan_pakan', ['tgl1' => $this->date, 'tgl2' => $this->date, 'id_kandang' => $this->kandang]))->assertOk();
        $this->get(route('pembukuan_biaya_pv'))->assertStatus(410);
        $this->post(route('bukukan_pv'), ['kategori' => 'pakan'])->assertStatus(410);
        $this->assertSame($before, $this->state());
        $this->get(route('history_perencanaan_pakan', ['kategori' => 'vitamin', 'tgl1' => $this->date, 'tgl2' => $this->date, 'id_kandang' => $this->kandang]))
            ->assertOk()->assertSee('Uji obat_air')->assertDontSee('Uji pakan');
        $this->get(route('history_perencanaan_pakan.edit', ['tgl' => '2099-01-03', 'id_kandang' => $this->kandang]))->assertRedirect()->assertSessionHas('error');
    }

    public function test_copied_kandang_record_loads_complete_detail(): void
    {
        $row = DB::table('tb_pakan_perencanaan')->where('id_kandang', '<>', $this->kandang)->orderByDesc('tgl')->first();
        $this->assertNotNull($row, 'Pengujian harus memakai data salinan kandang.');
        $this->get(route('history_perencanaan_pakan.edit', ['tgl' => $row->tgl, 'id_kandang' => $row->id_kandang]))
            ->assertOk()->assertSee('Obat/ekor ayam')->assertSee('Tambah Pakan');
        $data = app(\App\Services\PerencanaanExportService::class)->build($row->tgl, $row->tgl, (int) $row->id_kandang);
        $this->assertNotEmpty($data[1]['rows']);
        $this->assertCount(9, $data[1]['rows'][0]);
        $this->assertNotEmpty(\Maatwebsite\Excel\Facades\Excel::raw(new \App\Exports\LaporanPerencanaanExport($data), \Maatwebsite\Excel\Excel::XLSX));
    }

    public function test_copied_kandang_record_can_be_corrected_atomically(): void
    {
        $row = DB::table('tb_pakan_perencanaan')->where('id_kandang', '<>', $this->kandang)->orderByDesc('tgl')->first();
        $scope = ['tgl' => $row->tgl, 'id_kandang' => $row->id_kandang];
        $response = $this->get(route('history_perencanaan_pakan.edit', $scope))->assertOk();
        $form = $response->viewData('form');
        $form['snapshot'] = $response->viewData('snapshot');
        $form['target'] = $response->viewData('target');
        foreach (['obat_pakan', 'obat_air', 'obat_ayam'] as $category) {
            foreach ($form[$category] as &$medicine) {
                if (! empty($medicine['waktu'])) {
                    $medicine['waktu'] = substr($medicine['waktu'], 0, 5);
                }
            }
            unset($medicine);
        }
        $this->put(route('history_perencanaan_pakan.update'), $form)
            ->assertSessionHasNoErrors()->assertRedirect(route('history_perencanaan_pakan.detail', $scope));
        $journal = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $this->service->number($row->tgl, $row->id_kandang))->get();
        $this->assertNotEmpty($journal);
        $this->assertEquals($journal->sum('debit'), $journal->sum('kredit'));
    }

    public function test_reduction_credits_bags_and_debits_fertilizer_without_deleting_old_rows(): void
    {
        $this->save();
        $this->save(['gr_pakan_ekor' => 50, 'kg_pakan_box' => 5], true);
        $this->assertEquals(1.5, DB::table('stok_ayam')->where('tgl', $this->date)->where('jenis', 'pupuk')->sum(DB::raw('debit-kredit')));
        $this->assertEquals(-5, DB::table('stok_ayam')->where('tgl', $this->date)->where('jenis', 'karung')->sum(DB::raw('debit-kredit')));
        $this->assertSame(4, DB::table('stok_ayam')->where('tgl', $this->date)->count());
    }

    public function test_missing_read_and_create_permissions_block_direct_endpoints(): void
    {
        $buttons = DB::table('permission_button as b')->join('permission as p', 'p.id_permission', '=', 'b.permission_id')
            ->where('p.url', 'history_perencanaan_pakan')->where('b.jenis', 'create')->pluck('b.id_permission_button');
        DB::table('permission_role')->where('posisi_id', 1)->whereIn('id_permission_button', $buttons)->delete();
        $this->get(route('history_perencanaan_pakan.create'))->assertForbidden();
        $this->post(route('history_perencanaan_pakan.store'), $this->input())->assertForbidden();
        $buttons = DB::table('permission_button as b')->join('permission as p', 'p.id_permission', '=', 'b.permission_id')
            ->where('p.url', 'history_perencanaan_pakan')->where('b.jenis', 'read')->pluck('b.id_permission_button');
        DB::table('permission_role')->where('posisi_id', 1)->whereIn('id_permission_button', $buttons)->delete();
        $this->get(route('history_perencanaan_pakan'))->assertForbidden();
        $this->get(route('history_perencanaan_pakan.context', $this->scope()))->assertForbidden();
        $this->get(route('history_perencanaan_pakan.export'))->assertForbidden();
    }

    public function test_shared_journal_batch_preserves_other_transaction_and_updates_totals(): void
    {
        $this->save();
        $existing = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $this->service->number($this->date, $this->kandang))->first();
        $other = (array) $existing;
        unset($other['id_jurnal_perkiraan']);
        $other['nomor_transaksi'] = 'TRANSAKSI-LAIN-UJI';
        $other['debit'] = 123;
        $other['kredit'] = 0;
        $otherId = DB::table('jurnal_perkiraan')->insertGetId($other);
        $this->save(['gr_pakan_ekor' => 150], true);
        $this->assertEquals(123, DB::table('jurnal_perkiraan')->where('id_jurnal_perkiraan', $otherId)->value('debit'));
        $batch = DB::table('impor_jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $existing->id_impor_jurnal_perkiraan)->first();
        $this->assertEquals(123, $batch->total_debit);
        $this->assertSame(1, $batch->jumlah_detail);
    }

    public function test_legacy_stock_mismatch_and_insufficient_bags_reject_with_no_changes(): void
    {
        $this->save();
        DB::table('stok_produk_perencanaan')->where($this->scope())->where('id_pakan', 990101)->increment('pcs_kredit', 1);
        $before = $this->state();
        $this->rejected(fn () => $this->save(['gr_pakan_ekor' => 150], true), 'Detail dan stok lama');
        $this->assertSame($before, $this->state());
        DB::table('stok_produk_perencanaan')->where($this->scope())->where('id_pakan', 990101)->decrement('pcs_kredit', 1);
        $before = $this->state();
        $this->rejected(fn () => $this->save(['kg_pakan_box' => 10000000], true), 'Stok karung tidak cukup');
        $this->assertSame($before, $this->state());
    }

    public function test_complete_workbook_uses_filters_without_pagination_or_category_limits(): void
    {
        $this->save();
        DB::table('tb_obat_perencanaan')->where($this->scope())->where('kategori', 'obat_air')->update(['ket' => '=1+1']);
        DB::table('tb_vaksin_perencanaan')->insert($this->scope() + ['nm_vaksin' => 'Vaksin Lengkap', 'qty' => 5, 'ttl_rp' => 15, 'biaya_dll' => 1, 'admin' => 'uji']);
        for ($i = 0; $i < 26; $i++) {
            DB::table('stok_produk_perencanaan')->insert($this->stockRow(990105, 'VAKSIN-'.$i, ['pcs_kredit' => 1, 'total_rp' => 3]));
        }
        DB::table('stok_produk_perencanaan')->insert($this->stockRow(990105, 'DI-LUAR-FILTER', ['tgl' => '2099-01-02', 'pcs_kredit' => 1]));
        DB::table('stok_ayam')->insert(['tgl' => $this->date, 'no_nota' => '', 'debit' => 1, 'kredit' => 0, 'id_gudang' => 1,
            'admin' => 'uji', 'transfer' => 'T', 'cek' => 'T', 'jenis' => 'pupuk']);
        $before = $this->state();
        $data = app(\App\Services\PerencanaanExportService::class)->build($this->date, $this->date, $this->kandang);
        $bytes = \Maatwebsite\Excel\Facades\Excel::raw(new \App\Exports\LaporanPerencanaanExport($data), \Maatwebsite\Excel\Excel::XLSX);
        $path = tempnam(sys_get_temp_dir(), 'perencanaan-test-');
        try {
            file_put_contents($path, $bytes);
            $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $this->assertSame(['Ringkasan', 'Pakan', 'Obat Vitamin', 'Vaksin', 'Cocokkan Pemakaian', 'Mutasi Stok', 'Saldo Produk', 'Jurnal PPH'], $book->getSheetNames());
            $this->assertSame(41, $book->getSheetByName('Mutasi Stok')->getHighestRow());
            $summary = $book->getSheetByName('Ringkasan')->toArray(null, false, false);
            $this->assertCount(7, $summary);
            $this->assertEquals(10, $summary[5][4]);
            $this->assertEquals(20096, $summary[5][5]);
            $this->assertEquals(20018, $summary[5][6]);
            $this->assertEquals(0, $summary[5][8]);
            $this->assertEquals(100, $summary[5][2]);
            $this->assertEquals(100, $summary[5][3]);
            $this->assertSame('dd mmm yyyy', $book->getSheetByName('Ringkasan')->getStyle('A6')->getNumberFormat()->getFormatCode());
            $this->assertSame('TOTAL', $summary[6][0]);
            $this->assertEquals(20096, $summary[6][5]);
            $medicine = $book->getSheetByName('Obat Vitamin');
            $text = json_encode($medicine->toArray());
            foreach (['Uji obat_air', '08:30', 'Minum', '=1+1', 'Campuran input', 'Dosis input'] as $value) {
            $this->assertStringContainsString($value, $text);
            }
            foreach ($medicine->getRowIterator() as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    if ($cell->getValue() === '=1+1') {
                    $this->assertSame('s', $cell->getDataType());
                    }
                }
            }
            foreach ($book->getAllSheets() as $sheet) {
                $this->assertStringNotContainsString('id_kandang', json_encode($sheet->toArray()));
                $this->assertSame('C6', $sheet->getFreezePane());
            }
            $checks = $book->getSheetByName('Cocokkan Pemakaian')->toArray(null, false, false);
            $this->assertSame(['Cocok', 'Cocok', 'Cocok', 'Cocok'], array_column(array_slice($checks, 5), 7));
            $this->assertStringContainsString('Vaksin Lengkap', json_encode($book->getSheetByName('Vaksin')->toArray()));
            $this->assertStringNotContainsString('DI-LUAR-FILTER', json_encode($book->getSheetByName('Mutasi Stok')->toArray()));
            $book->disconnectWorksheets();
        } finally {
        unlink($path);
        }
        $this->get(route('history_perencanaan_pakan.export', ['tgl1' => $this->date, 'tgl2' => $this->date, 'id_kandang' => $this->kandang, 'kategori' => 'vitamin', 'per_page' => 25]))
            ->assertOk()->assertDownload('perencanaan-'.$this->date.'-'.$this->date.'.xlsx');
        $this->assertSame($before, $this->state());
        $empty = app(\App\Services\PerencanaanExportService::class)->build('2099-02-01', '2099-02-01', $this->kandang);
        $this->assertEmpty($empty[0]['rows']);
        $this->assertNotEmpty($empty[1]['columns']);
        $this->getJson(route('history_perencanaan_pakan.export', ['tgl1' => '2099-02-02', 'tgl2' => '2099-02-01']))->assertUnprocessable();
    }

    public function test_stock_ledger_includes_other_houses_and_opening_balance_and_flags_mismatch(): void
    {
        $this->save();
        DB::table('stok_produk_perencanaan')->insert($this->stockRow(990101, 'BELI-PERIODE', ['id_kandang' => 0, 'pcs' => 500, 'total_rp' => 1000]));
        DB::table('stok_produk_perencanaan')->insert($this->stockRow(990101, 'KANDANG-LAIN', ['id_kandang' => 0, 'pcs_kredit' => 100, 'total_rp' => 200]));
        DB::table('stok_produk_perencanaan')->where($this->scope())->where('id_pakan', 990102)->increment('pcs_kredit', 1);
        $data = collect(app(\App\Services\PerencanaanExportService::class)->build($this->date, $this->date, $this->kandang))->keyBy('title');
        $feedLedger = array_values(array_filter($data['Mutasi Stok']['rows'], fn ($r) => $r[1] === 'Uji pakan'));
        $this->assertEquals(100000, $feedLedger[0][6]);
        $this->assertEquals(90000, $feedLedger[1][6]);
        $this->assertEquals(90500, $feedLedger[2][6]);
        $this->assertEquals(90400, $feedLedger[3][6]);
        $balance = collect($data['Saldo Produk']['rows'])->first(fn ($r) => $r[0] === 'Uji pakan');
        $this->assertEquals([100000, 500, 10100, 90400, 10000], array_slice($balance, 2));
        $check = collect($data['Cocokkan Pemakaian']['rows'])->first(fn ($r) => $r[2] === 'Uji obat_pakan');
        $this->assertEquals(1, $check[6]);
        $this->assertSame('Periksa: input dan stok berbeda', $check[7]);
        $this->assertCount(1, $data['Ringkasan']['rows']);
    }
}
