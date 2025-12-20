<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\CountryCharge;
use Illuminate\Http\Request;

class CountryChargeApiController extends Controller
{
    /**
     * Get all countries with their charge data
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $countries = CountryCharge::with('chargeData')
                ->orderBy('name', 'asc')
                ->get();

            // Transform the data
            $data = $countries->map(function ($country) {
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'created_at' => $country->created_at->format('Y-m-d H:i:s'),
                    'charge_data_count' => $country->chargeData->count(),
                    'charge_data' => $country->chargeData->map(function ($chargeData) {
                        return [
                            'id' => $chargeData->id,
                            'name' => $chargeData->name,
                            'phone' => $chargeData->phone,
                            'service_provider' => $chargeData->service_provider,
                            'cliq_name' => $chargeData->cliq_name,
                            'created_at' => $chargeData->created_at->format('Y-m-d H:i:s'),
                        ];
                    })
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Countries retrieved successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve countries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}