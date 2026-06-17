<?php
namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index()
    {
        return response()->json(Sekolah::getAll());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_sekolah' => 'required|string|max:100',
            'alamat'       => 'nullable|string',
        ]);
        $sekolah = Sekolah::create($data);
        return response()->json($sekolah, 201);
    }

    public function show($id)
    {
        $sekolah = Sekolah::getByIdWithSppg($id);
        if (!$sekolah) {
            return response()->json(['message' => 'Sekolah not found'], 404);
        }
        return response()->json($sekolah);
    }

    public function update(Request $request, $id)
    {
        $sekolah = Sekolah::getById($id);
        if (!$sekolah) {
            return response()->json(['message' => 'Sekolah not found'], 404);
        }
        $data = $request->validate([
            'nama_sekolah' => 'sometimes|required|string|max:100',
            'alamat'       => 'nullable|string',
        ]);
        $updated = Sekolah::update($id, $data);
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $sekolah = Sekolah::getById($id);
        if (!$sekolah) {
            return response()->json(['message' => 'Sekolah not found'], 404);
        }
        Sekolah::delete($id);
        return response()->json(['message' => 'Sekolah deleted']);
    }
}
