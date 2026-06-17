<?php
namespace App\Http\Controllers;

use App\Models\LaporanKeracunan;
use Illuminate\Http\Request;

class LaporanKeracunanController extends Controller
{
    public function index()
    {
        return response()->json(LaporanKeracunan::getAll());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_laporan'      => 'required|unique:mongodb.laporan_keracunan',
            'tanggal_laporan' => 'required|date',
            'jumlah_korban'   => 'required|integer|min:0',
            'deskripsi'       => 'nullable|string',
            'id_sekolah'      => 'required|integer',
            'id_sppg'         => 'required|integer',
        ]);

        LaporanKeracunan::create($data);
        return response()->json(['message' => 'Laporan keracunan created'], 201);
    }

    public function show($id)
    {
        $laporan = LaporanKeracunan::getById($id);
        if (!$laporan) {
            return response()->json(['message' => 'Laporan not found'], 404);
        }
        return response()->json($laporan);
    }

    public function update(Request $request, $id)
    {
        $laporan = LaporanKeracunan::getById($id);
        if (!$laporan) {
            return response()->json(['message' => 'Laporan not found'], 404);
        }

        $data = $request->validate([
            'tanggal_laporan' => 'sometimes|required|date',
            'jumlah_korban'   => 'sometimes|required|integer|min:0',
            'deskripsi'       => 'nullable|string',
            'id_sekolah'      => 'sometimes|required|integer',
            'id_sppg'         => 'sometimes|required|integer',
        ]);

        LaporanKeracunan::update($id, $data);
        return response()->json(['message' => 'Laporan updated']);
    }

    public function destroy($id)
    {
        $laporan = LaporanKeracunan::getById($id);
        if (!$laporan) {
            return response()->json(['message' => 'Laporan not found'], 404);
        }

        LaporanKeracunan::delete($id);
        return response()->json(['message' => 'Laporan deleted']);
    }
}
