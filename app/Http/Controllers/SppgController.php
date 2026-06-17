<?php
namespace App\Http\Controllers;

use App\Models\Sppg;
use Illuminate\Http\Request;

class SppgController extends Controller
{
    public function index()
    {
        return response()->json(Sppg::getWithDetails());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal_distribusi' => 'nullable|date',
            'jumlah_porsi'       => 'nullable|integer',
            'alamat_sppg'        => 'nullable|string',
            'id_menu'            => 'required|exists:menu,id_menu',
            'id_sekolah'         => 'required|exists:sekolah,id_sekolah',
        ]);
        $sppg = Sppg::create($data);
        return response()->json($sppg, 201);
    }

    public function show($id)
    {
        $sppg = Sppg::getByIdWithDetails($id);
        if (!$sppg) {
            return response()->json(['message' => 'SPPG not found'], 404);
        }
        return response()->json($sppg);
    }

    public function update(Request $request, $id)
    {
        $sppg = Sppg::getById($id);
        if (!$sppg) {
            return response()->json(['message' => 'SPPG not found'], 404);
        }
        $data = $request->validate([
            'tanggal_distribusi' => 'nullable|date',
            'jumlah_porsi'       => 'nullable|integer',
            'alamat_sppg'        => 'nullable|string',
            'id_menu'            => 'sometimes|required|exists:menu,id_menu',
            'id_sekolah'         => 'sometimes|required|exists:sekolah,id_sekolah',
        ]);
        $updated = Sppg::update($id, $data);
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $sppg = Sppg::getById($id);
        if (!$sppg) {
            return response()->json(['message' => 'SPPG not found'], 404);
        }
        Sppg::delete($id);
        return response()->json(['message' => 'SPPG deleted']);
    }
}
