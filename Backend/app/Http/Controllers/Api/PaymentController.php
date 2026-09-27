<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query()->with(['event', 'customer', 'invoice']);

        if ($eventId = $request->query('event_id')) {
            $query->where('event_id', $eventId);
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
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:mobile_money,bank_transfer,card,cash'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (! empty($data['invoice_id'])) {
            $invoice = \App\Models\Invoice::findOrFail($data['invoice_id']);
            if ($data['amount'] > $invoice->balance) {
                return response()->json([
                    'message' => "Payment of {$data['amount']} exceeds the outstanding balance of {$invoice->balance}.",
                ], 422);
            }
        }

        $payment = DB::transaction(function () use ($data, $request) {
            $payment = Payment::create([
                'payment_reference' => 'PMT-'.strtoupper(Str::random(8)),
                'event_id' => $data['event_id'],
                'customer_id' => $data['customer_id'],
                'invoice_id' => $data['invoice_id'] ?? null,
                'amount' => $data['amount'],
                'method' => $data['method'],
                'status' => 'completed',
                'is_simulated' => true,
                'paid_at' => now(),
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $request->user()->id,
            ]);

            if (! empty($data['invoice_id'])) {
                $invoice = \App\Models\Invoice::findOrFail($data['invoice_id']);
                $invoice->amount_paid += $data['amount'];
                $invoice->balance = max($invoice->total - $invoice->amount_paid, 0);
                $invoice->status = $invoice->balance <= 0 ? 'paid' : 'partial';
                $invoice->save();
            }

            $event = $payment->event;
            if ($event->status === 'deposit') {
                $event->update(['status' => 'confirmed']);
            }

            if ($event->customer->user_id) {
                Notification::create([
                    'user_id' => $event->customer->user_id,
                    'title' => 'Payment received.',
                    'body' => "We've recorded a payment of {$data['amount']} for {$event->name}.",
                    'type' => 'payment',
                ]);
            }

            return $payment;
        });

        return response()->json($payment->load(['event', 'invoice']), 201);
    }

    public function show(Payment $payment)
    {
        return response()->json($payment->load(['event', 'customer', 'invoice']));
    }
}
