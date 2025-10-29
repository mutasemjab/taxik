<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OTPService
{
    private $smsConfig;
    private $otpConfig;

    public function __construct()
    {
        $this->smsConfig = [
            'sender_id' => config('sms.sender_id', 'HabibaStore'),
            'account_name' => config('sms.account_name'),
            'account_password' => config('sms.account_password'),
            'base_url' => config('sms.base_url', 'https://www.josms.net/SMSServices/Clients/Prof/RestSingleSMS/SendSMS'),
        ];

        $this->otpConfig = [
            'length' => config('otp.length', 4),
            'expiry_minutes' => config('otp.expiry_minutes', 5),
            'message_template' => config('otp.message_template', 'Your OTP code is: {otp}. Valid for {minutes} minutes.'),
        ];
    }

    /**
     * Generate a random OTP
     *
     * @return string
     */
    public function generateOTP(): string
    {
        $min = pow(10, $this->otpConfig['length'] - 1);
        $max = pow(10, $this->otpConfig['length']) - 1;
        
        return (string) rand($min, $max);
    }

    /**
     * Store OTP in cache with expiry
     *
     * @param string $identifier
     * @param string $otp
     * @return void
     */
    public function storeOTP(string $identifier, string $otp): void
    {
        $cacheKey = 'otp_' . $identifier;
        Cache::put($cacheKey, $otp, $this->otpConfig['expiry_minutes'] * 60);
        
        Log::info('OTP stored for identifier: ' . $identifier, [
            'cache_key' => $cacheKey,
            'expiry_minutes' => $this->otpConfig['expiry_minutes']
        ]);
    }

    /**
     * Verify OTP against stored value
     *
     * @param string $identifier
     * @param string $otp
     * @return bool
     */
    public function verifyOTP(string $identifier, string $otp): bool
    {
        $cacheKey = 'otp_' . $identifier;
        $storedOtp = Cache::get($cacheKey);

        if (!$storedOtp) {
            Log::warning('OTP verification failed - OTP not found or expired for identifier: ' . $identifier);
            return false;
        }

        if ($storedOtp === $otp) {
            // Remove OTP from cache after successful verification
            Cache::forget($cacheKey);
            Log::info('OTP verified successfully for identifier: ' . $identifier);
            return true;
        }

        Log::warning('OTP verification failed - Invalid OTP for identifier: ' . $identifier);
        return false;
    }

    /**
     * Check if OTP exists and is not expired
     *
     * @param string $identifier
     * @return bool
     */
    public function otpExists(string $identifier): bool
    {
        $cacheKey = 'otp_' . $identifier;
        return Cache::has($cacheKey);
    }

    /**
     * Send OTP via SMS
     *
     * @param string $mobile
     * @param string $otp
     * @return array
     */
    public function sendOTPSMS(string $mobile, string $otp): array
    {
        // Format mobile number (remove + if present)
        $formattedMobile = ltrim($mobile, '+');
        
        // Prepare message
        $message = str_replace(
            ['{otp}', '{minutes}'],
            [$otp, $this->otpConfig['expiry_minutes']],
            $this->otpConfig['message_template']
        );

        // Log SMS parameters
        Log::info('SMS Gateway Parameters:', [
            'sender_id' => $this->smsConfig['sender_id'],
            'numbers' => $formattedMobile,
            'account_name' => $this->smsConfig['account_name'],
            'message' => $message,
            'original_mobile' => $mobile,
            'formatted_mobile' => $formattedMobile,
            'otp' => $otp
        ]);

        // Build SMS gateway URL
        $url = $this->smsConfig['base_url'] . '?' . http_build_query([
            'senderid' => $this->smsConfig['sender_id'],
            'numbers' => $formattedMobile,
            'accname' => $this->smsConfig['account_name'],
            'AccPass' => $this->smsConfig['account_password'],
            'msg' => $message
        ]);

        // Log URL (hide password for security)
        Log::info('SMS Gateway URL:', [
            'url' => str_replace($this->smsConfig['account_password'], '***HIDDEN***', $url)
        ]);

        try {
            // Send SMS using cURL
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlInfo = curl_getinfo($curl);
            curl_close($curl);

            // Log complete response
            Log::info('SMS Gateway Response:', [
                'http_code' => $httpCode,
                'response' => $response,
                'curl_error' => $curlError,
                'curl_info' => $curlInfo
            ]);

            if ($httpCode == 200 && empty($curlError)) {
                Log::info('SMS sent successfully for mobile: ' . $formattedMobile);
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'response' => $response
                ];
            } else {
                Log::error('SMS sending failed:', [
                    'http_code' => $httpCode,
                    'curl_error' => $curlError,
                    'response' => $response,
                    'mobile' => $formattedMobile
                ]);
                return [
                    'success' => false,
                    'message' => 'Failed to send SMS',
                    'error' => $curlError ?: 'HTTP Error: ' . $httpCode,
                    'response' => $response
                ];
            }
        } catch (\Exception $e) {
            Log::error('SMS sending exception:', [
                'error' => $e->getMessage(),
                'mobile' => $formattedMobile
            ]);
            return [
                'success' => false,
                'message' => 'SMS sending failed with exception',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send OTP to mobile number (Generate + Store + Send)
     *
     * @param string $mobile
     * @return array
     */
    public function sendOTP(string $mobile): array
    {
        try {
            // Generate OTP
            $otp = $this->generateOTP();
            
            // Store OTP
            $this->storeOTP($mobile, $otp);
            
            // Send SMS
            $smsResult = $this->sendOTPSMS($mobile, $otp);
            
            if ($smsResult['success']) {
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully',
                    'otp' => config('app.debug') ? $otp : null, // Only show OTP in debug mode
                ];
            } else {
                // Remove OTP from cache if SMS failed
                Cache::forget('otp_' . $mobile);
                return $smsResult;
            }
        } catch (\Exception $e) {
            Log::error('Send OTP failed:', [
                'mobile' => $mobile,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle test case for development
     *
     * @param string $mobile
     * @param string $otp
     * @return bool
     */
    public function isTestCase(string $mobile, string $otp): bool
    {
        $testCases = config('otp.test_cases', []);
        
        return isset($testCases[$mobile]) && $testCases[$mobile] === $otp;
    }

    /**
     * Verify OTP with test case support
     *
     * @param string $mobile
     * @param string $otp
     * @return array
     */
    public function verifyOTPWithTestCase(string $mobile, string $otp): array
    {
        // Check for test case first
        if ($this->isTestCase($mobile, $otp)) {
            Log::info('Test case OTP verified for mobile: ' . $mobile);
            return [
                'success' => true,
                'message' => 'OTP verified successfully (test case)',
                'is_test_case' => true
            ];
        }

        // Regular OTP verification
        if ($this->verifyOTP($mobile, $otp)) {
            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'is_test_case' => false
            ];
        }

        // Check if OTP exists but is wrong
        if ($this->otpExists($mobile)) {
            return [
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
                'error_code' => 'INVALID_OTP'
            ];
        }

        // OTP expired or not found
        return [
            'success' => false,
            'message' => 'OTP has expired. Please request a new one.',
            'error_code' => 'OTP_EXPIRED'
        ];
    }

    /**
     * Clear OTP for a specific identifier
     *
     * @param string $identifier
     * @return void
     */
    public function clearOTP(string $identifier): void
    {
        $cacheKey = 'otp_' . $identifier;
        Cache::forget($cacheKey);
        Log::info('OTP cleared for identifier: ' . $identifier);
    }

    /**
     * Get remaining TTL for OTP in seconds
     *
     * @param string $identifier
     * @return int|null
     */
    public function getOTPTTL(string $identifier): ?int
    {
        $cacheKey = 'otp_' . $identifier;
        
        if (Cache::has($cacheKey)) {
            return Cache::getStore()->getRedis()->ttl($cacheKey);
        }
        
        return null;
    }
}