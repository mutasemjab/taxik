<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderSpam;
use App\Models\OrderDriverNotified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SpamOrderController extends Controller
{
    /**
     * Display a listing of spam orders
     */
    public function index(Request $request)
    {
        $query = OrderSpam::with(['user', 'driver', 'service']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('cancelled_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('cancelled_at', '<=', $request->to_date);
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->where('number', 'like', '%' . $request->search . '%');
        }

        // Get statistics
        $stats = [
            'total' => OrderSpam::count(),
            'user_cancelled' => OrderSpam::where('status', 'user_cancel_order')->count(),
            'driver_cancelled' => OrderSpam::where('status', 'driver_cancel_order')->count(),
            'auto_cancelled' => OrderSpam::where('status', 'cancel_cron_job')->count(),
            'today' => OrderSpam::whereDate('cancelled_at', today())->count(),
        ];

        $spamOrders = $query->orderBy('cancelled_at', 'desc')->paginate(20);

        return view('admin.spam-orders.index', compact('spamOrders', 'stats'));
    }

    /**
     * Display the specified spam order with detailed tracking
     */
    /**
     * Display the specified spam order with detailed tracking
     */
    public function show($id)
    {
        $spamOrder = OrderSpam::with(['user', 'driver', 'service'])
            ->findOrFail($id);

        // ✅ Get original order ID from spam table
        $originalOrderId = $spamOrder->original_order_id;

        if (!$originalOrderId) {
            // Fallback: if original_order_id is not set, try using spam order id
            $originalOrderId = $id;
        }

        // ✅ NOW THIS WORKS: Query using the original order ID
        // Since we removed the foreign key, order_id doesn't become NULL
        $driversNotified = OrderDriverNotified::with('driver')
            ->where('order_id', $originalOrderId)
            ->orderBy('distance_km', 'asc')
            ->get();

        // Get current driver IDs in Firebase
        $currentDriverIdsInFirebase = $this->getDriverIdsFromFirebase($originalOrderId);

        $notifiedDriverIds = $driversNotified->pluck('driver_id')->toArray();
        $assignedDriverId = $spamOrder->driver_id;

        $rejectedDriverIds = array_diff($notifiedDriverIds, $currentDriverIdsInFirebase, [$assignedDriverId]);
        $noResponseDriverIds = array_intersect($notifiedDriverIds, $currentDriverIdsInFirebase);

        $driversRejected = $driversNotified->whereIn('driver_id', $rejectedDriverIds);
        $driversNoResponse = $driversNotified->whereIn('driver_id', $noResponseDriverIds);

        $userCancellationHistory = OrderSpam::where('user_id', $spamOrder->user_id)
            ->where('id', '!=', $id)
            ->orderBy('cancelled_at', 'desc')
            ->take(5)
            ->get();

        $timeMetrics = $this->calculateTimeMetrics($spamOrder);

        $stats = [
            'total_notified' => $driversNotified->count(),
            'total_rejected' => count($rejectedDriverIds),
            'no_response' => count($noResponseDriverIds),
            'assigned' => $assignedDriverId ? 1 : 0,
        ];

        return view('admin.spam-orders.show', compact(
            'spamOrder',
            'driversNotified',
            'driversRejected',
            'driversNoResponse',
            'userCancellationHistory',
            'timeMetrics',
            'stats'
        ));
    }

    /**
     * Get driver IDs currently in Firebase for this order
     */
    private function getDriverIdsFromFirebase($orderId)
    {
        try {
            $projectId = config('firebase.project_id');
            $baseUrl = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents";

            $response = Http::timeout(10)->get(
                "{$baseUrl}/ride_requests/{$orderId}"
            );

            if (!$response->successful()) {
                \Log::warning("Order {$orderId} not found in Firebase");
                return [];
            }

            $orderData = $response->json();

            // Extract driver_ids array
            $driverIdsField = $orderData['fields']['driver_ids'] ?? null;

            if (!$driverIdsField || !isset($driverIdsField['arrayValue']['values'])) {
                return [];
            }

            $driverIds = [];
            foreach ($driverIdsField['arrayValue']['values'] as $value) {
                $id = (int)($value['integerValue'] ?? $value['stringValue'] ?? 0);
                if ($id > 0) {
                    $driverIds[] = $id;
                }
            }

            return $driverIds;
        } catch (\Exception $e) {
            \Log::error("Error getting driver IDs from Firebase for order {$orderId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get analytics dashboard for spam orders
     */
    public function analytics(Request $request)
    {
        $period = $request->input('period', '30'); // days

        // Cancellation reasons breakdown
        $cancellationReasons = OrderSpam::select('reason_for_cancel', DB::raw('count(*) as count'))
            ->whereDate('cancelled_at', '>=', now()->subDays($period))
            ->whereNotNull('reason_for_cancel')
            ->groupBy('reason_for_cancel')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        // Top users who cancel orders
        $topCancellingUsers = OrderSpam::select('user_id', DB::raw('count(*) as cancel_count'))
            ->with('user')
            ->whereDate('cancelled_at', '>=', now()->subDays($period))
            ->groupBy('user_id')
            ->orderBy('cancel_count', 'desc')
            ->take(10)
            ->get();

        // Services with most cancellations
        $servicesCancellations = OrderSpam::select('service_id', DB::raw('count(*) as cancel_count'))
            ->with('service')
            ->whereDate('cancelled_at', '>=', now()->subDays($period))
            ->groupBy('service_id')
            ->orderBy('cancel_count', 'desc')
            ->get();

        // Cancellation trends by day
        $dailyTrends = OrderSpam::select(
            DB::raw('DATE(cancelled_at) as date'),
            DB::raw('count(*) as count')
        )
            ->whereDate('cancelled_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Average time before cancellation
        $avgCancellationTime = OrderSpam::whereDate('cancelled_at', '>=', now()->subDays($period))
            ->get()
            ->map(function ($order) {
                return $order->cancelled_at->diffInMinutes($order->created_at);
            })
            ->average();

        return view('admin.spam-orders.analytics', compact(
            'cancellationReasons',
            'topCancellingUsers',
            'servicesCancellations',
            'dailyTrends',
            'avgCancellationTime',
            'period'
        ));
    }

    /**
     * Delete spam order permanently
     */
    public function destroy($id)
    {
        $spamOrder = OrderSpam::findOrFail($id);
        $spamOrder->delete();

        return redirect()->route('spam-orders.index')
            ->with('success', __('messages.Spam_Order_Deleted_Successfully'));
    }

    /**
     * Bulk delete spam orders
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:order_spam,id'
        ]);

        OrderSpam::whereIn('id', $request->order_ids)->delete();

        return redirect()->route('spam-orders.index')
            ->with('success', count($request->order_ids) . ' spam orders deleted successfully');
    }

    /**
     * Calculate time metrics for the spam order
     */
    private function calculateTimeMetrics($spamOrder)
    {
        $metrics = [];

        if ($spamOrder->cancelled_at && $spamOrder->created_at) {
            $minutes = $spamOrder->created_at->diffInMinutes($spamOrder->cancelled_at);
            $metrics['time_to_cancel'] = $minutes;
            $metrics['time_to_cancel_formatted'] = $this->formatMinutes($minutes);
        }

        if ($spamOrder->trip_started_at) {
            $metrics['trip_started'] = true;
            $metrics['trip_duration'] = $spamOrder->trip_started_at->diffInMinutes($spamOrder->cancelled_at ?? now());
        } else {
            $metrics['trip_started'] = false;
        }

        return $metrics;
    }

    /**
     * Format minutes to human readable format
     */
    private function formatMinutes($minutes)
    {
        if ($minutes < 60) {
            return $minutes . ' دقيقة';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours . ' ساعة ' . $remainingMinutes . ' دقيقة';
    }
}
