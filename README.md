# Pustaka API

Aplikasi pembelajaran Web Service pertemuan 10. Laravel 12 menyediakan REST API dan penyimpanan data. Frontend memakai Bootstrap 5.3.8 serta JavaScript fetch.

Target repositori: https://github.com/feryHendrix-ui/JSONDecode

Paket awal tersedia sebagai ZIP. Unggahan otomatis pada sesi pembuatan ditolak GitHub dengan Resource not accessible by integration. Repository tujuan belum terisi oleh paket ini. Setelah izin tulis koneksi GitHub tersedia, source dapat diunggah tanpa mengganti kode aplikasi.

## Yang dapat dicoba

- Membaca koleksi melalui API publik tanpa login.
- Mencari metadata buku dari Open Library.
- Menyimpan hasil pencarian ke database aplikasi sendiri.
- Menambah, membaca, mengubah, dan menghapus koleksi.
- Mencari buku, memilih kategori, dan berpindah halaman.
- Login pengelola menggunakan token Sanctum. Operasi tulis memerlukan akun admin dan ability books:write.

Open Library Search API menyediakan pencarian metadata. Tombol simpan, ubah, dan hapus pada aplikasi ini mengubah database Laravel, bukan katalog Open Library. Aplikasi tidak menggunakan layanan CRUD palsu yang mengabaikan perubahan.

## Menjalankan di laptop Windows dengan Laragon

Gunakan PHP 8.3 atau versi lebih baru yang kompatibel dengan composer.lock. Aktifkan pdo_sqlite, mbstring, openssl, dan extension standar Laravel. Composer dan Git perlu tersedia pada terminal.

Untuk paket ZIP, ekstrak proyek, buka terminal Laragon pada folder yang berisi artisan, lalu mulai dari composer install di bawah ini. Jika repository sudah terisi, Anda juga dapat memakai git clone dan cd terlebih dahulu:

    git clone https://github.com/feryHendrix-ui/JSONDecode.git
    cd JSONDecode
    composer install
    copy .env.example .env
    php artisan key:generate
    php artisan migrate --seed
    php artisan app:create-admin
    php artisan serve

Pada Linux atau macOS, ganti perintah copy dengan:

    cp .env.example .env

Perintah app:create-admin meminta email dan kata sandi minimal 12 karakter. Kata sandi tidak ditampilkan. Gunakan akun tersebut pada tombol Masuk pengelola.

Buka http://127.0.0.1:8000. Tidak perlu npm install atau npm run build. Bootstrap sudah tersedia pada public/vendor/bootstrap.

Untuk mengunggah paket ZIP dari Windows, jalankan tools/publish-github.ps1 pada PowerShell setelah Git siap. Script memakai target repository di atas dan git push biasa, tanpa force. Git dapat meminta login ke akun pemilik. Script tidak menyimpan token. Jika PowerShell membatasi script, jalankan perintah Git pada bagian berikut secara manual.

    git init -b main
    git remote add origin https://github.com/feryHendrix-ui/JSONDecode.git
    git add .
    git commit -m "Add Laravel 12 public API teaching project"
    git push -u origin main

Jika origin sudah terpasang, periksa dengan git remote -v dan lanjutkan hanya bila tujuannya benar. Untuk repository baru milik mahasiswa, ganti URL origin dengan repository kelompok.

Delapan buku awal merupakan data latihan fiktif. Data pencarian Open Library berasal dari layanan publik secara langsung. Buku impor ditandai sumber Open Library.

## Memastikan data tetap tersimpan

Tambah sebuah buku, catat ID-nya, lalu hentikan server dengan Ctrl+C. Jalankan php artisan serve kembali. Buka detail ID yang sama. Database SQLite tersimpan pada database/database.sqlite.

Jangan menjalankan migrate:fresh pada database yang berisi pekerjaan Anda. Perintah tersebut memang menghapus dan membuat ulang tabel.

Seeder tidak berjalan otomatis pada setiap startup produksi. Data yang dihapus tidak dimunculkan kembali oleh proses startup.

## Endpoint

Base URL lokal: http://127.0.0.1:8000/api/v1

| Method | Path | Akses |
|---|---|---|
| GET | /books | Publik |
| GET | /books/{id} | Publik |
| GET | /stats | Publik |
| GET | /open-library?q=programming | Publik |
| POST | /auth/login | Email dan kata sandi |
| GET | /auth/me | Token valid |
| POST | /auth/logout | Token valid |
| POST | /books | Token admin + books:write |
| PATCH | /books/{id} | Token admin + books:write |
| DELETE | /books/{id} | Token admin + books:write |

Daftar buku menerima q, kategori, page, dan per_page. Nilai per_page 1 sampai 24.

Contoh baca:

    GET /api/v1/books?q=Laravel&kategori=pemrograman&per_page=8

Contoh body tambah:

    {
      "kode": "MHS-001",
      "judul": "Catatan Praktikum API",
      "penulis": "Kelompok Mahasiswa",
      "tahun": 2026,
      "kategori": "pemrograman",
      "stok": 2
    }

Header untuk menulis:

    Accept: application/json
    Content-Type: application/json
    Authorization: Bearer TOKEN_ANDA

Status: 200 untuk baca/ubah, 201 untuk tambah, 204 untuk hapus, 401 untuk autentikasi, 403 untuk izin, 404 untuk data yang tidak ditemukan, 422 untuk validasi, dan 503 ketika penyedia eksternal tidak dapat diakses.

## Peta kode

- app/Models/Book.php: model koleksi.
- app/Http/Requests/BookRequest.php: validasi tambah dan ubah.
- app/Http/Resources/BookResource.php: kontrak JSON.
- app/Http/Controllers/BookController.php: CRUD dan statistik.
- app/Services/OpenLibraryService.php: HTTP Client, normalisasi metadata, timeout, dan cache.
- app/Http/Controllers/AuthController.php: login dan pencabutan token.
- resources/views/catalog.blade.php: halaman Bootstrap.
- public/js/app.js: request fetch dan interaksi halaman.
- routes/api.php: endpoint publik dan endpoint tulis.
- postman/Pustaka_API.postman_collection.json: koleksi pengujian.
- docs/DEPLOYMENT.md: publikasi pada hosting PHP/Docker.
- docs/TUGAS_PERTEMUAN_10.md: tugas mahasiswa.

## Pengujian

    php artisan test

Tes mencakup CRUD nyata, validasi, pembatasan admin/abilities, pencarian/pagination, CORS, token login/logout, cache, dan kegagalan API pihak ketiga. Tes memakai SQLite in-memory dan HTTP fake untuk pengujian otomatis yang konsisten. Pengujian tersebut tidak mengirim perubahan ke Open Library.

Pencarian live tetap memakai Open Library. Hasil penyedia eksternal dapat berubah. Jika penyedia tidak tersedia, aplikasi menampilkan pesan 503 dan koleksi lokal tetap dapat dibaca.

## GitHub dan alamat aplikasi

GitHub menyimpan kode. GitHub Pages hanya menjalankan HTML, CSS, dan JavaScript statis. Laravel memerlukan hosting dengan PHP atau Docker.

Proyek sudah menyertakan Dockerfile, Caddyfile, entrypoint, dan render.yaml. URL publik belum dibuat hanya dengan mengunggah kode. Selesaikan koneksi hosting dan konfigurasi database mengikuti docs/DEPLOYMENT.md.

## Referensi

- https://laravel.com/docs/12.x/http-client
- https://laravel.com/docs/12.x/sanctum
- https://openlibrary.org/dev/docs/api/search
- https://getbootstrap.com/docs/5.3/getting-started/introduction/
