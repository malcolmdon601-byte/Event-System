<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::withSum('reservations as reserved_quantity', 'quantity')->orderBy('name')->get();

        $equipment->each(function ($item) {
            $item->available_quantity = max($item->total_quantity - (int) $item->reserved_quantity, 0);
        });

        return response()->json($equipment);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'total_quantity' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        return response()->json(Equipment::create($validator->validated()), 201);
    }

    /**
     * Reserve equipment for an event's date range, preventing over-allocation
     * against equipment that is already reserved for overlapping dates.
     */
    public function reserve(Request $request, Equipment $equipment)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => ['required', 'exists:events,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $alreadyReserved = (int) EquipmentReservation::where('equipment_id', $equipment->id)
            ->whereDate('start_date', '<=', $data['end_date'])
            ->whereDate('end_date', '>=', $data['start_date'])
            ->sum('quantity');

        $available = $equipment->total_quantity - $alreadyReserved;

        if ($data['quantity'] > $available) {
            return response()->json([
                'message' => "Only {$available} of \"{$equipment->name}\" are available for those dates. {$alreadyReserved} are already reserved.",
            ], 422);
        }

        $reservation = EquipmentReservation::create([
            'equipment_id' => $equipment->id,
            'event_id' => $data['event_id'],
            'quantity' => $data['quantity'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        return response()->json($reservation->load('equipment', 'event'), 201);
    }

    public function destroyReservation(EquipmentReservation $reservation)
    {
        $reservation->delete();

        return response()->json(['message' => 'Reservation cancelled.']);
    }
}
