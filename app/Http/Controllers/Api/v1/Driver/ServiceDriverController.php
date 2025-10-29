<?php


namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverService;
use App\Models\Service;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ServiceDriverController extends Controller
{
    use Responses;

    public function index(Request $request)
    {
        // Get all active services
        $services = Service::where('activate', 1)->with('servicePayments')->get();
        
        // Check if a driver is authenticated
        $driver = auth('driver-api')->user();
        
        if ($driver) {
            // Get the driver's service statuses
            $driverServices = DriverService::where('driver_id', $driver->id)->pluck('status', 'service_id')->toArray();
            
            // Map services with their status for this driver
            $services = $services->map(function($service) use ($driverServices) {
                $status = isset($driverServices[$service->id]) ? $driverServices[$service->id] : 2; // Default to inactive (2) if no record
                $service->driver_status = $status;
                return $service;
            });
        }
        
        return $this->success_response('Services retrieved successfully', $services);
    }

    public function storeOrUpdateStatus(Request $request)
    {
        $driver_id = auth()->guard('driver-api')->user()->id;
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'status' => 'required|in:1,2', // 1 active, 2 inactive
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        // Check if record exists
        $driverService = DriverService::where('driver_id', $driver_id)
            ->where('service_id', $request->service_id)
            ->first();

        if ($driverService) {
            // Update existing record
            $driverService->status = $request->status;
            $driverService->save();
            
            $message = 'Driver service status updated successfully';
        } else {
            // Create new record
            $driverService = new DriverService();
            $driverService->driver_id = $driver_id;
            $driverService->service_id = $request->service_id;
            $driverService->status = $request->status;
            $driverService->save();
            
            $message = 'Driver service created successfully';
        }

        return $this->success_response($message, $driverService);
    }
}
