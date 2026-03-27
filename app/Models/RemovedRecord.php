<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemovedRecord extends Model
{
    protected $guarded = [];

    protected $casts = [
        'full_data' => 'array',
    ];

    /**
     * Archive a user before deletion.
     */
    public static function archiveUser(User $user, ?int $adminId = null, ?string $reason = null): self
    {
        return self::create([
            'type'                  => 'user',
            'original_id'           => $user->id,
            'name'                  => $user->name,
            'phone'                 => $user->phone,
            'country_code'          => $user->country_code,
            'email'                 => $user->email,
            'balance'               => $user->balance,
            'photo'                 => $user->photo,
            'activate'              => $user->activate,
            'full_data'             => $user->toArray(),
            'deleted_by_admin_id'   => $adminId,
            'delete_reason'         => $reason,
            'deleted_at_original'   => now(),
        ]);
    }

    /**
     * Archive a driver before deletion.
     */
    public static function archiveDriver(Driver $driver, ?int $adminId = null, ?string $reason = null): self
    {
        return self::create([
            'type'                  => 'driver',
            'original_id'           => $driver->id,
            'name'                  => $driver->name,
            'phone'                 => $driver->phone,
            'country_code'          => $driver->country_code,
            'email'                 => $driver->email,
            'balance'               => $driver->balance,
            'photo'                 => $driver->photo,
            'activate'              => $driver->activate,
            'model'                 => $driver->model,
            'plate_number'          => $driver->plate_number,
            'color'                 => $driver->color,
            'production_year'       => $driver->production_year,
            'full_data'             => $driver->toArray(),
            'deleted_by_admin_id'   => $adminId,
            'delete_reason'         => $reason,
            'deleted_at_original'   => now(),
        ]);
    }
}
