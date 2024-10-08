<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    public function success($data = [], String $message = 'API is success', int $successCode = 200) {
        return response()->json([
            'success' => true,
            'code' => $successCode,
            'message' => $message,
            'data' => $data
        ]); 
    }

    public function failed($data = [], String $message = "API is failed", int $errorCode = 500)
    {
        if (is_a($message, 'Exception')){
            $message = $message->getMessage();
        }
        return response()->json([
            'success' => false,
            'code' => $errorCode,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function getImage(Request $request) {
        $filename = base64_decode($request->filename);
        $path = storage_path($filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
