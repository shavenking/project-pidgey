<?php

namespace Tests\Feature;

use App\{
    User,
    Project,
    ProjectWork,
    Work
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
                        'id', 'name', 'amount', 'unit_price', 'engineering_type_id', 'project_id'
                    ]);

                    return array_merge($base, [
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
            'engineering_type_id' => $stdWork->engineering_type_id
        ];

        $this->assertDatabaseHas($project->works()->getRelated()->getTable(), $attrs);

        $work = $project->works()->where($attrs)->with('engineeringType')->first();

        $data = array_merge(
            array_only($work->toArray(), ['id', 'name', 'amount', 'unit_price', 'engineering_type_id', 'project_id']),
            [
                'engineering_type_main_title' => $work->engineeringType->main_title,
                'engineering_type_detailing_title' => $work->engineeringType->detailing_title
            ]
        );

        $response->assertStatus(201)->assertExactJson(compact('data'));

        // TODO: 驗證標準工料項目也要加入
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
            'engineering_type_id' => $work->engineering_type_id
        ]);

        $attrs = [
            'name' => $work->name,
            'amount' => $work->amount,
            'unit_price' => '0.00',
            'engineering_type_id' => $work->engineering_type_id
        ];

        $this->assertDatabaseHas($project->works()->getRelated()->getTable(), $attrs);

        $work = $project->works()->where($attrs)->with('engineeringType')->first();

        $data = array_merge(
            array_only($work->toArray(), ['id', 'name', 'amount', 'unit_price', 'engineering_type_id', 'project_id']),
            [
                'engineering_type_main_title' => $work->engineeringType->main_title,
                'engineering_type_detailing_title' => $work->engineeringType->detailing_title
            ]
        );

        $response->assertStatus(201)->assertExactJson(compact('data'));

        // 需檢查全新的專案工項沒有任何專案工料
    }

    public function testDelete()
    {
        $work = factory(ProjectWork::class)->create();
        $projectId = $work->project_id;
        $this->user = $work->project->user;

        $this->jsonWithToken('DELETE', "/api/v1/projects/{$projectId}/works/{$work->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing($work->getTable(), ['id' => $work->id]);
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
}
