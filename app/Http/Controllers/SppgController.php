<?php
namespace App\Http\Controllers;

use App\Models\Sppg;
use Illuminate\Http\Request;

class SppgController extends Controller
{
    public function index()
    {
        return response()->json(Sppg::with(['menu', 'sekolah'])->get());
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
        return response()->json(Sppg::create($data), 201);
    }

    public function show($id)
    {
        return response()->json(Sppg::with(['menu', 'sekolah'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $sppg = Sppg::findOrFail($id);
        $data = $request->validate([
            'tanggal_distribusi' => 'nullable|date',
            'jumlah_porsi'       => 'nullable|integer',
            'alamat_sppg'        => 'nullable|string',
            'id_menu'            => 'sometimes|required|exists:menu,id_menu',
            'id_sekolah'         => 'sometimes|required|exists:sekolah,id_sekolah',
        ]);
        $sppg->update($data);
        return response()->json($sppg);
    }

    public function destroy($id)
    {
        Sppg::findOrFail($id)->delete();
        return response()->json(['message' => 'Data SPPG dihapus']);
    }
}
