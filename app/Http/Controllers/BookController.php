<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->validate([
            'q' => 'nullable|string|max:100',
            'kategori' => ['nullable', Rule::in(array_keys(config('catalog.categories')))],
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:24',
        ]);
        $query = Book::query();
        if (! empty($params['q'])) {
            $q = $params['q'];
            $query->where(fn ($builder) => $builder
                ->where('judul', 'like', '%'.$q.'%')
                ->orWhere('penulis', 'like', '%'.$q.'%')
                ->orWhere('kode', 'like', '%'.$q.'%'));
        }
        if (! empty($params['kategori'])) {
            $query->where('kategori', $params['kategori']);
        }

        return BookResource::collection(
            $query->orderByDesc('id')->paginate($params['per_page'] ?? 8)->withQueryString()
        );
    }

    public function show(Book $book)
    {
        return new BookResource($book);
    }

    public function store(BookRequest $request)
    {
        $book = Book::create($request->validated());

        return (new BookResource($book))->response()->setStatusCode(201);
    }

    public function update(BookRequest $request, Book $book)
    {
        $book->update($request->validated());

        return new BookResource($book->refresh());
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->noContent();
    }

    public function stats()
    {
        return response()->json(['data' => [
            'total_buku' => Book::count(),
            'total_stok' => (int) Book::sum('stok'),
            'dari_api_publik' => Book::whereNotNull('open_library_key')->count(),
            'kategori' => config('catalog.categories'),
        ]]);
    }
}
