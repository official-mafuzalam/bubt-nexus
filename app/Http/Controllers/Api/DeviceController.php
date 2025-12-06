<?php

namespace App\Http\Controllers\Api;

use App\Models\UserDevice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    public function registerDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios',
            'device_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $device = UserDevice::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'device_token' => $request->device_token
                ],
                [
                    'device_type' => $request->device_type,
                    'device_id' => $request->device_id
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Device registered successfully',
                'data' => $device
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register device',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function unregisterDevice(Request $request, $token)
    {
        try {
            UserDevice::where('user_id', $request->user()->id)
                ->where('device_token', $token)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Device unregistered successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to unregister device',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}