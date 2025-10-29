<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\UploadPhotoVoice;
use Illuminate\Http\Request;


class UploadPhotoVoiceController extends Controller
{
   public function index()
    {
        $userId = auth()->user()->id;
        $data = UploadPhotoVoice::where('user_id', $userId)->get();
    
        return response()->json(['data' => $data]);
    }
    
    public function store(Request $request)
    {
        $uploadPhoto = new UploadPhotoVoice();
        
        // Associate the upload with the authenticated user
        $uploadPhoto->user_id = auth()->user()->id;
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo_path = uploadImage('assets/admin/uploads', $request->file('photo'));
            $uploadPhoto->photo = $photo_path;
        }
    
        // Handle voice upload
        if ($request->hasFile('voice')) {
            $voice_path = uploadImage('assets/admin/uploads', $request->file('voice'));
            $uploadPhoto->voice = $voice_path;
        }
    
        if ($uploadPhoto->save()) {
            return response()->json(['message' => 'Upload Successfully', 'data' => $uploadPhoto]);
        } else {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

}
