<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'kode', 'judul', 'penulis', 'tahun', 'kategori',
        'stok', 'open_library_key', 'cover_id',
    ];

    protected function casts(): array
    {
        return ['tahun' => 'integer', 'stok' => 'integer', 'cover_id' => 'integer'];
    }
}
