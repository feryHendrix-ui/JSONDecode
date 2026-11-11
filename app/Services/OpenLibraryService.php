<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use UnexpectedValueException;

class OpenLibraryService
{
    public function search(string $query): array
    {
        return Cache::remember('openlibrary:'.hash('sha256', Str::lower(trim($query))), 120, function () use ($query) {
            $response = Http::acceptJson()
                ->withUserAgent(config('catalog.openlibrary_agent'))
                ->connectTimeout(10)->timeout(20)
                ->get(rtrim(config('catalog.openlibrary_url'), '/').'/search.json', [
                    'q' => $query, 'limit' => 8,
                    'fields' => 'key,title,author_name,first_publish_year,cover_i',
                ]);
            $response->throw();
            $raw = $response->json();
            if (! is_array($raw) || ! isset($raw['docs']) || ! is_array($raw['docs'])) {
                throw new UnexpectedValueException('Format respons penyedia tidak sesuai.');
            }

            $data = collect($raw['docs'])->filter(fn ($row) =>
                is_array($row) && is_string($row['key'] ?? null)
                && preg_match('#^/works/OL[0-9]+W$#', $row['key'])
                && is_string($row['title'] ?? null)
            )->map(function ($row) {
                $year = filter_var($row['first_publish_year'] ?? null, FILTER_VALIDATE_INT);
                $authors = array_filter((array) ($row['author_name'] ?? []), 'is_string');
                $cover = filter_var($row['cover_i'] ?? null, FILTER_VALIDATE_INT);
                return [
                    'open_library_key' => $row['key'],
                    'judul' => Str::limit($row['title'], 247),
                    'penulis' => Str::limit(implode(', ', $authors) ?: 'Penulis tidak tercantum', 197),
                    'tahun' => $year && $year > 0 && $year <= date('Y') + 1 ? $year : null,
                    'cover_id' => $cover && $cover > 0 ? $cover : null,
                    'cover_url' => $cover && $cover > 0
                        ? 'https://covers.openlibrary.org/b/id/'.$cover.'-M.jpg?default=false' : null,
                    'source_url' => 'https://openlibrary.org'.$row['key'],
                ];
            })->values()->all();

            return ['data' => $data, 'meta' => [
                'source' => 'Open Library', 'query' => $query,
                'total' => (int) ($raw['numFound'] ?? $raw['num_found'] ?? count($data)),
                'returned' => count($data), 'cache_seconds' => 120,
            ]];
        });
    }
}
