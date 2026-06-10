<?php
namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index()
    {
        return response()->json(Sekolah::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_sekolah' => 'required|string|max:100',
            'alamat'       => 'nullable|string',
        ]);
        return response()->json(Sekolah::create($data), 201);
    }

    public function show($id)
    {
        return response()->json(Sekolah::with('sppg')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $sekolah = Sekolah::findOrFail($id);
        $data = $request->validate([
            'nama_sekolah' => 'sometimes|required|string|max:100',
            'alamat'       => 'nullable|string',
        ]);
        $sekolah->update($data);
        return response()->json($sekolah);
    }

    public function destroy($id)
    {
        Sekolah::findOrFail($id)->delete();
        return response()->json(['message' => 'Sekolah dihapus']);
    }
}
