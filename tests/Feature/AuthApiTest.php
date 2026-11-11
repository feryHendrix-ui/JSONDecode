<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_logout_revoke_the_same_real_token(): void
    {
        User::factory()->create(['email'=>'admin@example.test',
            'password'=>Hash::make('UjiKatalog!2026'),'is_admin'=>true]);
        $token=$this->postJson('/api/v1/auth/login',[
            'email'=>'admin@example.test','password'=>'UjiKatalog!2026',
        ])->assertOk()->assertJsonPath('user.is_admin',true)->json('access_token');
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk();
        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens',0);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email'=>'admin@example.test']);
        $this->postJson('/api/v1/auth/login',['email'=>'admin@example.test','password'=>'salah'])
            ->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens',0);
    }

    public function test_missing_credentials_produce_validation_errors(): void
    {
        $this->postJson('/api/v1/auth/login',[])->assertUnprocessable()
            ->assertJsonValidationErrors(['email','password']);
    }
}
