<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['photo_url'];
     protected function getImageUrl($imageName)
    {
        if ($imageName) {
            $baseUrl = rtrim(config('app.url'), '/');
            return $baseUrl . '/assets/admin/uploads/' . $imageName;
        }
        
        return null;
    }
    
    // Accessor for photo URL
    public function getPhotoUrlAttribute()
    {
        return $this->getImageUrl($this->photo);
    }
}
