<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Setting;
use App\Traits\Responses;
use Illuminate\Http\Request;

class ServicesController extends Controller
{ 
    use Responses;
    
    public function index(Request $request)
    {
        // Validate coordinates from the request
        $request->validate([
            'start_lat' => 'required|numeric',
            'start_lng' => 'required|numeric',
            'end_lat'   => 'nullable|numeric',
            'end_lng'   => 'nullable|numeric',
        ]);

        $startLat = $request->start_lat;
        $startLng = $request->start_lng;
        $endLat   = $request->end_lat;
        $endLng   = $request->end_lng;

        $distance = 0;

        // Only calculate distance if both end_lat and end_lng are present
        if (!is_null($endLat) && !is_null($endLng)) {
            $distance = $this->calculateDistanceWithOSRM($startLat, $startLng, $endLat, $endLng); // in KM
        }

        $services = Service::where('activate', 1)
            ->whereHas('driverServices', function ($query) {
                $query->where('status', 1);
            })
            ->with(['servicePayments', 'driverServices' => function ($query) {
                $query->where('status', 1);
            }])
            ->get();

        $isEvening = $this->isEveningTime();

        $data = $services->map(function ($service) use ($distance, $isEvening) {
            $startPrice = $isEvening ? $service->start_price_evening : $service->start_price_morning;
            $pricePerKm = $isEvening ? $service->price_per_km_evening : $service->price_per_km_morning;
            
            // Subtract 2.5 km from distance before calculating price (minimum 0)
            $billableDistance = max(0, $distance - 2.5);
            $price = $startPrice + ($pricePerKm * $billableDistance);

            $serviceData = $service->toArray();
            $serviceData['distance_km'] = round($distance, 2);
            $serviceData['estimated_price'] = round($price, 2);
            $serviceData['pricing_period'] = $isEvening ? 'evening' : 'morning';

            return $serviceData;
        });

        return $this->success_response('Services retrieved with full data and estimated prices', $data);
    }

    /**
     * Determine if current time is morning or evening
     * Evening: 22:00 (10 PM) to 06:00 (6 AM)
     */
    private function isEveningTime($dateTime = null)
    {
        $checkTime = $dateTime ?? now();
        $hour = $checkTime->format('H');
        return $hour >= 22 || $hour < 6;
    }

    /**
     * Calculate distance using OSRM (Open Source Routing Machine)
     * Returns distance in kilometers
     */
    private function calculateDistanceWithOSRM($originLat, $originLng, $destinationLat, $destinationLng)
    {
        try {
            // OSRM format: longitude,latitude (note: reversed from Google!)
            $url = "https://router.project-osrm.org/route/v1/driving/"
                . "{$originLng},{$originLat};"
                . "{$destinationLng},{$destinationLat}"
                . "?overview=false&alternatives=false&steps=false";

            // Use Guzzle or file_get_contents
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            // Check if the request was successful
            if ($data['code'] === 'Ok' && isset($data['routes'][0]['distance'])) {
                // Distance is returned in meters, convert to kilometers
                $distanceInMeters = $data['routes'][0]['distance'];
                return $distanceInMeters / 1000; // Convert to KM
            } else {
                // Fallback to Haversine formula if OSRM fails
                \Log::warning('OSRM API failed', [
                    'code' => $data['code'] ?? 'unknown',
                    'message' => $data['message'] ?? 'No message'
                ]);
                return $this->calculateDistanceFallback($originLat, $originLng, $destinationLat, $destinationLng);
            }
        } catch (\Exception $e) {
            // Fallback to Haversine formula on exception
            \Log::error('Exception in OSRM distance calculation', [
                'message' => $e->getMessage()
            ]);
            return $this->calculateDistanceFallback($originLat, $originLng, $destinationLat, $destinationLng);
        }
    }

    /**
     * Fallback distance calculation using Haversine formula
     * Returns distance in kilometers (straight line distance)
     */
    private function calculateDistanceFallback($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Radius in kilometers

        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $latDelta = $lat2 - $lat1;
        $lngDelta = $lng2 - $lng1;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lngDelta / 2), 2)));

        return $earthRadius * $angle;
    }
}
