<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'kode' => $this->kode,
            'judul' => $this->judul,
            'penulis' => $this->penulis,
            'tahun' => $this->tahun,
            'kategori' => $this->kategori,
            'kategori_label' => config('catalog.categories')[$this->kategori] ?? $this->kategori,
            'stok' => (int) $this->stok,
            'open_library_key' => $this->open_library_key,
            'cover_id' => $this->cover_id,
            'cover_url' => $this->cover_id
                ? 'https://covers.openlibrary.org/b/id/'.$this->cover_id.'-M.jpg?default=false'
                : null,
            'sumber' => $this->open_library_key ? 'Open Library' : 'Data manual',
            'source_url' => $this->open_library_key
                ? 'https://openlibrary.org'.$this->open_library_key : null,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
