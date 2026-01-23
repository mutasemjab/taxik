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
        try {
            // Get all active services
            $services = Service::where('activate', 1)->with('servicePayments')->get();

            // Check if a driver is authenticated
            $driver = auth('driver-api')->user();

            if ($driver) {
                // Get the driver's services with their types and statuses
                $driverServices = DriverService::where('driver_id', $driver->id)
                    ->get()
                    ->keyBy('service_id');

                // Map services with their flags for this driver
                $services = $services->map(function ($service) use ($driverServices) {
                    if (isset($driverServices[$service->id])) {
                        $driverService = $driverServices[$service->id];

                        // Service is assigned to driver
                        $service->driver_status = $driverService->status; // 1 = active, 2 = inactive

                        // Determine service type flag
                        if ($driverService->service_type == 1) {
                            // Primary service - cannot be toggled off
                            $service->service_flag = 'primary';
                            $service->can_toggle = false;
                        } else {
                            // Optional service - can be toggled
                            $service->service_flag = 'optional';
                            $service->can_toggle = true;
                        }
                    } else {
                        // Service is not assigned to this driver - unavailable
                        $service->service_flag = 'unavailable';
                        $service->driver_status = 2; // Show as inactive
                        $service->can_toggle = false;
                    }

                    return $service;
                });
            }

            return $this->success_response('Services retrieved successfully', $services);
        } catch (\Exception $e) {
            // ========== LOG SERVICE INDEX FAILURE ==========
            \Log::channel('home_driver')->error('Service index retrieval failed', [
                'driver_id' => $driver->id ?? null,
                'driver_phone' => $driver->phone ?? null,
                'driver_email' => $driver->email ?? null,
                'driver_name' => $driver->name ?? null,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ]);
            // ========== END LOG SERVICE INDEX FAILURE ==========

            return $this->error_response('Failed to retrieve services', $e->getMessage());
        }
    }

    public function storeOrUpdateStatus(Request $request)
    {
        $driver_id = auth()->guard('driver-api')->user()->id;
        $driver = auth()->guard('driver-api')->user();

        // Validate request
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'status' => 'required|in:1,2', // 1 active, 2 inactive
        ]);

        if ($validator->fails()) {
            // Get first validation error message for the message field
            $errorMessage = $validator->errors()->first();

            // ========== LOG VALIDATION FAILURE ==========
            \Log::channel('home_driver')->error('Service status update validation failed', [
                'driver_id' => $driver_id,
                'driver_phone' => $driver->phone ?? null,
                'driver_email' => $driver->email ?? null,
                'driver_name' => $driver->name ?? null,
                'service_id' => $request->service_id,
                'requested_status' => $request->status,
                'errors' => $validator->errors()->toArray(),
                'first_error' => $errorMessage,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ]);
            // ========== END LOG VALIDATION FAILURE ==========

            return $this->error_response(__('messages.Validation_error'), $validator->errors());
        }

        try {
            // Check if the service is assigned to this driver
            $driverService = DriverService::where('driver_id', $driver_id)
                ->where('service_id', $request->service_id)
                ->first();

            if (!$driverService) {
                // ========== LOG SERVICE NOT AVAILABLE ==========
                \Log::channel('home_driver')->warning('Service not available for driver', [
                    'driver_id' => $driver_id,
                    'driver_phone' => $driver->phone ?? null,
                    'driver_email' => $driver->email ?? null,
                    'driver_name' => $driver->name ?? null,
                    'service_id' => $request->service_id,
                    'requested_status' => $request->status,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
                // ========== END LOG SERVICE NOT AVAILABLE ==========

                // Service is not available for this driver (unavailable flag)
                return $this->error_response(__('messages.Service_not_available'), null, 403);
            }

            // Check service flag
            if ($driverService->service_type == 1) {
                // Primary service - cannot be disabled
                if ($request->status == 2) {
                    // ========== LOG PRIMARY SERVICE DISABLE ATTEMPT ==========
                    \Log::channel('home_driver')->warning('Attempt to disable primary service', [
                        'driver_id' => $driver_id,
                        'driver_phone' => $driver->phone ?? null,
                        'driver_email' => $driver->email ?? null,
                        'driver_name' => $driver->name ?? null,
                        'service_id' => $request->service_id,
                        'service_type' => $driverService->service_type,
                        'requested_status' => $request->status,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                    // ========== END LOG PRIMARY SERVICE DISABLE ATTEMPT ==========

                    return $this->error_response(__('messages.Cannot_disable_primary_service'), null, 403);
                }

                return $this->success_response(__('messages.Primary_service_is_always_active'), $driverService);
            }

            if ($driverService->service_type == 2) {
                // Optional service - can be toggled
                $oldStatus = $driverService->status;
                $driverService->status = $request->status;
                $driverService->save();

                // ========== LOG SUCCESSFUL STATUS UPDATE ==========
                \Log::channel('home_driver')->info('Service status updated successfully', [
                    'driver_id' => $driver_id,
                    'driver_phone' => $driver->phone ?? null,
                    'driver_email' => $driver->email ?? null,
                    'driver_name' => $driver->name ?? null,
                    'service_id' => $request->service_id,
                    'service_type' => $driverService->service_type,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
                // ========== END LOG SUCCESSFUL STATUS UPDATE ==========

                $statusText = $request->status == 1 ? __('messages.activated') : __('messages.deactivated');
                $message = __('messages.Service') . ' ' . $statusText . ' ' . __('messages.successfully');

                return $this->success_response($message, $driverService);
            }

            // ========== LOG INVALID SERVICE TYPE ==========
            \Log::channel('home_driver')->error('Invalid service type encountered', [
                'driver_id' => $driver_id,
                'driver_phone' => $driver->phone ?? null,
                'driver_email' => $driver->email ?? null,
                'driver_name' => $driver->name ?? null,
                'service_id' => $request->service_id,
                'service_type' => $driverService->service_type,
                'requested_status' => $request->status,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ]);
            // ========== END LOG INVALID SERVICE TYPE ==========

            return $this->error_response(__('messages.Invalid_service_type'), null, 400);
        } catch (\Exception $e) {
            // ========== LOG SERVICE STATUS UPDATE FAILURE ==========
            \Log::channel('home_driver')->error('Service status update failed', [
                'driver_id' => $driver_id,
                'driver_phone' => $driver->phone ?? null,
                'driver_email' => $driver->email ?? null,
                'driver_name' => $driver->name ?? null,
                'service_id' => $request->service_id,
                'requested_status' => $request->status,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ]);
            // ========== END LOG SERVICE STATUS UPDATE FAILURE ==========

            return $this->error_response('Service status update failed', $e->getMessage());
        }
    }
}