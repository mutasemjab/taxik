<?php


namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\DriverAlert;
use App\Models\Order;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AlertDriverController extends Controller
{
    use Responses;

    public function index()
    {
        try {
            $driver = auth()->guard('driver-api')->user(); 
            $alerts = DriverAlert::where('driver_id',$driver->id)->orderBy('created_at', 'desc')->get();

            return $this->success_response('Driver alerts retrieved successfully.', $alerts);
        } catch (\Exception $e) {
            return $this->error_response('Failed to retrieve driver alerts.', $e->getMessage());
        }
    }

    /**
     * Store a new driver alert.
     */
    public function store(Request $request)
    {
        // Validate request without driver_id
        $validated = $request->validate([
            'report' => 'required|string|max:255',
            'lat'    => 'required|numeric',
            'lng'    => 'required|numeric',
            'note'   => 'nullable|string',
            'address'   => 'nullable|string',
        ]);

        try {
            // Get the authenticated driver
            $driver = auth()->guard('driver-api')->user(); // adjust guard if needed

            // Merge driver_id into validated data
            $validated['driver_id'] = $driver->id;

            $alert = DriverAlert::create($validated);

            return $this->success_response('Driver alert created successfully.', $alert);
        } catch (\Exception $e) {
            return $this->error_response('Failed to create driver alert.', $e->getMessage());
        }
    }

}
