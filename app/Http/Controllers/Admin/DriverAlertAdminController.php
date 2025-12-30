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
        try {
            $alert = DriverAlert::with('driver')->findOrFail($id);

            // Find nearby drivers within 10 km (without creating Firebase order)
            $result = $this->driverLocationService->findNearbyDrivers(
                $alert->lat,
                $alert->lng,
                10 // 10 km radius
            );

            if (!$result['success'] || empty($result['driver_ids'])) {
                return redirect()->back()->with('error', __('messages.No_Nearby_Drivers_Found'));
            }

            // Prepare notification data
            $title = '🚨 ' . 'انتبهوا';
            $body = $alert->report;

            $customData = [
                'alert_id' => (string)$alert->id,
                'type' => 'driver_alert',
                'screen' => 'alert_details',
                'action' => 'view_alert',
                'lat' => (string)$alert->lat,
                'lng' => (string)$alert->lng,
                'address' => $alert->address ?? '',
                'report' => $alert->report,
                'driver_name' => $alert->driver->name ?? '',
                'distance' => 'nearby'
            ];

            // Send FCM notification to nearby drivers
            $fcmResult = EnhancedFCMService::sendBulkToDrivers(
                $result['driver_ids'],
                $title,
                $body,
                $customData
            );

            \Log::info("Alert {$id} notifications sent to {$result['count']} drivers. Sent: {$fcmResult['sent']}, Failed: {$fcmResult['failed']}");

            // Update alert status to done (optional)
            $alert->update(['status' => 'done']);

            $message = __('messages.Notification_Sent_To_Drivers', [
                'count' => $fcmResult['sent'],
                'total' => $result['count']
            ]);

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error("Error sending alert notifications: " . $e->getMessage());
            return redirect()->back()->with('error', __('messages.Error_Sending_Notifications'));
        }
    }
}
