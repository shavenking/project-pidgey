<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use JWTAuth;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    public function testGetProfile()
    {
        $user = factory(User::class)->create();
        $token = JWTAuth::attempt(['email' => $user->email, 'password' => 'secret']);

        $this->json('GET', '/api/v1/profile', [], ['Authorization' => "Bearer $token"])
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $user->toArray()
            ]);
    }
}
