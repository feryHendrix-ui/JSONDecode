# Publikasi aplikasi Laravel

## Pilihan server

Aplikasi dapat berjalan pada hosting Laravel/PHP, server Docker, Render, atau Railway yang mendukung Docker. Akun hosting dan database harus Anda sediakan atau hubungkan terlebih dahulu. Repository GitHub bukan server PHP.

Untuk halaman publik, gunakan URL HTTPS dari penyedia hosting. Anda tidak perlu membeli IP statis khusus hanya agar mahasiswa dapat membuka aplikasi lewat browser.

## Render atau server Docker

1. Hubungkan GitHub dan pilih feryHendrix-ui/JSONDecode, branch main.
2. Pilih runtime Docker. Gunakan Dockerfile pada root repository.
3. Jika memakai Render Blueprint, berkas render.yaml sudah tersedia. Periksa rencana layanan dan ketentuan akun sebelum menyetujui pembuatan resource.
4. Siapkan PostgreSQL persisten. Gunakan database yang sudah Anda miliki atau layanan sesuai pilihan Anda.
5. Isi environment variables pada hosting, bukan pada kode GitHub:
   - APP_ENV=production
   - APP_DEBUG=false
   - APP_KEY: hasil php artisan key:generate --show pada proyek lokal.
   - APP_URL: URL HTTPS yang diberikan hosting.
   - DB_CONNECTION=pgsql
   - DB_URL: connection URL PostgreSQL.
   - CACHE_STORE=file
   - SESSION_DRIVER=file
   - QUEUE_CONNECTION=sync
   - ADMIN_EMAIL: email pengelola.
   - ADMIN_PASSWORD: kata sandi pengelola minimal 12 karakter.
   - ADMIN_NAME: nama pengelola.
6. Arahkan health check ke /up.
7. Jalankan deploy. Entry point menjalankan migration dan menyiapkan akun admin dari environment. Token, buku, dan akun tersimpan pada PostgreSQL.
8. Buka URL aplikasi. Uji GET /api/v1/books, login, tambah, ubah, hapus, dan refresh.
9. Tambahkan buku manual atau impor hasil Open Library. Data contoh lokal tidak otomatis diunggah.
10. Jika ingin data latihan awal, jalankan php artisan db:seed --class=DemoBookSeeder --force satu kali melalui console hosting jika paket hosting menyediakan console. Anda juga dapat mengisi koleksi melalui antarmuka aplikasi. Render Free tidak menyediakan shell interaktif.

Mengatur ADMIN_PASSWORD pada hosting juga menjadi sumber kata sandi saat container mulai. Perbarui nilainya pada hosting untuk rotasi kata sandi. Jangan membagikan nilai tersebut kepada mahasiswa yang hanya perlu membaca API.

## Penyimpanan persisten

SQLite cocok untuk laptop dan server yang mempunyai volume persisten. Jika memakai SQLite dalam container, arahkan DB_DATABASE ke volume data yang dipasang. Database pada filesystem container yang tidak persisten dapat hilang ketika redeploy. Untuk blueprint yang disediakan, gunakan PostgreSQL melalui DB_URL.

Seeder hanya dijalankan atas perintah Anda. Startup tidak mengisi ulang buku yang telah dihapus.

## Sinkronisasi perubahan

Push perubahan ke GitHub, lalu hosting menarik source terbaru melalui pengaturan auto-deploy. render.yaml mendeklarasikan pemicu deploy berdasarkan commit. Aktifkan koneksi repository pada akun hosting Anda sebelum fitur ini dapat bekerja.

Jangan menjalankan migrate:fresh pada database produksi. Perubahan skema berikutnya menggunakan migration baru.

## Setelah deploy

- Buka halaman dari laptop atau ponsel dengan jaringan berbeda.
- Buka URL /api/v1/books tanpa login. Hasil harus berupa JSON.
- Operasi tulis tanpa token harus menghasilkan 401.
- Login dengan akun pengelola dan uji CRUD.
- Mulai ulang layanan, lalu periksa data yang tadi dibuat.
- Gunakan URL API HTTPS pada latihan mahasiswa.
