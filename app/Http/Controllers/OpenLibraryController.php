<?php

namespace App\Http\Controllers;

use App\Services\OpenLibraryService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use UnexpectedValueException;

class OpenLibraryController extends Controller
{
    public function __invoke(Request $request, OpenLibraryService $service)
    {
        $data = $request->validate(['q' => 'required|string|min:2|max:100']);

        try {
            return response()->json($service->search($data['q']));
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Open Library sedang tidak dapat diakses. Koleksi lokal tetap tersedia. Coba lagi nanti.',
                'code' => 'UPSTREAM_UNAVAILABLE',
            ], 503);
        }
    }
}
