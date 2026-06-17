<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Sppg 
{
    public static function getAll()
    {
        return DB::select('SELECT * FROM sppg');
    }

    public static function getWithDetails()
    {
        return DB::select("
            SELECT sp.*, m.nama_menu, s.nama_sekolah 
            FROM sppg sp
            LEFT JOIN menu m ON sp.id_menu = m.id_menu
            LEFT JOIN sekolah s ON sp.id_sekolah = s.id_sekolah
        ");
    }

    public static function getById($id)
    {
        $result = DB::select('SELECT * FROM sppg WHERE id_sppg = ?', [$id]);
        return count($result) > 0 ? $result[0] : null;
    }

    public static function getByIdWithDetails($id)
    {
        $result = DB::select("
            SELECT sp.*, m.id_menu, m.nama_menu, s.id_sekolah, s.nama_sekolah
            FROM sppg sp
            LEFT JOIN menu m ON sp.id_menu = m.id_menu
            LEFT JOIN sekolah s ON sp.id_sekolah = s.id_sekolah
            WHERE sp.id_sppg = ?
        ", [$id]);
        return count($result) > 0 ? $result[0] : null;
    }

    public static function create($data)
    {
        DB::insert('INSERT INTO sppg (tanggal_distribusi, jumlah_porsi, alamat_sppg, id_menu, id_sekolah, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())', [
            $data['tanggal_distribusi'] ?? null,
            $data['jumlah_porsi'] ?? null,
            $data['alamat_sppg'] ?? null,
            $data['id_menu'] ?? null,
            $data['id_sekolah'] ?? null
        ]);
        return DB::select('SELECT * FROM sppg WHERE id_sppg = LAST_INSERT_ID()')[0] ?? null;
    }

    public static function update($id, $data)
    {
        DB::update('UPDATE sppg SET tanggal_distribusi = ?, jumlah_porsi = ?, alamat_sppg = ?, id_menu = ?, id_sekolah = ?, updated_at = NOW() WHERE id_sppg = ?', [
            $data['tanggal_distribusi'] ?? null,
            $data['jumlah_porsi'] ?? null,
            $data['alamat_sppg'] ?? null,
            $data['id_menu'] ?? null,
            $data['id_sekolah'] ?? null,
            $id
        ]);
        return self::getById($id);
    }

    public static function delete($id)
    {
        return DB::delete('DELETE FROM sppg WHERE id_sppg = ?', [$id]) > 0;
    }
}