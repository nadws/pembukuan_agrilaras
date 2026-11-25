<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Stok keluar untuk JOMA. Hanya membaca: barang keluar (pcs_kredit > 0) yang bukan hasil opname.
 * Posisi dikirim lewat after (id baris terakhir) dan limit. ids[] mengambil baris tertentu saja,
 * dipakai JOMA untuk mencoba lagi baris yang sebelumnya belum bisa dicatat.
 */
class StokKeluarApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($this->tokenMatches($request), 401, 'Unauthenticated.');

        $validated = $request->validate([
            'after' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'ids' => ['nullable', 'array', 'max:500'],
            'ids.*' => ['integer', 'min:1'],
            'tgl1' => ['nullable', 'date_format:Y-m-d'],
            'tgl2' => ['nullable', 'date_format:Y-m-d', 'required_with:tgl1', 'after_or_equal:tgl1'],
        ]);

        $limit = (int) ($validated['limit'] ?? 200);
        $after = (int) ($validated['after'] ?? 0);

        $query = DB::table('stok_produk_perencanaan as a')
            ->join('tb_produk_perencanaan as p', 'p.id_produk', '=', 'a.id_pakan')
            ->join('kandang as k', 'k.id_kandang', '=', 'a.id_kandang')
            ->where('a.pcs_kredit', '>', 0)
            ->where('a.h_opname', 'T');

        if (isset($validated['ids'])) {
            $query->whereIn('a.id_stok_telur', $validated['ids']);
        } else {
            $query->where('a.id_stok_telur', '>', $after);
        }

        // Optional date range: only the stok keluar dated inside it.
        if (isset($validated['tgl1'])) {
            $query->whereBetween('a.tgl', [$validated['tgl1'], $validated['tgl2']]);
        }

        $rows = $query->orderBy('a.id_stok_telur')
            ->limit($limit + 1)
            ->get([
                'a.id_stok_telur as id',
                'a.id_kandang',
                'k.nm_kandang',
                'a.tgl',
                'p.kode_accurate as kode_barang',
                'p.nm_produk',
                'a.pcs_kredit as jumlah',
                'a.total_rp',
                'a.no_nota',
            ]);

        $hasMore = $rows->count() > $limit;
        $page = $rows->take($limit)->values();

        return response()->json([
            'data' => $page,
            'meta' => [
                'next_after' => $page->last()->id ?? $after,
                'has_more' => $hasMore,
            ],
        ]);
    }

    private function tokenMatches(Request $request): bool
    {
        $expected = (string) config('services.stok_api.token');
        $given = (string) $request->bearerToken();

        return $expected !== '' && hash_equals($expected, $given);
    }
}
