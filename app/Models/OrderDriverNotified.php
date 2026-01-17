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
            // Use insert ignore to prevent duplicates
            \DB::table('order_drivers_notified')->insertOrIgnore($records);
        }

        return count($records);
    }
}
