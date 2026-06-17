<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Supplier 
{
    public static function getAll()
    {
        return DB::select('SELECT * FROM supplier');
    }

    public static function getById($id)
    {
        $result = DB::select('SELECT * FROM supplier WHERE id_supplier = ?', [$id]);
        return count($result) > 0 ? $result[0] : null;
    }

    public static function getByIdWithBahan($id)
    {
        $supplier = self::getById($id);
        if ($supplier) {
            $supplier->bahan = DB::select('SELECT * FROM bahan_makanan WHERE id_supplier = ?', [$id]);
        }
        return $supplier;
    }

    public static function create($data)
    {
        DB::insert('INSERT INTO supplier (nama_supplier, alamat, no_telp, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [
            $data['nama_supplier'] ?? null,
            $data['alamat'] ?? null,
            $data['no_telp'] ?? null
        ]);
        return DB::select('SELECT * FROM supplier WHERE id_supplier = LAST_INSERT_ID()')[0] ?? null;
    }

    public static function update($id, $data)
    {
        DB::update('UPDATE supplier SET nama_supplier = ?, alamat = ?, no_telp = ?, updated_at = NOW() WHERE id_supplier = ?', [
            $data['nama_supplier'] ?? null,
            $data['alamat'] ?? null,
            $data['no_telp'] ?? null,
            $id
        ]);
        return self::getById($id);
    }

    public static function delete($id)
    {
        return DB::delete('DELETE FROM supplier WHERE id_supplier = ?', [$id]) > 0;
    }
}