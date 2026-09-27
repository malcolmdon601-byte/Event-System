<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::query()->with(['customer', 'event']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($customerId = $request->query('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        return response()->json($query->orderByDesc('id')->paginate(15));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => ['required', 'exists:events,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $event = Event::findOrFail($data['event_id']);

        $quotation = DB::transaction(function () use ($data, $event) {
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }
            $discount = $data['discount'] ?? 0;
            $total = max($subtotal - $discount, 0);
            $deposit = $data['deposit_amount'] ?? 0;

            $quotation = Quotation::create([
                'quotation_number' => 'QT-'.strtoupper(Str::random(6)),
                'event_id' => $event->id,
                'customer_id' => $event->customer_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'deposit_amount' => $deposit,
                'balance' => $total,
                'valid_until' => $data['valid_until'] ?? now()->addDays(14),
                'status' => 'sent',
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? 'Prices are valid until the stated date. A deposit is required to confirm booking.',
            ]);

            foreach ($data['items'] as $item) {
                $quotation->items()->create([
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            if ($event->status === 'enquiry') {
                $event->update(['status' => 'quotation']);
            }

            if ($event->customer->user_id) {
                Notification::create([
                    'user_id' => $event->customer->user_id,
                    'title' => "Your quotation {$quotation->quotation_number} is ready.",
                    'body' => 'View and accept your quotation in the customer portal.',
                    'type' => 'quotation',
                ]);
            }

            return $quotation;
        });

        return response()->json($quotation->load('items'), 201);
    }

    public function show(Quotation $quotation)
    {
        return response()->json($quotation->load(['items', 'customer', 'event']));
    }

    public function accept(Request $request, Quotation $quotation)
    {
        $user = $request->user();

        if ($user && $user->role === 'customer' && $quotation->event->customer?->user_id !== $user->id) {
            return response()->json(['message' => 'You can only accept your own quotation.'], 403);
        }

        if ($quotation->status === 'accepted') {
            return response()->json(['message' => 'This quotation has already been accepted.'], 422);
        }

        if (in_array($quotation->status, ['rejected', 'expired'], true)) {
            return response()->json(['message' => 'This quotation can no longer be accepted.'], 422);
        }

        DB::transaction(function () use ($quotation) {
            $quotation->update(['status' => 'accepted', 'accepted_at' => now()]);
            $quotation->event->update(['status' => 'deposit']);

            Invoice::create([
                'invoice_number' => 'INV-'.strtoupper(Str::random(6)),
                'event_id' => $quotation->event_id,
                'quotation_id' => $quotation->id,
                'total' => $quotation->total,
                'amount_paid' => 0,
                'balance' => $quotation->total,
                'status' => 'unpaid',
                'due_date' => now()->addDays(30),
            ]);
        });

        return response()->json($quotation->fresh(['items', 'event']));
    }

    public function reject(Request $request, Quotation $quotation)
    {
        $user = $request->user();

        if ($user && $user->role === 'customer' && $quotation->event->customer?->user_id !== $user->id) {
            return response()->json(['message' => 'You can only reject your own quotation.'], 403);
        }

        $quotation->update(['status' => 'rejected']);

        return response()->json($quotation);
    }
}
