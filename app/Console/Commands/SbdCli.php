<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SbdCli extends Command
{
    protected $signature = 'sbd:app';
    protected $description = 'Aplikasi CLI FP SBD (0 ORM)';

    public function handle()
    {
        while (true) {
            $this->info("\n--- SISTEM MANAJEMEN GIZI & KERACUNAN ---");
            $this->line("1. Lihat Data SPPG (MySQL)");
            $this->line("2. Tambah SPPG (MySQL)");
            $this->line("3. Lihat Laporan (MongoDB)");
            $this->line("4. Tambah Laporan (MongoDB)");
            $this->line("5. Keluar");

            $pilihan = $this->ask('Pilih menu (1-5)');

            switch ($pilihan) {
                case '1':
                    // 0 ORM: Menggunakan DB::select (Raw SQL)
                    $data = DB::select("SELECT sp.id_sppg, sp.tanggal_distribusi, m.nama_menu, s.nama_sekolah 
                                        FROM sppg sp 
                                        LEFT JOIN menu m ON sp.id_menu = m.id_menu 
                                        LEFT JOIN sekolah s ON sp.id_sekolah = s.id_sekolah");
                    foreach ($data as $row) {
                        $this->line("ID: {$row->id_sppg} | Menu: {$row->nama_menu} | Sekolah: {$row->nama_sekolah}");
                    }
                    break;
                case '2':
                    $tgl = $this->ask('Tgl (YYYY-MM-DD)');
                    $porsi = $this->ask('Porsi');
                    $id_m = $this->ask('ID Menu');
                    $id_s = $this->ask('ID Sekolah');
                    // 0 ORM: Menggunakan DB::insert (Raw SQL)
                    DB::insert('INSERT INTO sppg (tanggal_distribusi, jumlah_porsi, id_menu, id_sekolah) VALUES (?, ?, ?, ?)', [$tgl, $porsi, $id_m, $id_s]);
                    $this->info("Berhasil!");
                    break;
                case '3':
                    // 0 ORM: MongoDB menggunakan DB::connection
                    $laporan = DB::connection('mongodb')->table('laporan_keracunan')->get();
                    foreach ($laporan as $lap) {
                        $id = $lap->id_laporan ?? $lap['id_laporan'] ?? 'N/A';
                        $korban = $lap->jumlah_korban ?? $lap['jumlah_korban'] ?? 0;
                        $this->line("Laporan: {$id} | Korban: {$korban}");
                    }
                    break;
                case '4':
                    $id = $this->ask('ID Laporan');
                    $jml = $this->ask('Jumlah Korban');
                    // 0 ORM: MongoDB Insert
                    DB::connection('mongodb')->table('laporan_keracunan')->insert(['id_laporan' => $id, 'jumlah_korban' => (int)$jml]);
                    $this->info("Berhasil!");
                    break;
                case '5': 
                    return 0;
            }
        }
    }
}
