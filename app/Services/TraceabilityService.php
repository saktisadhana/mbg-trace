<?php

namespace App\Services;

use App\Models\LaporanKeracunan;
use App\Models\Sppg;
use App\Models\Supplier;

class TraceabilityService
{
    public function traceFromReport($id_laporan)
    {
        // 1. Ambil LaporanKeracunan (MongoDB) -> dapatkan id_sppg
        $laporan = LaporanKeracunan::findOrFail($id_laporan);
        $id_sppg = $laporan->id_sppg;

        // 2. Cari SPPG di MySQL beserta relasinya (Menu -> Bahan Makanan -> Supplier)
        $sppg = Sppg::with([
            'sekolah',
            'menu.bahanMakanan.supplier'
        ])->find($id_sppg);

        return [
            'laporan' => $laporan,
            'trace_sppg' => $sppg
        ];
    }

    public function traceFromSupplier($id_supplier)
    {
        // 1. Cari semua SPPG yang menggunakan bahan dari supplier tersebut (MySQL)
        $supplier = Supplier::with('bahanMakanan.menu.sppg')->findOrFail($id_supplier);

        $id_sppg_list = [];

        foreach ($supplier->bahanMakanan as $bahan) {
            foreach ($bahan->menu as $menu) {
                foreach ($menu->sppg as $sppg) {
                    $id_sppg_list[] = $sppg->id_sppg;
                }
            }
        }

        // Dapatkan kumpulan id_sppg yang unik
        $id_sppg_list = array_values(array_unique($id_sppg_list));

        // 2. Cari LaporanKeracunan di MongoDB dengan whereIn('id_sppg', [...])
        $laporan = LaporanKeracunan::whereIn('id_sppg', $id_sppg_list)->get();

        return [
            'supplier' => $supplier,
            'affected_sppg_ids' => $id_sppg_list,
            'laporan_keracunan' => $laporan
        ];
    }
}
