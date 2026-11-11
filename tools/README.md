# Server pengembangan opsional

Perintah standar tetap php artisan serve.

Jika Node.js tersedia, npm run dev menjalankan tools/serve.mjs. Script memakai PHP pada PATH. Untuk lingkungan yang telah menyediakan FrankenPHP, environment FRANKENPHP_BINARY dapat menunjuk binary tersebut. Nilai ini boleh diletakkan pada .env lokal dan tidak disimpan di GitHub.

tools/router.php hanya dipakai server pengembangan PHP bawaan. Server Docker produksi memakai FrankenPHP dengan Caddyfile.
