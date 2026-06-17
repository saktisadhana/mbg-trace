<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Sekolah 
{
    public static function getAll()
    {
        return DB::select('SELECT * FROM sekolah');
    }

    public static function getById($id)
    {
        $result = DB::select('SELECT * FROM sekolah WHERE id_sekolah = ?', [$id]);
        return count($result) > 0 ? $result[0] : null;
    }

    public static function getByIdWithSppg($id)
    {
        $sekolah = self::getById($id);
        if ($sekolah) {
            $sekolah->sppg = DB::select('SELECT * FROM sppg WHERE id_sekolah = ?', [$id]);
        }
        return $sekolah;
    }

    public static function create($data)
    {
        DB::insert('INSERT INTO sekolah (nama_sekolah, alamat, created_at, updated_at) VALUES (?, ?, NOW(), NOW())', [
            $data['nama_sekolah'] ?? null,
            $data['alamat'] ?? null
        ]);
        return DB::select('SELECT * FROM sekolah WHERE id_sekolah = LAST_INSERT_ID()')[0] ?? null;
    }

    public static function update($id, $data)
    {
        DB::update('UPDATE sekolah SET nama_sekolah = ?, alamat = ?, updated_at = NOW() WHERE id_sekolah = ?', [
            $data['nama_sekolah'] ?? null,
            $data['alamat'] ?? null,
            $id
        ]);
        return self::getById($id);
    }

    public static function delete($id)
    {
        return DB::delete('DELETE FROM sekolah WHERE id_sekolah = ?', [$id]) > 0;
    }
}