<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PublicController extends Controller
{
    public function services()
    {
        return response()->json(Service::where('active', true)->orderBy('category')->get());
    }

    public function eventTypes()
    {
        return response()->json(EventType::orderBy('name')->get());
    }

    /**
     * Public "Request a Quote" form. Creates (or reuses) a customer record
     * and an enquiry-stage event with no login required.
     */
    public function requestQuote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'event_type_id' => ['nullable', 'exists:event_types,id'],
            'event_date' => ['required', 'date'],
            'guest_count' => ['nullable', 'integer', 'min:1'],
            'venue' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'message' => ['nullable', 'string', 'max:2000'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['exists:services,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $event = DB::transaction(function () use ($data) {
            $customer = Customer::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'phone' => $data['phone'] ?? null]
            );

            $eventType = isset($data['event_type_id']) ? EventType::find($data['event_type_id']) : null;
            $event = Event::create([
                'customer_id' => $customer->id,
                'event_type_id' => $eventType?->id,
                'name' => ($eventType?->name ?? 'Event').' for '.$data['name'],
                'event_date' => $data['event_date'],
                'venue' => $data['venue'] ?? null,
                'guest_count' => $data['guest_count'] ?? null,
                'budget' => $data['budget'] ?? null,
                'status' => 'enquiry',
                'notes' => $data['message'] ?? null,
            ]);

            if (! empty($data['service_ids'])) {
                $services = Service::whereIn('id', $data['service_ids'])->get()->keyBy('id');
                $sync = [];
                foreach ($data['service_ids'] as $serviceId) {
                    $service = $services->get($serviceId);
                    $sync[$serviceId] = ['quantity' => 1, 'unit_price' => $service->price, 'subtotal' => $service->price];
                }
                $event->services()->sync($sync);
            }

            return $event;
        });

        return response()->json([
            'message' => 'Thanks! Your enquiry has been received and our team will send a quotation shortly.',
            'reference' => 'ENQ-'.$event->id,
        ], 201);
    }
}
