<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Enums\OrderStatus;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CancelPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:cancel-pending {--hours=1 : Hours threshold for pending orders}';

    /**
     * The console command description.
     */
    protected $description = 'Cancel orders that have been pending for more than specified hours';

    protected $firestore;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        
        try {
            $this->firestore = new FirestoreClient([
                'projectId' => config('firebase.project_id'),
                'keyFilePath' => config('firebase.credentials.file'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firestore in CancelPendingOrders command: ' . $e->getMessage());
        }
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoursThreshold = $this->option('hours');
        $this->info("Starting to process pending orders older than {$hoursThreshold} hour(s)...");

        // Calculate the threshold time
        $thresholdTime = Carbon::now()->subHours($hoursThreshold);

        // Find all pending orders older than threshold
        $pendingOrders = Order::where('status', OrderStatus::Pending)
            ->where('created_at', '<=', $thresholdTime)
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending orders found to cancel.');
            Log::info('CancelPendingOrders: No pending orders found to cancel.');
            return Command::SUCCESS;
        }

        $this->info("Found {$pendingOrders->count()} pending order(s) to cancel.");

        $successCount = 0;
        $failCount = 0;

        foreach ($pendingOrders as $order) {
            try {
                // Update order status to cancel_cron_job
                $order->status = OrderStatus::CancelCronJob;
                $order->reason_for_cancel = "Order automatically cancelled after being pending for {$hoursThreshold} hour(s) without driver acceptance.";
                $order->save();

                // Remove from Firestore
                $this->removeOrderFromFirestore($order->id);


                $successCount++;
                $this->info("✓ Order #{$order->id} cancelled successfully.");
                
                Log::info("Order #{$order->id} automatically cancelled by cron job", [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'created_at' => $order->created_at,
                    'hours_pending' => $order->created_at->diffInHours(now())
                ]);

            } catch (\Exception $e) {
                $failCount++;
                $this->error("✗ Failed to cancel order #{$order->id}: " . $e->getMessage());
                
                Log::error("Failed to cancel order #{$order->id} in cron job", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("\n=== Summary ===");
        $this->info("Total orders processed: {$pendingOrders->count()}");
        $this->info("Successfully cancelled: {$successCount}");
        $this->info("Failed: {$failCount}");

        Log::info('CancelPendingOrders cron job completed', [
            'total' => $pendingOrders->count(),
            'success' => $successCount,
            'failed' => $failCount
        ]);

        return Command::SUCCESS;
    }

    /**
     * Remove order from Firestore
     */
    private function removeOrderFromFirestore($orderId)
    {
        if (!$this->firestore) {
            Log::warning("Firestore not initialized, skipping Firebase removal for order #{$orderId}");
            return;
        }

        try {
            // Remove from ride_requests collection
            $rideRequestsCollection = $this->firestore->database()->collection('ride_requests');
            $rideRequestsCollection->document((string)$orderId)->delete();

            Log::info("Order #{$orderId} removed from Firestore ride_requests collection");
            
        } catch (\Exception $e) {
            Log::error("Error removing order #{$orderId} from Firestore: " . $e->getMessage());
            throw $e;
        }
    }

}