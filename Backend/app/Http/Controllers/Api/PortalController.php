<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * Aggregated view for the logged-in customer's own dashboard.
     */
    public function dashboard(Request $request)
    {
        $customer = $request->user()->customer;

        if (! $customer) {
            return response()->json(['message' => 'No customer profile is linked to this account.'], 404);
        }

        $events = $customer->events()->with(['eventType'])->orderByDesc('event_date')->get();
        $quotations = $customer->quotations()->with('items')->orderByDesc('id')->get();
        $payments = $customer->payments()->orderByDesc('id')->get();
        $invoices = \App\Models\Invoice::whereIn('event_id', $events->pluck('id'))->get();

        $nextEvent = $customer->events()
            ->where('event_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('event_date')
            ->first();

        return response()->json([
            'customer' => $customer,
            'events' => $events,
            'quotations' => $quotations,
            'payments' => $payments,
            'invoices' => $invoices,
            'next_event' => $nextEvent,
            'outstanding_balance' => (float) $invoices->sum('balance'),
        ]);
    }
}
