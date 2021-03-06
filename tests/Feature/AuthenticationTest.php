<?php

namespace Tests\Feature;

use App\User;
use JWTAuth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function testRegister()
    {
        $user = factory(User::class)->make();
        $crendentials = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'secret'
        ];

        JWTAuth::shouldReceive('attempt')
            ->once()
            ->with(array_only($crendentials, ['email', 'password']))
            ->andReturn('token');

        $response = $this->json('POST', '/api/v1/users', $crendentials)
            ->assertStatus(201)
            ->assertExactJson([
                'data' => [
                    'token' => 'token'
                ]
            ]);

        $this->assertDatabaseHas($user->getTable(), array_except($crendentials, 'password'));
    }

    public function testLogin()
    {
        $user = factory(User::class)->create();
        $crendentials = ['email' => $user->email, 'password' => 'secret'];

        JWTAuth::shouldReceive('attempt')
            ->once()
            ->with($crendentials)
            ->andReturn('token');

        $this->json('POST', '/api/v1/tokens', $crendentials)
            ->assertStatus(201)
            ->assertExactJson([
                'data' => [
                    'token' => 'token'
                ]
            ]);
    }
}
