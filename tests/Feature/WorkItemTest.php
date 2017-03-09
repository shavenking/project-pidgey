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
            $this->assertWorkItemAttributes($workItem);
        }
    }

    public function testAddNewWorkItemToWork()
    {
        $work = factory(Work::class)->create();
        $workItem = factory(WorkItem::class)->make();
        $amount = '10.21';
        $unitPrice = '11.01';

        $response = $this->json('POST', "/api/v1/works/{$work->id}/work-items", [
            'name' => $workItem->name,
            'unit_id' => $workItem->unit_id,
            'cost_type_id' => $workItem->cost_type_id,
            'amount' => $amount,
            'unit_price' => $unitPrice
        ])->assertStatus(201)->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
        $this->assertWorkItemAttributes($response['data']);
        $this->assertDatabaseHas((new Work)->getTable(), [
            'id' => $work->id,
            'unit_price' => bcadd($work->unit_price, bcmul($amount, $unitPrice, 2), 2)
        ]);
    }

    public function testAddExistingWorkItemToWork()
    {
        $work = factory(Work::class)->create();
        $workItem = factory(WorkItem::class)->create();
        $amount = '10.21';
        $unitPrice = '11.01';

        $response = $this->json('POST', "/api/v1/works/{$work->id}/work-items", [
            'work_item_id' => $workItem->id,
            'amount' => $amount,
            'unit_price' => $unitPrice
        ])->assertStatus(201)->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
        $this->assertWorkItemAttributes($response['data']);
        $this->assertDatabaseHas((new Work)->getTable(), [
            'id' => $work->id,
            'unit_price' => bcadd($work->unit_price, bcmul($amount, $unitPrice, 2), 2)
        ]);
    }

    private function assertWorkItemAttributes(array $workItem)
    {
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
