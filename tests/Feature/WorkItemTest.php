<?php

namespace Tests\Feature;

use App\{
    Work,
    WorkItem
};
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WorkItemTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetList()
    {
        $work = factory(Work::class)->create();
        $workItems = factory(WorkItem::class)->times(3)->create();
        $work->workItems()->attach($workItems, ['amount' => '0', 'unit_price' => '0']);

        $response = $this->json('GET', "/api/v1/works/{$work->id}/work-items")
            ->assertStatus(200)
            ->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);

        foreach ($response['data'] as $workItem) {
            $this->assertArrayHasKey('id', $workItem);
            $this->assertArrayHasKey('work_id', $workItem);
            $this->assertArrayHasKey('name', $workItem);
            $this->assertArrayHasKey('amount', $workItem);
            $this->assertArrayHasKey('unit_price', $workItem);

            // unit
            $this->assertArrayHasKey('unit_id', $workItem);
            $this->assertArrayHasKey('unit_name', $workItem);

            // cost_type
            $this->assertArrayHasKey('cost_type_id', $workItem);
            $this->assertArrayHasKey('cost_type_name', $workItem);
        }
    }
}
