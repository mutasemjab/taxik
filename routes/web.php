<?php

use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group whichf
| contains the "web" middleware group. Now create something great!
|
*/




Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {


     Route::get('/',[WebsiteController::class,'index'])->name('website');

     Route::get('/privacy-policy',[WebsiteController::class,'privacyPolicy'])->name('privacy-policy');

     Route::get('/track-order/{token}', [TrackingController::class, 'show'])->name('track.order');

     // API Route for real-time tracking data
     Route::get('/api/track-order/{token}', [TrackingController::class, 'getData'])->name('track.order.data');


});
