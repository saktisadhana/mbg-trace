-- =====================================================
-- MBG Traceability — Supabase (PostgreSQL) schema
-- Ported from db/sppg_database_lengkap.sql (MySQL).
-- Run this in the Supabase SQL Editor (one shot).
-- =====================================================

-- ---------- TABLES ----------
create table if not exists supplier (
    id_supplier   serial primary key,
    nama_supplier varchar(100) not null,
    alamat        text,
    no_telp       varchar(20),
    created_at    timestamptz default now()
);

create table if not exists bahan_makanan (
    id_bahan           serial primary key,
    nama_bahan         varchar(100) not null,
    tanggal_kadaluarsa date,
    id_supplier        int not null references supplier(id_supplier),
    created_at         timestamptz default now()
);

create table if not exists menu (
    id_menu          serial primary key,
    nama_menu        varchar(100) not null,
    tanggal_produksi timestamptz,
    created_at       timestamptz default now()
);

create table if not exists detail_menu (
    id_menu      int references menu(id_menu),
    id_bahan     int references bahan_makanan(id_bahan),
    jumlah_bahan int,
    created_at   timestamptz default now(),
    primary key (id_menu, id_bahan)
);

create table if not exists sekolah (
    id_sekolah   serial primary key,
    nama_sekolah varchar(100) not null,
    alamat       text,
    created_at   timestamptz default now()
);

do $$ begin
    create type status_distribusi_enum as enum ('diproses','dikirim','diterima');
exception when duplicate_object then null; end $$;

create table if not exists sppg (
    id_sppg            serial primary key,
    tanggal_distribusi timestamptz,
    jumlah_porsi       int,
    alamat_sppg        text,
    status_distribusi  status_distribusi_enum default 'diproses',
    id_menu            int references menu(id_menu),
    id_sekolah         int references sekolah(id_sekolah),
    created_at         timestamptz default now()
);

create table if not exists log_distribusi (
    id_log          serial primary key,
    id_sppg         int not null references sppg(id_sppg) on delete cascade on update cascade,
    status_lama     status_distribusi_enum,
    status_baru     status_distribusi_enum,
    waktu_perubahan timestamptz default now()
);

-- laporan_keracunan was MongoDB; here it's a Postgres table with a jsonb
-- column for the flexible audit trail (document-style data inside SQL).
create table if not exists laporan_keracunan (
    id                 serial primary key,
    id_laporan         text unique,
    tanggal_laporan    date not null,
    jumlah_korban      int  not null,
    deskripsi          text not null,
    id_sppg            int references sppg(id_sppg),
    detail_investigasi text,
    dokumentasi        text,
    riwayat_audit      jsonb default '[]'::jsonb,
    created_at         timestamptz default now()
);

-- ---------- SEED DATA ----------
-- Drop validation triggers first so the (intentionally past-dated) seed rows
-- load cleanly. They're (re)created after the seed. Makes this script
-- safely re-runnable. NOTE: ON CONFLICT still fires BEFORE INSERT triggers,
-- which is why a re-run would otherwise be blocked by the past-date check.
drop trigger if exists trg_validasi_detail_menu on detail_menu;
drop trigger if exists trg_validasi_sppg_insert on sppg;
drop trigger if exists trg_validasi_sppg_update on sppg;
drop trigger if exists trg_log_status_distribusi on sppg;

insert into supplier (id_supplier, nama_supplier, alamat, no_telp) values
(1,'PT Beras Nusantara','Surabaya','081111111111'),
(2,'PT Ayam Sehat','Sidoarjo','082222222222'),
(3,'PT Sayur Makmur','Gresik','083333333333'),
(4,'PT Telur Jaya','Mojokerto','084444444444'),
(5,'PT Bumbu Nasional','Lamongan','085555555555')
on conflict do nothing;

insert into bahan_makanan (id_bahan, nama_bahan, tanggal_kadaluarsa, id_supplier) values
(1,'Beras Premium','2026-12-31',1),
(2,'Daging Ayam','2026-08-31',2),
(3,'Wortel','2026-07-15',3),
(4,'Telur Ayam','2026-08-10',4),
(5,'Garam','2028-01-01',5),
(6,'Bayam','2026-07-20',3),
(7,'Bawang Putih','2027-01-01',5),
(8,'Minyak Goreng','2027-12-31',5)
on conflict do nothing;

insert into menu (id_menu, nama_menu, tanggal_produksi) values
(1,'Nasi Ayam Goreng','2026-06-01 05:00:00'),
(2,'Nasi Telur Balado','2026-06-02 05:00:00'),
(3,'Nasi Sayur Sehat','2026-06-03 05:00:00')
on conflict do nothing;

insert into detail_menu (id_menu, id_bahan, jumlah_bahan) values
(1,1,100),(1,2,50),(1,8,10),
(2,1,100),(2,4,40),(2,5,5),
(3,1,100),(3,3,30),(3,6,30),(3,5,5)
on conflict do nothing;

insert into sekolah (id_sekolah, nama_sekolah, alamat) values
(1,'SDN Ketintang 1','Surabaya'),
(2,'SDN Ketintang 2','Surabaya'),
(3,'SDN Wonokromo 1','Surabaya'),
(4,'SMPN 1 Surabaya','Surabaya'),
(5,'SMPN 2 Surabaya','Surabaya')
on conflict do nothing;

insert into sppg (id_sppg, tanggal_distribusi, jumlah_porsi, alamat_sppg, id_menu, id_sekolah) values
(1,'2026-06-01 07:00:00',500,'Dapur MBG Surabaya Barat',1,1),
(2,'2026-06-02 07:00:00',450,'Dapur MBG Surabaya Barat',2,2),
(3,'2026-06-03 07:00:00',600,'Dapur MBG Surabaya Timur',3,3),
(4,'2026-06-04 07:00:00',550,'Dapur MBG Surabaya Timur',1,4),
(5,'2026-06-05 07:00:00',700,'Dapur MBG Surabaya Selatan',2,5)
on conflict do nothing;

insert into laporan_keracunan (id_laporan, tanggal_laporan, jumlah_korban, deskripsi, id_sppg, detail_investigasi, dokumentasi, riwayat_audit) values
('LAP-001','2026-06-05',12,'Sebanyak 12 siswa mengalami mual dan muntah setelah makan siang menu Nasi Ayam Goreng.',1,'Diduga kontaminasi pada daging ayam dari PT Ayam Sehat. Sampel telah dikirim ke laboratorium.','foto_kejadian_sdn_ketintang1.jpg',
 '[{"tanggal":"2026-06-05","status":"Laporan diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-06","status":"Investigasi dimulai","petugas":"Tim Inspeksi Pangan"},{"tanggal":"2026-06-08","status":"Hasil lab: bakteri E.coli terdeteksi pada sampel ayam","petugas":"Lab Kesehatan Surabaya"}]'::jsonb),
('LAP-002','2026-06-07',5,'Lima siswa mengeluh sakit perut setelah mengkonsumsi Nasi Telur Balado. Gejala ringan, semua sudah pulih.',2,'Kemungkinan telur kurang matang saat proses memasak. Bukan dari bahan baku.',null,
 '[{"tanggal":"2026-06-07","status":"Laporan diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-08","status":"Investigasi selesai - masalah proses masak","petugas":"Tim Inspeksi Pangan"}]'::jsonb),
('LAP-003','2026-06-10',8,'Delapan siswa mengalami diare setelah makan Nasi Sayur Sehat. Diduga bayam tidak dicuci bersih.',3,'Pemeriksaan ke dapur MBG Surabaya Timur menunjukkan proses pencucian sayur tidak sesuai SOP.','laporan_inspeksi_dapur_timur.pdf',
 '[{"tanggal":"2026-06-10","status":"Laporan diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-11","status":"Inspeksi dapur dilakukan","petugas":"Tim Inspeksi Pangan"},{"tanggal":"2026-06-12","status":"Peringatan tertulis diberikan ke dapur","petugas":"Kepala Dinas Kesehatan"}]'::jsonb),
('LAP-004','2026-06-12',3,'Tiga siswa alergi setelah makan Nasi Ayam Goreng. Ternyata ada kandungan kacang pada bumbu.',4,'Bumbu dari PT Bumbu Nasional mengandung kacang tanah yang tidak tercantum di label. Supplier diberi peringatan.','label_bumbu_scan.jpg',
 '[{"tanggal":"2026-06-12","status":"Laporan diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-13","status":"Konfirmasi alergen dari lab","petugas":"Lab Kesehatan Surabaya"}]'::jsonb),
('LAP-005','2026-06-15',20,'Dua puluh siswa keracunan massal setelah makan Nasi Telur Balado. Gejala: mual, muntah, demam.',5,'Investigasi menunjukkan telur dari PT Telur Jaya batch terakhir terkontaminasi Salmonella. Seluruh stok ditarik.','hasil_lab_salmonella.pdf',
 '[{"tanggal":"2026-06-15","status":"Laporan darurat diterima","petugas":"Admin MBG Surabaya"},{"tanggal":"2026-06-15","status":"Tim medis dikirim ke sekolah","petugas":"Puskesmas Surabaya Selatan"},{"tanggal":"2026-06-16","status":"Sampel telur dikirim ke lab","petugas":"Tim Inspeksi Pangan"},{"tanggal":"2026-06-17","status":"Salmonella terkonfirmasi - stok telur ditarik","petugas":"Kepala Dinas Kesehatan"}]'::jsonb)
on conflict do nothing;

-- Keep the serial sequences in sync after explicit-id inserts
select setval(pg_get_serial_sequence('supplier','id_supplier'),       (select max(id_supplier) from supplier));
select setval(pg_get_serial_sequence('bahan_makanan','id_bahan'),     (select max(id_bahan) from bahan_makanan));
select setval(pg_get_serial_sequence('menu','id_menu'),               (select max(id_menu) from menu));
select setval(pg_get_serial_sequence('sekolah','id_sekolah'),         (select max(id_sekolah) from sekolah));
select setval(pg_get_serial_sequence('sppg','id_sppg'),               (select max(id_sppg) from sppg));
select setval(pg_get_serial_sequence('laporan_keracunan','id'),       (select max(id) from laporan_keracunan));

-- ---------- TRIGGERS (created AFTER seed so past-dated seed rows are allowed) ----------
create or replace function fn_validasi_detail_menu() returns trigger language plpgsql as $$
declare v_exp date; v_nama varchar(100);
begin
    if new.jumlah_bahan is null or new.jumlah_bahan <= 0 then
        raise exception 'DITOLAK: jumlah_bahan harus lebih dari 0.';
    end if;
    select tanggal_kadaluarsa, nama_bahan into v_exp, v_nama
        from bahan_makanan where id_bahan = new.id_bahan;
    if v_exp is not null and v_exp < current_date then
        raise exception 'DITOLAK: Bahan "%" sudah kadaluarsa sejak %. Tidak boleh digunakan.', v_nama, v_exp;
    end if;
    return new;
end $$;
drop trigger if exists trg_validasi_detail_menu on detail_menu;
create trigger trg_validasi_detail_menu before insert on detail_menu
    for each row execute function fn_validasi_detail_menu();

create or replace function fn_validasi_sppg_insert() returns trigger language plpgsql as $$
begin
    if new.jumlah_porsi is null or new.jumlah_porsi <= 0 then
        raise exception 'DITOLAK: jumlah_porsi harus lebih dari 0.';
    end if;
    if new.tanggal_distribusi is not null and new.tanggal_distribusi::date < current_date then
        raise exception 'DITOLAK: tanggal_distribusi tidak boleh di masa lalu.';
    end if;
    return new;
end $$;
drop trigger if exists trg_validasi_sppg_insert on sppg;
create trigger trg_validasi_sppg_insert before insert on sppg
    for each row execute function fn_validasi_sppg_insert();

create or replace function fn_validasi_sppg_update() returns trigger language plpgsql as $$
begin
    if new.jumlah_porsi is null or new.jumlah_porsi <= 0 then
        raise exception 'DITOLAK: jumlah_porsi harus lebih dari 0.';
    end if;
    return new;
end $$;
drop trigger if exists trg_validasi_sppg_update on sppg;
create trigger trg_validasi_sppg_update before update on sppg
    for each row execute function fn_validasi_sppg_update();

create or replace function fn_log_status_distribusi() returns trigger language plpgsql as $$
begin
    if old.status_distribusi is distinct from new.status_distribusi then
        insert into log_distribusi (id_sppg, status_lama, status_baru)
        values (new.id_sppg, old.status_distribusi, new.status_distribusi);
    end if;
    return new;
end $$;
drop trigger if exists trg_log_status_distribusi on sppg;
create trigger trg_log_status_distribusi after update on sppg
    for each row execute function fn_log_status_distribusi();

-- ---------- VIEWS ----------
create or replace view v_traceability_lengkap as
select sp.id_sppg, sp.tanggal_distribusi, sp.jumlah_porsi, sp.status_distribusi,
       sk.nama_sekolah, sk.alamat as alamat_sekolah,
       m.nama_menu, m.tanggal_produksi,
       bm.nama_bahan, bm.tanggal_kadaluarsa, dm.jumlah_bahan,
       s.nama_supplier, s.no_telp as telp_supplier
from sppg sp
    join sekolah sk       on sp.id_sekolah = sk.id_sekolah
    join menu m           on sp.id_menu    = m.id_menu
    join detail_menu dm   on m.id_menu     = dm.id_menu
    join bahan_makanan bm on dm.id_bahan   = bm.id_bahan
    join supplier s       on bm.id_supplier = s.id_supplier;

create or replace view v_bahan_hampir_kadaluarsa as
select bm.id_bahan, bm.nama_bahan, bm.tanggal_kadaluarsa, s.nama_supplier, s.no_telp,
       case when bm.tanggal_kadaluarsa < current_date then 'SUDAH KADALUARSA'
            when bm.tanggal_kadaluarsa <= current_date + interval '7 day' then 'HAMPIR KADALUARSA'
       end as status_kadaluarsa
from bahan_makanan bm
    join supplier s on bm.id_supplier = s.id_supplier
where bm.tanggal_kadaluarsa <= current_date + interval '7 day';

-- ---------- TRACEABILITY (replaces Laravel TraceabilityService) ----------
create or replace function trace_from_report(p_id text)
returns json language plpgsql security definer as $$
declare v_lap laporan_keracunan; v_result json;
begin
    select * into v_lap from laporan_keracunan
        where id_laporan = p_id or id::text = p_id limit 1;
    if not found then raise exception 'Laporan keracunan tidak ditemukan'; end if;

    select json_build_object(
        'laporan', to_json(v_lap),
        'trace', (
            select json_build_object(
                'id_sppg', s.id_sppg,
                'tanggal_distribusi', s.tanggal_distribusi,
                'jumlah_porsi', s.jumlah_porsi,
                'sekolah', (select to_json(sk) from sekolah sk where sk.id_sekolah = s.id_sekolah),
                'menu', (
                    select json_build_object(
                        'id_menu', m.id_menu, 'nama_menu', m.nama_menu,
                        'bahan_makanan', (
                            select coalesce(json_agg(json_build_object(
                                'id_bahan', b.id_bahan, 'nama_bahan', b.nama_bahan,
                                'jumlah_bahan', dm.jumlah_bahan,
                                'supplier', (select to_json(sp2) from supplier sp2 where sp2.id_supplier = b.id_supplier)
                            )), '[]'::json)
                            from detail_menu dm join bahan_makanan b on b.id_bahan = dm.id_bahan
                            where dm.id_menu = m.id_menu)
                    ) from menu m where m.id_menu = s.id_menu)
            ) from sppg s where s.id_sppg = v_lap.id_sppg)
    ) into v_result;
    return v_result;
end $$;

create or replace function trace_from_supplier(p_id int)
returns json language plpgsql security definer as $$
declare v_result json;
begin
    if not exists (select 1 from supplier where id_supplier = p_id) then
        raise exception 'Supplier tidak ditemukan';
    end if;
    select json_build_object(
        'supplier', (select to_json(s) from supplier s where s.id_supplier = p_id),
        'bahan_makanan', (select coalesce(json_agg(to_json(b)),'[]'::json) from bahan_makanan b where b.id_supplier = p_id),
        'terdampak_sppg_ids', (
            select coalesce(json_agg(distinct sp.id_sppg),'[]'::json)
            from sppg sp join detail_menu dm on dm.id_menu = sp.id_menu
                 join bahan_makanan b on b.id_bahan = dm.id_bahan
            where b.id_supplier = p_id),
        'laporan_keracunan', (
            select coalesce(json_agg(to_json(l)),'[]'::json) from laporan_keracunan l
            where l.id_sppg in (
                select distinct sp.id_sppg from sppg sp
                join detail_menu dm on dm.id_menu = sp.id_menu
                join bahan_makanan b on b.id_bahan = dm.id_bahan
                where b.id_supplier = p_id))
    ) into v_result;
    return v_result;
end $$;

-- ---------- ROW-LEVEL SECURITY (demo: anon has full access; no app auth) ----------
do $$
declare t text;
begin
    foreach t in array array['supplier','bahan_makanan','menu','detail_menu','sekolah','sppg','log_distribusi','laporan_keracunan']
    loop
        execute format('alter table %I enable row level security', t);
        execute format('drop policy if exists "demo_all" on %I', t);
        execute format('create policy "demo_all" on %I for all to anon, authenticated using (true) with check (true)', t);
    end loop;
end $$;

grant execute on function trace_from_report(text)    to anon, authenticated;
grant execute on function trace_from_supplier(int)   to anon, authenticated;
