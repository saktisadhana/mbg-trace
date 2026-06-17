<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        return response()->json(Menu::getWithBahan());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_menu'        => 'required|string|max:100',
            'tanggal_produksi' => 'nullable|date',
        ]);
        $menu = Menu::create($data);
        return response()->json($menu, 201);
    }

    public function show($id)
    {
        $menu = Menu::getByIdWithBahan($id);
        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }
        return response()->json($menu);
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::getById($id);
        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }
        $data = $request->validate([
            'nama_menu'        => 'sometimes|required|string|max:100',
            'tanggal_produksi' => 'nullable|date',
        ]);
        $updated = Menu::update($id, $data);
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $menu = Menu::getById($id);
        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }
        Menu::delete($id);
        return response()->json(['message' => 'Menu deleted']);
    }
}
