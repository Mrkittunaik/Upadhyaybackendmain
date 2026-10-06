<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesFixtures;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use CreatesFixtures, RefreshDatabase;

    private function payload(array $over = []): array
    {
        return $over + [
            'name' => 'Asha Rao', 'email' => 'Asha@Example.com', 'password' => 'Secret123',
            'role' => 'faculty',
        ];
    }

    public function test_faculty_can_register_and_gets_token_and_profile(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.user.role', 'faculty')
            ->assertJsonPath('data.user.email', 'asha@example.com')
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user' => ['faculty_profile']]]);

        $this->assertDatabaseHas('faculty_profiles', ['user_id' => 1]);
        $this->assertNotEquals('Secret123', \App\Models\User::first()->password);
    }

    public function test_company_main_registration_creates_company(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload([
            'role' => 'company_main', 'company_name' => 'ABC Technologies', 'city' => 'Hyderabad', 'city_code' => 'hyd',
        ]))->assertCreated()->assertJsonPath('data.user.company.name', 'ABC Technologies')
            ->assertJsonPath('data.user.company.location_display', 'Hyderabad (HYD)');
    }

    public function test_company_main_requires_company_name(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload(['role' => 'company_main']))
            ->assertStatus(422)->assertJsonValidationErrors('company_name');
    }

    public function test_admin_cannot_self_register(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload(['role' => 'admin']))
            ->assertStatus(422)->assertJsonValidationErrors('role');
    }

    public function test_duplicate_email_and_weak_password_rejected(): void
    {
        $this->makeUser(UserRole::Faculty, ['email' => 'asha@example.com']);

        $this->postJson('/api/v1/auth/register', $this->payload())->assertStatus(422)->assertJsonValidationErrors('email');
        $this->postJson('/api/v1/auth/register', $this->payload(['email' => 'new@example.com', 'password' => 'short']))
            ->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_login_success_and_failure(): void
    {
        $user = $this->makeUser(UserRole::Faculty, ['email' => 'a@example.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 'a@example.com', 'password' => 'Password123'])
            ->assertOk()->assertJsonStructure(['data' => ['token']]);
        $this->assertNotNull($user->fresh()->last_login_at);

        $this->postJson('/api/v1/auth/login', ['email' => 'a@example.com', 'password' => 'wrong'])
            ->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_suspended_user_cannot_login(): void
    {
        \App\Models\User::factory()->suspended()->create(['email' => 's@example.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 's@example.com', 'password' => 'Password123'])->assertForbidden();
    }

    public function test_me_requires_authentication_and_logout_revokes_token(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        $token = $this->postJson('/api/v1/auth/register', $this->payload())->json('data.token');

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('data.email', 'asha@example.com');
        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_role_middleware_blocks_wrong_roles(): void
    {
        $faculty = $this->makeUser(UserRole::Faculty);
        $this->actingAsUser($faculty)->getJson('/api/v1/company')->assertForbidden();
        $this->postJson('/api/v1/branches/requests', [])->assertForbidden();

        $branchUser = $this->makeUser(UserRole::CompanyBranch);
        $this->actingAsUser($branchUser)->getJson('/api/v1/faculty/profile')->assertForbidden();
    }

    public function test_forgot_password_is_generic(): void
    {
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'nobody@example.com'])->assertOk();
    }
}
