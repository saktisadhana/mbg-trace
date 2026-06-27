-- =====================================================
-- MBG Traceability — extra mock data (run AFTER schema.sql)
-- Run in the Supabase SQL Editor. Safe to re-run (idempotent).
-- Triggers are dropped during the insert (so past-dated sppg rows load)
-- and recreated afterward; the trigger functions from schema.sql are reused.
-- =====================================================

drop trigger if exists trg_validasi_detail_menu on detail_menu;
drop trigger if exists trg_validasi_sppg_insert on sppg;
drop trigger if exists trg_validasi_sppg_update on sppg;
drop trigger if exists trg_log_status_distribusi on sppg;

-- ---------- Suppliers (6-12) ----------
insert into supplier (id_supplier, nama_supplier, alamat, no_telp) values
(6, 'CV Ikan Segar Nusantara','Lamongan','081234560006'),
(7, 'PT Susu Sehat Indonesia','Malang','081234560007'),
(8, 'UD Tahu Tempe Berkah','Sidoarjo','081234560008'),
(9, 'PT Buah Segar Jaya','Batu','081234560009'),
(10,'CV Sayur Organik Hijau','Mojokerto','081234560010'),
(11,'PT Roti Gandum Sehat','Surabaya','081234560011'),
(12,'UD Rempah Nusantara','Gresik','081234560012')
on conflict do nothing;

-- ---------- Bahan makanan (9-24); a few near-expiry for the kadaluarsa view ----------
insert into bahan_makanan (id_bahan, nama_bahan, tanggal_kadaluarsa, id_supplier) values
(9, 'Ikan Lele','2026-08-15',6),
(10,'Ikan Tongkol','2026-07-30',6),
(11,'Susu UHT','2026-12-01',7),
(12,'Tahu Putih','2026-07-05',8),
(13,'Tempe','2026-07-03',8),
(14,'Pisang Cavendish','2026-07-02',9),
(15,'Apel Malang','2026-07-10',9),
(16,'Brokoli','2026-07-08',10),
(17,'Kentang','2026-09-01',10),
(18,'Roti Gandum','2026-07-01',11),
(19,'Gula Pasir','2027-06-01',12),
(20,'Tepung Terigu','2027-03-01',12),
(21,'Kecap Manis','2027-01-15',12),
(22,'Tomat','2026-07-04',10),
(23,'Daging Sapi','2026-08-20',2),
(24,'Jeruk','2026-07-12',9)
on conflict do nothing;

-- ---------- Menu (4-10) ----------
insert into menu (id_menu, nama_menu, tanggal_produksi) values
(4, 'Nasi Ikan Lele Goreng','2026-06-06 05:00:00'),
(5, 'Nasi Tahu Tempe Bacem','2026-06-07 05:00:00'),
(6, 'Nasi Ayam Brokoli','2026-06-08 05:00:00'),
(7, 'Nasi Daging Sapi Lada Hitam','2026-06-09 05:00:00'),
(8, 'Roti Gandum + Susu UHT','2026-06-10 05:00:00'),
(9, 'Nasi Ikan Tongkol Balado','2026-06-11 05:00:00'),
(10,'Nasi Sayur + Buah Segar','2026-06-12 05:00:00')
on conflict do nothing;

-- ---------- Detail menu (menu -> bahan) ----------
insert into detail_menu (id_menu, id_bahan, jumlah_bahan) values
(4,1,100),(4,9,60),(4,8,10),(4,7,5),
(5,1,100),(5,12,40),(5,13,40),(5,21,10),
(6,1,100),(6,2,50),(6,16,30),(6,7,5),
(7,1,100),(7,23,60),(7,17,40),(7,5,5),
(8,18,30),(8,11,50),(8,19,10),
(9,1,100),(9,10,60),(9,22,20),(9,5,5),
(10,1,100),(10,16,20),(10,14,30),(10,15,30)
on conflict do nothing;

-- ---------- Sekolah (6-15) ----------
insert into sekolah (id_sekolah, nama_sekolah, alamat) values
(6, 'SDN Gubeng 1','Surabaya'),
(7, 'SDN Gubeng 2','Surabaya'),
(8, 'SDN Rungkut 1','Surabaya'),
(9, 'SDN Tandes 1','Surabaya'),
(10,'SMPN 3 Surabaya','Surabaya'),
(11,'SMPN 4 Surabaya','Surabaya'),
(12,'SMAN 1 Surabaya','Surabaya'),
(13,'SDN Sukolilo 1','Surabaya'),
(14,'SDN Mulyorejo 1','Surabaya'),
(15,'MI Al-Hidayah','Surabaya')
on conflict do nothing;

-- ---------- SPPG / distribusi (6-20) ----------
insert into sppg (id_sppg, tanggal_distribusi, jumlah_porsi, alamat_sppg, id_menu, id_sekolah) values
(6, '2026-06-06 07:00:00',520,'Dapur MBG Surabaya Barat',4,6),
(7, '2026-06-07 07:00:00',480,'Dapur MBG Surabaya Barat',5,7),
(8, '2026-06-08 07:00:00',610,'Dapur MBG Surabaya Timur',6,8),
(9, '2026-06-09 07:00:00',540,'Dapur MBG Surabaya Timur',7,9),
(10,'2026-06-10 07:00:00',700,'Dapur MBG Surabaya Selatan',8,10),
(11,'2026-06-11 07:00:00',650,'Dapur MBG Surabaya Selatan',9,11),
(12,'2026-06-12 07:00:00',720,'Dapur MBG Surabaya Pusat',10,12),
(13,'2026-06-13 07:00:00',500,'Dapur MBG Surabaya Pusat',4,13),
(14,'2026-06-14 07:00:00',460,'Dapur MBG Surabaya Barat',5,14),
(15,'2026-06-15 07:00:00',580,'Dapur MBG Surabaya Timur',6,15),
(16,'2026-06-16 07:00:00',640,'Dapur MBG Surabaya Selatan',1,6),
(17,'2026-06-17 07:00:00',690,'Dapur MBG Surabaya Pusat',2,7),
(18,'2026-06-18 07:00:00',530,'Dapur MBG Surabaya Barat',3,8),
(19,'2026-06-19 07:00:00',610,'Dapur MBG Surabaya Timur',7,9),
(20,'2026-06-20 07:00:00',700,'Dapur MBG Surabaya Selatan',9,10)
on conflict do nothing;

-- ---------- Laporan keracunan (LAP-006 .. LAP-010) ----------
insert into laporan_keracunan (id_laporan, tanggal_laporan, jumlah_korban, deskripsi, id_sppg, detail_investigasi, dokumentasi, riwayat_audit) values
('LAP-006','2026-06-08',6,'Enam siswa mual setelah makan Nasi Ikan Lele Goreng. Diduga ikan kurang segar.',6,'Pemeriksaan menunjukkan ikan lele dari CV Ikan Segar Nusantara disimpan tanpa pendingin yang memadai.','foto_lele_sdn_gubeng1.jpg',
 '[{"tanggal":"2026-06-08","status":"Laporan diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-09","status":"Investigasi rantai dingin supplier","petugas":"Tim Inspeksi Pangan"}]'::jsonb),
('LAP-007','2026-06-11',4,'Empat siswa gatal-gatal setelah makan Nasi Ayam Brokoli. Diduga reaksi alergi.',8,'Tidak ditemukan kontaminasi. Kemungkinan alergi individual terhadap brokoli.',null,
 '[{"tanggal":"2026-06-11","status":"Laporan diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-12","status":"Konsultasi medis - alergi individual","petugas":"Puskesmas Rungkut"}]'::jsonb),
('LAP-008','2026-06-13',9,'Sembilan siswa diare setelah makan Nasi Daging Sapi Lada Hitam. Diduga daging tidak matang sempurna.',9,'Suhu pemasakan daging sapi tidak mencapai standar. Dapur diberi peringatan SOP.','laporan_dapur_timur_0613.pdf',
 '[{"tanggal":"2026-06-13","status":"Laporan diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-14","status":"Audit suhu pemasakan","petugas":"Tim Inspeksi Pangan"},{"tanggal":"2026-06-15","status":"Peringatan SOP ke dapur","petugas":"Kepala Dinas Kesehatan"}]'::jsonb),
('LAP-009','2026-06-16',15,'Lima belas siswa keracunan setelah makan Nasi Ikan Tongkol Balado. Gejala mual dan pusing (histamin).',11,'Investigasi mengindikasikan kadar histamin tinggi pada ikan tongkol dari CV Ikan Segar Nusantara. Stok ditarik.','hasil_lab_histamin_tongkol.pdf',
 '[{"tanggal":"2026-06-16","status":"Laporan darurat diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-16","status":"Tim medis dikirim","petugas":"Puskesmas Surabaya Selatan"},{"tanggal":"2026-06-18","status":"Histamin terkonfirmasi - stok ikan ditarik","petugas":"Lab Kesehatan Surabaya"}]'::jsonb),
('LAP-010','2026-06-19',2,'Dua siswa sakit perut setelah makan paket Roti Gandum + Susu UHT. Diduga susu mendekati kadaluarsa.',10,'Pengecekan stok susu UHT menunjukkan satu batch mendekati tanggal kadaluarsa. Batch ditarik sebagai pencegahan.',null,
 '[{"tanggal":"2026-06-19","status":"Laporan diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-20","status":"Pengecekan tanggal kadaluarsa stok","petugas":"Tim Inspeksi Pangan"}]'::jsonb)
on conflict do nothing;

-- ---------- Resync serial sequences ----------
select setval(pg_get_serial_sequence('supplier','id_supplier'),       (select max(id_supplier) from supplier));
select setval(pg_get_serial_sequence('bahan_makanan','id_bahan'),     (select max(id_bahan) from bahan_makanan));
select setval(pg_get_serial_sequence('menu','id_menu'),               (select max(id_menu) from menu));
select setval(pg_get_serial_sequence('sekolah','id_sekolah'),         (select max(id_sekolah) from sekolah));
select setval(pg_get_serial_sequence('sppg','id_sppg'),               (select max(id_sppg) from sppg));
select setval(pg_get_serial_sequence('laporan_keracunan','id'),       (select max(id) from laporan_keracunan));

-- ---------- Recreate the validation/log triggers ----------
create trigger trg_validasi_detail_menu before insert on detail_menu
    for each row execute function fn_validasi_detail_menu();
create trigger trg_validasi_sppg_insert before insert on sppg
    for each row execute function fn_validasi_sppg_insert();
create trigger trg_validasi_sppg_update before update on sppg
    for each row execute function fn_validasi_sppg_update();
create trigger trg_log_status_distribusi after update on sppg
    for each row execute function fn_log_status_distribusi();
