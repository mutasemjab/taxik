<?php

namespace App\Traits;

trait Responses
{
    public function success_response($message, $data)
    {
        return response()->json([
            "status" => true,
            "message" => $message,
            "data" => $data
        ]);
    }

    public function error_response($message, $data)
    {
        return response()->json([
            "status" => false,
            "message" => $message,
            "data" => $data
        ]);
    }
}
