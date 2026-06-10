<?php
namespace App\Http\Controllers;

use App\Models\Sppg;

class TraceabilityController extends Controller
{
    // GET /api/traceability/{id_sppg}
    // Telusuri 1 distribusi: SPPG -> menu -> bahan -> supplier (+ sekolah)
    public function show($id_sppg)
    {
        $hasil = Sppg::with([
            'sekolah',
            'menu.bahan.supplier',
        ])->findOrFail($id_sppg);

        return response()->json($hasil);
    }
}
