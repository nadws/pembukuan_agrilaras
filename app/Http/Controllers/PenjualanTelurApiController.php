<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Penjualan telur untuk JOMA. Hanya membaca: satu nota dengan semua barangnya, per rentang tanggal.
 * Memakai token di PENJUALAN_API_TOKEN (dikirim sebagai Bearer).
 */
class PenjualanTelurApiController extends Controller
{
    private const MAX_NOTA = 300;

    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($this->tokenMatches($request), 401, 'Unauthenticated.');

        $validated = $request->validate([
            'tgl1' => ['required', 'date_format:Y-m-d'],
            'tgl2' => ['required', 'date_format:Y-m-d', 'after_or_equal:tgl1'],
            'lokasi' => ['nullable', 'string', 'max:20'],
        ]);

        $lokasi = $validated['lokasi'] ?? 'alpa';

        $rows = DB::table('invoice_telur as i')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
            ->leftJoin('customer as c2', 'c2.id_customer', '=', 'i.id_customer2')
            ->leftJoin('telur_produk as p', 'p.id_produk_telur', '=', 'i.id_produk')
            ->where('i.lokasi', $lokasi)
            ->whereBetween('i.tgl', [$validated['tgl1'], $validated['tgl2']])
            ->orderBy('i.urutan')
            ->orderBy('i.id_invoice_telur')
            ->get([
                'i.id_invoice_telur', 'i.no_nota', 'i.tgl', 'i.tipe', 'i.status',
                'i.id_customer', 'c.nm_customer', 'i.id_customer2', 'c2.nm_customer as nm_customer2',
                'i.id_produk', 'p.nm_telur', 'i.pcs', 'i.kg', 'i.kg_jual', 'i.rp_satuan', 'i.total_rp', 'i.admin',
            ]);

        $notas = $rows->groupBy('no_nota')->take(self::MAX_NOTA)->map(function ($items) {
            $first = $items->first();

            return [
                'no_nota' => $first->no_nota,
                'tgl' => $first->tgl,
                'status' => $first->status,
                'tipe' => $first->tipe,
                'pelanggan' => ['id' => (int) $first->id_customer, 'nama' => $first->nm_customer],
                'pelanggan_kedua' => $first->id_customer2 ? ['id' => (int) $first->id_customer2, 'nama' => $first->nm_customer2] : null,
                'total' => round((float) $items->sum('total_rp'), 2),
                'admin' => $first->admin,
                'barang' => $items->map(fn ($row) => [
                    'id_baris' => (int) $row->id_invoice_telur,
                    'id_produk' => (int) $row->id_produk,
                    'nama' => $row->nm_telur,
                    // Satu produk bisa dijual per butir (pcs) atau per kg: dipisah supaya bisa dipetakan ke barang yang berbeda di JOMA.
                    'produk_jual' => $row->id_produk.'-'.$row->tipe,
                    'label' => $row->nm_telur.($row->tipe === 'pcs' ? ' (PCS)' : ' (GR/KG)'),
                    'tipe' => $row->tipe,
                    'pcs' => (float) $row->pcs,
                    'kg' => (float) $row->kg,
                    'kg_jual' => (float) $row->kg_jual,
                    'harga_satuan' => (float) $row->rp_satuan,
                    'total' => (float) $row->total_rp,
                ])->values(),
            ];
        })->values();

        return response()->json([
            'data' => $notas,
            'meta' => [
                'jumlah_nota' => $notas->count(),
                'terpotong' => $rows->groupBy('no_nota')->count() > self::MAX_NOTA,
            ],
        ]);
    }

    private function tokenMatches(Request $request): bool
    {
        $expected = (string) config('services.penjualan_api.token');
        $given = (string) $request->bearerToken();

        return $expected !== '' && hash_equals($expected, $given);
    }
}
