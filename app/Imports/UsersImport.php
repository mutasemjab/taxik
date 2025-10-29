<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersImport implements ToModel, WithHeadingRow
{
   
    /**
     * Create a new user from each row in the Excel file.
     */
    public function model(array $row)
    {
        return new User([
            'name'     => $row['name'],
           'date_of_birth' => $this->convertExcelDate($row['date_of_birth'] ?? null),
            'password'  => Hash::make($row['password']),
            'identity_number' => $row['identity_number'],
            'user_type' => 1,
            'clas_id' => $row['clas_id'],

        ]);
    }
    
    private function convertExcelDate($value)
    {
        if (is_numeric($value)) {
            // Convert Excel serial number to a date
            return Carbon::createFromTimestamp(($value - 25569) * 86400)->format('Y-m-d');
        } elseif (strtotime($value)) {
            // If it's already a valid date string
            return Carbon::parse($value)->format('Y-m-d');
        }
    
        // Return null for invalid or empty values
        return null;
    }


}

