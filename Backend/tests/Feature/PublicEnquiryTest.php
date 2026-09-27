<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEnquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_enquiry_creates_customer_event_and_selected_services(): void
    {
        $eventType = EventType::create(['name' => 'Wedding']);
        $service = Service::create([
            'name' => 'Planning',
            'price' => 125000,
            'pricing_type' => 'fixed',
            'active' => true,
        ]);

        $response = $this->postJson('/api/public/request-quote', [
            'name' => 'Taylor Guest',
            'email' => 'taylor@example.test',
            'event_type_id' => $eventType->id,
            'event_date' => now()->addMonth()->toDateString(),
            'guest_count' => 80,
            'venue' => 'Kampala',
            'budget' => 2500000,
            'message' => 'Please contact me about planning.',
            'service_ids' => [$service->id],
        ]);

        $event = Event::with('services')->first();

        $response->assertCreated()
            ->assertJsonPath('reference', 'ENQ-'.$event?->id);

        $this->assertDatabaseHas('customers', ['email' => 'taylor@example.test']);
        $this->assertDatabaseHas('events', [
            'customer_id' => Customer::where('email', 'taylor@example.test')->value('id'),
            'event_type_id' => $eventType->id,
            'status' => 'enquiry',
            'venue' => 'Kampala',
        ]);
        $this->assertSame(1, $event?->services->count());
        $this->assertEquals(125000, $event?->services->first()?->pivot->unit_price);
    }

    public function test_invalid_enquiry_does_not_create_a_customer_or_event(): void
    {
        $response = $this->postJson('/api/public/request-quote', [
            'name' => 'Taylor Guest',
            'email' => 'not-an-email',
            'event_date' => 'not-a-date',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'event_date']);

        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('events', 0);
    }
}
