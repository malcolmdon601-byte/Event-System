<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEvents = Event::count();
        $upcomingEvents = Event::where('event_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();
        $pendingEnquiries = Event::where('status', 'enquiry')->count();
        $confirmedBookings = Event::whereIn('status', ['confirmed', 'planning', 'ready'])->count();
        $revenue = (float) Payment::where('status', 'completed')->sum('amount');
        $outstanding = (float) \App\Models\Invoice::sum('balance');

        $eventsByStatus = Event::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $monthlyRevenueQuery = Payment::where('status', 'completed')
            ->whereNotNull('paid_at');

        if (DB::getDriverName() === 'sqlite') {
            $monthlyRevenue = $monthlyRevenueQuery
                ->selectRaw("strftime('%Y-%m', paid_at) as month, sum(amount) as total")
                ->groupByRaw("strftime('%Y-%m', paid_at)")
                ->orderByRaw("strftime('%Y-%m', paid_at) asc")
                ->limit(12)
                ->get();
        } else {
            $monthlyRevenue = $monthlyRevenueQuery
                ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, sum(amount) as total")
                ->groupByRaw("DATE_FORMAT(paid_at, '%Y-%m')")
                ->orderByRaw("DATE_FORMAT(paid_at, '%Y-%m') asc")
                ->limit(12)
                ->get();
        }

        $upcoming = Event::with('customer')
            ->where('event_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('event_date')
            ->limit(6)
            ->get();

        $recentPayments = Payment::with(['event', 'customer'])->orderByDesc('id')->limit(6)->get();

        return response()->json([
            'stats' => [
                'total_events' => $totalEvents,
                'upcoming_events' => $upcomingEvents,
                'pending_enquiries' => $pendingEnquiries,
                'confirmed_bookings' => $confirmedBookings,
                'revenue' => $revenue,
                'outstanding' => $outstanding,
            ],
            'events_by_status' => $eventsByStatus,
            'monthly_revenue' => $monthlyRevenue,
            'upcoming_events' => $upcoming,
            'recent_payments' => $recentPayments,
        ]);
    }
}
