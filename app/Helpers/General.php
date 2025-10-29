<?php

function uploadImage($folder, $image)
{
  $extension = strtolower($image->extension());
 // $filename = time() . rand(100, 999) . '.' . $extension;
  $filename= $image->getClientOriginalName() ;
  $image->move($folder, $filename);
  return $filename;
}


function uploadFile($file, $folder)
{
    $path = $file->store($folder);
    return $path;
}



