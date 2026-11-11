<?php
namespace Tests\Feature;
use App\Models\User;
use Database\Seeders\DemoBookSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_merge(['kode'=>'TES-001','judul'=>'Buku Pengujian','penulis'=>'Tim Praktikum',
            'tahun'=>2026,'kategori'=>'pemrograman','stok'=>3], $extra);
    }

    public function test_public_can_read_search_filter_and_paginate(): void
    {
        $this->seed(DemoBookSeeder::class);
        $this->getJson('/api/v1/books?per_page=2')->assertOk()
            ->assertJsonCount(2,'data')->assertJsonPath('meta.total',8);
        $this->getJson('/api/v1/books?q=Laravel&kategori=pemrograman')
            ->assertOk()->assertJsonPath('meta.total',1)
            ->assertJsonPath('data.0.judul','Praktikum Laravel 12');
        $this->getJson('/api/v1/books?per_page=999')->assertUnprocessable();
        $this->getJson('/api/v1/stats')->assertOk()->assertJsonPath('data.total_buku',8);
    }

    public function test_guest_cannot_modify_catalog(): void
    {
        $this->postJson('/api/v1/books',$this->payload())->assertUnauthorized();
        $this->assertDatabaseCount('books',0);
    }

    public function test_admin_can_create_read_update_and_delete_a_persisted_book(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin'=>true]),['books:write']);
        $id=$this->postJson('/api/v1/books',$this->payload())->assertCreated()
            ->assertJsonPath('data.judul','Buku Pengujian')->json('data.id');
        $this->assertDatabaseHas('books',['id'=>$id,'kode'=>'TES-001']);
        $this->getJson('/api/v1/books/'.$id)->assertOk()->assertJsonPath('data.stok',3);
        $this->patchJson('/api/v1/books/'.$id,['judul'=>'Judul Revisi','stok'=>7])
            ->assertOk()->assertJsonPath('data.judul','Judul Revisi');
        $this->assertDatabaseHas('books',['id'=>$id,'stok'=>7]);
        $this->deleteJson('/api/v1/books/'.$id)->assertNoContent();
        $this->getJson('/api/v1/books/'.$id)->assertNotFound();
        $this->assertDatabaseMissing('books',['id'=>$id]);
    }

    public function test_role_and_token_ability_are_both_required(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin'=>false]),['books:write']);
        $this->postJson('/api/v1/books',$this->payload())->assertForbidden();
        Sanctum::actingAs(User::factory()->create(['is_admin'=>true]),[]);
        $this->postJson('/api/v1/books',$this->payload())->assertForbidden();
    }

    public function test_duplicate_code_negative_stock_and_invalid_source_are_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin'=>true]),['books:write']);
        $this->postJson('/api/v1/books',$this->payload())->assertCreated();
        $this->postJson('/api/v1/books',$this->payload())->assertUnprocessable()->assertJsonValidationErrors('kode');
        $this->postJson('/api/v1/books',$this->payload(['kode'=>'TES-002','stok'=>-1]))
            ->assertUnprocessable()->assertJsonValidationErrors('stok');
        $this->postJson('/api/v1/books',$this->payload(['kode'=>'TES-003','open_library_key'=>'javascript:alert(1)']))
            ->assertUnprocessable()->assertJsonValidationErrors('open_library_key');
    }

    public function test_external_metadata_is_saved_locally_and_duplicate_import_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin'=>true]),['books:write']);
        $this->postJson('/api/v1/books',$this->payload(['open_library_key'=>'/works/OL123W','cover_id'=>123]))
            ->assertCreated()->assertJsonPath('data.sumber','Open Library');
        $this->postJson('/api/v1/books',$this->payload(['kode'=>'TES-002','open_library_key'=>'/works/OL123W']))
            ->assertUnprocessable()->assertJsonValidationErrors('open_library_key');
    }

    public function test_public_api_supports_cross_origin_reads(): void
    {
        $this->getJson('/api/v1/books',['Origin'=>'https://student.example.test'])
            ->assertOk()->assertHeader('Access-Control-Allow-Origin','*');
    }

    public function test_home_page_uses_local_bootstrap_assets(): void
    {
        $this->get('/')->assertOk()->assertSee('Pustaka API')->assertSee('/vendor/bootstrap/bootstrap.min.css');
    }
}
