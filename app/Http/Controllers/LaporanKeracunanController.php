<?php

namespace App\Http\Controllers;

use App\Models\LaporanKeracunan;
use Illuminate\Http\Request;

class LaporanKeracunanController extends Controller
{
    public function index()
    {
        return response()->json(LaporanKeracunan::all());
    }

    public function store(Request $request)
    {
        $laporan = LaporanKeracunan::create($request->all());
        return response()->json($laporan, 201);
    }

    public function show($id)
    {
        $laporan = LaporanKeracunan::findOrFail($id);
        return response()->json($laporan);
    }

    public function update(Request $request, $id)
    {
        $laporan = LaporanKeracunan::findOrFail($id);
        $data = $request->all();

        // Handle riwayat_audit append
        if (isset($data['riwayat_audit'])) {
            $riwayatBaru = $data['riwayat_audit'];
            unset($data['riwayat_audit']); // Remove from bulk assignment

            $riwayatLama = $laporan->riwayat_audit ?? [];
            if (!is_array($riwayatLama)) {
                $riwayatLama = [$riwayatLama];
            }

            if (is_array($riwayatBaru)) {
                // If the new audit is an array of entries, merge them. 
                // Alternatively, if it's a single entry that is an array itself, we append.
                // Assuming $riwayatBaru is a single entry (string or associative array) for simplicity:
                $riwayatLama[] = $riwayatBaru;
            } else {
                $riwayatLama[] = $riwayatBaru;
            }

            $laporan->riwayat_audit = $riwayatLama;
        }

        $laporan->fill($data);
        $laporan->save();

        return response()->json($laporan);
    }

    public function destroy($id)
    {
        $laporan = LaporanKeracunan::findOrFail($id);
        $laporan->delete();

        return response()->json(null, 204);
    }
}
