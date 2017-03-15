<?php

namespace Tests\Feature;

use App\{
    CostType,
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

    public function testCreate()
    {
        $workItem = factory(WorkItem::class)->make();

        $response = $this->json('POST', '/api/v1/work-items', [
            'name' => $workItem->name,
            'unit_id' => $workItem->unit_id,
            'cost_type_id' => $workItem->cost_type_id
        ])->assertStatus(201);

        $workItem = WorkItem::where(['name' => $workItem->name, 'unit_id' => $workItem->unit_id])
            ->with('unit', 'costType')
            ->first();

        $response->assertExactJson([
            'data' => [
                'id' => $workItem->id,
                'name' => $workItem->name,
                'unit_id' => $workItem->unit_id,
                'unit_name' => $workItem->unit->name,
                'cost_type_id' => $workItem->cost_type_id,
                'cost_type_name' => $workItem->costType->name
            ]
        ]);
    }

    public function testGetListWithoutWork()
    {
        $workItems = factory(WorkItem::class, 2)->create()->map(function (WorkItem $workItem) {
            return array_only(
                $workItem
                    ->setAttribute('unit_name', $workItem->unit->name)
                    ->setAttribute('cost_type_name', $workItem->costType->name)
                    ->toArray(),
                ['id', 'name', 'unit_id', 'unit_name', 'cost_type_id', 'cost_type_name']
            );
        });

        $response = $this->json('GET', '/api/v1/work-items')
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $workItems->toArray()
            ]);
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

    public function testDelete()
    {
        $work = factory(Work::class)->create();
        $workItem = factory(WorkItem::class)->create();
        $work->workItems()->attach($workItem, ['amount' => '0', 'unit_price' => '0']);

        $this->json('DELETE', "/api/v1/works/{$work->id}/work-items/{$workItem->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing($work->workItems()->getTable(), [
            'work_id' => $work->id,
            'work_item_id' => $workItem->id
        ]);
    }

    public function testGetStatsByCostType()
    {
        $costTypes = factory(CostType::class, 2)->create();

        $dataSet = [
            [
                'work_item' => factory(WorkItem::class)->create(['cost_type_id' => $costTypes[0]->id]),
                'amount' => '11.11',
                'unit_price' => '0.11'
            ],
            [
                'work_item' => factory(WorkItem::class)->create(['cost_type_id' => $costTypes[0]->id]),
                'amount' => '94.87',
                'unit_price' => '0.11'
            ],
            [
                'work_item' => factory(WorkItem::class)->create(['cost_type_id' => $costTypes[1]->id]),
                'amount' => '12.34',
                'unit_price' => '0.22'
            ]
        ];

        $work = factory(Work::class)->create();
        foreach ($dataSet as $data) {
            $work->workItems()->attach(
                $data['work_item'],
                array_only($data, ['amount', 'unit_price'])
            );
        }

        $response = $this->json('GET', "/api/v1/works/{$work->id}/work-items/stats")
            ->assertStatus(200)
            ->assertExactJson([
                'data' => [
                    ['cost_type_id' => $costTypes[0]->id, 'cost_type_name' => $costTypes[0]->name, 'sum' => '11.65'],
                    ['cost_type_id' => $costTypes[1]->id, 'cost_type_name' => $costTypes[1]->name, 'sum' => '2.71']
                ]
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
