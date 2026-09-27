<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::create(['name' => 'Manager', 'email' => 'manager@test.com', 'password' => bcrypt('password'), 'role' => 'manager']);
    }

    public function test_manager_can_create_customer(): void
    {
        $response = $this->actingAs($this->manager(), 'sanctum')->postJson('/api/customers', [
            'name' => 'Jane Doe', 'email' => 'jane@test.com', 'phone' => '0700000000',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('customers', ['email' => 'jane@test.com']);
    }

    public function test_manager_can_update_customer(): void
    {
        $customer = Customer::create(['name' => 'Jane Doe', 'email' => 'jane@test.com']);

        $response = $this->actingAs($this->manager(), 'sanctum')->putJson("/api/customers/{$customer->id}", ['name' => 'Jane Updated']);

        $response->assertStatus(200)->assertJsonFragment(['name' => 'Jane Updated']);
    }

    public function test_manager_can_archive_customer(): void
    {
        $customer = Customer::create(['name' => 'Jane Doe', 'email' => 'jane@test.com']);

        $response = $this->actingAs($this->manager(), 'sanctum')->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_customer_role_cannot_access_customer_management(): void
    {
        $customerUser = User::create(['name' => 'Cust', 'email' => 'cust@test.com', 'password' => bcrypt('password'), 'role' => 'customer']);

        $response = $this->actingAs($customerUser, 'sanctum')->getJson('/api/customers');

        $response->assertStatus(403);
    }
}
