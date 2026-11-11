<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_normalizes_the_response_and_caches_it(): void
    {
        Cache::flush();
        Http::preventStrayRequests();
        Http::fake(['openlibrary.org/*'=>Http::response([
            'numFound'=>12,'docs'=>[
                ['key'=>'/works/OL123W','title'=>'Buku API','author_name'=>['Penulis A'],
                    'first_publish_year'=>2020,'cover_i'=>321],
                ['key'=>'javascript:alert(1)','title'=>'Harus disaring'],
            ],
        ])]);
        $url='/api/v1/open-library?q=programming';
        $this->getJson($url)->assertOk()->assertJsonCount(1,'data')
            ->assertJsonPath('data.0.open_library_key','/works/OL123W')->assertJsonPath('meta.total',12);
        $this->getJson($url)->assertOk();
        Http::assertSentCount(1);
    }

    public function test_upstream_failure_does_not_break_local_catalog(): void
    {
        Cache::flush();
        Http::fake(fn () => throw new ConnectionException('Simulasi timeout'));
        $this->getJson('/api/v1/open-library?q=timeout')->assertStatus(503)
            ->assertJsonPath('code','UPSTREAM_UNAVAILABLE');
        $this->getJson('/api/v1/books')->assertOk();
    }

    public function test_invalid_query_is_rejected_before_calling_provider(): void
    {
        Http::preventStrayRequests();
        $this->getJson('/api/v1/open-library?q=a')->assertUnprocessable();
        Http::assertNothingSent();
    }
}
