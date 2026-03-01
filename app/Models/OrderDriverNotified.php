<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDriverNotified extends Model
{
    use HasFactory;
    protected $table = 'order_drivers_notified';

    protected $fillable = [
        'order_id',
        'driver_id',
        'distance_km',
        'search_radius_km',
        'notified',
        'notified_at',
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'notified' => 'boolean',
        'notified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the driver
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Bulk insert notified drivers for an order
     */
    public static function recordNotifiedDrivers($orderId, array $drivers, $searchRadius = null)
    {
        try {
            // ✅ Get already notified driver IDs for this order
            $alreadyNotifiedIds = self::where('order_id', $orderId)
                ->pluck('driver_id')
                ->toArray();

            \Log::info("Order {$orderId}: Found " . count($alreadyNotifiedIds) . " already notified drivers");

            $records = [];
            $skipped = 0;
            $now = now();

            foreach ($drivers as $driver) {
                // ✅ Skip if driver already notified
                if (in_array($driver['id'], $alreadyNotifiedIds)) {
                    \Log::debug("Driver {$driver['id']} already notified for order {$orderId}, skipping");
                    $skipped++;
                    continue;
                }

                $records[] = [
                    'order_id' => $orderId,
                    'driver_id' => $driver['id'],
                    'distance_km' => $driver['distance'] ?? null,
                    'search_radius_km' => $searchRadius,
                    'notified' => true,
                    'notified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($records)) {
                \Log::info("Attempting to insert " . count($records) . " notified drivers for order {$orderId}" .
                    ($skipped > 0 ? " (skipped {$skipped} duplicates)" : ""));

                // Use insert to catch any remaining errors
                $inserted = \DB::table('order_drivers_notified')->insert($records);

                \Log::info("Successfully inserted " . count($records) . " records for order {$orderId}");

                return count($records);
            }

            \Log::info("No NEW records to insert for order {$orderId}" .
                ($skipped > 0 ? " (skipped {$skipped} duplicates)" : ""));
            return 0;
        } catch (\Exception $e) {
            \Log::error("Failed to record notified drivers for order {$orderId}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw to see the error
        }
    }
}
