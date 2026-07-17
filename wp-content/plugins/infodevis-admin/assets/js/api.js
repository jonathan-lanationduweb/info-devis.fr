/**
 * Client REST — cookies WordPress + nonce X-WP-Nonce.
 */

import { toast } from './ui.js';

const CFG = window.IDA;

async function request(method, path, body) {
  const url = path.startsWith('http') ? path : CFG.restRoot + path;
  const options = {
    method,
    credentials: 'same-origin',
    headers: { 'X-WP-Nonce': CFG.nonce },
  };
  if (body !== undefined) {
    options.headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify(body);
  }
  const res = await fetch(url, options);
  let data = null;
  try { data = await res.json(); } catch (e) { /* réponse vide */ }
  if (!res.ok) {
    const message = (data && data.message) || `Erreur ${res.status}`;
    throw new Error(message);
  }
  return data;
}

export const api = {
  get: (path) => request('GET', path),
  post: (path, body) => request('POST', path, body ?? {}),
  del: (path) => request('DELETE', path),
};

/** Variante avec toast d'erreur automatique. */
export async function tryApi(promise, errorPrefix = 'Erreur') {
  try {
    return await promise;
  } catch (e) {
    toast(`${errorPrefix} : ${e.message}`, 'error');
    throw e;
  }
}

/** Upload d'un fichier vers la médiathèque WordPress (wp/v2/media). */
export async function uploadMedia(file) {
  const form = new FormData();
  form.append('file', file);
  const res = await fetch(CFG.wpRest + 'wp/v2/media', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-WP-Nonce': CFG.nonce },
    body: form,
  });
  const data = await res.json();
  if (!res.ok) {
    throw new Error(data.message || 'Échec de l\'envoi');
  }
  return { id: data.id, url: data.source_url };
}

/** Petit debounce utilitaire. */
export function debounce(fn, delay = 250) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}

/** Échappement HTML. */
export function esc(value) {
  return String(value ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[c]));
}
