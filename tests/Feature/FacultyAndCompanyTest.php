<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesFixtures;
use Tests\TestCase;

class FacultyAndCompanyTest extends TestCase
{
    use CreatesFixtures, RefreshDatabase;

    public function test_faculty_updates_profile_with_relations(): void
    {
        $faculty = $this->makeUser(UserRole::Faculty);

        $this->actingAsUser($faculty)->putJson('/api/v1/faculty/profile', [
            'designation' => 'Assistant Professor', 'experience_years' => 4.5,
            'education' => [['degree' => 'M.Tech', 'institution' => 'IIIT']],
            'experience' => [['title' => 'Lecturer', 'organization' => 'XYZ College']],
            'skills' => ['Python', 'python', 'ML'],
        ])->assertOk()
            ->assertJsonPath('data.designation', 'Assistant Professor')
            ->assertJsonCount(1, 'data.education')
            ->assertJsonCount(2, 'data.skills');

        $this->getJson('/api/v1/faculty/profile')->assertOk()->assertJsonPath('data.education.0.degree', 'M.Tech');
    }

    public function test_faculty_profile_validation(): void
    {
        $this->actingAsUser($this->makeUser())->putJson('/api/v1/faculty/profile', [
            'education' => [['institution' => 'x']], 'experience_years' => 200,
        ])->assertStatus(422)->assertJsonValidationErrors(['education.0.degree', 'experience_years']);
    }

    public function test_company_main_creates_and_updates_company(): void
    {
        $owner = $this->makeUser(UserRole::CompanyMain);

        $this->actingAsUser($owner)->postJson('/api/v1/company', ['name' => 'Acme', 'city' => 'Pune', 'city_code' => 'pun'])
            ->assertCreated()->assertJsonPath('data.profile.city_code', 'PUN');

        $this->postJson('/api/v1/company', ['name' => 'Again'])->assertStatus(409);

        $this->putJson('/api/v1/company', ['website' => 'https://acme.test', 'name' => 'Acme Ltd'])
            ->assertOk()->assertJsonPath('data.name', 'Acme Ltd')->assertJsonPath('data.profile.website', 'https://acme.test');
    }

    public function test_company_search_by_name_code_and_city_with_pagination(): void
    {
        $a = $this->makeCompany(null, [], 'ABC Technologies');
        $a->update(['company_code' => '12345']);
        $this->makeCompany(null, ['city' => 'Pune', 'city_code' => 'PUN'], 'Zeta Labs');

        $viewer = $this->makeUser(UserRole::CompanyBranch);
        $this->actingAsUser($viewer);

        $this->getJson('/api/v1/companies/search?q=abc')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.location_display', 'Hyderabad (HYD)');
        $this->getJson('/api/v1/companies/search?q=12345')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/companies/search?q=Pune')->assertOk()->assertJsonPath('data.0.name', 'Zeta Labs');
        $this->getJson('/api/v1/companies/search?per_page=1')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 2);
    }

    public function test_company_search_requires_auth(): void
    {
        $this->getJson('/api/v1/companies/search?q=a')->assertUnauthorized();
    }
}
