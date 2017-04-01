<?php

namespace Tests\Feature;

use App\{
    User,
    Project,
    ProjectWork,
    ProjectWorkItem,
    Work,
    WorkItem
};
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectWorkTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetList()
    {
        $project = factory(Project::class)->create();
        $works = factory(ProjectWork::class, 2)->create(['project_id' => $project->id]);
        $this->user = $project->user;

        // work that belongs to other project should not be returned
        factory(ProjectWork::class)->create();

        $this->jsonWithToken('GET', "/api/v1/projects/{$project->id}/works")
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $works->map(function ($work) {
                    $base = array_only($work->toArray(), [
                        'id', 'name', 'amount', 'unit_price', 'engineering_type_id', 'project_id', 'unit_id'
                    ]);

                    return array_merge($base, [
                        'unit_name' => $work->unit->name,
                        'engineering_type_main_title' => $work->engineeringType->main_title,
                        'engineering_type_detailing_title' => $work->engineeringType->detailing_title
                    ]);
                })->toArray()
            ]);
    }

    public function testUserThatIsNotProjectOwnerCanNotGetList()
    {
        $project = factory(Project::class)->create();
        $works = factory(ProjectWork::class, 2)->create(['project_id' => $project->id]);

        $this->jsonWithToken('GET', "/api/v1/projects/{$project->id}/works")
            ->assertStatus(403);
    }

    /**
     * 使用者可以選擇標準工項新增到專案
     */
    public function testCreateWithWorkId()
    {
        $stdWork = factory(Work::class)->create();
        $stdWorkItems = factory(WorkItem::class, 2)->create();
        $stdWork->workItems()->attach($stdWorkItems->pluck('id'), ['amount' => '10.00', 'unit_price' => '0.10']);
        $this->user = factory(User::class)->create();
        $project = factory(Project::class)->create(['user_id' => $this->user->id]);

        $response = $this->jsonWithToken('POST', "/api/v1/projects/{$project->id}/works", [
            'work_id' => $stdWork->id,
            'amount' => '10.55'
        ]);

        $attrs = [
            'name' => $stdWork->name,
            'amount' => '10.55',
            'unit_price' => $stdWork->unit_price,
            'unit_id' => $stdWork->unit->id,
            'engineering_type_id' => $stdWork->engineering_type_id
        ];

        $this->assertDatabaseHas($project->works()->getRelated()->getTable(), $attrs);

        $work = $project->works()->where($attrs)->with('engineeringType', 'unit')->first();

        $data = array_merge(
            array_only($work->toArray(), [
                'id', 'name', 'amount', 'unit_price', 'engineering_type_id', 'project_id', 'unit_id'
            ]), [
                'unit_name' => $work->unit->name,
                'engineering_type_main_title' => $work->engineeringType->main_title,
                'engineering_type_detailing_title' => $work->engineeringType->detailing_title
            ]
        );

        $response->assertStatus(201)->assertExactJson(compact('data'));

        // WorkItem should also be copied
        $stdWork->workItems->each(function ($stdWorkItem) use ($work, $stdWork) {
            $workItem = ProjectWorkItem::where(array_only($stdWorkItem->toArray(), [
                'unit_id', 'cost_type_id', 'name'
            ]))->first();

            $this->assertNotNull($workItem);
            $this->assertDatabaseHas($work->workItems()->getTable(), [
                'project_work_id' => $work->id,
                'project_work_item_id' => $workItem->id,
                'amount' => $stdWorkItem->pivot->amount,
                'unit_price' => $stdWorkItem->pivot->unit_price
            ]);
        });
    }

    /**
     * 使用者可以建立全新專案工項
     */
    public function testCreateNew()
    {
        $this->user = factory(User::class)->create();
        $project = factory(Project::class)->create(['user_id' => $this->user->id]);
        $work = factory(ProjectWork::class)->make();

        $response = $this->jsonWithToken('POST', "/api/v1/projects/{$project->id}/works", [
            'name' => $work->name,
            'amount' => $work->amount,
            'unit_id' => $work->unit_id,
            'engineering_type_id' => $work->engineering_type_id
        ]);

        $attrs = [
            'name' => $work->name,
            'amount' => $work->amount,
            'unit_price' => '0.00',
            'unit_id' => $work->unit_id,
            'engineering_type_id' => $work->engineering_type_id
        ];

        $this->assertDatabaseHas($project->works()->getRelated()->getTable(), $attrs);

        $work = $project->works()->where($attrs)->with('engineeringType', 'unit')->first();

        $data = array_merge(
            array_only($work->toArray(), [
                'id', 'name', 'amount', 'unit_price', 'engineering_type_id', 'project_id', 'unit_id'
            ]), [
                'unit_name' => $work->unit->name,
                'engineering_type_main_title' => $work->engineeringType->main_title,
                'engineering_type_detailing_title' => $work->engineeringType->detailing_title
            ]
        );

        $response->assertStatus(201)->assertExactJson(compact('data'));

        // ProjectWorkItem should be empty for the whole new ProjectWorkItem
        $this->assertSame(0, $work->workItems->count());
    }

    public function testDelete()
    {
        $work = factory(ProjectWork::class)->create();
        $workItems = factory(ProjectWorkItem::class, 2)->create();
        $work->workItems()->attach($workItems->pluck('id'), ['amount' => '10.00', 'unit_price' => '0.10']);
        $projectId = $work->project_id;
        $this->user = $work->project->user;

        $this->jsonWithToken('DELETE', "/api/v1/projects/{$projectId}/works/{$work->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing($work->getTable(), ['id' => $work->id]);
        $workItems->each(function ($workItem) use ($work) {
            $this->assertDatabaseHas($work->workItems()->getRelated()->getTable(), ['id' => $workItem->id]);
            $this->assertDatabaseMissing($work->workItems()->getTable(), [
                'project_work_id' => $work->id,
                'project_work_item_id' => $workItem->id
            ]);
        });
    }

    public function testCanNotDeleteIfUserIsNotProjectOwner()
    {
        $work = factory(ProjectWork::class)->create();
        $projectId = $work->project_id;

        $this->jsonWithToken('DELETE', "/api/v1/projects/{$projectId}/works/{$work->id}")
            ->assertStatus(403);
    }

    public function testCanNotDeleteIfWorkNotBelongsToProject()
    {
        $work = factory(ProjectWork::class)->create();
        $otherProject = factory(Project::class)->create();
        $this->user = $otherProject->user;

        $this->jsonWithToken('DELETE', "/api/v1/projects/{$otherProject->id}/works/{$work->id}")
            ->assertStatus(400);
    }

    public function testGetStats()
    {
        $project = factory(Project::class)->create();
        $workItems = factory(ProjectWorkItem::class, 3)->create();
        $works = factory(ProjectWork::class, 2)->create(['project_id' => $project->id, 'unit_price' => '0.00']);

        $works[0]->workItems()->attach([
            $workItems[0]->id, $workItems[1]->id
        ], ['amount' => '10.00', 'unit_price' => '0.10']);

        $works[1]->workItems()->attach([
            $workItems[1]->id, $workItems[2]->id
        ], ['amount' => '10.00', 'unit_price' => '0.10']);

        $this->user = $project->user;
        $this->jsonWithToken('GET', "/api/v1/projects/{$project->id}/works/stats")
            ->assertStatus(200)
            ->assertExactJson([
                'data' => [
                    'total' => '4.00',
                    'costTypes' => [
                        [
                            'cost_type_id' => $workItems[0]->costType->id,
                            'cost_type_name' => $workItems[0]->costType->name,
                            'sum' => '1.00'
                        ],
                        [
                            'cost_type_id' => $workItems[1]->costType->id,
                            'cost_type_name' => $workItems[1]->costType->name,
                            'sum' => '2.00'
                        ],
                        [
                            'cost_type_id' => $workItems[2]->costType->id,
                            'cost_type_name' => $workItems[2]->costType->name,
                            'sum' => '1.00'
                        ]
                    ]
                ]
            ]);
    }
}
