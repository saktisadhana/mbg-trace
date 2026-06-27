<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKeracunanController extends Controller
{
    private function collection()
    {
        return DB::connection('mongodb')->table('laporan_keracunan');
    }

    public function index()
    {
        $items = $this->collection()->get();

        // Per ERD: laporan_keracunan hanya punya FK id_sppg.
        // Sekolah diturunkan lewat relasi sppg -> sekolah.
        $sppgIds = $items->pluck('id_sppg')->filter()->unique();
        $sppgs = DB::table('sppg')
            ->whereIn('id_sppg', $sppgIds)->get()->keyBy('id_sppg');

        $sekolahIds = $sppgs->pluck('id_sekolah')->unique();
        $sekolahs = DB::table('sekolah')
            ->whereIn('id_sekolah', $sekolahIds)->get()->keyBy('id_sekolah');

        foreach ($items as $item) {
            $sppg = $sppgs->get($item->id_sppg);
            $item->sppg    = $sppg;
            $item->sekolah = $sppg ? $sekolahs->get($sppg->id_sekolah) : null;
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_laporan'         => 'nullable|string',
            'tanggal_laporan'    => 'required|date',
            'jumlah_korban'      => 'required|integer|min:0',
            'deskripsi'          => 'required|string',
            'id_sppg'            => 'required|integer',
            'detail_investigasi' => 'nullable|string',
            'dokumentasi'        => 'nullable|string',
            'riwayat_audit'      => 'nullable|array',
        ]);

        $id      = $this->collection()->insertGetId($data);
        $laporan = $this->collection()->where('_id', $id)->first();

        return response()->json($laporan, 201);
    }

    public function show($id)
    {
        // Accept either the human-readable id_laporan or the raw Mongo _id.
        $laporan = $this->collection()->where('id_laporan', $id)->first()
            ?? $this->collection()->where('_id', $id)->first();
        if (!$laporan) {
            return response()->json(['message' => 'Laporan tidak ditemukan'], 404);
        }

        // Per ERD: sekolah diturunkan lewat relasi sppg -> sekolah.
        if (isset($laporan->id_sppg)) {
            $sppg = DB::table('sppg')->where('id_sppg', $laporan->id_sppg)->first();
            $laporan->sppg = $sppg;
            $laporan->sekolah = $sppg
                ? DB::table('sekolah')->where('id_sekolah', $sppg->id_sekolah)->first()
                : null;
        }

        return response()->json($laporan);
    }

    public function update(Request $request, $id)
    {
        $laporan = $this->collection()->where('_id', $id)->first();
        if (!$laporan) {
            return response()->json(['message' => 'Laporan tidak ditemukan'], 404);
        }

        $data = $request->validate([
            'tanggal_laporan'    => 'sometimes|required|date',
            'jumlah_korban'      => 'sometimes|required|integer|min:0',
            'deskripsi'          => 'sometimes|required|string',
            'id_sppg'            => 'sometimes|required|integer',
            'detail_investigasi' => 'nullable|string',
            'dokumentasi'        => 'nullable|string',
            'riwayat_audit'      => 'nullable',
        ]);

        if ($request->has('riwayat_audit')) {
            $newAudit      = $request->input('riwayat_audit');
            $existingAudit = $laporan->riwayat_audit ?? [];

            if (!is_array($existingAudit)) {
                $existingAudit = [$existingAudit];
            }

            if (is_array($newAudit) && !isset($newAudit[0])) {
                $existingAudit[] = $newAudit;
            } elseif (is_array($newAudit)) {
                $existingAudit = array_merge($existingAudit, $newAudit);
            } else {
                $existingAudit[] = $newAudit;
            }

            $data['riwayat_audit'] = $existingAudit;
        }

        $this->collection()->where('_id', $id)->update($data);
        $laporan = $this->collection()->where('_id', $id)->first();

        return response()->json($laporan);
    }

    public function destroy($id)
    {
        $deleted = $this->collection()->where('_id', $id)->delete();
        if (!$deleted) {
            return response()->json(['message' => 'Laporan tidak ditemukan'], 404);
        }

        return response()->json(['message' => 'Laporan Keracunan berhasil dihapus']);
    }
}
