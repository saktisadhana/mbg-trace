<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class DetailMenu 
{
    public static function getAll()
    {
        return DB::select('SELECT * FROM detail_menu');
    }

    public static function getWithBahanDanMenu()
    {
        return DB::select("
            SELECT dm.*, m.nama_menu, b.nama_bahan 
            FROM detail_menu dm
            LEFT JOIN menu m ON dm.id_menu = m.id_menu
            LEFT JOIN bahan_makanan b ON dm.id_bahan = b.id_bahan
        ");
    }

    public static function getByMenuId($id_menu)
    {
        return DB::select("
            SELECT dm.*, m.nama_menu, b.nama_bahan 
            FROM detail_menu dm
            LEFT JOIN menu m ON dm.id_menu = m.id_menu
            LEFT JOIN bahan_makanan b ON dm.id_bahan = b.id_bahan
            WHERE dm.id_menu = ?
        ", [$id_menu]);
    }

    public static function getById($id_menu, $id_bahan)
    {
        $result = DB::select('SELECT * FROM detail_menu WHERE id_menu = ? AND id_bahan = ?', [$id_menu, $id_bahan]);
        return count($result) > 0 ? $result[0] : null;
    }

    public static function create($data)
    {
        DB::insert('INSERT INTO detail_menu (id_menu, id_bahan, jumlah_bahan, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [
            $data['id_menu'] ?? null,
            $data['id_bahan'] ?? null,
            $data['jumlah_bahan'] ?? null
        ]);
        return true;
    }

    public static function update($id_menu, $id_bahan, $data)
    {
        DB::update('UPDATE detail_menu SET jumlah_bahan = ?, updated_at = NOW() WHERE id_menu = ? AND id_bahan = ?', [
            $data['jumlah_bahan'] ?? null,
            $id_menu,
            $id_bahan
        ]);
        return self::getById($id_menu, $id_bahan);
    }

    public static function delete($id_menu, $id_bahan)
    {
        return DB::delete('DELETE FROM detail_menu WHERE id_menu = ? AND id_bahan = ?', [$id_menu, $id_bahan]) > 0;
    }

    public static function deleteByMenuId($id_menu)
    {
        return DB::delete('DELETE FROM detail_menu WHERE id_menu = ?', [$id_menu]) > 0;
    }
}