<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class FirebaseServiceProvider extends ServiceProvider
{
   
   public function register(): void
    {
        $this->app->singleton(Firestore::class, function ($app) {
            $credentialsPath = config('firebase.credentials.file');

            if (!$credentialsPath || !file_exists(base_path($credentialsPath))) {
                throw new \Exception("Firebase credentials file not found at: $credentialsPath");
            }

            return (new Factory)
                ->withServiceAccount(base_path($credentialsPath))
                ->createFirestore();
        });
    }


    public function boot(): void
    {
        //
    }
}