
'use strict';

/** Ambil elemen tunggal, null-safe. */
const $  = (sel, ctx = document) => ctx.querySelector(sel);
/** Ambil semua elemen, selalu array. */
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/** Format angka ke Rupiah. */
function formatRupiah(num) {
    return 'Rp ' + Number(num).toLocaleString('id-ID');
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}


/** Simpan / baca / hapus dari localStorage dengan try-catch. */
const Store = {
    get:    (k)    => { try { return localStorage.getItem(k); }    catch { return null; } },
    set:    (k, v) => { try { localStorage.setItem(k, v); }        catch {} },
    remove: (k)    => { try { localStorage.removeItem(k); }        catch {} },
};