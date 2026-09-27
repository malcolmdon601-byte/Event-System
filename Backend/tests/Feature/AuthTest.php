<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(): void
    {
        User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => Hash::make('password'), 'role' => 'super_admin']);

        $response = $this->postJson('/api/auth/login', ['email' => 'admin@test.com', 'password' => 'password']);

        $response->assertStatus(200)->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => Hash::make('password'), 'role' => 'super_admin']);

        $response = $this->postJson('/api/auth/login', ['email' => 'admin@test.com', 'password' => 'wrong']);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => Hash::make('password'), 'role' => 'super_admin']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }
}
