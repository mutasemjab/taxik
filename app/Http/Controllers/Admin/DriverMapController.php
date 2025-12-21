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
    public function getDriverLocations(Request $request)
    {
        try {
            // Fetch all driver documents from Firestore using REST API
            $response = Http::timeout(10)->get("{$this->baseUrl}/drivers");

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch drivers from Firestore: ' . $response->body());
            }

            $firestoreData = $response->json();
            $driverLocations = [];

            // Check if documents exist
            if (isset($firestoreData['documents']) && is_array($firestoreData['documents'])) {
                foreach ($firestoreData['documents'] as $document) {
                    // Extract driver ID from document name
                    // Format: projects/{project}/databases/(default)/documents/drivers/{driverId}
                    $nameParts = explode('/', $document['name']);
                    $driverId = end($nameParts);

                    // Extract fields from Firestore document
                    $fields = $document['fields'] ?? [];
                    
                    // Get lat and lng from Firestore
                    $lat = $this->getFieldValue($fields, 'lat');
                    $lng = $this->getFieldValue($fields, 'lng');

                    // Skip if no valid location
                    if (empty($lat) || empty($lng)) {
                        continue;
                    }

                    // Get driver info from MySQL
                    $driver = Driver::find($driverId);

                    if ($driver) {
                        $driverLocations[] = [
                            'id' => $driverId,
                            'name' => $driver->name ?? 'Driver #' . $driverId,
                            'phone' => $driver->phone ?? '',
                            'status' => $driver->status == 1 ? 'online' : 'offline',
                            'activate' => $driver->activate == 1,
                            'balance' => $driver->balance ?? 0,
                            'lat' => (float)$lat,
                            'lng' => (float)$lng,
                            'last_updated' => $this->getFieldValue($fields, 'updated_at') ?? now()->toISOString(),
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'drivers' => $driverLocations,
                'total' => count($driverLocations),
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
            // Fetch specific driver document from Firestore
            $response = Http::timeout(10)->get("{$this->baseUrl}/drivers/{$driverId}");

            if ($response->status() === 404) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver location not found in Firebase'
                ], 404);
            }

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch driver from Firestore');
            }

            $document = $response->json();
            $fields = $document['fields'] ?? [];

            // Get driver info from MySQL
            $driver = Driver::find($driverId);

            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found in database'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'driver' => [
                    'id' => $driverId,
                    'name' => $driver->name,
                    'phone' => $driver->phone,
                    'status' => $driver->status == 1 ? 'online' : 'offline',
                    'lat' => (float)$this->getFieldValue($fields, 'lat'),
                    'lng' => (float)$this->getFieldValue($fields, 'lng'),
                    'last_updated' => $this->getFieldValue($fields, 'updated_at') ?? now()->toISOString(),
                ]
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
     * Firestore REST API returns fields in this format:
     * { "fieldName": { "stringValue": "value" } } or { "fieldName": { "doubleValue": 1.23 } }
     */
    private function getFieldValue($fields, $fieldName)
    {
        if (!isset($fields[$fieldName])) {
            return null;
        }

        $field = $fields[$fieldName];

        // Check for different value types
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