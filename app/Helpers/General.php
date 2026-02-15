<?php

function uploadImage($folder, $image, $subFolder = null)
{
    try {
        // ✅ CRITICAL: Check if file exists and is valid FIRST
        if (!$image || !$image->isValid()) {
            throw new \Exception('Invalid file upload: File is missing or corrupted');
        }

        // ✅ Check if file is readable
        $tempPath = $image->getRealPath();
        if (!$tempPath || !is_readable($tempPath)) {
            throw new \Exception('File is not readable. The uploaded file may have been deleted from temporary storage.');
        }

        // ✅ Get extension safely with fallback
        try {
            $extension = strtolower($image->extension());
        } catch (\Exception $e) {
            // Fallback to client extension if server can't determine
            $extension = strtolower($image->getClientOriginalExtension());
        }
        
        // Validate extension
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            throw new \Exception('Invalid file type. Only JPG, JPEG, and PNG are allowed.');
        }
        
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '_' . rand(1000, 9999) . '.' . $extension;
        
        // If subfolder is provided (e.g., driver ID), create it
        if ($subFolder) {
            $folder = $folder . '/' . $subFolder;
            
            // Create directory if it doesn't exist
            if (!file_exists($folder)) {
                if (!mkdir($folder, 0755, true)) {
                    throw new \Exception('Failed to create upload directory');
                }
            }
        }
        
        // ✅ Check folder is writable
        if (!is_writable($folder)) {
            throw new \Exception('Upload directory is not writable: ' . $folder);
        }
        
        // ✅ Move file with error checking
        if (!$image->move($folder, $filename)) {
            throw new \Exception('Failed to move uploaded file to destination');
        }
        
        // Return relative path for database storage
        return $subFolder ? $subFolder . '/' . $filename : $filename;
        
    } catch (\Exception $e) {
        // Log the specific error for debugging
        \Log::error('File upload failed in uploadImage()', [
            'error' => $e->getMessage(),
            'folder' => $folder ?? 'unknown',
            'subfolder' => $subFolder ?? 'none',
            'file_name' => $image ? $image->getClientOriginalName() : 'unknown',
            'file_size' => $image ? $image->getSize() : 'unknown',
            'temp_path' => $image ? $image->getRealPath() : 'unknown',
            'temp_exists' => $image && $image->getRealPath() ? file_exists($image->getRealPath()) : false,
        ]);
        
        // Re-throw with user-friendly message
        throw new \Exception('File upload failed: ' . $e->getMessage());
    }
}


function uploadFile($file, $folder)
{
    $path = $file->store($folder);
    return $path;
}



