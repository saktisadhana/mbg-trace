<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Menu 
{
    public static function getAll()
    {
        return DB::select('SELECT * FROM menu');
    }

    public static function getWithBahan()
    {
        return DB::select("
            SELECT m.id_menu, m.nama_menu, m.tanggal_produksi, 
                   b.nama_bahan, dm.jumlah_bahan 
            FROM menu m
            LEFT JOIN detail_menu dm ON m.id_menu = dm.id_menu
            LEFT JOIN bahan_makanan b ON dm.id_bahan = b.id_bahan
        ");
    }

    public static function getById($id)
    {
        $result = DB::select('SELECT * FROM menu WHERE id_menu = ?', [$id]);
        return count($result) > 0 ? $result[0] : null;
    }

    public static function getByIdWithBahan($id)
    {
        $result = DB::select("
            SELECT m.id_menu, m.nama_menu, m.tanggal_produksi, 
                   b.id_bahan, b.nama_bahan, dm.jumlah_bahan 
            FROM menu m
            LEFT JOIN detail_menu dm ON m.id_menu = dm.id_menu
            LEFT JOIN bahan_makanan b ON dm.id_bahan = b.id_bahan
            WHERE m.id_menu = ?
        ", [$id]);
        return $result;
    }

    public static function create($data)
    {
        DB::insert('INSERT INTO menu (nama_menu, tanggal_produksi, created_at, updated_at) VALUES (?, ?, NOW(), NOW())', [
            $data['nama_menu'] ?? null,
            $data['tanggal_produksi'] ?? null
        ]);
        return DB::select('SELECT * FROM menu WHERE id_menu = LAST_INSERT_ID()')[0] ?? null;
    }

    public static function update($id, $data)
    {
        DB::update('UPDATE menu SET nama_menu = ?, tanggal_produksi = ?, updated_at = NOW() WHERE id_menu = ?', [
            $data['nama_menu'] ?? null,
            $data['tanggal_produksi'] ?? null,
            $id
        ]);
        return self::getById($id);
    }

    public static function delete($id)
    {
        return DB::delete('DELETE FROM menu WHERE id_menu = ?', [$id]) > 0;
    }
}