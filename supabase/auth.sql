-- =====================================================
-- MBG Traceability — simple custom auth (users table + RPC)
-- Run in the Supabase SQL Editor. Safe to re-run.
-- Passwords are bcrypt-hashed; the table is NOT exposed to anon — login and
-- register go only through the SECURITY DEFINER functions below.
-- =====================================================

create extension if not exists pgcrypto with schema extensions;

create table if not exists users (
    id         serial primary key,
    email      text unique not null,
    password   text not null,          -- bcrypt hash, never plaintext
    created_at timestamptz default now()
);

-- RLS on, no policies -> anon/authenticated cannot read or write the table
-- directly. All access is via the RPCs (which run as owner, bypassing RLS).
alter table users enable row level security;

-- ---------- Register ----------
create or replace function register_user(p_email text, p_password text)
returns json
language plpgsql security definer
set search_path = public, extensions
as $$
declare v_id int;
begin
    if p_email is null or p_email = '' or p_password is null or length(p_password) < 6 then
        raise exception 'Email wajib diisi dan password minimal 6 karakter';
    end if;
    if exists (select 1 from users where lower(email) = lower(p_email)) then
        raise exception 'Email sudah terdaftar';
    end if;
    insert into users (email, password)
        values (lower(p_email), crypt(p_password, gen_salt('bf')))
        returning id into v_id;
    return json_build_object('id', v_id, 'email', lower(p_email));
end $$;

-- ---------- Login ----------
create or replace function login_user(p_email text, p_password text)
returns json
language plpgsql security definer
set search_path = public, extensions
as $$
declare v_user users;
begin
    select * into v_user from users
        where lower(email) = lower(p_email)
          and password = crypt(p_password, password);
    if not found then
        return null;            -- invalid credentials
    end if;
    return json_build_object('id', v_user.id, 'email', v_user.email);
end $$;

grant execute on function register_user(text, text) to anon, authenticated;
grant execute on function login_user(text, text)    to anon, authenticated;

-- ---------- Seed demo accounts (password = "password") ----------
do $$ begin perform register_user('admin@pangan.com', 'password'); exception when others then null; end $$;
do $$ begin perform register_user('admin@sekolah.id', 'password'); exception when others then null; end $$;
