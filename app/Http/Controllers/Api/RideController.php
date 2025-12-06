<?php

namespace App\Http\Controllers\Api;

use App\Events\RideLocationUpdated;
use App\Events\RideMessageSent;
use App\Events\RideRequestEvent;
use App\Events\RideRequestStatusUpdated;
use App\Models\Ride;
use App\Models\RideRequest;
use App\Models\UserDevice;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RideLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RideController extends Controller
{
    // Create a new ride
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255',
            'from_lat' => 'required|numeric|between:-90,90',
            'from_lng' => 'required|numeric|between:-180,180',
            'to_lat' => 'required|numeric|between:-90,90',
            'to_lng' => 'required|numeric|between:-180,180',
            'total_seats' => 'required|integer|min:1|max:10',
            'fare_per_seat' => 'required|numeric|min:0',
            'departure_time' => 'required|date|after:now',
            'notes' => 'nullable|string',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_number' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ride = Ride::create([
                'driver_id' => $request->user()->id,
                'from_location' => $request->from_location,
                'to_location' => $request->to_location,
                'from_lat' => $request->from_lat,
                'from_lng' => $request->from_lng,
                'to_lat' => $request->to_lat,
                'to_lng' => $request->to_lng,
                'available_seats' => $request->total_seats,
                'total_seats' => $request->total_seats,
                'fare_per_seat' => $request->fare_per_seat,
                'departure_time' => $request->departure_time,
                'notes' => $request->notes,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'status' => 'pending'
            ]);

            // Update ride status to active after 5 minutes (or immediately)
            $ride->update(['status' => 'active']);

            return response()->json([
                'status' => 'success',
                'message' => 'Ride created successfully',
                'data' => $ride->load('driver')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create ride',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get available rides nearby
    public function getNearbyRides(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'integer|min:1|max:100',
            'max_seats' => 'integer|min:1',
            'max_fare' => 'numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = $request->radius ?? 20; // Default 20km
        $maxSeats = $request->max_seats;
        $maxFare = $request->max_fare;

        $rides = Ride::available()
            ->nearby($lat, $lng, $radius)
            ->when($maxSeats, function ($query) use ($maxSeats) {
                return $query->where('available_seats', '>=', $maxSeats);
            })
            ->when($maxFare, function ($query) use ($maxFare) {
                return $query->where('fare_per_seat', '<=', $maxFare);
            })
            ->with(['driver', 'confirmedPassengers'])
            ->orderBy('departure_time')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $rides
        ]);
    }

    // Get ride details
    public function show($id)
    {
        $ride = Ride::with(['driver', 'confirmedPassengers.passenger', 'requests.passenger'])
            ->find($id);

        if (!$ride) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ride not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $ride
        ]);
    }

    // Request to join a ride
    public function requestRide(Request $request, $rideId)
    {
        $ride = Ride::available()->find($rideId);

        if (!$ride) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ride not found or not available'
            ], 404);
        }

        if ($ride->driver_id == $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot request your own ride'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'requested_seats' => 'required|integer|min:1|max:' . $ride->available_seats,
            'message' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if user already has a pending request
        $existingRequest = RideRequest::where('ride_id', $rideId)
            ->where('passenger_id', $request->user()->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existingRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a request for this ride'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $rideRequest = RideRequest::create([
                'ride_id' => $rideId,
                'passenger_id' => $request->user()->id,
                'requested_seats' => $request->requested_seats,
                'message' => $request->message,
                'status' => 'pending'
            ]);

            // Send real-time notification to driver
            broadcast(new RideRequestEvent($rideRequest));

            // Also send push notification
            $this->sendPushNotification(
                $ride->driver_id,
                'New Ride Request',
                $request->user()->name . ' wants to join your ride',
                [
                    'type' => 'ride_request',
                    'ride_id' => $rideId,
                    'request_id' => $rideRequest->id
                ]
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Ride request sent successfully',
                'data' => $rideRequest->load('passenger')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send ride request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Accept/reject ride request (for driver)
    public function handleRequest(Request $request, $rideId, $requestId)
    {
        $ride = Ride::where('driver_id', $request->user()->id)
            ->find($rideId);

        if (!$ride) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ride not found or you are not the driver'
            ], 404);
        }

        $rideRequest = RideRequest::where('ride_id', $rideId)
            ->where('id', $requestId)
            ->where('status', 'pending')
            ->first();

        if (!$rideRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request not found or already processed'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:accept,reject'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            if ($request->action == 'accept') {
                // Check if seats are still available
                if ($ride->available_seats < $rideRequest->requested_seats) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Not enough seats available'
                    ], 400);
                }

                // UPDATE THE STATUS FIRST
                $rideRequest->status = 'accepted';
                $rideRequest->save();

                // Broadcast after updating status
                broadcast(new RideRequestStatusUpdated($rideRequest));

                $ride->available_seats -= $rideRequest->requested_seats;
                $ride->save();

                // Send notification to passenger
                $this->sendPushNotification(
                    $rideRequest->passenger_id,
                    'Ride Request Accepted',
                    'Your ride request has been accepted by ' . $ride->driver->name,
                    [
                        'type' => 'request_accepted',
                        'ride_id' => $rideId
                    ]
                );

            } else {
                $rideRequest->status = 'rejected';
                $rideRequest->save();

                // Broadcast after updating status
                broadcast(new RideRequestStatusUpdated($rideRequest));

                // Send notification to passenger
                $this->sendPushNotification(
                    $rideRequest->passenger_id,
                    'Ride Request Rejected',
                    'Your ride request has been rejected',
                    [
                        'type' => 'request_rejected',
                        'ride_id' => $rideId
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Request ' . $request->action . 'ed successfully',
                'data' => $rideRequest->load('passenger')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update ride status
    public function updateStatus(Request $request, $rideId)
    {
        $ride = Ride::where('driver_id', $request->user()->id)
            ->find($rideId);

        if (!$ride) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ride not found or you are not the driver'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ride->status = $request->status;
        $ride->save();

        // Notify all passengers about status change
        $passengers = $ride->confirmedPassengers;
        foreach ($passengers as $request) {
            $this->sendPushNotification(
                $request->passenger_id,
                'Ride Status Updated',
                'Ride status changed to ' . $request->status,
                [
                    'type' => 'ride_status_update',
                    'ride_id' => $rideId,
                    'status' => $request->status
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ride status updated successfully',
            'data' => $ride
        ]);
    }

    // Get user's rides (as driver or passenger)
    public function myRides(Request $request)
    {
        $user = $request->user();

        $asDriver = Ride::where('driver_id', $user->id)
            ->with(['confirmedPassengers.passenger'])
            ->orderBy('created_at', 'desc')
            ->get();

        $asPassenger = RideRequest::where('passenger_id', $user->id)
            ->where('status', 'accepted')
            ->with(['ride.driver', 'ride.confirmedPassengers'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('ride');

        return response()->json([
            'status' => 'success',
            'data' => [
                'as_driver' => $asDriver,
                'as_passenger' => $asPassenger
            ]
        ]);
    }

    // Update location (real-time sharing)
    public function updateLocation(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'bearing' => 'nullable|numeric|between:0,360',
            'accuracy' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if user is part of this ride
        $ride = Ride::where(function ($query) use ($request, $rideId) {
            $query->where('id', $rideId)
                ->where('driver_id', $request->user()->id);
        })
            ->orWhereHas('confirmedPassengers', function ($query) use ($request) {
                $query->where('passenger_id', $request->user()->id);
            })
            ->first();

        if (!$ride) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not part of this ride'
            ], 403);
        }

        try {
            $location = RideLocation::create([
                'ride_id' => $rideId,
                'user_id' => $request->user()->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'speed' => $request->speed,
                'bearing' => $request->bearing,
                'accuracy' => $request->accuracy,
                'recorded_at' => now() // Add this line
            ]);

            // Load user relationship
            $location->load('user');

            // Broadcast location via Reverb
            broadcast(new RideLocationUpdated($rideId, $location));

            return response()->json([
                'status' => 'success',
                'message' => 'Location updated',
                'data' => $location
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get ride locations (for tracking)
    public function getLocations($rideId)
    {
        $locations = \App\Models\RideLocation::where('ride_id', $rideId)
            ->with('user')
            ->orderBy('recorded_at', 'desc')
            ->limit(100) // Limit to last 100 locations
            ->get()
            ->groupBy('user_id')
            ->map(function ($userLocations) {
                return $userLocations->first(); // Get latest location per user
            });

        return response()->json([
            'status' => 'success',
            'data' => $locations->values()
        ]);
    }

    // Send message in ride chat
    public function sendMessage(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'type' => 'required|in:text,location,image'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if user is part of this ride
        $ride = Ride::where(function ($query) use ($request, $rideId) {
            $query->where('id', $rideId)
                ->where('driver_id', $request->user()->id);
        })
            ->orWhereHas('confirmedPassengers', function ($query) use ($request) {
                $query->where('passenger_id', $request->user()->id)
                    ->where('status', 'accepted');
            })
            ->first();

        if (!$ride) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not part of this ride'
            ], 403);
        }

        try {
            $message = Message::create([
                'ride_id' => $rideId,
                'sender_id' => $request->user()->id,
                'message' => $request->message,
                'type' => $request->type
            ]);

            // Load sender relationship
            $message->load('sender');

            // Broadcast message using Reverb
            broadcast(new RideMessageSent($rideId, $message));

            // Send push notifications to other participants
            $participants = collect([$ride->driver_id])
                ->merge($ride->confirmedPassengers->pluck('passenger_id'))
                ->filter(fn($id) => $id != $request->user()->id)
                ->unique();

            foreach ($participants as $userId) {
                $this->sendPushNotification(
                    $userId,
                    'New Message in Ride Chat',
                    $request->user()->name . ': ' . substr($request->message, 0, 50) . '...',
                    [
                        'type' => 'ride_message',
                        'ride_id' => $rideId,
                        'message_id' => $message->id
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent',
                'data' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get ride messages
    public function getMessages($rideId)
    {
        $messages = Message::where('ride_id', $rideId)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    // Helper function for push notifications
    private function sendPushNotification($userId, $title, $body, $data = [])
    {
        try {
            $devices = UserDevice::where('user_id', $userId)->get();

            foreach ($devices as $device) {
                // Implement FCM (Firebase Cloud Messaging) here
                // Example:
                // $fcm = new FCM();
                // $fcm->send($device->device_token, $title, $body, $data);
            }

            // For now, just log
            Log::info("Push notification sent to user $userId: $title - $body", $data);

        } catch (\Exception $e) {
            Log::error('Failed to send push notification: ' . $e->getMessage());
        }
    }
}