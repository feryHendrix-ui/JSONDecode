'use strict';

const $ = id => document.getElementById(id);
const state = { token: sessionStorage.getItem('pustaka_token'), user: null, page: 1, last: 1, external: [], pendingImport: null };
const ui = {};
let collectionVersion = 0;
const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const rupiahFreeNumber = value => new Intl.NumberFormat('id-ID').format(value || 0);
const categories = {pemrograman:'Pemrograman',jaringan:'Jaringan','data-sains':'Data Sains',umum:'Umum'};

async function api(path, options = {}) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 28000);
    try {
        const headers = {Accept:'application/json', ...(options.headers || {})};
        if (options.body) headers['Content-Type'] = 'application/json';
        if (state.token) headers.Authorization = 'Bearer ' + state.token;
        const response = await fetch('/api/v1' + path, {...options, headers, signal:controller.signal});
        const body = response.status === 204 ? null : await response.json().catch(() => ({}));
        if (!response.ok) {
            if (response.status === 401 && path !== '/auth/login') clearAccount();
            const fields = body.errors ? Object.values(body.errors).flat().join(' ') : '';
            const err = new Error(fields || body.message || 'Permintaan belum berhasil. Silakan coba lagi.');
            err.status = response.status;
            throw err;
        }
        return body;
    } catch (error) {
        if (error.name === 'AbortError') throw new Error('Permintaan terlalu lama. Silakan coba lagi.');
        throw error;
    } finally { clearTimeout(timeout); }
}

function notify(message) { $('notificationText').textContent = message; ui.toast.show(); }
function showError(id, error) { $(id).textContent = error.message || String(error); $(id).hidden = false; }
function clearAccount() { state.token = null; state.user = null; sessionStorage.removeItem('pustaka_token'); updateAccount(); }
function updateAccount() {
    $('loginButton').hidden = !!state.user;
    $('accountArea').hidden = !state.user;
    $('accountArea').classList.toggle('d-flex', !!state.user);
    $('accountName').textContent = state.user?.name || '';
    document.querySelectorAll('[data-editor]').forEach(el => { el.hidden = !state.user?.is_admin; });
}
function setView(name) {
    document.querySelectorAll('.app-view').forEach(el => { el.hidden = el.id !== 'view-' + name; });
    document.querySelectorAll('[data-view]').forEach(el => {
        const selected = el.dataset.view === name;
        el.classList.toggle('active', selected); el.setAttribute('aria-pressed', String(selected));
    });
    if (name === 'explore') $('externalQuery').focus();
}
function bindCovers(parent) {
    parent.querySelectorAll('.book-cover img').forEach(img => img.addEventListener('error', () => { img.hidden = true; }, {once:true}));
}
function cover(book, i = 0) {
    return '<div class="book-cover tone-' + (i % 4) + '"><span aria-hidden="true">' +
        escapeHtml((book.judul || 'B').slice(0,1)) + '</span>' +
        (book.cover_url ? '<img loading="lazy" src="' + escapeHtml(book.cover_url) + '" alt="Sampul ' + escapeHtml(book.judul) + '">' : '') + '</div>';
}
function bookCard(book, i, external = false) {
    const label = external ? 'Open Library' : (book.kategori_label || categories[book.kategori] || 'Umum');
    const actions = external
        ? '<button class="btn btn-outline-primary" data-import="' + i + '">Simpan ke koleksi</button>'
        : '<span class="book-stock">Stok ' + escapeHtml(book.stok) + '</span><button class="btn btn-light" data-detail="' + book.id + '">Rincian</button>' +
            '<button class="btn btn-outline-primary" data-edit="' + book.id + '" data-editor' + (state.user?.is_admin ? '' : ' hidden') + '>Ubah</button>';
    return '<div class="col"><article class="book-card"><div class="book-inner">' + cover(book,i) +
        '<div class="book-copy"><p class="book-category">' + escapeHtml(label) + '</p><h3 class="book-title">' + escapeHtml(book.judul) +
        '</h3><p class="book-author">' + escapeHtml(book.penulis) + '</p><div class="book-year">' + escapeHtml(book.tahun || 'Tahun tidak tercantum') +
        '</div></div></div><div class="book-bottom">' + actions + '</div>' +
        (!external ? '<div class="source-caption"><span class="book-code">' + escapeHtml(book.kode) + '</span> · ' + escapeHtml(book.sumber) + '</div>' : '') +
        '</article></div>';
}
async function loadStats() {
    try { const {data} = await api('/stats'); $('totalBooks').textContent = rupiahFreeNumber(data.total_buku); $('totalStock').textContent = rupiahFreeNumber(data.total_stok); $('totalExternal').textContent = rupiahFreeNumber(data.dari_api_publik); }
    catch { $('totalBooks').textContent = '-'; $('totalStock').textContent = '-'; $('totalExternal').textContent = '-'; }
}
async function loadBooks() {
    const version = ++collectionVersion;
    $('collectionMessage').textContent = 'Memuat koleksi...'; $('bookGrid').innerHTML = '';
    const params = new URLSearchParams({page:state.page,per_page:8});
    if ($('searchInput').value.trim()) params.set('q',$('searchInput').value.trim());
    if ($('categoryFilter').value) params.set('kategori',$('categoryFilter').value);
    try {
        const result = await api('/books?' + params);
        if (version !== collectionVersion) return;
        if (!result.data.length && result.meta.current_page > result.meta.last_page) { state.page = result.meta.last_page; return loadBooks(); }
        state.page = result.meta.current_page; state.last = result.meta.last_page;
        $('collectionMessage').textContent = '';
        $('bookGrid').innerHTML = result.data.length ? result.data.map((b,i) => bookCard(b,i)).join('') :
            '<div class="col-12 w-100"><div class="empty-state"><h3>Belum ada buku yang cocok.</h3><p>Coba kata kunci lain atau tambahkan buku ke koleksi.</p></div></div>';
        $('pageInfo').textContent = result.meta.total ? 'Menampilkan ' + result.meta.from + '-' + result.meta.to + ' dari ' + rupiahFreeNumber(result.meta.total) + ' buku' : '0 buku';
        $('pageNumber').textContent = 'Halaman ' + state.page + ' / ' + state.last;
        $('prevPage').disabled = state.page <= 1; $('nextPage').disabled = state.page >= state.last;
        bindCovers($('bookGrid'));
    } catch (error) { if (version === collectionVersion) { $('collectionMessage').textContent = error.message; $('pageInfo').textContent = ''; } }
}
function openBookForm(book = null, imported = false) {
    if (!state.user?.is_admin) { if(imported) state.pendingImport = book; ui.login.show(); return; }
    const form = $('bookForm'); form.reset(); $('bookError').hidden = true;
    form.elements.id.value = !imported && book?.id ? book.id : '';
    form.elements.kode.value = book?.kode || 'BKU-' + Date.now().toString(36).slice(-6).toUpperCase();
    for (const field of ['judul','penulis','tahun','open_library_key','cover_id']) form.elements[field].value = book?.[field] ?? '';
    form.elements.kategori.value = book?.kategori || 'umum';
    form.elements.stok.value = book?.stok ?? 1;
    $('bookFormTitle').textContent = form.elements.id.value ? 'Ubah buku' : (imported ? 'Simpan dari Open Library' : 'Tambah buku');
    $('deleteBookButton').hidden = !form.elements.id.value;
    $('bookSourceInfo').textContent = book?.open_library_key ? 'Metadata berasal dari Open Library. Buku ini disimpan ke koleksi aplikasi Anda.' : 'Data manual untuk koleksi kampus.';
    ui.book.show();
}
async function showDetail(id) {
    try {
        const {data:b} = await api('/books/' + id);
        $('detailBody').innerHTML = '<h3 class="h4 mb-3">' + escapeHtml(b.judul) + '</h3><dl class="detail-list">' +
            [['Kode',b.kode],['Penulis',b.penulis],['Tahun',b.tahun || '-'],['Kategori',b.kategori_label],['Stok',b.stok],['Sumber',b.sumber]]
                .map(([k,v]) => '<dt>' + escapeHtml(k) + '</dt><dd>' + escapeHtml(v) + '</dd>').join('') + '</dl>';
        if (b.source_url && /^https:\/\/openlibrary\.org\/works\/OL[0-9]+W$/.test(b.source_url)) {
            const a = document.createElement('a'); a.href=b.source_url; a.target='_blank'; a.rel='noopener noreferrer'; a.textContent='Lihat sumber di Open Library'; a.className='json-link'; $('detailBody').append(a);
        }
        ui.detail.show();
    } catch (error) { notify(error.message); }
}
async function restoreAccount() {
    if (!state.token) return;
    try { const r=await api('/auth/me'); state.user=r.data; updateAccount(); }
    catch { clearAccount(); }
}

document.addEventListener('DOMContentLoaded', async () => {
    ui.login=bootstrap.Modal.getOrCreateInstance($('loginModal')); ui.book=bootstrap.Modal.getOrCreateInstance($('bookModal'));
    ui.detail=bootstrap.Modal.getOrCreateInstance($('detailModal')); ui.delete=bootstrap.Modal.getOrCreateInstance($('deleteModal'));
    ui.toast=bootstrap.Toast.getOrCreateInstance($('notification'),{delay:4500});
    $('apiAddress').value = new URL('/api/v1/books',location.origin).href;
    $('fetchExample').textContent = 'const response = await fetch("' + $('apiAddress').value + '");\nconst result = await response.json();\nconsole.log(result.data);';
    document.querySelectorAll('[data-view]').forEach(btn => btn.addEventListener('click',()=>setView(btn.dataset.view)));
    $('exploreShortcut').addEventListener('click',()=>setView('explore'));
    $('loginButton').addEventListener('click',()=>{ $('loginError').hidden=true; ui.login.show(); });
    $('loginModal').addEventListener('shown.bs.modal',()=>$('loginEmail').focus());
    $('addBookButton').addEventListener('click',()=>openBookForm());
    $('collectionSearch').addEventListener('submit',event=>{event.preventDefault();state.page=1;loadBooks();});
    $('categoryFilter').addEventListener('change',()=>{state.page=1;loadBooks();});
    $('prevPage').addEventListener('click',()=>{if(state.page>1){state.page--;loadBooks();}});
    $('nextPage').addEventListener('click',()=>{if(state.page<state.last){state.page++;loadBooks();}});
    $('copyApiButton').addEventListener('click',async()=>{try{await navigator.clipboard.writeText($('apiAddress').value);notify('URL API berhasil disalin.');}catch{$('apiAddress').select();notify('Pilih Salin atau tekan Ctrl+C untuk menyalin URL.');}});
    $('bookGrid').addEventListener('click',async event=>{
        const detail=event.target.closest('[data-detail]'),edit=event.target.closest('[data-edit]');
        if(detail)showDetail(detail.dataset.detail);
        if(edit){try{const r=await api('/books/'+edit.dataset.edit);openBookForm(r.data);}catch(error){notify(error.message);}}
    });
    $('externalGrid').addEventListener('click',event=>{
        const btn=event.target.closest('[data-import]');if(btn)openBookForm(state.external[Number(btn.dataset.import)],true);
    });
    $('externalSearch').addEventListener('submit',async event=>{
        event.preventDefault();$('externalSearchButton').disabled=true;$('externalSearchButton').textContent='Mencari...';$('externalGrid').innerHTML='';$('externalMessage').textContent='Menghubungi Open Library...';
        try{
            const r=await api('/open-library?q='+encodeURIComponent($('externalQuery').value.trim()));state.external=r.data;
            $('externalMessage').textContent=r.data.length ? 'Menampilkan '+r.data.length+' hasil dari '+rupiahFreeNumber(r.meta.total)+' buku yang cocok.' : 'Belum ada hasil. Coba kata kunci lain.';
            $('externalGrid').innerHTML=r.data.map((b,i)=>bookCard(b,i,true)).join('');bindCovers($('externalGrid'));
        }catch(error){$('externalMessage').textContent=error.message;}
        finally{$('externalSearchButton').disabled=false;$('externalSearchButton').textContent='Cari di API';}
    });
    $('loginForm').addEventListener('submit',async event=>{
        event.preventDefault();$('loginError').hidden=true;$('loginSubmit').disabled=true;
        try{
            const r=await api('/auth/login',{method:'POST',body:JSON.stringify({email:$('loginEmail').value,password:$('loginPassword').value})});
            state.token=r.access_token;state.user=r.user;sessionStorage.setItem('pustaka_token',state.token);updateAccount();$('loginPassword').value='';
            const pending=state.pendingImport;state.pendingImport=null;
            if(pending && state.user.is_admin)$('loginModal').addEventListener('hidden.bs.modal',()=>openBookForm(pending,true),{once:true});
            ui.login.hide();notify(state.user.is_admin?'Anda masuk sebagai pengelola.':'Akun ini memiliki akses baca.');
        }catch(error){showError('loginError',error);}
        finally{$('loginSubmit').disabled=false;}
    });
    $('logoutButton').addEventListener('click',async()=>{
        try{await api('/auth/logout',{method:'POST'});clearAccount();notify('Anda sudah keluar.');}
        catch(error){notify(error.message);}
    });
    $('bookForm').addEventListener('submit',async event=>{
        event.preventDefault();$('bookError').hidden=true;$('saveBookButton').disabled=true;
        const form=event.target,raw=Object.fromEntries(new FormData(form));
        const payload={kode:raw.kode,judul:raw.judul,penulis:raw.penulis,tahun:raw.tahun?Number(raw.tahun):null,kategori:raw.kategori,stok:Number(raw.stok),open_library_key:raw.open_library_key||null,cover_id:raw.cover_id?Number(raw.cover_id):null};
        try{await api('/books'+(raw.id?'/'+raw.id:''),{method:raw.id?'PATCH':'POST',body:JSON.stringify(payload)});ui.book.hide();setView('collection');state.page=1;await Promise.all([loadBooks(),loadStats()]);notify(raw.id?'Perubahan buku sudah disimpan.':'Buku sudah ditambahkan ke koleksi.');}
        catch(error){showError('bookError',error);}
        finally{$('saveBookButton').disabled=false;}
    });
    $('deleteBookButton').addEventListener('click',()=>{
        $('deleteDescription').textContent='Buku "'+$('bookTitle').value+'" akan dihapus dari koleksi aplikasi ini.';
        $('deleteError').hidden=true;
        $('bookModal').addEventListener('hidden.bs.modal',()=>ui.delete.show(),{once:true});ui.book.hide();
    });
    $('confirmDeleteButton').addEventListener('click',async()=>{
        $('confirmDeleteButton').disabled=true;
        try{await api('/books/'+$('bookForm').elements.id.value,{method:'DELETE'});ui.delete.hide();await Promise.all([loadBooks(),loadStats()]);notify('Buku sudah dihapus dari koleksi.');}
        catch(error){showError('deleteError',error);}
        finally{$('confirmDeleteButton').disabled=false;}
    });
    await restoreAccount();
    await Promise.all([loadStats(),loadBooks()]);
});
