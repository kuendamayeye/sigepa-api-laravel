<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@sigepa.ao',
            'password' => 'password123',
        ])->json('access_token');

        $this->withToken($token);
    }

    public function test_members_can_be_listed_with_search_and_filters(): void
    {
        $this->getJson('/api/v1/members?search=Ana')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.fullName', 'Ana Manuel Domingos');

        $this->getJson('/api/v1/members?provinceId=prov-cabinda')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_member_crud_and_activation(): void
    {
        $created = $this->postJson('/api/v1/members', [
            'fullName' => 'Novo Membro Teste',
            'gender' => 'Female',
            'residenceProvinceId' => 'prov-luanda',
            'residenceMunicipalityId' => 'mun-belas',
            'email' => '',
        ])->assertCreated()->json();

        $this->assertNotEmpty($created['id']);
        $this->assertSame('active', $created['status']);

        $this->patchJson('/api/v1/members/'.$created['id'], ['profession' => 'Médica'])
            ->assertOk()
            ->assertJsonPath('profession', 'Médica');

        $this->postJson('/api/v1/members/'.$created['id'].'/deactivate')
            ->assertOk()
            ->assertJsonPath('isActive', false)
            ->assertJsonPath('status', 'inactive');

        $this->deleteJson('/api/v1/members/'.$created['id'])->assertOk();
        $this->assertDatabaseMissing('members', ['id' => $created['id']]);
    }

    public function test_statistics_start_with_total(): void
    {
        $stats = $this->getJson('/api/v1/members/statistics/overview')->assertOk()->json();

        $this->assertSame('total', array_key_first($stats));
        $this->assertSame(Member::query()->count(), $stats['total']);
        $this->assertArrayHasKey('male', $stats);
        $this->assertArrayHasKey('female', $stats);

        $structures = $this->getJson('/api/v1/structures/statistics/overview')->assertOk()->json();
        $this->assertArrayHasKey('localCommittees', $structures);
    }

    public function test_geography_filter_and_catalogue_crud(): void
    {
        $this->getJson('/api/v1/municipalities?provinceId=prov-luanda')
            ->assertOk()
            ->assertJsonCount(2);

        $created = $this->postJson('/api/v1/idioms', ['name' => 'Kwanyama'])
            ->assertCreated()
            ->json();

        $this->getJson('/api/v1/idioms/'.$created['id'])
            ->assertOk()
            ->assertJsonPath('name', 'Kwanyama');

        $this->deleteJson('/api/v1/idioms/'.$created['id'])->assertOk();
    }

    public function test_user_can_be_updated_without_resending_password(): void
    {
        $created = $this->postJson('/api/v1/users', [
            'name' => 'Novo Utilizador',
            'email' => 'novo@sigepa.ao',
            'password' => 'password123',
            'role' => 'operador',
        ])->assertCreated()->json();

        $this->patchJson('/api/v1/users/'.$created['id'], [
            'name' => 'Utilizador Actualizado',
            'email' => 'novo@sigepa.ao',
            'password' => '',
            'role' => 'visualizador',
        ])->assertOk()->assertJsonPath('role', 'visualizador');
    }

    public function test_non_admin_cannot_list_users(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'operador@sigepa.ao',
            'password' => 'password123',
        ])->json('access_token');

        $this->withToken($token)->getJson('/api/v1/users')->assertForbidden();
    }
}
