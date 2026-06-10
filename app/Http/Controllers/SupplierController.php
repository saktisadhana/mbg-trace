<?php
namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return response()->json(Supplier::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_supplier' => 'required|string|max:100',
            'alamat'        => 'nullable|string',
            'no_telp'       => 'nullable|string|max:20',
        ]);
        return response()->json(Supplier::create($data), 201);
    }

    public function show($id)
    {
        return response()->json(Supplier::with('bahan')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $data = $request->validate([
            'nama_supplier' => 'sometimes|required|string|max:100',
            'alamat'        => 'nullable|string',
            'no_telp'       => 'nullable|string|max:20',
        ]);
        $supplier->update($data);
        return response()->json($supplier);
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return response()->json(['message' => 'Supplier dihapus']);
    }
}
