<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $presence = $this->isMethod('POST') ? 'required' : 'sometimes';
        $book = $this->route('book');

        return [
            'kode' => [$presence, 'required', 'string', 'max:24',
                Rule::unique('books', 'kode')->ignore($book)],
            'judul' => [$presence, 'required', 'string', 'max:250'],
            'penulis' => [$presence, 'required', 'string', 'max:200'],
            'tahun' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:'.(date('Y') + 1)],
            'kategori' => [$presence, 'required', Rule::in(array_keys(config('catalog.categories')))],
            'stok' => [$presence, 'required', 'integer', 'min:0', 'max:99999'],
            'open_library_key' => ['sometimes', 'nullable', 'string', 'max:50',
                'regex:#^/works/OL[0-9]+W$#',
                Rule::unique('books', 'open_library_key')->ignore($book)],
            'cover_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return ['kode' => 'kode buku', 'judul' => 'judul', 'penulis' => 'penulis',
            'tahun' => 'tahun terbit', 'kategori' => 'kategori', 'stok' => 'stok',
            'open_library_key' => 'buku dari Open Library'];
    }
}
