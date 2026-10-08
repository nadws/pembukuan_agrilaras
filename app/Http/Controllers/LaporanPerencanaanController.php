<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPerencanaanExport;
use App\Services\LaporanPerencanaanService;
use App\Services\PerencanaanExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPerencanaanController extends Controller
{
    public function __construct(private LaporanPerencanaanService $service)
    {
        $this->middleware('auth');
    }

    private function allowed(string $kind): bool
    {
        $kind = $kind === 'update' ? 'create' : $kind;

        return DB::table('permission_role as r')->join('permission_button as b', 'b.id_permission_button', '=', 'r.id_permission_button')
            ->join('permission as p', 'p.id_permission', '=', 'b.permission_id')->where('p.url', 'history_perencanaan_pakan')
            ->where('r.posisi_id', auth()->user()->posisi_id)->where('b.jenis', $kind)->exists();
    }

    private function authorizeAction(string $kind): void
    {
        abort_unless($this->allowed('read') && $this->allowed($kind), 403, 'Akses History Perencanaan tidak tersedia.');
    }

    public function index(Request $request)
    {
        $this->authorizeAction('read');

        return redirect()->route('history_perencanaan_pakan', $request->query());
    }

    public function history(Request $request)
    {
        $this->authorizeAction('read');
        $filters = $this->filters($request);
        $tgl1 = $filters['tgl1'] ?? date('Y-m-01');
        $tgl2 = $filters['tgl2'] ?? date('Y-m-d');
        $idKandang = $filters['id_kandang'] ?? null;
        $kategori = $filters['kategori'] ?? 'pakan';
        $stok = DB::table('stok_produk_perencanaan as a')->join('tb_produk_perencanaan as b', 'b.id_produk', '=', 'a.id_pakan')
            ->join('kandang as c', 'c.id_kandang', '=', 'a.id_kandang')->leftJoin('tb_satuan as d', 'd.id_satuan', '=', 'b.dosis_satuan')
            ->whereIn('b.kategori', $kategori === 'pakan' ? ['pakan'] : ['obat_pakan', 'obat_air', 'obat_ayam', 'vitamin', 'vaksin'])
            ->where('a.pcs_kredit', '>', 0)->where('a.h_opname', 'T')->whereBetween('a.tgl', [$tgl1, $tgl2])
            ->when($idKandang, fn ($q) => $q->where('a.id_kandang', $idKandang))
            ->select('a.*', 'b.nm_produk', 'c.nm_kandang', 'd.nm_satuan')->orderByDesc('a.tgl')->orderBy('c.nm_kandang')->orderBy('b.nm_produk')
            ->paginate((int) ($filters['per_page'] ?? 25))->withQueryString();

        return view('stok_pakan.history_pakan', ['title' => 'History Perencanaan', 'stok' => $stok, 'tgl1' => $tgl1, 'tgl2' => $tgl2,
            'idKandang' => $idKandang, 'kategori' => $kategori, 'kandang' => DB::table('kandang')->orderBy('nm_kandang')->get(),
            'canUpdate' => $this->allowed('update'), 'canCreate' => $this->allowed('create')]);
    }

    private function filters(Request $request): array
    {
        $request->merge(['tgl1' => $request->input('tgl1') ?: date('Y-m-01'), 'tgl2' => $request->input('tgl2') ?: date('Y-m-d')]);

        return $request->validate(['tgl1' => 'required|date_format:Y-m-d', 'tgl2' => 'required|date_format:Y-m-d|after_or_equal:tgl1',
            'id_kandang' => 'nullable|integer|exists:kandang,id_kandang', 'kategori' => 'nullable|in:pakan,vitamin', 'per_page' => 'nullable|integer|in:25,50,100']);
    }

    public function export(Request $request, PerencanaanExportService $export)
    {
        $this->authorizeAction('read');
        $filters = $this->filters($request);
        $data = $export->build($filters['tgl1'], $filters['tgl2'], empty($filters['id_kandang']) ? null : (int) $filters['id_kandang']);

        return Excel::download(new LaporanPerencanaanExport($data), 'perencanaan-'.$filters['tgl1'].'-'.$filters['tgl2'].'.xlsx');
    }

    public function create(Request $request)
    {
        $this->authorizeAction('create');
        $data = $request->validate(['tgl' => 'nullable|date_format:Y-m-d', 'id_kandang' => 'nullable|integer|exists:kandang,id_kandang']);
        if (! empty($data['tgl']) && ! empty($data['id_kandang']) && $this->service->exists($data['tgl'], $data['id_kandang'])) {
            return redirect()->route('history_perencanaan_pakan.edit', $data)->with('error', 'Tanggal/kandang sudah ada. Gunakan Koreksi.');
        }

        return $this->form($data['tgl'] ?? date('Y-m-d'), (int) ($data['id_kandang'] ?? 0), 'create');
    }

    public function edit(Request $request)
    {
        $this->authorizeAction('update');
        $data = $this->identity($request);
        if (! $this->service->exists($data['tgl'], $data['id_kandang'])) {
            return redirect()->route('history_perencanaan_pakan', ['tgl1' => $data['tgl'], 'tgl2' => $data['tgl'], 'id_kandang' => $data['id_kandang']])
                ->with('error', 'Belum ada perencanaan untuk tanggal/kandang ini. Gunakan Tambah Tertinggal.');
        }

        return $this->form($data['tgl'], $data['id_kandang'], 'edit');
    }

    public function detail(Request $request)
    {
        $this->authorizeAction('read');
        $data = $this->identity($request);
        abort_unless($this->service->exists($data['tgl'], $data['id_kandang']), 404);

        return $this->form($data['tgl'], $data['id_kandang'], 'detail');
    }

    public function context(Request $request)
    {
        $this->authorizeAction('read');
        $data = $this->identity($request);

        return response()->json(['populasi' => $this->service->population($data['tgl'], $data['id_kandang']),
            'exists' => $this->service->exists($data['tgl'], $data['id_kandang']),
            'edit_url' => route('history_perencanaan_pakan.edit', $data), 'stok' => $this->stockOptions()]);
    }

    private function identity(Request $request): array
    {
        return $request->validate(['tgl' => 'required|date_format:Y-m-d', 'id_kandang' => 'required|integer|exists:kandang,id_kandang']);
    }

    private function stockOptions(): array
    {
        return DB::table('stok_produk_perencanaan')->select('id_pakan')->selectRaw('SUM(pcs-pcs_kredit) as saldo')
            ->groupBy('id_pakan')->pluck('saldo', 'id_pakan')->all();
    }

    private function form(string $date, int $kandang, string $mode)
    {
        $scope = ['tgl' => $date, 'id_kandang' => $kandang];
        $feed = DB::table('tb_pakan_perencanaan')->where($scope)->orderBy('id_pakan_perencanaan')->get();
        $medicine = DB::table('tb_obat_perencanaan')->where($scope)->orderBy('id_obat_perencanaan')->get();
        $population = $kandang ? $this->service->population($date, $kandang) : 0;
        $bags = DB::table('tb_karung_perencanaan')->where($scope)->get();
        $form = ['tgl' => $date, 'id_kandang' => $kandang, 'kg_pakan_box' => $bags->sum('karung') ?: '',
            'gr_pakan_ekor' => $population ? $feed->sum('gr') / $population : 0,
            'pakan' => $feed->map(fn ($r) => ['id_produk' => $r->id_produk_pakan, 'persen' => $r->persen, 'gr' => $r->gr])->all()];
        foreach (['obat_pakan', 'obat_air', 'obat_ayam'] as $category) {
            $form[$category] = $medicine->where('kategori', $category)->map(fn ($r) => (array) $r)->values()->all();
        }
        $products = DB::table('tb_produk_perencanaan as p')->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.dosis_satuan')
            ->leftJoin('tb_satuan as c', 'c.id_satuan', '=', 'p.campuran_satuan')->select('p.*', 's.nm_satuan as satuan', 'c.nm_satuan as satuan_campuran')
            ->orderBy('p.nm_produk')->get();

        return view('laporan.perencanaan.form', ['form' => $form, 'mode' => $mode, 'population' => $population,
            'products' => $products, 'stock' => $this->stockOptions(), 'kandang' => DB::table('kandang')->orderBy('nm_kandang')->get(),
            'snapshot' => $this->service->snapshot($date, $kandang),
            'target' => encrypt(['tgl' => $date, 'id_kandang' => $kandang]), 'canUpdate' => $this->allowed('update'),
            'otherMedicine' => $medicine->whereNotIn('kategori', ['obat_pakan', 'obat_air', 'obat_ayam']),
            'vaccines' => DB::table('tb_vaksin_perencanaan')->where($scope)->get(),
            'stockRows' => DB::table('stok_produk_perencanaan')->where($scope)->get(),
            'journalRows' => DB::table('jurnal_perkiraan')->where('nomor_transaksi', $this->service->number($date, $kandang))
                ->where('tipe_transaksi', 'Pemakaian Pakan Harian')->get()]);
    }

    public function store(Request $request)
    {
        return $this->persist($request, false);
    }

    public function update(Request $request)
    {
        return $this->persist($request, true);
    }

    private function persist(Request $request, bool $correction)
    {
        $this->authorizeAction($correction ? 'update' : 'create');
        $data = $request->validate(['tgl' => 'required|date_format:Y-m-d', 'id_kandang' => 'required|integer|exists:kandang,id_kandang',
            'target' => $correction ? 'required|string' : 'nullable', 'snapshot' => $correction ? 'required|string|size:64' : 'nullable',
            'kg_pakan_box' => 'required|numeric|gt:0|max:1000000', 'gr_pakan_ekor' => 'required|numeric|gt:0|max:10000',
            'pakan' => 'required|array|min:1|max:100', 'pakan.*.id_produk' => 'required|integer|exists:tb_produk_perencanaan,id_produk',
            'pakan.*.persen' => 'required|numeric|gt:0|lte:100',
            'obat_pakan' => 'nullable|array|max:100', 'obat_air' => 'nullable|array|max:100', 'obat_ayam' => 'nullable|array|max:100',
            'obat_pakan.*.id_produk' => 'nullable|integer|exists:tb_produk_perencanaan,id_produk', 'obat_pakan.*.dosis' => 'nullable|numeric|min:0|max:1000000000',
            'obat_pakan.*.campuran' => 'nullable|numeric|min:0|max:1000000000',
            'obat_air.*.id_produk' => 'nullable|integer|exists:tb_produk_perencanaan,id_produk', 'obat_air.*.dosis' => 'nullable|numeric|min:0|max:1000000000',
            'obat_air.*.campuran' => 'nullable|numeric|min:0|max:1000000000', 'obat_air.*.waktu' => 'nullable|date_format:H:i',
            'obat_air.*.cara_pemakaian' => 'nullable|string|max:200', 'obat_air.*.ket' => 'nullable|string|max:200',
            'obat_ayam.*.id_produk' => 'nullable|integer|exists:tb_produk_perencanaan,id_produk', 'obat_ayam.*.dosis' => 'nullable|numeric|min:0|max:1000000000']);
        if ($correction) {
            try {
                $target = decrypt($data['target']);
            } catch (\Throwable) {
                abort(422, 'Identitas koreksi tidak valid.');
            }
            abort_unless(($target['tgl'] ?? null) === $data['tgl'] && (int) ($target['id_kandang'] ?? 0) === (int) $data['id_kandang'], 422, 'Tanggal/kandang koreksi terkunci.');
        } elseif ($this->service->exists($data['tgl'], $data['id_kandang'])) {
            return redirect()->route('history_perencanaan_pakan.edit', $request->only('tgl', 'id_kandang'))->with('error', 'Tanggal/kandang sudah ada. Gunakan Koreksi.');
        }
        $saved = $this->service->save($data, $data['tgl'], (int) $data['id_kandang'], $correction, auth()->user()->name);
        if (! $saved) {
            return redirect()->route('history_perencanaan_pakan.edit', $request->only('tgl', 'id_kandang'))->with('error', 'Tanggal/kandang sudah ada. Gunakan Koreksi.');
        }

        return redirect()->route('history_perencanaan_pakan.detail', $request->only('tgl', 'id_kandang'))->with('sukses', 'Perencanaan, stok, dan jurnal PPH berhasil disimpan.');
    }
}
