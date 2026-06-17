<?php
namespace App\Http\Controllers;

use App\Models\DetailMenu;
use Illuminate\Http\Request;

class DetailMenuController extends Controller
{
    public function index()
    {
        return response()->json(DetailMenu::getWithBahanDanMenu());
    }

    public function store(Request $request, $id = null)
    {
        // Support both /detail-menu POST and /menu/{id}/bahan POST
        if ($id && !$request->has('id_menu')) {
            $request->merge(['id_menu' => $id]);
        }

        $data = $request->validate([
            'id_menu'      => 'required|exists:menu,id_menu',
            'id_bahan'     => 'required|exists:bahan_makanan,id_bahan',
            'jumlah_bahan' => 'required|integer|min:1',
        ]);

        DetailMenu::create($data);
        return response()->json(['message' => 'Bahan ditambahkan ke menu'], 201);
    }

    public function show($id_menu, $id_bahan = null)
    {
        // Support both routes: /detail-menu/{id} and /menu/{id}/bahan/{bahan_id}
        if ($id_bahan === null) {
            $details = DetailMenu::getByMenuId($id_menu);
            if (empty($details)) {
                return response()->json(['message' => 'Detail menu not found'], 404);
            }
            return response()->json($details);
        }

        $detail = DetailMenu::getById($id_menu, $id_bahan);
        if (!$detail) {
            return response()->json(['message' => 'Detail menu not found'], 404);
        }
        return response()->json($detail);
    }

    public function update(Request $request, $id_menu, $id_bahan)
    {
        $detail = DetailMenu::getById($id_menu, $id_bahan);
        if (!$detail) {
            return response()->json(['message' => 'Detail menu not found'], 404);
        }

        $data = $request->validate([
            'jumlah_bahan' => 'required|integer|min:1',
        ]);

        $updated = DetailMenu::update($id_menu, $id_bahan, $data);
        return response()->json($updated);
    }

    public function destroy($id_menu, $id_bahan = null)
    {
        if ($id_bahan === null) {
            // Delete all bahan for a menu
            DetailMenu::deleteByMenuId($id_menu);
            return response()->json(['message' => 'Semua bahan dihapus dari menu']);
        }

        $detail = DetailMenu::getById($id_menu, $id_bahan);
        if (!$detail) {
            return response()->json(['message' => 'Detail menu not found'], 404);
        }

        DetailMenu::delete($id_menu, $id_bahan);
        return response()->json(['message' => 'Bahan dihapus dari menu']);
    }
}
