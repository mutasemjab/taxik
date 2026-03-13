<?php
// config/firebase.php

return [
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS_PATH'),
    ],
    
    'project_id' => env('FIREBASE_PROJECT_ID'),
    
    'database_url' => env('FIREBASE_DATABASE_URL'),

    'google' => [
        'maps_key' => env('GOOGLE_MAPS_API_KEY'),
    ],
];