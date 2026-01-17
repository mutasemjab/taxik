<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Models\OrderSpam;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class CleanupFinishedOrders extends Command
{
    
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:cleanup-finished {--hours=1 : Hours threshold for finished orders}';

    /**
     * The console command description.
     */
    protected $description = 'Remove all finished orders (completed/cancelled) and spam orders from Firestore that are older than specified hours';

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
        $this->info("Starting cleanup of finished orders older than {$hoursThreshold} hour(s) from Firestore...");

        // Calculate the threshold time
        $thresholdTime = Carbon::now()->subHours($hoursThreshold);

        // ===================================
        // STEP 1: Find finished orders
        // ===================================
        $finishedOrders = Order::whereIn('status', [
                OrderStatus::Delivered,
                OrderStatus::UserCancelOrder,
                OrderStatus::DriverCancelOrder,
                OrderStatus::CancelCronJob,
            ])
            ->where('updated_at', '<=', $thresholdTime)
            ->get();

        // ===================================
        // STEP 2: Find spam orders
        // ===================================
        $spamOrders = OrderSpam::where('cancelled_at', '<=', $thresholdTime)
            ->get();

        $totalOrders = $finishedOrders->count() + $spamOrders->count();

        if ($totalOrders === 0) {
            $this->info('No finished or spam orders found to cleanup from Firestore.');
            Log::info('CleanupFinishedOrders: No orders found to cleanup.');
            return Command::SUCCESS;
        }

        $this->info("Found {$finishedOrders->count()} finished order(s) from orders table");
        $this->info("Found {$spamOrders->count()} spam order(s) from order_spam table");
        $this->info("Total: {$totalOrders} orders to remove from Firestore");

        // Group finished orders by status for reporting
        if ($finishedOrders->isNotEmpty()) {
            $byStatus = $finishedOrders->groupBy(function ($order) {
                return $order->status->value;
            });
            $this->info("\nFinished Orders Breakdown by status:");
            foreach ($byStatus as $status => $orders) {
                $this->info("  - {$status}: {$orders->count()} orders");
            }
        }

        // Group spam orders by status for reporting
        if ($spamOrders->isNotEmpty()) {
            $spamByStatus = $spamOrders->groupBy('status');
            $this->info("\nSpam Orders Breakdown by status:");
            foreach ($spamByStatus as $status => $orders) {
                $this->info("  - {$status}: {$orders->count()} orders");
            }
        }

        $this->newLine();

        $successCount = 0;
        $failCount = 0;
        $notFoundCount = 0;

        $progressBar = $this->output->createProgressBar($totalOrders);
        $progressBar->start();

        // ===================================
        // STEP 3: Process finished orders
        // ===================================
        foreach ($finishedOrders as $order) {
            try {
                $removed = $this->removeOrderFromFirestore($order->id);

                if ($removed === 'not_found') {
                    $notFoundCount++;
                } elseif ($removed) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                $progressBar->advance();

            } catch (\Exception $e) {
                $failCount++;
                Log::error("Failed to remove finished order #{$order->id} from Firestore", [
                    'order_id' => $order->id,
                    'status' => $order->status->value,
                    'error' => $e->getMessage()
                ]);
                
                $progressBar->advance();
            }
        }

        // ===================================
        // STEP 4: Process spam orders
        // ===================================
        foreach ($spamOrders as $spamOrder) {
            try {
                $removed = $this->removeOrderFromFirestore($spamOrder->id);

                if ($removed === 'not_found') {
                    $notFoundCount++;
                } elseif ($removed) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                $progressBar->advance();

            } catch (\Exception $e) {
                $failCount++;
                Log::error("Failed to remove spam order #{$spamOrder->id} from Firestore", [
                    'order_id' => $spamOrder->id,
                    'status' => $spamOrder->status,
                    'error' => $e->getMessage()
                ]);
                
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // ===================================
        // STEP 5: Summary
        // ===================================
        $this->info("=== Cleanup Summary ===");
        $this->info("Total orders processed: {$totalOrders}");
        $this->info("  - Finished orders: {$finishedOrders->count()}");
        $this->info("  - Spam orders: {$spamOrders->count()}");
        $this->newLine();
        $this->info("Successfully removed from Firestore: {$successCount}");
        $this->info("Not found (already removed): {$notFoundCount}");
        $this->info("Failed: {$failCount}");

        Log::info('CleanupFinishedOrders cron job completed', [
            'total' => $totalOrders,
            'finished_orders' => $finishedOrders->count(),
            'spam_orders' => $spamOrders->count(),
            'success' => $successCount,
            'not_found' => $notFoundCount,
            'failed' => $failCount,
            'hours_threshold' => $hoursThreshold,
            'finished_by_status' => isset($byStatus) ? $byStatus->map->count()->toArray() : [],
            'spam_by_status' => isset($spamByStatus) ? $spamByStatus->map->count()->toArray() : [],
        ]);

        return Command::SUCCESS;
    }

    /**
     * Remove order from Firestore using REST API
     */
    private function removeOrderFromFirestore($orderId)
    {
        try {
            // First, try to get the document to check if it exists
            $getResponse = Http::timeout(10)->get(
                "{$this->baseUrl}/ride_requests/{$orderId}"
            );

            if ($getResponse->status() === 404) {
                return 'not_found';
            }

            // Delete the document
            $deleteResponse = Http::timeout(10)->delete(
                "{$this->baseUrl}/ride_requests/{$orderId}"
            );

            if ($deleteResponse->successful()) {
                Log::info("Order #{$orderId} removed from Firestore successfully");
                return true;
            } else {
                Log::error("Failed to delete order #{$orderId} from Firestore", [
                    'status' => $deleteResponse->status(),
                    'body' => $deleteResponse->body()
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error("Error removing order #{$orderId} from Firestore: " . $e->getMessage());
            return false;
        }
    }
}