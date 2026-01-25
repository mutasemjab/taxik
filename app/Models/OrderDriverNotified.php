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
            $records = [];
            $now = now();

            foreach ($drivers as $driver) {
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
                \Log::info("Attempting to insert " . count($records) . " notified drivers for order {$orderId}");

                // Use insert instead of insertOrIgnore to see errors
                $inserted = \DB::table('order_drivers_notified')->insert($records);

                \Log::info("Successfully inserted {$inserted} records for order {$orderId}");

                return count($records);
            }

            \Log::warning("No records to insert for order {$orderId}");
            return 0;
        } catch (\Exception $e) {
            \Log::error("Failed to record notified drivers for order {$orderId}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw to see the error
        }
    }
}
