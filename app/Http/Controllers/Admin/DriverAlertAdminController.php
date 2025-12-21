<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DriverAlert;
use App\Models\Driver;
use App\Services\DriverLocationService;
use App\Services\EnhancedFCMService;

class DriverAlertAdminController extends Controller
{

    protected $driverLocationService;

    public function __construct(DriverLocationService $driverLocationService)
    {
        $this->driverLocationService = $driverLocationService;
        $this->middleware('permission:driver_alert-table')->only('index');
        $this->middleware('permission:driver_alert-edit')->only('updateStatus', 'notify');
        $this->middleware('permission:driver_alert-delete')->only('destroy');
    }


    /**
     * Display all alerts
     */
    public function index()
    {
        $alerts = DriverAlert::with('driver')->orderBy('created_at', 'desc')->get();
        return view('admin.driver_alerts.index', compact('alerts'));
    }

    /**
     * Update status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,done',
        ]);

        $alert = DriverAlert::findOrFail($id);
        $alert->status = $request->status;
        $alert->save();

        return redirect()->back()->with('success', 'Alert status updated.');
    }

    /**
     * Delete alert
     */
    public function destroy($id)
    {
        $alert = DriverAlert::findOrFail($id);
        $alert->delete();

        return redirect()->back()->with('success', 'Alert deleted successfully.');
    }

    /**
     * Send notifications to nearby drivers
     */
     public function notify($id)
    {
        $alert = DriverAlert::findOrFail($id);

        // Using your service to get nearby drivers within 10 km
        $result = $this->driverLocationService->findAndStoreOrderInFirebase(
            $alert->lat,
            $alert->lng,
            $alert->id,
            $serviceId = null,   // You can define a service ID if needed
            $radius = 10         // 10 km radius
        );

        if ($result['success'] && !empty($result['drivers'])) {
            // Send FCM notification to these drivers
            $title = '🚨 ' . __('messages.New Alert');
            $body  = $alert->report;
            EnhancedFCMService::sendBulkToDrivers($result['drivers_found'], $title, $body, ['alert_id' => $alert->id]);
        }

        return redirect()->back()->with('success', __('messages.Notify_Nearby'));
    }
}
