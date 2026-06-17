<?php

namespace App\Models;

use Illuminate\Support\Facades\DB; 

class LaporanKeracunan 
{
    public static function getAll()
    {
        return DB::connection('mongodb')->collection('laporan_keracunan')->get();
    }

    public static function getById($id)
    {
        return DB::connection('mongodb')->collection('laporan_keracunan')->where('_id', $id)->first();
    }

    public static function create($data)
    {
        return DB::connection('mongodb')->collection('laporan_keracunan')->insert([
            'id_laporan'      => $data['id_laporan'] ?? null,
            'tanggal_laporan' => $data['tanggal_laporan'] ?? null,
            'jumlah_korban'   => (int) ($data['jumlah_korban'] ?? 0),
            'deskripsi'       => $data['deskripsi'] ?? null,
            'id_sekolah'      => (int) ($data['id_sekolah'] ?? null),
            'id_sppg'         => (int) ($data['id_sppg'] ?? null)
        ]);
    }

    public static function update($id, $data)
    {
        return DB::connection('mongodb')->collection('laporan_keracunan')->where('_id', $id)->update([
            'tanggal_laporan' => $data['tanggal_laporan'] ?? null,
            'jumlah_korban'   => (int) ($data['jumlah_korban'] ?? 0),
            'deskripsi'       => $data['deskripsi'] ?? null,
            'id_sekolah'      => (int) ($data['id_sekolah'] ?? null),
            'id_sppg'         => (int) ($data['id_sppg'] ?? null)
        ]);
    }

    public static function delete($id)
    {
        return DB::connection('mongodb')->collection('laporan_keracunan')->where('_id', $id)->delete();
    }
}