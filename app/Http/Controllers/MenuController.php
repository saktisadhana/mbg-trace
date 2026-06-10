<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        return response()->json(Menu::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_menu'        => 'required|string|max:100',
            'tanggal_produksi' => 'nullable|date',
        ]);
        return response()->json(Menu::create($data), 201);
    }

    public function show($id)
    {
        return response()->json(Menu::with('bahan')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $data = $request->validate([
            'nama_menu'        => 'sometimes|required|string|max:100',
            'tanggal_produksi' => 'nullable|date',
        ]);
        $menu->update($data);
        return response()->json($menu);
    }

    public function destroy($id)
    {
        Menu::findOrFail($id)->delete();
        return response()->json(['message' => 'Menu dihapus']);
    }
}
