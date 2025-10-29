<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Teacher;
use App\Models\ClassTeacher;
use App\Models\LessonTeacher;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeachersImport implements ToModel, WithHeadingRow
{
    /**
     * Process each row in the Excel file.
     */
    public function model(array $row)
    {
        // Extract the first clas_id (if provided)
        $firstClasId = null;
        if (!empty($row['clas_id'])) {
            $classIds = explode(',', $row['clas_id']); // Split multiple class IDs
            $firstClasId = trim($classIds[0]); // Take the first ID
        }

        // Insert into the users table
        $user = User::create([
            'name' => $row['name'],
            'password' => Hash::make($row['password'] ?? 'default_password'),
            'identity_number' => $row['identity_number'],
            'user_type' => 2, // Assuming '2' indicates a teacher
            'address' => $row['address'] ?? null,
            'photo' => $row['photo'] ?? null,
            'fcm_token' => $row['fcm_token'] ?? null,
            'activate' => $row['activate'] ?? 1,
            'clas_id' => $firstClasId, // Save the first clas_id here
        ]);

        // Insert into the teachers table
        $teacher = Teacher::create([
            'name' => $row['name'],
            'password' => Hash::make($row['password'] ?? 'default_password'),
            'identity_number' => $row['identity_number'],
            'photo' => $row['photo'] ?? null,
            'fcm_token' => $row['fcm_token'] ?? null,
            'activate' => $row['activate'] ?? 1,
            'user_id' => $user->id,
        ]);

        // Insert into class_teachers table for all clas_ids
      if (!empty($row['clas_id'])) {
            // Replace any non-standard commas (e.g., Arabic commas) with standard ones
            $row['clas_id'] = str_replace('،', ',', $row['clas_id']);
            $classIds = explode(',', $row['clas_id']); // Split multiple class IDs
        
            foreach ($classIds as $clasId) {
                $clasId = trim($clasId); // Trim spaces around the ID
                if (is_numeric($clasId)) { // Validate that it's a number
                    ClassTeacher::create([
                        'teacher_id' => $teacher->id,
                        'clas_id' => (int)$clasId, // Cast to integer
                    ]);
                } else {
                    // Log invalid clas_id for debugging
                    Log::warning("Invalid clas_id: '$clasId' for teacher_id: {$teacher->id}, row: " . json_encode($row));
                }
            }
        } else {
            // Log when clas_id is empty
            Log::warning("Empty clas_id for teacher_id: {$teacher->id}, row: " . json_encode($row));
        }


        // Insert into lesson_teachers table for all lesson_ids
        if (!empty($row['lesson_id'])) {
            $lessonIds = explode(',', $row['lesson_id']); // Split multiple lesson IDs
            foreach ($lessonIds as $lessonId) {
                LessonTeacher::create([
                    'teacher_id' => $teacher->id,
                    'lesson_id' => trim($lessonId),
                ]);
            }
        }
    }
}


