<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class BahanMakanan 
{
    public static function getAll()
    {
        return DB::select('SELECT * FROM bahan_makanan');
    }

    public static function getWithSupplier()
    {
        return DB::select("
            SELECT b.*, s.nama_supplier 
            FROM bahan_makanan b
            LEFT JOIN supplier s ON b.id_supplier = s.id_supplier
        ");
    }

    public static function getById($id)
    {
        $result = DB::select('SELECT * FROM bahan_makanan WHERE id_bahan = ?', [$id]);
        return count($result) > 0 ? $result[0] : null;
    }

    public static function getByIdWithSupplier($id)
    {
        $result = DB::select("
            SELECT b.*, s.nama_supplier, s.id_supplier
            FROM bahan_makanan b
            LEFT JOIN supplier s ON b.id_supplier = s.id_supplier
            WHERE b.id_bahan = ?
        ", [$id]);
        return count($result) > 0 ? $result[0] : null;
    }

    public static function create($data)
    {
        DB::insert('INSERT INTO bahan_makanan (nama_bahan, tanggal_kadaluarsa, id_supplier, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [
            $data['nama_bahan'] ?? null,
            $data['tanggal_kadaluarsa'] ?? null,
            $data['id_supplier'] ?? null
        ]);
        return DB::select('SELECT * FROM bahan_makanan WHERE id_bahan = LAST_INSERT_ID()')[0] ?? null;
    }

    public static function update($id, $data)
    {
        DB::update('UPDATE bahan_makanan SET nama_bahan = ?, tanggal_kadaluarsa = ?, id_supplier = ?, updated_at = NOW() WHERE id_bahan = ?', [
            $data['nama_bahan'] ?? null,
            $data['tanggal_kadaluarsa'] ?? null,
            $data['id_supplier'] ?? null,
            $id
        ]);
        return self::getById($id);
    }

    public static function delete($id)
    {
        return DB::delete('DELETE FROM bahan_makanan WHERE id_bahan = ?', [$id]) > 0;
    }
}