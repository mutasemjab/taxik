<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
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

    protected $projectId;
    protected $baseUrl;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->projectId = config('firebase.project_id');
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
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
                // Update order status using the enum value
                $order->status = OrderStatus::CancelCronJob->value;
                $order->reason_for_cancel = "Order automatically cancelled after being pending for {$hoursThreshold} hour(s) without driver acceptance.";
                $order->save();

                // Remove from Firestore using REST API
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
     * Remove order from Firestore using REST API
     */
    private function removeOrderFromFirestore($orderId)
    {
        try {
            $response = Http::timeout(10)->delete(
                "{$this->baseUrl}/ride_requests/{$orderId}"
            );

            if ($response->successful() || $response->status() === 404) {
                Log::info("Order #{$orderId} removed from Firestore ride_requests collection");
            } else {
                Log::warning("Failed to remove order #{$orderId} from Firestore", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("Error removing order #{$orderId} from Firestore: " . $e->getMessage());
            // Don't throw exception - we don't want Firestore errors to stop the cancellation
        }
    }
}