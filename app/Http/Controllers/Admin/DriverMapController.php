<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Driver;

class DriverMapController extends Controller
{
    protected $projectId;
    protected $baseUrl;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id');
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }

    /**
     * Display the driver locations map
     */
    public function index()
    {
        return view('admin.drivers.map');
    }

    /**
     * Get all driver locations from Firebase using REST API
     */
    /**
     * Get all driver locations from Firebase using REST API
     */
    public function getDriverLocations(Request $request)
    {
        try {
            // Fetch all driver documents from Firestore using REST API
            $response = Http::timeout(10)->get("{$this->baseUrl}/drivers");

            $firebaseDriverIds = [];
            $firebaseLocations = [];

            // Process Firebase data if available
            if ($response->successful()) {
                $firestoreData = $response->json();

                if (isset($firestoreData['documents']) && is_array($firestoreData['documents'])) {
                    foreach ($firestoreData['documents'] as $document) {
                        // Extract driver ID from document name
                        // Format: projects/{project}/databases/(default)/documents/drivers/{driverId}
                        $nameParts = explode('/', $document['name']);
                        $driverId = end($nameParts);

                        // Convert to integer to match MySQL ID type
                        $driverId = (int)$driverId;

                        $firebaseDriverIds[] = $driverId;

                        $fields = $document['fields'] ?? [];
                        $lat = $this->getFieldValue($fields, 'lat');
                        $lng = $this->getFieldValue($fields, 'lng');

                        // Store location data even if lat/lng might be empty
                        $firebaseLocations[$driverId] = [
                            'lat' => !empty($lat) ? (float)$lat : null,
                            'lng' => !empty($lng) ? (float)$lng : null,
                            'last_updated' => $this->getFieldValue($fields, 'updated_at') ?? now()->toISOString(),
                            'has_valid_coordinates' => !empty($lat) && !empty($lng),
                        ];
                    }
                }
            }

            // Get all active drivers from MySQL
            $drivers = Driver::where('activate', 1)->get();
            $driverLocations = [];

            foreach ($drivers as $driver) {
                $driverId = (int)$driver->id;

                // Check if driver exists in Firebase
                if (isset($firebaseLocations[$driverId])) {
                    $location = $firebaseLocations[$driverId];

                    // Check if has valid coordinates
                    if ($location['has_valid_coordinates']) {
                        // Driver found in Firebase with valid location
                        $driverLocations[] = [
                            'id' => $driverId,
                            'name' => $driver->name ?? 'Driver #' . $driverId,
                            'phone' => $driver->phone ?? '',
                            'status' => $driver->status == 1 ? 'online' : 'offline',
                            'activate' => $driver->activate == 1,
                            'balance' => $driver->balance ?? 0,
                            'lat' => $location['lat'],
                            'lng' => $location['lng'],
                            'last_updated' => $location['last_updated'],
                            'has_location' => true,
                        ];
                    } else {
                        // Driver exists in Firebase but has no valid coordinates
                        $driverLocations[] = [
                            'id' => $driverId,
                            'name' => $driver->name ?? 'Driver #' . $driverId,
                            'phone' => $driver->phone ?? '',
                            'status' => $driver->status == 1 ? 'online' : 'offline',
                            'activate' => $driver->activate == 1,
                            'balance' => $driver->balance ?? 0,
                            'lat' => null,
                            'lng' => null,
                            'last_updated' => $location['last_updated'],
                            'has_location' => false,
                            'in_firebase' => true,
                            'reason' => 'No valid coordinates'
                        ];
                    }
                } else {
                    // Driver NOT found in Firebase at all
                    $driverLocations[] = [
                        'id' => $driverId,
                        'name' => $driver->name ?? 'Driver #' . $driverId,
                        'phone' => $driver->phone ?? '',
                        'status' => $driver->status == 1 ? 'online' : 'offline',
                        'activate' => $driver->activate == 1,
                        'balance' => $driver->balance ?? 0,
                        'lat' => null,
                        'lng' => null,
                        'last_updated' => null,
                        'has_location' => false,
                        'in_firebase' => false,
                        'reason' => 'Not in Firebase'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'drivers' => $driverLocations,
                'total' => count($driverLocations),
                'drivers_with_location' => count(array_filter($driverLocations, fn($d) => $d['has_location'])),
                'drivers_without_location' => count(array_filter($driverLocations, fn($d) => !$d['has_location'])),
                'firebase_driver_ids' => $firebaseDriverIds, // For debugging
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching driver locations from Firebase: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching driver locations: ' . $e->getMessage(),
                'drivers' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get location for a specific driver using REST API
     */
    public function getDriverLocation($driverId)
    {
        try {
            // Get driver info from MySQL first
            $driver = Driver::find($driverId);

            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found in database'
                ], 404);
            }

            // Try to fetch location from Firestore
            $response = Http::timeout(10)->get("{$this->baseUrl}/drivers/{$driverId}");

            $locationData = [
                'id' => $driverId,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'status' => $driver->status == 1 ? 'online' : 'offline', // From MySQL
                'activate' => $driver->activate == 1,
                'balance' => $driver->balance ?? 0,
            ];

            if ($response->successful()) {
                $document = $response->json();
                $fields = $document['fields'] ?? [];

                $locationData['lat'] = (float)$this->getFieldValue($fields, 'lat');
                $locationData['lng'] = (float)$this->getFieldValue($fields, 'lng');
                $locationData['last_updated'] = $this->getFieldValue($fields, 'updated_at') ?? now()->toISOString();
                $locationData['has_location'] = true;
            } else {
                // Driver not in Firebase
                $locationData['lat'] = null;
                $locationData['lng'] = null;
                $locationData['last_updated'] = null;
                $locationData['has_location'] = false;
            }

            return response()->json([
                'success' => true,
                'driver' => $locationData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching driver location: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching driver location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to extract value from Firestore field structure
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
        if (isset($field['booleanValue'])) {
            return $field['booleanValue'];
        }
        if (isset($field['timestampValue'])) {
            return $field['timestampValue'];
        }

        return null;
    }
}
