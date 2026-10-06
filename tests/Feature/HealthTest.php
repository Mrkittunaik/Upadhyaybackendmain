<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_standard_envelope(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'API is running']);
    }

    public function test_unknown_api_route_returns_json_404(): void
    {
        $this->getJson('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertJson(['success' => false]);
    }
}
