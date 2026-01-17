<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TrackingController extends Controller
{
    protected $projectId;
    protected $baseUrl;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id');
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }

    /**
     * Show tracking page
     */
    public function show($token)
    {
        $order = Order::with(['user', 'driver', 'service'])
            ->where('tracking_token', $token)
            ->first();

        if (!$order) {
            return view('not-found');
        }

        // Check if order can be tracked
        if (!$order->canBeTracked()) {
            return view('not-available', compact('order'));
        }

        return view('tracking-live', compact('order'));
    }

    /**
     * Get real-time tracking data API
     */
    public function getData($token)
    {
        $order = Order::with(['user', 'driver', 'service'])
            ->where('tracking_token', $token)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check if order can be tracked
        if (!$order->canBeTracked()) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be tracked',
                'reason' => 'Order status: ' . $order->status->value
            ], 403);
        }

        // Get driver location from Firestore if driver is assigned
        $driverLocation = null;
        if ($order->driver_id) {
            $driverLocation = $this->getDriverLocationFromFirestore($order->driver_id);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'number' => $order->number,
                    'status' => $order->status->value,
                    'status_text' => $order->getTrackingStatusText(),
                    'pickup' => [
                        'name' => $order->pick_name,
                        'lat' => $order->pick_lat,
                        'lng' => $order->pick_lng,
                    ],
                    'dropoff' => [
                        'name' => $order->drop_name,
                        'lat' => $order->drop_lat,
                        'lng' => $order->drop_lng,
                    ],
                    'estimated_time' => $order->estimated_time,
                    'trip_started_at' => $order->trip_started_at,
                ],
                'driver' => $order->driver ? [
                    'name' => $order->driver->name,
                    'phone' => $order->driver->phone,
                    'photo' => $order->driver->photo ? asset('assets/admin/uploads/' . $order->driver->photo) : null,
                    'location' => $driverLocation,
                    'car_info' => [
                        'model' => $order->driver->model,
                        'color' => $order->driver->color,
                        'plate_number' => $order->driver->plate_number,
                    ],
                ] : null,
                'service' => [
                    'name_en' => $order->service->name_en,
                    'name_ar' => $order->service->name_ar,
                ],
            ]
        ]);
    }

    /**
     * Get driver location from Firestore
     */
    private function getDriverLocationFromFirestore($driverId)
    {
        try {
            $response = Http::timeout(5)->get(
                "{$this->baseUrl}/drivers/{$driverId}"
            );

            if (!$response->successful()) {
                return null;
            }

            $driverData = $response->json();
            $fields = $driverData['fields'] ?? [];

            $lat = $this->getFieldValue($fields, 'lat');
            $lng = $this->getFieldValue($fields, 'lng');

            if ($lat && $lng) {
                return [
                    'lat' => (float)$lat,
                    'lng' => (float)$lng,
                    'updated_at' => $this->getFieldValue($fields, 'updated_at'),
                ];
            }

            return null;
        } catch (\Exception $e) {
            \Log::error("Failed to get driver location from Firestore: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper method to extract value from Firestore field
     */
    private function getFieldValue($fields, $fieldName)
    {
        if (!isset($fields[$fieldName])) {
            return null;
        }

        $field = $fields[$fieldName];

        if (isset($field['stringValue'])) {
            return $field['stringValue'];
        }
        if (isset($field['integerValue'])) {
            return $field['integerValue'];
        }
        if (isset($field['doubleValue'])) {
            return $field['doubleValue'];
        }
        if (isset($field['timestampValue'])) {
            return $field['timestampValue'];
        }

        return null;
    }
}