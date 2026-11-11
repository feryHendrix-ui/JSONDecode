<?php

return [
    'categories' => [
        'pemrograman' => 'Pemrograman',
        'jaringan' => 'Jaringan',
        'data-sains' => 'Data Sains',
        'umum' => 'Umum',
    ],
    'admin_email' => env('ADMIN_EMAIL'),
    'admin_password' => env('ADMIN_PASSWORD'),
    'admin_name' => env('ADMIN_NAME', 'Pengelola'),
    'openlibrary_url' => env('OPENLIBRARY_URL', 'https://openlibrary.org'),
    'openlibrary_agent' => env('OPENLIBRARY_AGENT',
        'PustakaAPILab/1.0 (https://github.com/feryHendrix-ui/JSONDecode)'),
];
