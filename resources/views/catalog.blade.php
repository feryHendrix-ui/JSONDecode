<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Katalog buku kampus. Temukan buku dari Open Library dan kelola koleksi melalui REST API Laravel.">
    <title>Pustaka API - Katalog Buku Kampus</title>
    <link rel="stylesheet" href="/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/css/app.css">
    <script src="/vendor/bootstrap/bootstrap.bundle.min.js" defer></script>
    <script src="/js/app.js" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Langsung ke konten</a>
<header class="site-header">
    <div class="container d-flex flex-wrap align-items-center gap-3 py-3">
        <a href="/" class="brand text-decoration-none"><span class="brand-mark" aria-hidden="true">P.</span> Pustaka API</a>
        <span class="course-label d-none d-lg-inline">Laboratorium Web Service</span>
        <nav class="nav main-nav ms-md-auto" aria-label="Menu utama">
            <button class="nav-link active" data-view="collection" aria-pressed="true">Koleksi</button>
            <button class="nav-link" data-view="explore" aria-pressed="false">Jelajah API</button>
            <button class="nav-link" data-view="docs" aria-pressed="false">Panduan API</button>
        </nav>
        <button class="btn btn-outline-primary btn-sm" id="loginButton">Masuk pengelola</button>
        <div id="accountArea" class="align-items-center gap-2" hidden><span id="accountName"></span><button class="btn btn-light btn-sm" id="logoutButton">Keluar</button></div>
    </div>
</header>
<main id="main" class="container">
    <section id="view-collection" class="app-view">
        <div class="hero row align-items-end gy-4">
            <div class="col-lg-7">
                <p class="eyebrow">KOLEKSI KAMPUS</p>
                <h1>Katalog bacaan kampus.</h1>
                <p class="hero-copy">Cari buku, jelajahi referensi publik, dan susun koleksi yang Anda butuhkan.</p>
                <div class="d-flex gap-4 mt-4 stats" aria-label="Ringkasan koleksi">
                    <div><strong id="totalBooks">-</strong><span>judul tersimpan</span></div>
                    <div><strong id="totalStock">-</strong><span>eksemplar</span></div>
                    <div><strong id="totalExternal">-</strong><span>dari Open Library</span></div>
                </div>
            </div>
            <div class="col-lg-5 text-lg-end">
                <button class="btn btn-primary" id="addBookButton" data-editor hidden>+ Tambah buku</button>
                <button class="btn btn-soft ms-2" id="exploreShortcut">Jelajahi buku publik</button>
            </div>
        </div>
        <div class="section-rule"></div>
        <form id="collectionSearch" class="row g-3 align-items-end search-toolbar">
            <div class="col-md-7"><label class="form-label" for="searchInput">Cari dalam koleksi</label><input class="form-control" id="searchInput" type="search" placeholder="Judul, penulis, atau kode buku" maxlength="100"></div>
            <div class="col-md-3"><label class="form-label" for="categoryFilter">Kategori</label><select class="form-select" id="categoryFilter"><option value="">Semua kategori</option><option value="pemrograman">Pemrograman</option><option value="jaringan">Jaringan</option><option value="data-sains">Data Sains</option><option value="umum">Umum</option></select></div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary">Cari buku</button></div>
        </form>
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3"><h2 class="section-title">Koleksi tersimpan</h2><a class="json-link" id="publicJsonLink" href="/api/v1/books" target="_blank" rel="noopener">Buka JSON</a></div>
        <div id="collectionMessage" class="status-message" role="status" aria-live="polite"></div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="bookGrid"></div>
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap py-4">
            <p class="text-muted mb-0 small" id="pageInfo"></p>
            <nav class="d-flex gap-2 align-items-center" aria-label="Halaman koleksi"><button id="prevPage" class="btn btn-outline-primary btn-sm" disabled>Sebelumnya</button><span id="pageNumber" class="small"></span><button id="nextPage" class="btn btn-outline-primary btn-sm" disabled>Berikutnya</button></nav>
        </div>
    </section>
    <section id="view-explore" class="app-view" hidden>
        <div class="hero"><p class="eyebrow">REFERENSI DARI API PUBLIK</p><h1>Temukan bacaan baru.</h1><p class="hero-copy">Telusuri metadata buku dari Open Library. Simpan pilihan Anda ke koleksi kampus.</p></div>
        <form id="externalSearch" class="row g-3 mb-4">
            <div class="col-md-10"><label for="externalQuery" class="form-label">Topik, judul, atau penulis</label><input id="externalQuery" class="form-control" type="search" placeholder="Contoh: programming, computer networks, data mining" required minlength="2" maxlength="100"></div>
            <div class="col-md-2 d-grid align-self-end"><button class="btn btn-primary" id="externalSearchButton">Cari di API</button></div>
        </form>
        <p class="small text-muted">Hasil pencarian berasal dari Open Library. Perubahan koleksi tersimpan pada database aplikasi ini.</p>
        <div id="externalMessage" class="status-message" role="status" aria-live="polite">Masukkan kata kunci untuk mulai mencari.</div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-5" id="externalGrid"></div>
    </section>
    <section id="view-docs" class="app-view pb-5" hidden>
        <div class="hero"><p class="eyebrow">KONTRAK DATA</p><h1>API yang dapat Anda pakai.</h1><p class="hero-copy">Endpoint baca terbuka untuk publik. Tambah, ubah, dan hapus data memerlukan token akun pengelola.</p></div>
        <label class="form-label" for="apiAddress">Alamat API pada server ini</label>
        <div class="input-group mb-4"><input class="form-control font-monospace" id="apiAddress" readonly><button class="btn btn-outline-primary" id="copyApiButton">Salin URL</button><a class="btn btn-primary" href="/api/v1/books" target="_blank" rel="noopener">Buka JSON</a></div>
        <div class="table-responsive"><table class="table api-table"><thead><tr><th>Method</th><th>Endpoint</th><th>Akses</th><th>Kegunaan</th></tr></thead><tbody>
            <tr><td>GET</td><td>/api/v1/books</td><td>Publik</td><td>Daftar, pencarian, filter, dan pagination</td></tr>
            <tr><td>GET</td><td>/api/v1/books/{id}</td><td>Publik</td><td>Detail satu buku</td></tr>
            <tr><td>GET</td><td>/api/v1/open-library?q=programming</td><td>Publik</td><td>Pencarian metadata Open Library</td></tr>
            <tr><td>POST</td><td>/api/v1/auth/login</td><td>Kredensial</td><td>Mendapatkan Bearer token</td></tr>
            <tr><td>POST</td><td>/api/v1/books</td><td>Pengelola</td><td>Menyimpan buku</td></tr>
            <tr><td>PATCH</td><td>/api/v1/books/{id}</td><td>Pengelola</td><td>Mengubah buku</td></tr>
            <tr><td>DELETE</td><td>/api/v1/books/{id}</td><td>Pengelola</td><td>Menghapus buku</td></tr>
            <tr><td>POST</td><td>/api/v1/auth/logout</td><td>Token valid</td><td>Mencabut token saat ini</td></tr>
        </tbody></table></div>
        <h2 class="h5 mt-4">Contoh membaca API melalui JavaScript</h2>
        <pre class="code-example"><code id="fetchExample"></code></pre>
        <p class="small text-muted">Parameter koleksi: q, kategori, page, per_page. Token dikirim melalui header Authorization: Bearer TOKEN_ANDA. CORS mengizinkan akses API lintas origin sesuai konfigurasi server.</p>
    </section>
</main>
<footer class="site-footer"><div class="container d-flex flex-wrap justify-content-between gap-2 py-4"><span>Pustaka API. Praktikum Web Service.</span><a href="https://openlibrary.org/dev/docs/api/search" target="_blank" rel="noopener noreferrer">Sumber pencarian: Open Library</a></div></footer>

<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form id="loginForm"><div class="modal-header"><h2 class="modal-title fs-5" id="loginTitle">Masuk sebagai pengelola</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body"><p class="text-muted">Gunakan akun pengelola untuk menyimpan dan mengubah koleksi.</p><div id="loginError" class="alert alert-danger" role="alert" hidden></div>
    <div class="mb-3"><label class="form-label" for="loginEmail">Email</label><input class="form-control" id="loginEmail" type="email" autocomplete="username" required></div>
    <div><label class="form-label" for="loginPassword">Kata sandi</label><input class="form-control" id="loginPassword" type="password" autocomplete="current-password" required></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" id="loginSubmit">Masuk</button></div></form>
</div></div></div>

<div class="modal fade" id="bookModal" tabindex="-1" aria-labelledby="bookFormTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
    <form id="bookForm"><div class="modal-header"><div><p class="eyebrow mb-1">KOLEKSI KAMPUS</p><h2 class="modal-title fs-4" id="bookFormTitle">Tambah buku</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body"><div id="bookError" class="alert alert-danger" role="alert" hidden></div><input type="hidden" name="id"><input type="hidden" name="open_library_key"><input type="hidden" name="cover_id">
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="bookCode">Kode buku</label><input class="form-control" id="bookCode" name="kode" required maxlength="24"></div>
        <div class="col-md-8"><label class="form-label" for="bookTitle">Judul</label><input class="form-control" id="bookTitle" name="judul" required maxlength="250"></div>
        <div class="col-md-8"><label class="form-label" for="bookAuthor">Penulis</label><input class="form-control" id="bookAuthor" name="penulis" required maxlength="200"></div>
        <div class="col-md-4"><label class="form-label" for="bookYear">Tahun terbit</label><input class="form-control" id="bookYear" name="tahun" type="number" min="1" max="{{ date('Y') + 1 }}" placeholder="Opsional"></div>
        <div class="col-md-8"><label class="form-label" for="bookCategory">Kategori</label><select class="form-select" id="bookCategory" name="kategori" required><option value="pemrograman">Pemrograman</option><option value="jaringan">Jaringan</option><option value="data-sains">Data Sains</option><option value="umum">Umum</option></select></div>
        <div class="col-md-4"><label class="form-label" for="bookStock">Stok</label><input class="form-control" id="bookStock" name="stok" type="number" min="0" max="99999" value="1" required></div>
    </div><p class="small text-muted mt-3 mb-0" id="bookSourceInfo"></p></div>
    <div class="modal-footer justify-content-between"><button type="button" class="btn btn-outline-danger" id="deleteBookButton" hidden>Hapus buku</button><div class="ms-auto"><button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" id="saveBookButton">Simpan buku</button></div></div></form>
</div></div></div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="detailTitle">Rincian buku</h2><button class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body" id="detailBody"></div></div></div></div>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="deleteTitle">Hapus dari koleksi?</h2><button class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><p id="deleteDescription"></p><div id="deleteError" class="alert alert-danger" role="alert" hidden></div></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" id="confirmDeleteButton">Ya, hapus buku</button></div></div></div></div>
<div class="toast-container position-fixed bottom-0 end-0 p-3"><div id="notification" class="toast" role="status" aria-live="polite" aria-atomic="true"><div class="toast-body d-flex justify-content-between gap-3"><span id="notificationText"></span><button class="btn-close" data-bs-dismiss="toast" aria-label="Tutup pemberitahuan"></button></div></div></div>
</body>
</html>
