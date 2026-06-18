-- =====================================================
-- Database Schema + Data Dummy
-- Sistem Traceability Program Makan Bergizi Gratis (MBG)
-- Engine: MySQL / MariaDB | Charset: utf8mb4
-- =====================================================

-- ====== BAGIAN 1: SETUP DATABASE ======
CREATE DATABASE IF NOT EXISTS sppg_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sppg_db;

-- =====================================================
-- BAGIAN 2: PEMBUATAN TABEL (SCHEMA)
-- Urutan: tabel induk dulu, baru tabel anak (yang punya FK)
-- =====================================================

-- 1. Tabel Supplier
CREATE TABLE supplier (
    id_supplier   INT AUTO_INCREMENT PRIMARY KEY,
    nama_supplier VARCHAR(100) NOT NULL,
    alamat        TEXT,
    no_telp       VARCHAR(20)
) ENGINE=InnoDB;

-- 2. Tabel Bahan_Makanan
CREATE TABLE bahan_makanan (
    id_bahan           INT AUTO_INCREMENT PRIMARY KEY,
    nama_bahan         VARCHAR(100) NOT NULL,
    tanggal_kadaluarsa DATE,
    id_supplier        INT NOT NULL,

    FOREIGN KEY (id_supplier)
        REFERENCES supplier(id_supplier)
) ENGINE=InnoDB;

-- 3. Tabel Menu
CREATE TABLE menu (
    id_menu          INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu        VARCHAR(100) NOT NULL,
    tanggal_produksi DATETIME
) ENGINE=InnoDB;

-- 4. Tabel Detail_Menu (junction M:N menu <-> bahan_makanan)
CREATE TABLE detail_menu (
    id_menu      INT,
    id_bahan     INT,
    jumlah_bahan INT,

    PRIMARY KEY (id_menu, id_bahan),

    FOREIGN KEY (id_menu)
        REFERENCES menu(id_menu),

    FOREIGN KEY (id_bahan)
        REFERENCES bahan_makanan(id_bahan)
) ENGINE=InnoDB;

-- 5. Tabel Sekolah
CREATE TABLE sekolah (
    id_sekolah   INT AUTO_INCREMENT PRIMARY KEY,
    nama_sekolah VARCHAR(100) NOT NULL,
    alamat       TEXT
) ENGINE=InnoDB;

-- 6. Tabel SPPG
CREATE TABLE sppg (
    id_sppg            INT AUTO_INCREMENT PRIMARY KEY,
    tanggal_distribusi DATETIME,
    jumlah_porsi       INT,
    alamat_sppg        TEXT,

    id_menu            INT,
    id_sekolah         INT,

    FOREIGN KEY (id_menu)
        REFERENCES menu(id_menu),

    FOREIGN KEY (id_sekolah)
        REFERENCES sekolah(id_sekolah)
) ENGINE=InnoDB;

-- =====================================================
-- BAGIAN 3: DATA DUMMY (INSERT)
-- Urutan insert mengikuti urutan tabel di atas:
-- induk diisi dulu agar FK di tabel anak tidak gagal.
-- =====================================================

-- Data Supplier
INSERT INTO supplier (id_supplier, nama_supplier, alamat, no_telp) VALUES
(1, 'PT Beras Nusantara', 'Surabaya', '081111111111'),
(2, 'PT Ayam Sehat', 'Sidoarjo', '082222222222'),
(3, 'PT Sayur Makmur', 'Gresik', '083333333333'),
(4, 'PT Telur Jaya', 'Mojokerto', '084444444444'),
(5, 'PT Bumbu Nasional', 'Lamongan', '085555555555');

-- Data Bahan_Makanan
INSERT INTO bahan_makanan (id_bahan, nama_bahan, tanggal_kadaluarsa, id_supplier) VALUES
(1, 'Beras Premium', '2026-12-31', 1),
(2, 'Daging Ayam', '2026-08-31', 2),
(3, 'Wortel', '2026-07-15', 3),
(4, 'Telur Ayam', '2026-08-10', 4),
(5, 'Garam', '2028-01-01', 5),
(6, 'Bayam', '2026-07-20', 3),
(7, 'Bawang Putih', '2027-01-01', 5),
(8, 'Minyak Goreng', '2027-12-31', 5);

-- Data Menu
INSERT INTO menu (id_menu, nama_menu, tanggal_produksi) VALUES
(1, 'Nasi Ayam Goreng', '2026-06-01 05:00:00'),
(2, 'Nasi Telur Balado', '2026-06-02 05:00:00'),
(3, 'Nasi Sayur Sehat', '2026-06-03 05:00:00');

-- Data Detail_Menu
INSERT INTO detail_menu (id_menu, id_bahan, jumlah_bahan) VALUES
(1, 1, 100),
(1, 2, 50),
(1, 8, 10),

(2, 1, 100),
(2, 4, 40),
(2, 5, 5),

(3, 1, 100),
(3, 3, 30),
(3, 6, 30),
(3, 5, 5);

-- Data Sekolah
INSERT INTO sekolah (id_sekolah, nama_sekolah, alamat) VALUES
(1, 'SDN Ketintang 1', 'Surabaya'),
(2, 'SDN Ketintang 2', 'Surabaya'),
(3, 'SDN Wonokromo 1', 'Surabaya'),
(4, 'SMPN 1 Surabaya', 'Surabaya'),
(5, 'SMPN 2 Surabaya', 'Surabaya');

-- Data SPPG
INSERT INTO sppg (id_sppg, tanggal_distribusi, jumlah_porsi, alamat_sppg, id_menu, id_sekolah) VALUES
(1, '2026-06-01 07:00:00', 500, 'Dapur MBG Surabaya Barat',   1, 1),
(2, '2026-06-02 07:00:00', 450, 'Dapur MBG Surabaya Barat',   2, 2),
(3, '2026-06-03 07:00:00', 600, 'Dapur MBG Surabaya Timur',   3, 3),
(4, '2026-06-04 07:00:00', 550, 'Dapur MBG Surabaya Timur',   1, 4),
(5, '2026-06-05 07:00:00', 700, 'Dapur MBG Surabaya Selatan', 2, 5);

-- =====================================================
-- BAGIAN 4: KOLOM TAMBAHAN PENDUKUNG TRIGGER
-- Kolom-kolom ini diperlukan agar trigger bisa berfungsi
-- =====================================================

-- Tambah kolom status_distribusi di tabel sppg
ALTER TABLE sppg
    ADD COLUMN status_distribusi ENUM('diproses','dikirim','diterima')
    DEFAULT 'diproses' AFTER alamat_sppg;

-- Tambah kolom created_at di semua tabel untuk jejak audit
ALTER TABLE supplier       ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE bahan_makanan  ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE menu           ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE detail_menu    ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE sekolah        ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE sppg           ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Tabel log untuk mencatat perubahan status distribusi (audit trail)
CREATE TABLE log_distribusi (
    id_log         INT AUTO_INCREMENT PRIMARY KEY,
    id_sppg        INT NOT NULL,
    status_lama    ENUM('diproses','dikirim','diterima'),
    status_baru    ENUM('diproses','dikirim','diterima'),
    waktu_perubahan DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_sppg)
        REFERENCES sppg(id_sppg)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- BAGIAN 5: TRIGGER
-- =====================================================

-- -------------------------------------------------
-- TRIGGER 1: trg_validasi_detail_menu
-- Tabel  : detail_menu (BEFORE INSERT)
-- Tujuan : 1) Cek bahan tidak boleh kadaluarsa
--             → mencegah keracunan makanan
--          2) Cek jumlah_bahan harus > 0
--             → validasi data input
-- -------------------------------------------------
DELIMITER //

CREATE TRIGGER trg_validasi_detail_menu
BEFORE INSERT ON detail_menu
FOR EACH ROW
BEGIN
    DECLARE v_tgl_exp DATE;
    DECLARE v_nama    VARCHAR(100);
    DECLARE v_msg     VARCHAR(500);

    -- Validasi 1: jumlah_bahan harus lebih dari 0
    IF NEW.jumlah_bahan IS NULL OR NEW.jumlah_bahan <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'DITOLAK: jumlah_bahan harus lebih dari 0.';
    END IF;

    -- Validasi 2: bahan tidak boleh sudah kadaluarsa
    SELECT tanggal_kadaluarsa, nama_bahan
    INTO v_tgl_exp, v_nama
    FROM bahan_makanan
    WHERE id_bahan = NEW.id_bahan;

    IF v_tgl_exp IS NOT NULL AND v_tgl_exp < CURDATE() THEN
        SET v_msg = CONCAT(
            'DITOLAK: Bahan "', v_nama,
            '" sudah kadaluarsa sejak ', v_tgl_exp,
            '. Tidak boleh digunakan dalam menu.'
        );
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_msg;
    END IF;
END //

DELIMITER ;

-- -------------------------------------------------
-- TRIGGER 2: trg_validasi_sppg_insert
-- Tabel  : sppg (BEFORE INSERT)
-- Tujuan : 1) Cek jumlah_porsi harus > 0
--             → validasi distribusi
--          2) Cek tanggal_distribusi tidak boleh masa lalu
--             → mencegah data distribusi tidak valid
-- -------------------------------------------------
DELIMITER //

CREATE TRIGGER trg_validasi_sppg_insert
BEFORE INSERT ON sppg
FOR EACH ROW
BEGIN
    -- Validasi 1: jumlah porsi harus lebih dari 0
    IF NEW.jumlah_porsi IS NULL OR NEW.jumlah_porsi <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'DITOLAK: jumlah_porsi harus lebih dari 0.';
    END IF;

    -- Validasi 2: tanggal distribusi tidak boleh di masa lalu
    IF NEW.tanggal_distribusi IS NOT NULL
       AND DATE(NEW.tanggal_distribusi) < CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'DITOLAK: tanggal_distribusi tidak boleh di masa lalu.';
    END IF;
END //

DELIMITER ;

-- -------------------------------------------------
-- TRIGGER 3: trg_validasi_sppg_update
-- Tabel  : sppg (BEFORE UPDATE)
-- Tujuan : Cek jumlah_porsi tetap valid saat di-update
-- -------------------------------------------------
DELIMITER //

CREATE TRIGGER trg_validasi_sppg_update
BEFORE UPDATE ON sppg
FOR EACH ROW
BEGIN
    IF NEW.jumlah_porsi IS NULL OR NEW.jumlah_porsi <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'DITOLAK: jumlah_porsi harus lebih dari 0.';
    END IF;
END //

DELIMITER ;

-- -------------------------------------------------
-- TRIGGER 4: trg_log_status_distribusi
-- Tabel  : sppg (AFTER UPDATE)
-- Tujuan : Mencatat setiap perubahan status distribusi
--          ke tabel log_distribusi sebagai audit trail
--          → menjawab masalah Transparansi & Distribusi
-- -------------------------------------------------
DELIMITER //

CREATE TRIGGER trg_log_status_distribusi
AFTER UPDATE ON sppg
FOR EACH ROW
BEGIN
    IF OLD.status_distribusi <> NEW.status_distribusi THEN
        INSERT INTO log_distribusi (id_sppg, status_lama, status_baru, waktu_perubahan)
        VALUES (NEW.id_sppg, OLD.status_distribusi, NEW.status_distribusi, NOW());
    END IF;
END //

DELIMITER ;

-- =====================================================
-- BAGIAN 6: VIEW TRACEABILITY
-- =====================================================

-- View lengkap: trace dari distribusi -> menu -> bahan -> supplier
CREATE VIEW v_traceability_lengkap AS
SELECT
    sp.id_sppg,
    sp.tanggal_distribusi,
    sp.jumlah_porsi,
    sp.status_distribusi,
    sk.nama_sekolah,
    sk.alamat            AS alamat_sekolah,
    m.nama_menu,
    m.tanggal_produksi,
    bm.nama_bahan,
    bm.tanggal_kadaluarsa,
    dm.jumlah_bahan,
    s.nama_supplier,
    s.no_telp            AS telp_supplier
FROM sppg sp
    JOIN sekolah sk       ON sp.id_sekolah = sk.id_sekolah
    JOIN menu m           ON sp.id_menu    = m.id_menu
    JOIN detail_menu dm   ON m.id_menu     = dm.id_menu
    JOIN bahan_makanan bm ON dm.id_bahan   = bm.id_bahan
    JOIN supplier s       ON bm.id_supplier = s.id_supplier;

-- View bahan yang sudah / hampir kadaluarsa (7 hari ke depan)
CREATE VIEW v_bahan_hampir_kadaluarsa AS
SELECT
    bm.id_bahan,
    bm.nama_bahan,
    bm.tanggal_kadaluarsa,
    s.nama_supplier,
    s.no_telp,
    CASE
        WHEN bm.tanggal_kadaluarsa < CURDATE()
            THEN 'SUDAH KADALUARSA'
        WHEN bm.tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            THEN 'HAMPIR KADALUARSA'
    END AS status_kadaluarsa
FROM bahan_makanan bm
    JOIN supplier s ON bm.id_supplier = s.id_supplier
WHERE bm.tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 7 DAY);

 -- rtestararaa