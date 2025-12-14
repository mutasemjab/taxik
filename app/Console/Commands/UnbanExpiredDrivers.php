<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\DriverBan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UnbanExpiredDrivers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:unban-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically unban drivers whose ban period has expired';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for expired bans...');

        // Get all active temporary bans that have expired
        $expiredBans = DriverBan::active()
            ->temporary()
            ->whereNotNull('ban_until')
            ->where('ban_until', '<=', Carbon::now())
            ->get();

        $unbannedCount = 0;

        foreach ($expiredBans as $ban) {
            try {
                $driver = $ban->driver;
                
                // Unban the driver
                $ban->update([
                    'is_active' => false,
                    'unbanned_at' => now(),
                    'unban_reason' => 'Automatic unban - ban period expired',
                ]);

                // Update driver status to active
                $driver->update(['activate' => 1]);

                $unbannedCount++;
                $this->info("Unbanned driver: {$driver->name} (ID: {$driver->id})");
                
            } catch (\Exception $e) {
                $this->error("Failed to unban driver ID {$ban->driver_id}: {$e->getMessage()}");
            }
        }

        if ($unbannedCount > 0) {
            $this->info("Successfully unbanned {$unbannedCount} driver(s).");
        } else {
            $this->info('No expired bans found.');
        }

        return Command::SUCCESS;
    }
}