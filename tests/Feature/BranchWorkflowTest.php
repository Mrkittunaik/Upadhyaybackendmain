<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\BranchRequest;
use App\Models\CompanyBranch;
use App\Models\Notification;
use App\Services\BranchCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesFixtures;
use Tests\TestCase;

class BranchWorkflowTest extends TestCase
{
    use CreatesFixtures, RefreshDatabase;

    private function setupFlow(): array
    {
        $owner = $this->makeUser(UserRole::CompanyMain);
        $company = $this->makeCompany($owner);
        $branchUser = $this->makeUser(UserRole::CompanyBranch);

        return [$owner, $company, $branchUser];
    }

    private function sendRequest($branchUser, $company): int
    {
        return $this->actingAsUser($branchUser)
            ->postJson('/api/v1/branches/requests', $this->branchPayload($company->id))
            ->assertCreated()->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.location_display', 'Hyderabad (HYD)')
            ->json('data.id');
    }

    public function test_branch_user_sends_request_and_owner_is_notified(): void
    {
        [$owner, $company, $branchUser] = $this->setupFlow();
        $this->sendRequest($branchUser, $company);

        $this->assertDatabaseHas('notifications', ['user_id' => $owner->id, 'type' => 'branch_request']);
    }

    public function test_duplicate_pending_request_is_rejected_with_409(): void
    {
        [, $company, $branchUser] = $this->setupFlow();
        $this->sendRequest($branchUser, $company);

        $this->postJson('/api/v1/branches/requests', $this->branchPayload($company->id))->assertStatus(409);
    }

    public function test_request_validation(): void
    {
        [, , $branchUser] = $this->setupFlow();

        $this->actingAsUser($branchUser)->postJson('/api/v1/branches/requests', ['company_id' => 9999])
            ->assertStatus(422)->assertJsonValidationErrors(['company_id', 'branch_name', 'city']);
    }

    public function test_main_campus_sees_requests_and_approves_with_5_digit_code(): void
    {
        [$owner, $company, $branchUser] = $this->setupFlow();
        $id = $this->sendRequest($branchUser, $company);

        $this->actingAsUser($owner)->getJson('/api/v1/branches/requests?status=pending')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 1);

        $res = $this->postJson("/api/v1/branches/requests/{$id}/approve")->assertOk()
            ->assertJsonPath('data.status', 'active');

        $code = $res->json('data.branch_code');
        $this->assertMatchesRegularExpression('/^\d{5}$/', $code);
        $this->assertTrue(BranchCodeGenerator::isValid($code));

        $this->assertSame('approved', BranchRequest::find($id)->status->value);
        $this->assertDatabaseHas('notifications', ['user_id' => $branchUser->id, 'type' => 'branch_approved']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'branch.approved', 'user_id' => $owner->id]);
        $this->assertSame(1, CompanyBranch::count());

        // approving twice never creates a second code/branch
        $this->postJson("/api/v1/branches/requests/{$id}/approve")->assertStatus(409);
        $this->assertSame(1, CompanyBranch::count());

        $this->getJson('/api/v1/branches')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.branch_code', $code);
    }

    public function test_approved_branch_codes_are_unique(): void
    {
        $owner = $this->makeUser(UserRole::CompanyMain);
        $company = $this->makeCompany($owner);
        $codes = [];

        foreach (range(1, 5) as $i) {
            $u = $this->makeUser(UserRole::CompanyBranch);
            $id = $this->actingAsUser($u)->postJson('/api/v1/branches/requests', $this->branchPayload($company->id))->json('data.id');
            $codes[] = $this->actingAsUser($owner)->postJson("/api/v1/branches/requests/{$id}/approve")->json('data.branch_code');
        }

        $this->assertCount(5, array_unique($codes));
    }

    public function test_reject_flow_and_resubmit(): void
    {
        [$owner, $company, $branchUser] = $this->setupFlow();
        $id = $this->sendRequest($branchUser, $company);

        $this->actingAsUser($owner)->postJson("/api/v1/branches/requests/{$id}/reject", ['reason' => 'Not recognised'])
            ->assertOk()->assertJsonPath('data.status', 'rejected')->assertJsonPath('data.rejection_reason', 'Not recognised');

        $this->assertSame(0, CompanyBranch::count());
        $this->assertDatabaseHas('notifications', ['user_id' => $branchUser->id, 'type' => 'branch_rejected']);
        $this->postJson("/api/v1/branches/requests/{$id}/approve")->assertStatus(409);

        $this->actingAsUser($branchUser)->postJson('/api/v1/branches/requests', $this->branchPayload($company->id))->assertCreated();
    }

    public function test_branch_user_cannot_approve_own_request(): void
    {
        [, $company, $branchUser] = $this->setupFlow();
        $id = $this->sendRequest($branchUser, $company);

        $this->postJson("/api/v1/branches/requests/{$id}/approve")->assertForbidden();
        $this->postJson("/api/v1/branches/requests/{$id}/reject")->assertForbidden();
        $this->assertSame(0, CompanyBranch::count());
    }

    public function test_other_company_owner_cannot_approve(): void
    {
        [, $company, $branchUser] = $this->setupFlow();
        $id = $this->sendRequest($branchUser, $company);

        $intruder = $this->makeUser(UserRole::CompanyMain);
        $this->makeCompany($intruder, [], 'Other Co');

        $this->actingAsUser($intruder)->postJson("/api/v1/branches/requests/{$id}/approve")->assertForbidden();
        $this->getJson("/api/v1/branches/requests/{$id}")->assertForbidden();
    }

    public function test_cancel_own_pending_request(): void
    {
        [, $company, $branchUser] = $this->setupFlow();
        $id = $this->sendRequest($branchUser, $company);

        $other = $this->makeUser(UserRole::CompanyBranch);
        $this->actingAsUser($other)->postJson("/api/v1/branches/requests/{$id}/cancel")->assertForbidden();

        $this->actingAsUser($branchUser)->postJson("/api/v1/branches/requests/{$id}/cancel")
            ->assertOk()->assertJsonPath('data.status', 'cancelled');
    }

    public function test_dashboard_locked_then_unlocked_after_approval(): void
    {
        [$owner, $company, $branchUser] = $this->setupFlow();
        $id = $this->sendRequest($branchUser, $company);

        $this->getJson('/api/v1/branch/dashboard')->assertOk()
            ->assertJsonPath('data.unlocked', false)->assertJsonPath('data.latest_request.status', 'pending');

        $this->actingAsUser($owner)->postJson("/api/v1/branches/requests/{$id}/approve")->assertOk();

        $this->actingAsUser($branchUser)->getJson('/api/v1/branch/dashboard')->assertOk()
            ->assertJsonPath('data.unlocked', true)
            ->assertJsonPath('data.company.name', 'ABC Technologies')
            ->assertJsonPath('data.stats.unread_notifications', 1);
    }

    public function test_branch_visibility_rules(): void
    {
        [$owner, $company, $branchUser] = $this->setupFlow();
        $id = $this->sendRequest($branchUser, $company);
        $branchId = $this->actingAsUser($owner)->postJson("/api/v1/branches/requests/{$id}/approve")->json('data.id');

        $this->getJson("/api/v1/branches/{$branchId}")->assertOk();
        $this->getJson("/api/v1/branches/{$branchId}/jobs")->assertOk()->assertJsonPath('meta.total', 0);

        $this->actingAsUser($branchUser)->getJson("/api/v1/branches/{$branchId}")->assertOk();

        $stranger = $this->makeUser(UserRole::CompanyBranch);
        $this->actingAsUser($stranger)->getJson("/api/v1/branches/{$branchId}")->assertForbidden();
        $this->actingAsUser($this->makeUser(UserRole::Faculty))->getJson("/api/v1/branches/{$branchId}")->assertForbidden();
    }
}
