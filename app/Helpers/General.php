<?php

function uploadImage($folder, $image, $subFolder = null)
{
    $extension = strtolower($image->extension());
    
    // Generate unique filename
    $filename = time() . '_' . uniqid() . '_' . rand(1000, 9999) . '.' . $extension;
    
    // If subfolder is provided (e.g., driver ID), create it
    if ($subFolder) {
        $folder = $folder . '/' . $subFolder;
        
        // Create directory if it doesn't exist
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }
    }
    
    $image->move($folder, $filename);
    
    // Return relative path for database storage
    return $subFolder ? $subFolder . '/' . $filename : $filename;
}


function uploadFile($file, $folder)
{
    $path = $file->store($folder);
    return $path;
}



