<?php

function uploadImage($folder, $image, $subFolder = null)
{
    try {
        // ✅ ONLY check if file is valid - don't access temp file properties
        if (!$image || !$image->isValid()) {
            throw new \Exception('Invalid file upload: File is missing or corrupted');
        }

        // ✅ Get extension from CLIENT side (doesn't touch temp file)
        $extension = strtolower($image->getClientOriginalExtension());
        
        // Validate extension
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            throw new \Exception('Invalid file type. Only JPG, JPEG, and PNG are allowed.');
        }
        
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '_' . rand(1000, 9999) . '.' . $extension;
        
        // Create subfolder if needed
        if ($subFolder) {
            $folder = rtrim($folder, '/') . '/' . $subFolder;
            if (!file_exists($folder)) {
                if (!mkdir($folder, 0755, true) && !is_dir($folder)) {
                    throw new \Exception('Failed to create upload directory');
                }
            }
        }
        
        // ✅ MOVE FILE IMMEDIATELY - no other checks before this
        $moved = $image->move($folder, $filename);
        
        if (!$moved) {
            throw new \Exception('Failed to move uploaded file');
        }
        
        return $subFolder ? $subFolder . '/' . $filename : $filename;
        
    } catch (\Exception $e) {
        \Log::error('File upload failed', [
            'error' => $e->getMessage(),
            'folder' => $folder ?? 'unknown',
            'subfolder' => $subFolder ?? 'none',
            'file_name' => $image ? $image->getClientOriginalName() : 'unknown',
            // ❌ DON'T call getSize() here - temp file might be gone
            'has_file' => $image ? 'yes' : 'no',
        ]);
        
        throw $e;
    }
}


function uploadFile($file, $folder)
{
    $path = $file->store($folder);
    return $path;
}



