<?php
namespace App\Http\Controllers;

use App\Models\Sppg;
use App\Models\DetailMenu;
use App\Models\LaporanKeracunan;
use Illuminate\Support\Facades\DB;

class TraceabilityController extends Controller
{
    // GET /api/trace/report/{id}
    // Telusuri laporan keracunan: mulai dari laporan → SPPG → menu → bahan → supplier (+ sekolah)
    public function traceFromReport($id_laporan)
    {
        // Get report from MongoDB
        $laporan = LaporanKeracunan::getById($id_laporan);
        if (!$laporan) {
            return response()->json(['message' => 'Laporan not found'], 404);
        }

        // Trace: laporan → SPPG
        $sppg = Sppg::getByIdWithDetails($laporan->id_sppg ?? $laporan['id_sppg']);
        if (!$sppg) {
            return response()->json(['message' => 'SPPG not found'], 404);
        }

        // Trace: SPPG → menu → bahan → supplier
        $details = DetailMenu::getByMenuId($sppg->id_menu ?? $sppg['id_menu']);
        
        $trace = [
            'laporan' => $laporan,
            'sppg' => $sppg,
            'detail_menu' => $details
        ];

        return response()->json($trace);
    }

    // GET /api/trace/supplier/{id}
    // Telusuri supplier: mulai dari supplier → bahan → menu (detail_menu) → SPPG → sekolah
    public function traceFromSupplier($id_supplier)
    {
        // Get all bahan from this supplier
        $bahans = DB::select("
            SELECT * FROM bahan_makanan WHERE id_supplier = ?
        ", [$id_supplier]);

        if (empty($bahans)) {
            return response()->json(['message' => 'No bahan for supplier'], 404);
        }

        // For each bahan, find which menus use it
        $bahanIds = array_map(fn($b) => $b->id_bahan ?? $b['id_bahan'], $bahans);
        $placeholders = implode(',', array_fill(0, count($bahanIds), '?'));

        $details = DB::select("
            SELECT dm.*, m.nama_menu, b.nama_bahan
            FROM detail_menu dm
            LEFT JOIN menu m ON dm.id_menu = m.id_menu
            LEFT JOIN bahan_makanan b ON dm.id_bahan = b.id_bahan
            WHERE dm.id_bahan IN ($placeholders)
        ", $bahanIds);

        // For each menu, find SPPG distribution
        $menuIds = array_unique(array_map(fn($d) => $d->id_menu ?? $d['id_menu'], $details));
        $menuPlaceholders = implode(',', array_fill(0, count($menuIds), '?'));

        $sppgs = DB::select("
            SELECT sp.*, m.nama_menu, s.nama_sekolah
            FROM sppg sp
            LEFT JOIN menu m ON sp.id_menu = m.id_menu
            LEFT JOIN sekolah s ON sp.id_sekolah = s.id_sekolah
            WHERE sp.id_menu IN ($menuPlaceholders)
        ", $menuIds);

        $trace = [
            'supplier_id' => $id_supplier,
            'bahans' => $bahans,
            'menu_details' => $details,
            'sppg_distributions' => $sppgs
        ];

        return response()->json($trace);
    }
}
