<?php
namespace App\Http\Controllers;

use App\Models\BahanMakanan;
use Illuminate\Http\Request;

class BahanMakananController extends Controller
{
    public function index()
    {
        return response()->json(BahanMakanan::with('supplier')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_bahan'         => 'required|string|max:100',
            'tanggal_kadaluarsa' => 'nullable|date',
            'id_supplier'        => 'required|exists:supplier,id_supplier',
        ]);
        return response()->json(BahanMakanan::create($data), 201);
    }

    public function show($id)
    {
        return response()->json(BahanMakanan::with('supplier')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $bahan = BahanMakanan::findOrFail($id);
        $data = $request->validate([
            'nama_bahan'         => 'sometimes|required|string|max:100',
            'tanggal_kadaluarsa' => 'nullable|date',
            'id_supplier'        => 'sometimes|required|exists:supplier,id_supplier',
        ]);
        $bahan->update($data);
        return response()->json($bahan);
    }

    public function destroy($id)
    {
        BahanMakanan::findOrFail($id)->delete();
        return response()->json(['message' => 'Bahan makanan dihapus']);
    }
}
