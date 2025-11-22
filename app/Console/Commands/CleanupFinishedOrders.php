<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;
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
    protected $description = 'Remove all finished orders (completed/cancelled) from Firestore that are older than specified hours';

    protected $firestore;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Initialize Firestore from service container (avoids gRPC error)
        try {
            $this->firestore = app('firebase.firestore')->database();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firestore in CleanupFinishedOrders command: ' . $e->getMessage());
            $this->error('Firestore not initialized. Please check Firebase configuration.');
            return Command::FAILURE;
        }

        $hoursThreshold = $this->option('hours');
        $this->info("Starting cleanup of finished orders older than {$hoursThreshold} hour(s) from Firestore...");

        // Calculate the threshold time
        $thresholdTime = Carbon::now()->subHours($hoursThreshold);

        // Find all finished orders (completed and all types of cancelled)
        $finishedOrders = Order::whereIn('status', [
                OrderStatus::Delivered,
                OrderStatus::UserCancelOrder,
                OrderStatus::DriverCancelOrder,
                OrderStatus::CancelCronJob,
            ])
            ->where('updated_at', '<=', $thresholdTime)
            ->get();

        if ($finishedOrders->isEmpty()) {
            $this->info('No finished orders found to cleanup from Firestore.');
            Log::info('CleanupFinishedOrders: No finished orders found to cleanup.');
            return Command::SUCCESS;
        }

        $this->info("Found {$finishedOrders->count()} finished order(s) to remove from Firestore.");

        // Group by status for reporting
        $byStatus = $finishedOrders->groupBy('status');
        $this->info("\nBreakdown by status:");
        foreach ($byStatus as $status => $orders) {
            $this->info("  - {$status}: {$orders->count()} orders");
        }
        $this->newLine();

        $successCount = 0;
        $failCount = 0;
        $notFoundCount = 0;

        $progressBar = $this->output->createProgressBar($finishedOrders->count());
        $progressBar->start();

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

        $progressBar->finish();
        $this->newLine(2);

        $this->info("=== Cleanup Summary ===");
        $this->info("Total orders processed: {$finishedOrders->count()}");
        $this->info("Successfully removed: {$successCount}");
        $this->info("Not found (already removed): {$notFoundCount}");
        $this->info("Failed: {$failCount}");

        Log::info('CleanupFinishedOrders cron job completed', [
            'total' => $finishedOrders->count(),
            'success' => $successCount,
            'not_found' => $notFoundCount,
            'failed' => $failCount,
            'hours_threshold' => $hoursThreshold,
            'by_status' => $byStatus->map->count()->toArray()
        ]);

        return Command::SUCCESS;
    }

    /**
     * Remove order from Firestore
     */
    private function removeOrderFromFirestore($orderId)
    {
        try {
            $rideRequestsCollection = $this->firestore->collection('ride_requests');
            $document = $rideRequestsCollection->document((string)$orderId);

            // Check if document exists
            $snapshot = $document->snapshot();
            
            if (!$snapshot->exists()) {
                return 'not_found';
            }

            // Delete the document
            $document->delete();

            Log::info("Finished order #{$orderId} removed from Firestore successfully");

            return true;
            
        } catch (\Exception $e) {
            Log::error("Error removing finished order #{$orderId} from Firestore: " . $e->getMessage());
            return false;
        }
    }
}