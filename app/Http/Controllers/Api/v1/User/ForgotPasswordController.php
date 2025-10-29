<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{

    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|exists:users,phone',
            'password' => 'required'
        ]);
        if (!User::where('phone', $request->phone)->exists()) {
            return response(['errors' => ['This phone is not registered']], 403);
        }
        DB::beginTransaction();
        try {
            $user = User::where('phone', $request->phone)->first();
            $user->password = Hash::make($request->password);
            $user->save();
            DB::commit();
            return response(['message' => 'password updated', 'user' => $user], 403);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            DB::rollBack();
            return response(['errors' => $e->getMessage()], 403);
        }
    }
}
