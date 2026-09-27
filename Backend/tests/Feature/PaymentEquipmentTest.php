<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentEquipmentTest extends TestCase
{
    use RefreshDatabase;

    private function finance(): User
    {
        return User::create(['name' => 'Finance', 'email' => 'finance@test.com', 'password' => bcrypt('password'), 'role' => 'finance']);
    }

    private function manager(): User
    {
        return User::create(['name' => 'Manager', 'email' => 'manager@test.com', 'password' => bcrypt('password'), 'role' => 'manager']);
    }

    public function test_payment_updates_invoice_balance(): void
    {
        $customer = Customer::create(['name' => 'Jane Doe']);
        $event = Event::create(['customer_id' => $customer->id, 'name' => 'Test Event', 'event_date' => now()->addDays(5), 'status' => 'deposit']);
        $invoice = Invoice::create(['invoice_number' => 'INV-1', 'event_id' => $event->id, 'total' => 100000, 'amount_paid' => 0, 'balance' => 100000, 'status' => 'unpaid']);

        $response = $this->actingAs($this->finance(), 'sanctum')->postJson('/api/payments', [
            'event_id' => $event->id, 'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'amount' => 40000, 'method' => 'cash',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'amount_paid' => 40000, 'balance' => 60000, 'status' => 'partial']);
    }

    public function test_payment_cannot_exceed_invoice_balance(): void
    {
        $customer = Customer::create(['name' => 'Jane Doe']);
        $event = Event::create(['customer_id' => $customer->id, 'name' => 'Test Event', 'event_date' => now()->addDays(5), 'status' => 'deposit']);
        $invoice = Invoice::create(['invoice_number' => 'INV-1', 'event_id' => $event->id, 'total' => 100000, 'amount_paid' => 0, 'balance' => 100000, 'status' => 'unpaid']);

        $response = $this->actingAs($this->finance(), 'sanctum')->postJson('/api/payments', [
            'event_id' => $event->id, 'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'amount' => 150000, 'method' => 'cash',
        ]);

        $response->assertStatus(422);
    }

    public function test_equipment_reservation_prevents_over_allocation(): void
    {
        $customer = Customer::create(['name' => 'Jane Doe']);
        $eventA = Event::create(['customer_id' => $customer->id, 'name' => 'Event A', 'event_date' => '2027-01-10', 'status' => 'confirmed']);
        $eventB = Event::create(['customer_id' => $customer->id, 'name' => 'Event B', 'event_date' => '2027-01-10', 'status' => 'confirmed']);
        $chairs = Equipment::create(['name' => 'Chairs', 'total_quantity' => 100]);

        $manager = $this->manager();

        $first = $this->actingAs($manager, 'sanctum')->postJson("/api/equipment/{$chairs->id}/reserve", [
            'event_id' => $eventA->id, 'quantity' => 80, 'start_date' => '2027-01-10', 'end_date' => '2027-01-10',
        ]);
        $first->assertStatus(201);

        $second = $this->actingAs($manager, 'sanctum')->postJson("/api/equipment/{$chairs->id}/reserve", [
            'event_id' => $eventB->id, 'quantity' => 50, 'start_date' => '2027-01-10', 'end_date' => '2027-01-10',
        ]);
        $second->assertStatus(422);
    }

    public function test_dashboard_statistics_reflect_seeded_data(): void
    {
        $customer = Customer::create(['name' => 'Jane Doe']);
        Event::create(['customer_id' => $customer->id, 'name' => 'Event A', 'event_date' => now()->addDays(5), 'status' => 'confirmed']);
        Event::create(['customer_id' => $customer->id, 'name' => 'Event B', 'event_date' => now()->addDays(5), 'status' => 'enquiry']);

        $response = $this->actingAs($this->manager(), 'sanctum')->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('stats.total_events', 2)
            ->assertJsonPath('stats.pending_enquiries', 1);
    }
}
