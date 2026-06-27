// Drop-in replacement for the old axios client. Keeps the same
// api.get/post/put/delete(path, body) interface the pages already use, but
// routes everything to Supabase (PostgREST + RPC) instead of the Laravel API.
// This means none of the page components needed to change.
import { supabase } from './supabase';

type Resp<T = any> = { data: T };

function apiError(message: string, status = 400) {
  const err: any = new Error(message);
  err.response = { data: { message }, status };
  return err;
}

// REST resource -> Postgres table + primary key + select (with FK embeds)
const RES: Record<string, { table: string; pk: string; select: string }> = {
  'suppliers':     { table: 'supplier',      pk: 'id_supplier', select: '*' },
  'bahan-makanan': { table: 'bahan_makanan', pk: 'id_bahan',    select: '*, supplier(*)' },
  'menu':          { table: 'menu',          pk: 'id_menu',     select: '*' },
  'sekolah':       { table: 'sekolah',       pk: 'id_sekolah',  select: '*' },
  'sppg':          { table: 'sppg',          pk: 'id_sppg',     select: '*, menu(*), sekolah(*)' },
};

// Friendly messages for FK-protected deletes (Postgres error code 23503)
const DELETE_MSG: Record<string, string> = {
  'suppliers':     'Supplier tidak bisa dihapus karena masih memiliki bahan makanan terkait.',
  'bahan-makanan': 'Bahan makanan tidak bisa dihapus karena masih digunakan dalam menu.',
  'menu':          'Menu tidak bisa dihapus karena masih dipakai pada detail menu atau distribusi (SPPG).',
  'sekolah':       'Sekolah tidak bisa dihapus karena masih memiliki data distribusi (SPPG).',
};

// Normalize Postgres ISO timestamps ("2026-06-01T07:00:00+00:00") to the
// "YYYY-MM-DD HH:MM:SS" shape the UI's date parsing expects.
function normTs(v: any) {
  return typeof v === 'string' && v.includes('T') ? v.replace('T', ' ').slice(0, 19) : v;
}
function normalizeRow(table: string, row: any) {
  if (!row) return row;
  if (table === 'sppg' && row.tanggal_distribusi) row.tanggal_distribusi = normTs(row.tanggal_distribusi);
  if (table === 'menu' && row.tanggal_produksi) row.tanggal_produksi = normTs(row.tanggal_produksi);
  return row;
}

function seg(path: string): string[] {
  return path.replace(/^\/+/, '').split('?')[0].split('/');
}

async function get(path: string): Promise<Resp> {
  const p = seg(path);

  // /trace/report/{id} | /trace/supplier/{id}
  if (p[0] === 'trace') {
    const fn = p[1] === 'report' ? 'trace_from_report' : 'trace_from_supplier';
    const arg = p[1] === 'report' ? { p_id: p[2] } : { p_id: Number(p[2]) };
    const { data, error } = await supabase.rpc(fn, arg);
    if (error) throw apiError(error.message, 404);
    return { data };
  }

  // /laporan-keracunan (with sppg -> sekolah, like the old controller)
  if (p[0] === 'laporan-keracunan') {
    const { data, error } = await supabase
      .from('laporan_keracunan')
      .select('*, sppg(*, sekolah(*))')
      .order('id', { ascending: true });
    if (error) throw apiError(error.message);
    const rows = (data || []).map((r: any) => ({ ...r, sekolah: r.sppg?.sekolah ?? null }));
    return { data: rows };
  }

  const res = RES[p[0]];
  if (res && p.length === 1) {
    const { data, error } = await supabase.from(res.table).select(res.select).order(res.pk, { ascending: true });
    if (error) throw apiError(error.message);
    return { data: (data || []).map((r: any) => normalizeRow(res.table, r)) };
  }
  if (res && p.length === 2) {
    const { data, error } = await supabase.from(res.table).select(res.select).eq(res.pk, p[1]).single();
    if (error) throw apiError(error.message, 404);
    return { data: normalizeRow(res.table, data) };
  }
  throw apiError('Unknown endpoint: ' + path, 404);
}

async function post(path: string, body: any): Promise<Resp> {
  const p = seg(path);

  // /menu/{id}/bahan -> upsert detail_menu
  if (p[0] === 'menu' && p[2] === 'bahan') {
    const row = { id_menu: Number(p[1]), id_bahan: body.id_bahan, jumlah_bahan: body.jumlah_bahan };
    const { data, error } = await supabase.from('detail_menu').upsert(row, { onConflict: 'id_menu,id_bahan' }).select().single();
    if (error) throw apiError(error.message);
    return { data };
  }

  if (p[0] === 'laporan-keracunan') {
    const { data, error } = await supabase.from('laporan_keracunan').insert(body).select().single();
    if (error) throw apiError(error.message);
    return { data };
  }

  const res = RES[p[0]];
  if (res && p.length === 1) {
    const { data, error } = await supabase.from(res.table).insert(body).select().single();
    if (error) throw apiError(error.message);
    return { data: normalizeRow(res.table, data) };
  }
  throw apiError('Unknown endpoint: ' + path, 404);
}

async function put(path: string, body: any): Promise<Resp> {
  const p = seg(path);
  const res = RES[p[0]];
  if (res && p.length === 2) {
    const { data, error } = await supabase.from(res.table).update(body).eq(res.pk, p[1]).select().single();
    if (error) throw apiError(error.message);
    return { data: normalizeRow(res.table, data) };
  }
  throw apiError('Unknown endpoint: ' + path, 404);
}

async function del(path: string): Promise<Resp> {
  const p = seg(path);
  const res = RES[p[0]];
  if (res && p.length === 2) {
    const { error } = await supabase.from(res.table).delete().eq(res.pk, p[1]);
    if (error) {
      if (error.code === '23503') throw apiError(DELETE_MSG[p[0]] || 'Data tidak bisa dihapus.', 409);
      throw apiError(error.message);
    }
    return { data: { message: 'Dihapus' } };
  }
  throw apiError('Unknown endpoint: ' + path, 404);
}

const api = { get, post, put, delete: del };
export default api;
