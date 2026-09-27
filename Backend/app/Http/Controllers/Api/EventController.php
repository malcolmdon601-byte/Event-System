<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query()->with(['customer', 'eventType'])->withCount(['tasks', 'staff']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($customerId = $request->query('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->orderByDesc('event_date')->paginate(15));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => ['required', 'exists:customers,id'],
            'event_type_id' => ['nullable', 'exists:event_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'guest_count' => ['nullable', 'integer', 'min:0'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:enquiry,quotation,deposit,confirmed,planning,ready,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['status'] = $data['status'] ?? 'enquiry';
        $data['created_by'] = $request->user()->id;

        $event = Event::create($data);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'event.created',
            'subject_type' => Event::class,
            'subject_id' => $event->id,
            'description' => "Created event \"{$event->name}\"",
        ]);

        return response()->json($event->load('customer'), 201);
    }

    public function show(Event $event)
    {
        return response()->json($event->load([
            'customer', 'eventType', 'services', 'staff', 'vendors',
            'quotations', 'invoices', 'payments', 'equipmentReservations.equipment',
            'tasks.assignedStaff', 'messages.sender',
        ]));
    }

    public function update(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'event_type_id' => ['nullable', 'exists:event_types,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'event_date' => ['sometimes', 'required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'guest_count' => ['nullable', 'integer', 'min:0'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'in:enquiry,quotation,deposit,confirmed,planning,ready,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        $event->update($validator->validated());

        return response()->json($event->fresh(['customer', 'eventType']));
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(['message' => 'Event deleted.']);
    }

    public function attachServices(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'exists:services,id'],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request, $event) {
            $sync = [];
            foreach ($request->services as $row) {
                $service = Service::findOrFail($row['service_id']);
                $sync[$service->id] = [
                    'quantity' => $row['quantity'],
                    'unit_price' => $service->price,
                    'subtotal' => $service->price * $row['quantity'],
                ];
            }
            $event->services()->sync($sync);
        });

        return response()->json($event->fresh('services'));
    }

    public function assignStaff(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'staff_ids' => ['required', 'array'],
            'staff_ids.*' => ['exists:staff,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        $event->staff()->sync($request->staff_ids);

        return response()->json($event->fresh('staff'));
    }

    public function assignVendors(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'vendor_ids' => ['required', 'array'],
            'vendor_ids.*' => ['exists:vendors,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        $event->vendors()->sync($request->vendor_ids);

        return response()->json($event->fresh('vendors'));
    }
}
