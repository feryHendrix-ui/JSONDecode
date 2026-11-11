# Proyek Pertemuan 10: Katalog dari API Publik

Bangun aplikasi katalog buku memakai Laravel 12, Bootstrap 5, Open Library Search API, dan database sendiri. Aplikasi harus membaca API eksternal, menampilkan data di browser, lalu menyimpan pilihan pengguna ke koleksi yang mendukung CRUD nyata.

## Hasil belajar

1. Membedakan API publik, izin menulis, spesifikasi OpenAPI, repository, dan hosting.
2. Mengambil JSON memakai Laravel HTTP Client dan JavaScript fetch.
3. Memetakan data eksternal ke struktur data aplikasi.
4. Membuat REST API, validasi, autentikasi token, dan pembatasan pengelola.
5. Membuktikan perubahan tetap tersimpan setelah refresh dan restart server.
6. Mengunggah proyek ke GitHub tanpa menyertakan rahasia.

## Aturan proyek

Kerjakan dengan kelompok yang sudah ditetapkan dosen. Tenggat dan jadwal presentasi mengikuti arahan dosen. Jika terdapat empat kelompok, gunakan fokus berikut: referensi pemrograman, referensi jaringan, referensi data sains, dan literasi umum. Semua kelompok mengerjakan fitur inti dan rubrik yang sama.

Buat proyek baru. Gunakan aplikasi dosen sebagai rujukan alur dan kontrak, bukan hasil akhir untuk dikumpulkan tanpa pengembangan. Setiap kelompok wajib membuat tiga pengembangan: satu field database baru, satu filter backend baru yang berfungsi, dan satu ringkasan pada tampilan berdasarkan data tersimpan. Jelaskan kontribusi setiap anggota melalui commit dan presentasi.

Contoh: field bahasa, filter bahasa, serta jumlah buku per bahasa. Tambahkan migration, validasi, respons JSON, formulir, dan pengujian untuk field tersebut.

## Fitur wajib

- Koleksi tersimpan, pencarian, filter, detail, dan pagination.
- Pencarian Open Library melalui backend Laravel.
- Impor buku pilihan ke database aplikasi, dengan identitas sumber yang unik.
- Tambah manual, ubah, dan hapus buku tersimpan.
- Login, logout, token kedaluwarsa, dan izin pengelola untuk operasi tulis.
- Validasi kode unik, judul, penulis, kategori, tahun, serta stok nonnegatif.
- Status memuat, kosong, sukses, validasi gagal, dan penyedia eksternal gagal.
- Tampilan Bootstrap yang dapat dipakai pada laptop dan ponsel.
- README, koleksi Postman, bukti uji, dan repository GitHub.

Open Library pada proyek ini dipakai untuk pencarian metadata melalui GET. POST, PATCH, dan DELETE mengubah koleksi di database Anda. Akses publik tidak otomatis memberi izin mengubah data penyedia. OpenAPI adalah spesifikasi dokumentasi API, bukan izin CRUD.

## Langkah 1. Rancang alur

Gambar hubungan browser, REST API Laravel, Open Library, dan database. Bedakan alur pencarian eksternal dari alur menyimpan koleksi. Tetapkan tiga pengembangan kelompok. Hasil: gambar alur dan daftar fitur.

## Langkah 2. Buat proyek baru

Gunakan PHP 8.3, Composer, Git, SQLite, browser, dan Postman. PHP harus menyediakan pdo_sqlite, mbstring, openssl, dan extension Laravel yang diperlukan.

    composer create-project laravel/laravel katalog-kelompok "12.*"
    cd katalog-kelompok
    php artisan install:api
    php artisan key:generate

Gunakan DB_CONNECTION=sqlite di .env. Pastikan database/database.sqlite tersedia. Perintah install:api memasang dukungan API dan Sanctum. Jalankan perintah satu per satu serta selesaikan pesan instalasi yang muncul. Tambahkan Bootstrap 5.3 melalui aset lokal atau CDN resmi. Hasil: halaman Laravel terbuka.

## Langkah 3. Buat model dan migration

    php artisan make:model Book -m
    php artisan make:controller BookController
    php artisan make:request BookRequest
    php artisan make:resource BookResource
    php artisan make:controller AuthController
    php artisan make:controller OpenLibraryController --invokable

Tabel books: id, kode unik maksimal 24 karakter, judul maksimal 250, penulis maksimal 200, tahun opsional, kategori, stok bilangan bulat nonnegatif, open_library_key opsional dan unik, cover_id opsional, timestamps, serta field pengembangan kelompok. Model hanya menerima field yang Anda izinkan melalui fillable.

Tambahkan is_admin boolean default false ke users. Jangan izinkan is_admin berubah melalui body request katalog. Model User memakai HasApiTokens. Setelah migration selesai, jalankan php artisan migrate. Hasil: tabel dan kolom cocok dengan rancangan.

## Langkah 4. Buat data awal dan validasi

Isi minimal delapan buku latihan fiktif melalui seeder. Pisahkan data latihan dari metadata eksternal melalui label sumber.

BookRequest harus memvalidasi POST dan PATCH. Pada POST, kode, judul, penulis, kategori, dan stok wajib terisi. Pada PATCH, validasi field yang dikirim. Pertahankan kode unik dengan mengabaikan model yang sedang diubah, bukan ID bebas dari body. Tahun boleh kosong, atau 1 sampai tahun berjalan + 1. Gunakan validated(), bukan seluruh input mentah. Pada rancangan ini, authorize() mengembalikan true karena middleware route memeriksa autentikasi dan peran admin. Jangan membiarkan nilai bawaan false yang menolak semua request.

Gunakan type hint BookRequest pada metode store dan update. Jika class hanya di-import tetapi metode masih memakai Request, editor dapat menandai import sebagai tidak terpakai. Hasil: data salah mendapat JSON 422 dengan errors.

## Langkah 5. Buat endpoint publik

Gunakan prefix /api/v1. Buat GET /books, GET /books/{id}, GET /stats, dan GET /open-library?q=programming. Index mengembalikan data serta meta pagination. Dukung q, kategori, page, dan per_page dengan batas maksimum. Gunakan urutan konsisten.

BookResource mengembalikan id, kode, judul, penulis, tahun, kategori, stok, identitas sumber, dan field kelompok. Hasil: URL daftar dan detail bisa dibuka tanpa login.

## Langkah 6. Buat autentikasi dan izin

Buat POST /auth/login, GET /auth/me, dan POST /auth/logout. Hash kata sandi, periksa kredensial, buat token Sanctum dengan masa berlaku, dan cabut token saat logout. Siapkan akun admin lewat command lokal atau environment hosting. Jangan menyimpan kata sandi di GitHub.

Lindungi POST /books, PATCH /books/{id}, dan DELETE /books/{id} dengan auth:sanctum serta pemeriksaan admin. Jika memakai abilities, berikan books:write hanya untuk admin dan tetap periksa perannya. Hasil: tamu mendapat 401, pengguna tanpa izin mendapat 403, dan admin dapat menulis.

## Langkah 7. Lengkapi CRUD

Store menyimpan data valid dan mengembalikan 201 beserta data. Update mengubah model yang ditemukan dan mengembalikan 200. Destroy menghapus data uji yang dipilih dan mengembalikan 204 tanpa body. Gunakan route model binding agar ID tidak ditemukan mendapat 404. Hasil: GET menampilkan perubahan yang benar-benar tersimpan.

## Langkah 8. Hubungkan Open Library

Gunakan Laravel HTTP Client ke https://openlibrary.org/search.json dengan q, limit=8, dan fields=key,title,author_name,first_publish_year,cover_i. Batasi input q, gunakan host penyedia tetap, timeout, cache singkat, dan pembatasan permintaan. Jangan menerima URL tujuan bebas dari pengguna.

Petakan key ke open_library_key, title ke judul, gabungan author_name ke penulis, first_publish_year ke tahun, serta cover_i ke cover_id. Tangani penulis, tahun, dan sampul yang kosong. Bila provider gagal, kembalikan JSON 503 dengan pesan jelas. Jangan mengganti kegagalan dengan data palsu yang diklaim live. Hasil: sedikitnya satu pencarian langsung berhasil.

## Langkah 9. Bangun halaman Bootstrap

Siapkan navigasi Koleksi, Jelajah API, dan Panduan API. Buat kartu atau tabel, formulir, modal detail, modal login, konfirmasi hapus, serta pagination. Gunakan fetch untuk endpoint Laravel. Baca result.data, bukan seluruh objek respons sebagai daftar. Tampilkan data eksternal sebagai teks aman agar judul dari API tidak menjadi HTML aktif.

Gunakan URL relatif /api/v1 saat frontend satu origin dengan Laravel. Untuk aplikasi pada port berbeda, konfigurasikan CORS pada backend. Hasil: pengguna melihat status memuat, kosong, berhasil, dan gagal.

## Langkah 10. Hubungkan formulir dan impor

Simpan access_token hasil login sebagai state sesi browser dan kirim melalui header Authorization: Bearer. Jangan menaruh token pada URL, source, atau tangkapan layar. Tambah memakai POST, ubah memakai PATCH, dan hapus memakai DELETE.

Tombol Simpan ke koleksi membuka formulir berisi metadata terpilih. Pengelola memeriksa judul dan tahun, lalu menentukan kode, kategori, dan stok. Simpan ke endpoint lokal /books. Cegah impor dua kali untuk open_library_key yang sama. Hasil: hasil impor muncul pada daftar lokal serta dapat diubah tanpa mengubah Open Library.

## Langkah 11. Tambahkan pengembangan kelompok

Hubungkan field, filter, dan ringkasan baru dari migration sampai frontend. Tambahkan uji input valid dan tidak valid. Mengganti warna, judul halaman, atau logo saja belum memenuhi pengembangan. Hasil: pengembangan dapat dibuktikan melalui respons API dan tampilan.

## Langkah 12. Uji dan catat bukti

Uji GET daftar/detail, pencarian, kategori, pagination, POST valid, PATCH, DELETE, kode duplikat, judul kosong, stok negatif, tahun tidak valid, ID tidak ditemukan, tamu 401, pengguna tanpa izin 403, login salah, logout mencabut token, impor ganda, data eksternal kosong, kegagalan provider 503, serta persistensi setelah refresh dan restart.

Gunakan database uji terpisah. HTTP fake cocok untuk pengujian otomatis provider. Sertakan satu bukti pencarian live untuk integrasi nyata. Jangan memakai migrate:fresh pada database pekerjaan yang ingin dipertahankan. Hasil: tabel berisi langkah uji, hasil yang diharapkan, hasil aktual, dan status.

## Langkah 13. Unggah GitHub

Inisialisasi repository milik kelompok. Commit composer.lock, migration, seeder, kode, .env.example, README, dan Postman. Abaikan .env, vendor, node_modules, token, kata sandi, dan database yang berisi data pribadi. Gunakan beberapa commit bermakna. Hasil: anggota lain dapat clone dan mengikuti README sampai aplikasi berjalan.

## Langkah 14. Publikasikan dan presentasikan

GitHub menyimpan kode. GitHub Pages tidak menjalankan backend PHP. Untuk URL publik, gunakan hosting PHP/Docker dengan HTTPS dan database persisten. Public IP statis khusus tidak wajib. Jika hosting disediakan dosen, deploy aplikasi dan uji dari jaringan berbeda. Jika belum tersedia, demonstrasikan lokal dan serahkan konfigurasi deploy tanpa menyatakan URL publik sudah aktif.

Setel APP_ENV=production, APP_DEBUG=false, APP_KEY, APP_URL, koneksi database, dan akun pengelola melalui environment hosting. Jalankan migration, uji CRUD dan restart, lalu bagikan hanya endpoint baca kepada kelas. Hasil: URL aktif jika layanan tersedia, atau panduan deploy yang dapat dijalankan.

## Berkas yang dikumpulkan

1. Tautan repository dan commit akhir.
2. README berisi anggota, kontribusi, instalasi, schema, endpoint, API sumber, pengembangan, dan pengujian.
3. Koleksi Postman tanpa token atau kredensial nyata.
4. Laporan PDF singkat dengan diagram, bukti fitur, hasil uji, dan status deployment.
5. URL aplikasi jika hosting tersedia. Jika belum, tulis status lokal dan kebutuhan hosting.
6. Demo: pencarian live, impor, CRUD, akses tanpa izin, persistensi, dan tiga pengembangan.

## Rubrik 100 poin

| Aspek | Bobot | Bukti |
|---|---:|---|
| CRUD dan database | 25 | Tambah, detail, ubah, hapus, dan persistensi berjalan |
| Integrasi API publik | 15 | Live search, pemetaan data, impor, dan atribusi sumber |
| Bootstrap dan pengembangan | 15 | Tampilan responsif, state lengkap, field/filter/ringkasan baru |
| Autentikasi dan izin | 15 | Token valid, batas admin, logout, dan rahasia terjaga |
| Validasi dan pengujian | 15 | Kasus berhasil dan gagal memiliki bukti |
| GitHub dan dokumentasi | 10 | Clone dapat dijalankan, commit bermakna, deploy dijelaskan |
| Presentasi dan kontribusi | 5 | Anggota menjelaskan kode dan alur data dengan tepat |

Penilaian URL publik mengikuti ketersediaan hosting yang ditetapkan dosen. Semua kelompok memakai standar fitur inti yang sama.

## Referensi dan aplikasi dosen

- Aplikasi dosen: https://github.com/feryHendrix-ui/JSONDecode
- Laravel 12 HTTP Client: https://laravel.com/docs/12.x/http-client
- Laravel 12 Sanctum: https://laravel.com/docs/12.x/sanctum
- Laravel 12 validation: https://laravel.com/docs/12.x/validation
- Open Library Search: https://openlibrary.org/dev/docs/api/search
- Bootstrap: https://getbootstrap.com/docs/5.3/getting-started/introduction/
- GitHub Pages: https://docs.github.com/en/pages/getting-started-with-github-pages/what-is-github-pages

Panduan ini berisi urutan pekerjaan dan kriteria hasil. Mahasiswa tetap menulis dan menjelaskan implementasinya sendiri.
