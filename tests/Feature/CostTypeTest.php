<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CostTypeTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetList()
    {
        $response = $this->json('GET', '/api/v1/cost-types')
            ->assertStatus(200)
            ->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);

        foreach ($response['data'] as $costType) {
            $this->assertArrayHasKey('id', $costType);
            $this->assertArrayHasKey('name', $costType);
        }
    }
}
