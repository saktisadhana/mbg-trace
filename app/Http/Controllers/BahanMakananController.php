<?php
namespace App\Http\Controllers;

use App\Models\BahanMakanan;
use Illuminate\Http\Request;

class BahanMakananController extends Controller
{
    public function index()
    {
        return response()->json(BahanMakanan::getWithSupplier());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_bahan'         => 'required|string|max:100',
            'tanggal_kadaluarsa' => 'nullable|date',
            'id_supplier'        => 'required|exists:supplier,id_supplier',
        ]);
        $bahan = BahanMakanan::create($data);
        return response()->json($bahan, 201);
    }

    public function show($id)
    {
        $bahan = BahanMakanan::getByIdWithSupplier($id);
        if (!$bahan) {
            return response()->json(['message' => 'Bahan makanan not found'], 404);
        }
        return response()->json($bahan);
    }

    public function update(Request $request, $id)
    {
        $bahan = BahanMakanan::getById($id);
        if (!$bahan) {
            return response()->json(['message' => 'Bahan makanan not found'], 404);
        }
        $data = $request->validate([
            'nama_bahan'         => 'sometimes|required|string|max:100',
            'tanggal_kadaluarsa' => 'nullable|date',
            'id_supplier'        => 'sometimes|required|exists:supplier,id_supplier',
        ]);
        $updated = BahanMakanan::update($id, $data);
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $bahan = BahanMakanan::getById($id);
        if (!$bahan) {
            return response()->json(['message' => 'Bahan makanan not found'], 404);
        }
        BahanMakanan::delete($id);
        return response()->json(['message' => 'Bahan makanan deleted']);
    }
}
