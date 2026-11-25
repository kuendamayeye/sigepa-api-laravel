<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_returns_tokens_and_user(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@sigepa.ao',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', 'admin@sigepa.ao')
            ->assertJsonPath('user.role', 'admin')
            ->assertJsonStructure(['access_token', 'refresh_token']);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@sigepa.ao',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    public function test_profile_requires_a_valid_token(): void
    {
        $this->getJson('/api/v1/auth/profile')->assertUnauthorized();
    }

    public function test_refresh_rotates_tokens(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'operador@sigepa.ao',
            'password' => 'password123',
        ])->assertOk();

        $this->withToken($login->json('refresh_token'))
            ->postJson('/api/v1/auth/refresh')
            ->assertOk()
            ->assertJsonStructure(['access_token', 'refresh_token']);

        $this->withToken($login->json('refresh_token'))
            ->postJson('/api/v1/auth/refresh')
            ->assertUnauthorized();
    }

    public function test_viewer_cannot_create_members(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'visualizador@sigepa.ao',
            'password' => 'password123',
        ])->assertOk();

        $this->withToken($login->json('access_token'))
            ->postJson('/api/v1/members', [
                'fullName' => 'Teste Visualizador',
                'gender' => 'Male',
                'residenceProvinceId' => 'prov-luanda',
                'residenceMunicipalityId' => 'mun-belas',
            ])
            ->assertForbidden();
    }
}
