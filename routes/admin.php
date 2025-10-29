<?php

use App\Http\Controllers\Admin\CardController;
use App\Http\Controllers\Admin\CardNumberController;
use App\Http\Controllers\Admin\CouponController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\WalletTransactionController;
use App\Http\Controllers\Admin\WithdrawalRequestController;
use App\Http\Controllers\Admin\AppConfigController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\RatingController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Permission\Models\Permission;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

define('PAGINATION_COUNT',11);
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {




 Route::group(['prefix'=>'admin','middleware'=>'auth:admin'],function(){
 Route::get('/',[DashboardController::class,'index'])->name('admin.dashboard');
 Route::get('logout',[LoginController::class,'logout'])->name('admin.logout');


 // other route


/*         start  update login admin                 */
Route::get('/admin/edit/{id}',[LoginController::class,'editlogin'])->name('admin.login.edit');
Route::post('/admin/update/{id}',[LoginController::class,'updatelogin'])->name('admin.login.update');
/*         end  update login admin                */

/// Role and permission
Route::resource('employee', 'App\Http\Controllers\Admin\EmployeeController',[ 'as' => 'admin']);
Route::get('role', 'App\Http\Controllers\Admin\RoleController@index')->name('admin.role.index');
Route::get('role/create', 'App\Http\Controllers\Admin\RoleController@create')->name('admin.role.create');
Route::get('role/{id}/edit', 'App\Http\Controllers\Admin\RoleController@edit')->name('admin.role.edit');
Route::patch('role/{id}', 'App\Http\Controllers\Admin\RoleController@update')->name('admin.role.update');
Route::post('role', 'App\Http\Controllers\Admin\RoleController@store')->name('admin.role.store');
Route::post('admin/role/delete', 'App\Http\Controllers\Admin\RoleController@delete')->name('admin.role.delete');

Route::get('/permissions/{guard_name}', function($guard_name){
    return response()->json(Permission::where('guard_name',$guard_name)->get());
});



// Notification
Route::get('/notifications/create',[NotificationController::class,'create'])->name('notifications.create');
Route::post('/notifications/send',[NotificationController::class,'send'])->name('notifications.send');



Route::prefix('pages')->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('pages.index');
    Route::get('/create', [PageController::class, 'create'])->name('pages.create');
    Route::post('/store', [PageController::class, 'store'])->name('pages.store');
    Route::get('/edit/{id}', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('/update/{id}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/delete/{id}', [PageController::class, 'destroy'])->name('pages.destroy');
});


// Resource Route
Route::resource('banners', BannerController::class);
Route::resource('ratings', RatingController::class);
Route::resource('complaints', ComplaintController::class);
Route::resource('app-configs', AppConfigController::class);
Route::resource('settings', SettingController::class);
Route::resource('users', UserController::class);
Route::resource('drivers', DriverController::class);
Route::resource('services', ServiceController::class);
Route::resource('coupons', CouponController::class);
Route::resource('pos', POSController::class);
Route::resource('cards', CardController::class);

// Additional Cards Routes
Route::post('cards/{card}/regenerate-numbers', [CardController::class, 'regenerateNumbers'])->name('cards.regenerate-numbers');
Route::get('cards/{card}/card-numbers', [CardController::class, 'showNumbers'])->name('cards.card-numbers');

// Card Numbers Routes
Route::patch('card-numbers/{cardNumber}/toggle-status', [CardNumberController::class, 'toggleStatus'])->name('card-numbers.toggle-status');
Route::patch('card-numbers/{cardNumber}/toggle-activate', [CardNumberController::class, 'toggleActivate'])->name('card-numbers.toggle-activate');

Route::resource('wallet_transactions', WalletTransactionController::class)->except(['edit', 'update', 'destroy']);
Route::get('wallet_transactions/filter', [WalletTransactionController::class, 'filter'])->name('wallet_transactions.filter');
Route::get('users/{id}/transactions', [WalletTransactionController::class, 'userTransactions'])->name('wallet_transactions.userTransactions');
Route::get('drivers/{id}/transactions', [WalletTransactionController::class, 'driverTransactions'])->name('wallet_transactions.driverTransactions');

Route::resource('orders', OrderController::class);
Route::get('orders/filter', [OrderController::class, 'filter'])->name('orders.filter');
Route::post('orders/update-status/{id}', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
Route::post('orders/update-payment-status/{id}', [OrderController::class, 'updatePaymentStatus'])->name('orders.updatePaymentStatus');
Route::get('users/{id}/orders', [OrderController::class, 'userOrders'])->name('orders.userOrders');
Route::get('drivers/{id}/orders', [OrderController::class, 'driverOrders'])->name('orders.driverOrders');

Route::get('/withdrawals', [WithdrawalRequestController::class, 'index'])->name('withdrawals.index');
Route::get('/history/{id}', [WithdrawalRequestController::class, 'history'])->name('admin.withdrawals.history');
Route::post('/approve/{id}', [WithdrawalRequestController::class, 'approve'])->name('admin.withdrawals.approve');
Route::post('/reject/{id}', [WithdrawalRequestController::class, 'reject'])->name('admin.withdrawals.reject');


// functionloty routes
Route::post('drivers/topUp/{id}', [DriverController::class, 'topUp'])->name('drivers.topUp');
Route::get('drivers/transactions/{id}', [DriverController::class, 'transactions'])->name('drivers.transactions');
Route::post('/complaints/{complaint}/update-status', [ComplaintController::class, 'updateStatus'])
    ->name('complaints.update-status');

});
});



Route::group(['namespace'=>'Admin','prefix'=>'admin','middleware'=>'guest:admin'],function(){
    Route::get('login',[LoginController::class,'show_login_view'])->name('admin.showlogin');
    Route::post('login',[LoginController::class,'login'])->name('admin.login');

});







