<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventQuotationTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::create(['name' => 'Manager', 'email' => 'manager@test.com', 'password' => bcrypt('password'), 'role' => 'manager']);
    }

    public function test_manager_can_create_event(): void
    {
        $customer = Customer::create(['name' => 'Jane Doe']);

        $response = $this->actingAs($this->manager(), 'sanctum')->postJson('/api/events', [
            'customer_id' => $customer->id, 'name' => 'Jane\'s Birthday', 'event_date' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertStatus(201)->assertJsonFragment(['status' => 'enquiry']);
    }

    public function test_event_status_can_be_updated(): void
    {
        $customer = Customer::create(['name' => 'Jane Doe']);
        $event = Event::create(['customer_id' => $customer->id, 'name' => 'Test Event', 'event_date' => now()->addDays(5), 'status' => 'enquiry']);

        $response = $this->actingAs($this->manager(), 'sanctum')->putJson("/api/events/{$event->id}", ['status' => 'confirmed']);

        $response->assertStatus(200)->assertJsonFragment(['status' => 'confirmed']);
    }

    public function test_quotation_totals_are_calculated_correctly(): void
    {
        $customer = Customer::create(['name' => 'Jane Doe']);
        $event = Event::create(['customer_id' => $customer->id, 'name' => 'Test Event', 'event_date' => now()->addDays(5), 'status' => 'enquiry']);

        $response = $this->actingAs($this->manager(), 'sanctum')->postJson('/api/quotations', [
            'event_id' => $event->id,
            'discount' => 10000,
            'items' => [
                ['description' => 'Catering', 'quantity' => 10, 'unit_price' => 5000],
                ['description' => 'Décor', 'quantity' => 1, 'unit_price' => 20000],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('subtotal', '70000.00');
        $response->assertJsonPath('total', '60000.00');
    }

    public function test_customer_can_accept_own_quotation(): void
    {
        $customerUser = User::create(['name' => 'Cust', 'email' => 'cust@test.com', 'password' => bcrypt('password'), 'role' => 'customer']);
        $customer = Customer::create(['user_id' => $customerUser->id, 'name' => 'Cust']);
        $event = Event::create(['customer_id' => $customer->id, 'name' => 'Test Event', 'event_date' => now()->addDays(5), 'status' => 'quotation']);

        $manager = $this->manager();
        $quoteResponse = $this->actingAs($manager, 'sanctum')->postJson('/api/quotations', [
            'event_id' => $event->id,
            'items' => [['description' => 'Catering', 'quantity' => 1, 'unit_price' => 100000]],
        ]);
        $quotationId = $quoteResponse->json('id');

        $response = $this->actingAs($customerUser, 'sanctum')->postJson("/api/quotations/{$quotationId}/accept");

        $response->assertStatus(200)->assertJsonFragment(['status' => 'accepted']);
        $this->assertDatabaseHas('invoices', ['quotation_id' => $quotationId]);
    }
}
