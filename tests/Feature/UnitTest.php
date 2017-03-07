<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UnitTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetList()
    {
        $response = $this->json('GET', '/api/v1/units')
            ->assertStatus(200)
            ->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);

        foreach ($response['data'] as $unit) {
            $this->assertArrayHasKey('id', $unit);
            $this->assertArrayHasKey('name', $unit);
        }
    }
}
