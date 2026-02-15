<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\ClassTeacher;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Admin\FCMController; // <-- Import the FCMController here
use App\Models\ParentStudent;
use App\Services\OTPService;
use App\Traits\Responses;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    use Responses;

    protected $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }


    public function updateStatusOnOff()
    {
        $driver = auth('driver-api')->user();

        // Check if driver exists and has a valid status
        if (!in_array($driver->status, [1, 2])) {
            return response()->json(['message' => 'Invalid status value.'], 400);
        }

        // Toggle status
        $driver->status = $driver->status == 1 ? 2 : 1;
        $driver->save();
        return $this->success_response('Status updated successfully.', $driver->status);
    }

    public function getStatusOfDriver()
    {
        $driver = auth('driver-api')->user()->activate;

        return response()->json([
            'message' => 'Driver is active',
            'status' => 1
        ]);
    }

    public function active()
    {
        $user = auth()->user();
        if ($user->activate == 2) {
            return $this->error_response('Your account has been InActive', null);
        }

        return $this->success_response('User retrieved successfully', $user);
    }

    public function deleteAccount(Request $request)
    {
        try {
            // Define disallowed statuses
            $disallowedStatuses = [
                'pending',
                'accepted',
                'on_the_way',
                'arrived',
                'waiting_payment',
                'started'
            ];

            // Check if the request is for a user or driver
            $userApi = auth('user-api')->user();
            $driverApi = auth('driver-api')->user();

            if ($userApi) {
                // Check if the user has any active orders
                $hasActiveOrders = \App\Models\Order::where('user_id', $userApi->id)
                    ->whereIn('status', $disallowedStatuses)
                    ->exists();

                if ($hasActiveOrders) {
                    return $this->error_response('You cannot delete your account while you have active or pending orders.', [], 400);
                }

                // Deactivate and revoke tokens
                $userApi->update(['activate' => 2]);
                $userApi->tokens()->delete();

                return $this->success_response('User account deleted successfully', null);
            } elseif ($driverApi) {
                // Check if the driver has any active orders
                $hasActiveOrders = \App\Models\Order::where('driver_id', $driverApi->id)
                    ->whereIn('status', $disallowedStatuses)
                    ->exists();

                if ($hasActiveOrders) {
                    return $this->error_response('You cannot delete your account while you have active or pending orders.', [], 400);
                }

                // Deactivate and revoke tokens
                $driverApi->update(['activate' => 2]);
                $driverApi->tokens()->delete();

                return $this->success_response('Driver account deleted successfully', null);
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Exception $e) {
            \Log::error('Account deletion error: ' . $e->getMessage());
            return $this->error_response('Failed to delete account', ['error' => $e->getMessage()]);
        }
    }

    public function logout()
    {
        try {
            // Check if the request is authenticated with user-api guard
            $userApi = auth('user-api')->user();

            // Check if the request is authenticated with driver-api guard
            $driverApi = auth('driver-api')->user();

            if ($userApi) {
                // Revoke all tokens for user
                $userApi->tokens()->delete();
                return $this->success_response('User logout successful', []);
            } elseif ($driverApi) {
                // Revoke all tokens for driver
                $driverApi->tokens()->delete();
                return $this->success_response('Driver logout successful', []);
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Throwable $th) {
            // Log the error for debugging
            \Log::error('Logout error: ' . $th->getMessage());
            return $this->error_response('Failed to logout', []);
        }
    }

    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'country_code' => 'required|string',
            'fcm_token' => 'nullable|string',
            'user_type' => 'nullable|in:user,driver'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $phone = $request->phone;
        $countryCode = $request->country_code;
        $userType = $request->user_type ?? 'user';

        $model = ($userType == 'driver') ? \App\Models\Driver::class : \App\Models\User::class;
        $guard = ($userType == 'driver') ? 'driver-api' : 'user-api';

        $user = $model::where('phone', $phone)
            ->where('country_code', $countryCode)
            ->first();

        if ($user) {
            if ($request->has('fcm_token')) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }

            $accessToken = $user->createToken('authToken')->accessToken;

            return $this->success_response('Success', [
                'user_exists' => true,
                'account_status' => 'active',
                'user_type' => $userType,
                'user' => $user,
                'token' => $accessToken,
            ]);
        }

        return $this->success_response('Phone number not registered', [
            'user_exists' => false,
            'user_type' => $userType,
            'country_code' => $countryCode,
        ]);
    }

    public function register(Request $request)
{
    $userType = $request->user_type ?? 'user';

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'country_code' => 'required',
        'phone' => 'required|string|unique:' . ($userType === 'driver' ? 'drivers' : 'users'),
        'email' => 'nullable|email|unique:' . ($userType === 'driver' ? 'drivers' : 'users'),
        'fcm_token' => 'nullable|string',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg',
    ]);

    // ========== ADD REFERRAL CODE VALIDATION ==========
    if ($userType === 'user') {
        $validator->addRules([
            'referral_code' => 'nullable|string',
        ]);
    }
    // ========== END REFERRAL CODE VALIDATION ==========

    if ($userType === 'driver') {
        $validator->addRules([
            'sos_phone' => 'nullable|string',
            'option_ids' => 'required|array',
            'option_ids.*' => 'required|exists:options,id',
            'photo_of_car' => 'required|image|mimes:jpeg,png,jpg',
            'passenger_number' => 'nullable',
            'model' => 'nullable|string|max:255',
            'production_year' => 'nullable|string|max:4',
            'color' => 'nullable|string|max:255',
            'plate_number' => 'nullable|string|max:255',
            'driving_license_front' => 'required|image|mimes:jpeg,png,jpg|max:6048',
            'driving_license_back' => 'required|image|mimes:jpeg,png,jpg|max:6048',
            'car_license_front' => 'required|image|mimes:jpeg,png,jpg|max:6048',
            'car_license_back' => 'required|image|mimes:jpeg,png,jpg|max:6048',
            'no_criminal_record' => 'required|image|mimes:jpeg,png,jpg|max:6048',
        ]);
    }

    if ($validator->fails()) {
        $errorMessage = $validator->errors()->first();

        \Log::channel('register_failed')->error('Registration validation failed', [
            'user_type' => $userType,
            'phone' => $request->phone,
            'email' => $request->email,
            'name' => $request->name,
            'errors' => $validator->errors()->toArray(),
            'first_error' => $errorMessage,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return $this->error_response($errorMessage, $validator->errors());
    }

    try {
        DB::beginTransaction();

        $userData = $request->only(['name', 'country_code', 'phone', 'email', 'fcm_token']);
        $userData['balance'] = 0;

        // Validate file before processing
        if ($request->hasFile('photo')) {
            $photoFile = $request->file('photo');
            if (!$photoFile->isValid()) {
                throw new \Exception('The photo file is invalid or corrupted. Please try uploading again.');
            }
            if (!$photoFile->getRealPath() || !is_readable($photoFile->getRealPath())) {
                throw new \Exception('The photo file cannot be read. Please try uploading again.');
            }
            $userData['photo'] = uploadImage('assets/admin/uploads', $photoFile);
        }

        $welcomeBonus = 0;
        $welcomeBonusApplied = false;

        if ($userType === 'driver') {
            // Validate all required files before processing
            $requiredFiles = [
                'photo_of_car',
                'driving_license_front',
                'driving_license_back',
                'car_license_front',
                'car_license_back',
                'no_criminal_record'
            ];

            foreach ($requiredFiles as $fileField) {
                if ($request->hasFile($fileField)) {
                    $file = $request->file($fileField);
                    if (!$file->isValid()) {
                        throw new \Exception("The {$fileField} file is invalid or corrupted. Please try uploading again.");
                    }
                    if (!$file->getRealPath() || !is_readable($file->getRealPath())) {
                        throw new \Exception("The {$fileField} file cannot be read. The file may have been deleted during upload. Please try again.");
                    }
                }
            }

            $welcomeBonus = $this->getSettingValue('new_driver_register_add_balance', 0);

            $userData['sos_phone'] = $request->sos_phone;
            $userData['activate'] = 3;
            $userData['status'] = 2;
            $userData['balance'] = $welcomeBonus;
            $userData['referral_code'] = $this->generateReferralCode();

            $userData = array_merge($userData, $request->only(['passenger_number', 'model', 'production_year', 'color', 'plate_number']));

            // Upload driver photo if exists (before creating driver record)
            if ($request->hasFile('photo')) {
                $userData['photo'] = uploadImage('assets/admin/uploads', $request->file('photo'));
            }

            // Create driver record first to get the ID
            $user = \App\Models\Driver::create($userData);
            $driverId = 'driver_' . $user->id;

            // Now upload all driver-specific images to their folder
            try {
                if ($request->hasFile('photo_of_car')) {
                    $user->photo_of_car = uploadImage('assets/admin/uploads', $request->file('photo_of_car'), $driverId);
                }

                if ($request->hasFile('driving_license_front')) {
                    $user->driving_license_front = uploadImage('assets/admin/uploads', $request->file('driving_license_front'), $driverId);
                }

                if ($request->hasFile('driving_license_back')) {
                    $user->driving_license_back = uploadImage('assets/admin/uploads', $request->file('driving_license_back'), $driverId);
                }

                if ($request->hasFile('car_license_front')) {
                    $user->car_license_front = uploadImage('assets/admin/uploads', $request->file('car_license_front'), $driverId);
                }

                if ($request->hasFile('car_license_back')) {
                    $user->car_license_back = uploadImage('assets/admin/uploads', $request->file('car_license_back'), $driverId);
                }

                if ($request->hasFile('no_criminal_record')) {
                    $user->no_criminal_record = uploadImage('assets/admin/uploads', $request->file('no_criminal_record'), $driverId);
                }
            } catch (\Exception $fileException) {
                throw new \Exception('File upload failed: ' . $fileException->getMessage() . '. Please check your internet connection and try again.');
            }

            // Save the updated image paths to database
            $user->save();

            if ($request->has('option_ids') && is_array($request->option_ids)) {
                $user->options()->attach($request->option_ids);
            }

            if ($welcomeBonus > 0) {
                DB::table('wallet_transactions')->insert([
                    'driver_id' => $user->id,
                    'amount' => $welcomeBonus,
                    'type_of_transaction' => 1,
                    'note' => 'Welcome bonus for new driver registration',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $welcomeBonusApplied = true;
            }
        } else {
            // ========== USER REGISTRATION ==========
            $welcomeBonus = $this->getSettingValue('new_user_register_add_balance', 0);

            $userData['referral_code'] = $this->generateReferralCode();
            $userData['balance'] = $welcomeBonus;

            $user = \App\Models\User::create($userData);

            if ($welcomeBonus > 0) {
                DB::table('wallet_transactions')->insert([
                    'user_id' => $user->id,
                    'amount' => $welcomeBonus,
                    'type_of_transaction' => 1,
                    'note' => 'Welcome bonus for new user registration',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $welcomeBonusApplied = true;
            }

            \App\Models\WalletDistribution::applyToUser($user);

            // ========== PROCESS REFERRAL CODE ==========
            if ($request->has('referral_code') && !empty($request->referral_code)) {
                try {
                    $referralService = app(\App\Services\ReferralService::class);
                    $referralResult = $referralService->processUserReferral($user, $request->referral_code);

                    if ($referralResult['success']) {
                        Log::info("Referral processed successfully for user {$user->id}", $referralResult);
                    } else {
                        Log::warning("Referral processing failed for user {$user->id}: {$referralResult['message']}");
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing referral for user {$user->id}: " . $e->getMessage());
                }
            }
            // ========== END PROCESS REFERRAL CODE ==========
        }

        DB::commit();

        $accessToken = $user->createToken('authToken')->accessToken;

        $responseData = [
            'token' => $accessToken,
            'user' => $user,
            'new_user' => true,
        ];

        if ($welcomeBonusApplied) {
            $responseData['welcome_bonus'] = [
                'amount' => $welcomeBonus,
                'message' => $userType === 'driver'
                    ? "Welcome! You've received {$welcomeBonus} JD as a welcome bonus."
                    : "Welcome! You've received {$welcomeBonus} JD as a welcome bonus.",
                'current_balance' => $user->balance
            ];
        }

        return $this->success_response('Registration successful', $responseData);
    } catch (\Exception $e) {
        DB::rollBack();

        \Log::channel('register_failed')->error('Registration process failed', [
            'user_type' => $userType,
            'phone' => $request->phone,
            'email' => $request->email,
            'name' => $request->name,
            'error_message' => $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString(),
            'request_data' => $request->except(['photo', 'photo_of_car', 'driving_license_front', 'driving_license_back', 'car_license_front', 'car_license_back', 'no_criminal_record']),
            'uploaded_files' => [
                'photo' => $request->hasFile('photo') ? 'present' : 'missing',
                'photo_of_car' => $request->hasFile('photo_of_car') ? 'present' : 'missing',
                'driving_license_front' => $request->hasFile('driving_license_front') ? 'present' : 'missing',
                'driving_license_back' => $request->hasFile('driving_license_back') ? 'present' : 'missing',
                'car_license_front' => $request->hasFile('car_license_front') ? 'present' : 'missing',
                'car_license_back' => $request->hasFile('car_license_back') ? 'present' : 'missing',
                'no_criminal_record' => $request->hasFile('no_criminal_record') ? 'present' : 'missing',
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return $this->error_response('Registration failed. Please try again.', ['error' => $e->getMessage()]);
    }
}


    /**
     * Get setting value by key with default fallback
     */
    private function getSettingValue($key, $default = 0)
    {
        $setting = DB::table('settings')->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'country_code' => 'required|string',
        ]);

        $fullPhone = $request->country_code . $request->phone;
        $otpResult = $this->otpService->sendOTP($fullPhone);

        if ($otpResult['success']) {
            return $this->success_response('OTP sent successfully', [
                'debug_otp' => $otpResult['otp'] ?? null,
            ]);
        }

        return $this->error_response($otpResult['message'], $otpResult['error'] ?? null);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'country_code' => 'required|string',
            'otp' => 'required|string',
        ]);

        $fullPhone = $request->country_code . $request->phone;
        $otpResult = $this->otpService->verifyOTPWithTestCase($fullPhone, $request->otp);

        if ($otpResult['success']) {
            return $this->success_response('OTP verified successfully', []);
        }

        return $this->error_response($otpResult['message'], $otpResult['error_code'] ?? null);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'country_code' => 'required|string',
        ]);

        $fullPhone = $request->country_code . $request->phone;
        $otpResult = $this->otpService->sendOTP($fullPhone);

        if ($otpResult['success']) {
            return $this->success_response('OTP resent successfully', [
                'debug_otp' => $otpResult['otp'] ?? null,
            ]);
        }

        return $this->error_response($otpResult['message'], $otpResult['error'] ?? null);
    }



    public function userProfile()
    {
        try {
            // Check both authentication guards
            $userApi = auth('user-api')->user();

            if ($userApi) {
                // If it's a regular user
                return $this->success_response('User profile retrieved', $userApi);
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Throwable $th) {
            \Log::error('Profile retrieval error: ' . $th->getMessage());
            return $this->error_response('Failed to retrieve profile', []);
        }
    }

    public function driverProfile()
    {
        try {
            $driverApi = auth('driver-api')->user();

            if ($driverApi) {
                $driverApi->load('options');
                return $this->success_response('Driver profile retrieved', $driverApi);
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Throwable $th) {
            \Log::error('Profile retrieval error: ' . $th->getMessage());
            return $this->error_response('Failed to retrieve profile', []);
        }
    }

    public function updateUserProfile(Request $request)
    {
        try {
            $user = auth('user-api')->user();

            if (!$user) {
                return $this->error_response('Unauthenticated', [], 401);
            }

            $validationRules = [
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string',
                'sos_phone' => 'nullable|string',
                'country_code' => 'nullable|string',
                'photo' => 'nullable|image',
            ];

            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }

            $data = $request->only(['name', 'email', 'phone', 'sos_phone', 'country_code']);

            if ($request->hasFile('photo')) {
                if ($user->photo && file_exists('assets/admin/uploads/' . $user->photo)) {
                    unlink('assets/admin/uploads/' . $user->photo);
                }
                $data['photo'] = uploadImage('assets/admin/uploads', $request->file('photo'));
            }

            $user->update($data);

            return $this->success_response('User profile updated successfully', $user);
        } catch (\Throwable $th) {
            \Log::error('User Profile update error: ' . $th->getMessage());
            return $this->error_response('Failed to update profile', ['message' => $th->getMessage()]);
        }
    }

    public function updateDriverProfile(Request $request)
    {
        try {
            $driver = auth('driver-api')->user();

            if (!$driver) {
                return $this->error_response('Unauthenticated', [], 401);
            }

            $validationRules = [
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:drivers,email,' . $driver->id,
                'phone' => 'nullable|string',
                'sos_phone' => 'nullable|string',
                'country_code' => 'nullable|string',
                'photo' => 'nullable|image',

                'photo_of_car' => 'nullable|image',
                'model' => 'nullable|string|max:255',
                'production_year' => 'nullable|string|max:4',
                'color' => 'nullable|string|max:255',
                'plate_number' => 'nullable|string|max:255',
                'no_criminal_record' => 'nullable|image',
                'driving_license_front' => 'nullable|image',
                'driving_license_back' => 'nullable|image',
                'car_license_front' => 'nullable|image',
                'car_license_back' => 'nullable|image',
                'option_ids' => 'nullable|array',
                'option_ids.*' => 'nullable|exists:options,id'
            ];

            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }

            $data = $request->only([
                'name',
                'email',
                'phone',
                'sos_phone',
                'country_code',
                'model',
                'production_year',
                'color',
                'plate_number'
            ]);

            // Handle photo uploads
            $photoFields = [
                'photo' => 'assets/admin/uploads',
                'photo_of_car' => 'assets/admin/uploads',
                'driving_license_front' => 'assets/admin/uploads',
                'driving_license_back' => 'assets/admin/uploads',
                'car_license_front' => 'assets/admin/uploads',
                'car_license_back' => 'assets/admin/uploads',
                'no_criminal_record' => 'assets/admin/uploads',
            ];

            foreach ($photoFields as $field => $path) {
                if ($request->hasFile($field)) {
                    if ($driver->$field && file_exists($path . '/' . $driver->$field)) {
                        unlink($path . '/' . $driver->$field);
                    }
                    $data[$field] = uploadImage($path, $request->file($field));
                }
            }

            $driver->update($data);

            if ($request->has('option_ids') && is_array($request->option_ids)) {
                $driver->options()->sync($request->option_ids);
            }

            $driver->load('options');

            return $this->success_response('Driver profile updated successfully', $driver);
        } catch (\Throwable $th) {
            \Log::error('Driver Profile update error: ' . $th->getMessage());
            return $this->error_response('Failed to update profile', ['message' => $th->getMessage()]);
        }
    }



    public function notifications()
    {
        $user = auth()->user();

        // Define user_type-based notification types
        $userTypeMapping = [
            1 => 1, // Regular Users
            2 => 3, // Teachers
            3 => 2, // Parents
        ];

        // Fetch notifications
        $notifications = Notification::query()
            ->where(function ($query) use ($user, $userTypeMapping) {
                $query->where('type', 0) // Global notifications (for all users)
                    ->orWhere(function ($q) use ($user) {
                        // Notifications specifically for this user
                        $q->where('type', 4)->where('user_id', $user->id);
                    });

                // Include user_type-specific notifications if applicable
                if (isset($userTypeMapping[$user->user_type])) {
                    $query->orWhere('type', $userTypeMapping[$user->user_type]);
                }
            })
            ->orderBy('id', 'DESC')
            ->get();

        return $this->success_response('Notifications retrieved successfully', $notifications);
    }

    public function sendToUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'title' => 'required|string',
            'body' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            // Call the sendMessageToUser method in the FCMController
            $response = FCMController::sendMessageToUser(
                $request->title,
                $request->body,
                $request->user_id,
            );

            if ($response) {
                return redirect()->back()->with('message', 'Notification sent successfully to the user');
            } else {
                return redirect()->back()->with('error', 'Notification was not sent to the user');
            }
        } catch (\Exception $e) {
            \Log::error('FCM Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique referral code for both users and drivers
     */
    private function generateReferralCode()
    {
        do {
            $referralCode = strtoupper(substr(md5(time() . rand(1000, 9999)), 0, 8));

            // Check uniqueness in both users and drivers tables
            $existsInUsers = \App\Models\User::where('referral_code', $referralCode)->exists();
            $existsInDrivers = \App\Models\Driver::where('referral_code', $referralCode)->exists();
        } while ($existsInUsers || $existsInDrivers);

        return $referralCode;
    }
}
