<?php

namespace App\Http\Controllers;

use App\Models\DetailMenu;
use Illuminate\Http\Request;

class DetailMenuController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'id_bahan' => 'required',
            'jumlah_bahan' => 'required|numeric'
        ]);

        $detail = DetailMenu::create([
            'id_menu' => $id,
            'id_bahan' => $request->id_bahan,
            'jumlah_bahan' => $request->jumlah_bahan
        ]);

        return response()->json($detail, 201);
    }
}
