<?php
namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return response()->json(Supplier::getAll());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_supplier' => 'required|string|max:100',
            'alamat'        => 'nullable|string',
            'no_telp'       => 'nullable|string|max:20',
        ]);
        $supplier = Supplier::create($data);
        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = Supplier::getByIdWithBahan($id);
        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::getById($id);
        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
        $data = $request->validate([
            'nama_supplier' => 'sometimes|required|string|max:100',
            'alamat'        => 'nullable|string',
            'no_telp'       => 'nullable|string|max:20',
        ]);
        $updated = Supplier::update($id, $data);
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $supplier = Supplier::getById($id);
        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
        Supplier::delete($id);
        return response()->json(['message' => 'Supplier deleted']);
    }
}
