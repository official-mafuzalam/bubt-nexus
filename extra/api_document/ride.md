# BUBT Nexus Ride-Sharing API Documentation

**Base URL:** `{{apiBase}}`  
**Authentication:** Bearer Token (Sanctum)  
**Content-Type:** `application/json`

---

## Table of Contents

1. [Authentication](#authentication)
2. [Ride Endpoints](#ride-endpoints)
3. [Ride Request Endpoints](#ride-request-endpoints)
4. [Location Tracking Endpoints](#location-tracking-endpoints)
5. [Messaging Endpoints](#messaging-endpoints)
6. [Device Management Endpoints](#device-management-endpoints)
7. [Response Format](#response-format)
8. [Error Handling](#error-handling)

---

## Authentication

All API endpoints (except registration and login) require a Bearer token in the `Authorization` header.

```
Authorization: Bearer {access_token}
```

**Token Acquisition:**

- Register: `POST /api/register`
- Login: `POST /api/login`

---

## Ride Endpoints

### 1. Create a New Ride

Creates a new ride offering by a driver.

**Endpoint:** `POST /api/rides/create`

**Authentication:** Required (Bearer Token)

**Request Body:**

```json
{
    "from_location": "BUBT, Dhaka",
    "to_location": "Farmgate, Dhaka",
    "from_lat": 23.810331,
    "from_lng": 90.412521,
    "to_lat": 23.755861,
    "to_lng": 90.389982,
    "total_seats": 4,
    "fare_per_seat": 50.0,
    "departure_time": "2024-12-25 14:30:00",
    "notes": "Going via Mirpur road",
    "vehicle_type": "Car",
    "vehicle_number": "DHA-12345"
}
```

**Request Parameters:**

| Field            | Type     | Required | Description                                |
| ---------------- | -------- | -------- | ------------------------------------------ |
| `from_location`  | string   | ✓        | Starting location name (max 255 chars)     |
| `to_location`    | string   | ✓        | Destination location name (max 255 chars)  |
| `from_lat`       | decimal  | ✓        | Starting latitude (-90 to 90)              |
| `from_lng`       | decimal  | ✓        | Starting longitude (-180 to 180)           |
| `to_lat`         | decimal  | ✓        | Destination latitude (-90 to 90)           |
| `to_lng`         | decimal  | ✓        | Destination longitude (-180 to 180)        |
| `total_seats`    | integer  | ✓        | Total available seats (1-10)               |
| `fare_per_seat`  | decimal  | ✓        | Price per seat (minimum 0)                 |
| `departure_time` | datetime | ✓        | Ride departure time (must be in future)    |
| `notes`          | string   | ✗        | Optional ride notes/description            |
| `vehicle_type`   | string   | ✗        | Vehicle type (max 50 chars)                |
| `vehicle_number` | string   | ✗        | Vehicle registration number (max 20 chars) |

**Success Response:** `201 Created`

```json
{
    "status": "success",
    "message": "Ride created successfully",
    "data": {
        "id": 1,
        "driver_id": 1,
        "from_location": "BUBT, Dhaka",
        "to_location": "Farmgate, Dhaka",
        "from_lat": "23.81033100",
        "from_lng": "90.41252100",
        "to_lat": "23.75586100",
        "to_lng": "90.38998200",
        "available_seats": 4,
        "total_seats": 4,
        "fare_per_seat": "50.00",
        "departure_time": "2025-12-25T14:30:00.000000Z",
        "notes": "Going via Mirpur road",
        "vehicle_type": "Car",
        "vehicle_number": "DHA-12345",
        "status": "active",
        "created_at": "2025-12-05T05:51:27.000000Z",
        "updated_at": "2025-12-05T05:51:27.000000Z",
        "driver": {
            "id": 1,
            "name": "Admin",
            "email": "admin@gmail.com",
            "status": "active"
        }
    }
}
```

**Error Response:** `422 Unprocessable Entity`

```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "total_seats": ["The total seats field is required."],
        "departure_time": ["The departure time must be after now."]
    }
}
```

---

### 2. Get Nearby Rides

Retrieves available rides within a specified radius (geolocation-based search).

**Endpoint:** `GET /api/rides/nearby`

**Authentication:** Required (Bearer Token)

**Query Parameters:**

| Parameter   | Type    | Required | Description                              |
| ----------- | ------- | -------- | ---------------------------------------- |
| `latitude`  | decimal | ✓        | User's current latitude (-90 to 90)      |
| `longitude` | decimal | ✓        | User's current longitude (-180 to 180)   |
| `radius`    | integer | ✗        | Search radius in km (1-100, default: 20) |
| `max_seats` | integer | ✗        | Minimum available seats required         |
| `max_fare`  | decimal | ✗        | Maximum acceptable fare per seat         |

**Example Request:**

```
GET /api/rides/nearby?latitude=23.810331&longitude=90.412521&radius=20
```

**Success Response:** `200 OK`

```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "driver_id": 1,
            "from_location": "BUBT, Dhaka",
            "to_location": "Farmgate, Dhaka",
            "from_lat": "23.81033100",
            "from_lng": "90.41252100",
            "to_lat": "23.75586100",
            "to_lng": "90.38998200",
            "available_seats": 4,
            "total_seats": 4,
            "fare_per_seat": "50.00",
            "status": "active",
            "departure_time": "2025-12-25T14:30:00.000000Z",
            "notes": "Going via Mirpur road",
            "distance": 0,
            "driver": {
                "id": 1,
                "name": "Admin",
                "email": "admin@gmail.com",
                "status": "active"
            },
            "confirmed_passengers": []
        }
    ]
}
```

---

### 3. Get Ride Details

Retrieves detailed information about a specific ride.

**Endpoint:** `GET /api/rides/{id}`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description |
| --------- | ------- | -------- | ----------- |
| `id`      | integer | ✓        | Ride ID     |

**Example Request:**

```
GET /api/rides/1
```

**Success Response:** `200 OK`

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "driver_id": 1,
        "from_location": "BUBT, Dhaka",
        "to_location": "Farmgate, Dhaka",
        "from_lat": "23.81033100",
        "from_lng": "90.41252100",
        "to_lat": "23.75586100",
        "to_lng": "90.38998200",
        "available_seats": 4,
        "total_seats": 4,
        "fare_per_seat": "50.00",
        "status": "active",
        "departure_time": "2025-12-25T14:30:00.000000Z",
        "notes": "Going via Mirpur road",
        "vehicle_type": "Car",
        "vehicle_number": "DHA-12345",
        "created_at": "2025-12-05T05:51:27.000000Z",
        "updated_at": "2025-12-05T05:51:27.000000Z",
        "driver": {
            "id": 1,
            "name": "Admin",
            "email": "admin@gmail.com",
            "status": "active"
        },
        "confirmed_passengers": [],
        "requests": []
    }
}
```

**Error Response:** `404 Not Found`

```json
{
    "status": "error",
    "message": "Ride not found"
}
```

---

### 4. Get My Rides

Retrieves rides where the authenticated user is either a driver or passenger.

**Endpoint:** `GET /api/rides/my-rides`

**Authentication:** Required (Bearer Token)

**Success Response:** `200 OK`

```json
{
    "status": "success",
    "data": {
        "as_driver": [
            {
                "id": 1,
                "driver_id": 1,
                "from_location": "BUBT, Dhaka",
                "to_location": "Farmgate, Dhaka",
                "from_lat": "23.81033100",
                "from_lng": "90.41252100",
                "to_lat": "23.75586100",
                "to_lng": "90.38998200",
                "available_seats": 2,
                "total_seats": 4,
                "fare_per_seat": "50.00",
                "status": "active",
                "departure_time": "2025-12-25T14:30:00.000000Z",
                "notes": "Going via Mirpur road",
                "vehicle_type": "Car",
                "vehicle_number": "DHA-12345",
                "created_at": "2025-12-05T05:51:27.000000Z",
                "updated_at": "2025-12-05T05:58:29.000000Z",
                "confirmed_passengers": []
            }
        ],
        "as_passenger": []
    }
}
```

---

### 5. Update Ride Status

Updates the status of a ride (active, completed, cancelled, etc.).

**Endpoint:** `PUT /api/rides/{rideId}/status`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description |
| --------- | ------- | -------- | ----------- |
| `rideId`  | integer | ✓        | Ride ID     |

**Request Body:**

```json
{
    "status": "active"
}
```

**Request Parameters:**

| Field    | Type   | Required | Description                                     |
| -------- | ------ | -------- | ----------------------------------------------- |
| `status` | string | ✓        | New status (pending/active/completed/cancelled) |

**Success Response:** `200 OK`

```json
{
    "status": "success",
    "message": "Ride status updated successfully",
    "data": {
        "id": 1,
        "driver_id": 1,
        "from_location": "BUBT, Dhaka",
        "to_location": "Farmgate, Dhaka",
        "from_lat": "23.81033100",
        "from_lng": "90.41252100",
        "to_lat": "23.75586100",
        "to_lng": "90.38998200",
        "available_seats": 2,
        "total_seats": 4,
        "fare_per_seat": "50.00",
        "status": "active",
        "departure_time": "2025-12-25T14:30:00.000000Z",
        "notes": "Going via Mirpur road",
        "vehicle_type": "Car",
        "vehicle_number": "DHA-12345",
        "created_at": "2025-12-05T05:51:27.000000Z",
        "updated_at": "2025-12-05T05:58:29.000000Z",
        "confirmed_passengers": []
    }
}
```

---

## Ride Request Endpoints

### 1. Request to Join a Ride

A passenger requests to join a specific ride.

**Endpoint:** `POST /api/rides/{rideId}/request`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description        |
| --------- | ------- | -------- | ------------------ |
| `rideId`  | integer | ✓        | Ride ID to request |

**Request Body:**

```json
{
    "requested_seats": 2,
    "message": "Can I join your ride? I'm going the same way."
}
```

**Request Parameters:**

| Field             | Type    | Required | Description                 |
| ----------------- | ------- | -------- | --------------------------- |
| `requested_seats` | integer | ✓        | Number of seats needed (1+) |
| `message`         | string  | ✗        | Optional message to driver  |

**Success Response:** `201 Created`

```json
{
    "status": "success",
    "message": "Ride request sent successfully",
    "data": {
        "id": 1,
        "ride_id": 1,
        "passenger_id": 2,
        "requested_seats": 2,
        "message": "Can I join your ride? I'm going the same way.",
        "status": "pending",
        "created_at": "2025-12-05T05:56:39.000000Z",
        "updated_at": "2025-12-05T05:56:39.000000Z",
        "passenger": {
            "id": 2,
            "name": "User 2",
            "email": "user@gmail.com",
            "status": "active"
        }
    }
}
```

**Error Response:** `409 Conflict` (Duplicate request)

```json
{
    "status": "error",
    "message": "You have already requested this ride"
}
```

---

### 2. Handle Ride Request (Accept/Reject)

Driver accepts or rejects a passenger's ride request.

**Endpoint:** `PUT /api/rides/{rideId}/request/{requestId}`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter   | Type    | Required | Description     |
| ----------- | ------- | -------- | --------------- |
| `rideId`    | integer | ✓        | Ride ID         |
| `requestId` | integer | ✓        | Ride request ID |

**Request Body:**

```json
{
    "action": "accept"
}
```

**Request Parameters:**

| Field    | Type   | Required | Description                    |
| -------- | ------ | -------- | ------------------------------ |
| `action` | string | ✓        | Action to take (accept/reject) |

**Success Response:** `200 OK`

```json
{
    "status": "success",
    "message": "Request accepted successfully",
    "data": {
        "id": 1,
        "ride_id": 1,
        "passenger_id": 2,
        "requested_seats": 2,
        "status": "pending",
        "message": "Can I join your ride? I'm going the same way.",
        "created_at": "2025-12-05T05:56:39.000000Z",
        "updated_at": "2025-12-05T05:56:39.000000Z",
        "passenger": {
            "id": 2,
            "name": "User 2",
            "email": "user@gmail.com",
            "status": "active"
        }
    }
}
```

**Real-time Events:**

- Driver's action triggers WebSocket broadcast to passenger's channel: `user.{passenger_id}.requests`
- Event name: `RideRequestStatusUpdated`

---

## Location Tracking Endpoints

### 1. Update Driver Location

Driver updates their real-time location during an active ride.

**Endpoint:** `POST /api/rides/{rideId}/location`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description |
| --------- | ------- | -------- | ----------- |
| `rideId`  | integer | ✓        | Ride ID     |

**Request Body:**

```json
{
    "latitude": 23.810331,
    "longitude": 90.412521,
    "speed": 45.5,
    "bearing": 90.0,
    "accuracy": 5.0
}
```

**Request Parameters:**

| Field       | Type    | Required | Description                     |
| ----------- | ------- | -------- | ------------------------------- |
| `latitude`  | decimal | ✓        | Current latitude (-90 to 90)    |
| `longitude` | decimal | ✓        | Current longitude (-180 to 180) |
| `speed`     | decimal | ✗        | Current speed in km/h           |
| `bearing`   | decimal | ✗        | Direction in degrees (0-360)    |
| `accuracy`  | decimal | ✗        | GPS accuracy in meters          |

**Success Response:** `201 Created`

```json
{
    "status": "success",
    "message": "Location updated",
    "data": {
        "id": 1,
        "ride_id": 1,
        "user_id": 1,
        "latitude": "23.81033100",
        "longitude": "90.41252100",
        "speed": "45.50",
        "bearing": "90.00",
        "accuracy": "5.00",
        "created_at": "2025-12-05T05:59:14.000000Z",
        "updated_at": "2025-12-05T05:59:14.000000Z",
        "user": {
            "id": 1,
            "name": "Admin",
            "email": "admin@gmail.com",
            "status": "active"
        }
    }
}
```

**Real-time Events:**

- Broadcasts to channel: `ride.{rideId}.location`
- Event name: `RideLocationUpdated`
- Connected passengers receive live driver position in real-time

---

### 2. Get Ride Location History

Retrieves all location records for a specific ride.

**Endpoint:** `GET /api/rides/{rideId}/locations`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description |
| --------- | ------- | -------- | ----------- |
| `rideId`  | integer | ✓        | Ride ID     |

**Success Response:** `200 OK`

```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "ride_id": 1,
            "user_id": 1,
            "latitude": "23.81033100",
            "longitude": "90.41252100",
            "speed": "45.50",
            "bearing": "90.00",
            "accuracy": "5.00",
            "recorded_at": "2025-12-05T11:59:14.000000Z",
            "created_at": "2025-12-05T05:59:14.000000Z",
            "updated_at": "2025-12-05T05:59:14.000000Z",
            "user": {
                "id": 1,
                "name": "Admin",
                "email": "admin@gmail.com",
                "status": "active"
            }
        }
    ]
}
```

---

## Messaging Endpoints

### 1. Send Message to Ride

Send a message in an active ride (visible to driver and all passengers).

**Endpoint:** `POST /api/rides/{rideId}/message`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description |
| --------- | ------- | -------- | ----------- |
| `rideId`  | integer | ✓        | Ride ID     |

**Request Body:**

```json
{
    "message": "Hello everyone, I'm on my way!",
    "type": "text"
}
```

**Request Parameters:**

| Field     | Type   | Required | Description                                      |
| --------- | ------ | -------- | ------------------------------------------------ |
| `message` | string | ✓        | Message content (text only for now)              |
| `type`    | string | ✗        | Message type (text, image, etc. - default: text) |

**Success Response:** `201 Created`

```json
{
    "status": "success",
    "message": "Message sent",
    "data": {
        "id": 1,
        "ride_id": 1,
        "sender_id": 1,
        "message": "Hello everyone, I'm on my way!",
        "type": "text",
        "created_at": "2025-12-05T06:00:43.000000Z",
        "updated_at": "2025-12-05T06:00:43.000000Z",
        "sender": {
            "id": 1,
            "name": "Admin",
            "email": "admin@gmail.com",
            "status": "active"
        }
    }
}
```

**Real-time Events:**

- Broadcasts to channel: `ride.{rideId}.messages`
- Event name: `RideMessageSent`
- Immediately visible to all ride participants

---

### 2. Get Ride Messages

Retrieves paginated message history for a ride.

**Endpoint:** `GET /api/rides/{rideId}/messages`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description |
| --------- | ------- | -------- | ----------- |
| `rideId`  | integer | ✓        | Ride ID     |

**Query Parameters:**

| Parameter  | Type    | Required | Description                     |
| ---------- | ------- | -------- | ------------------------------- |
| `page`     | integer | ✗        | Page number (default: 1)        |
| `per_page` | integer | ✗        | Messages per page (default: 50) |

**Success Response:** `200 OK`

```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "ride_id": 1,
                "sender_id": 1,
                "message": "Hello everyone, I'm on my way!",
                "type": "text",
                "read_at": null,
                "created_at": "2025-12-05T06:00:43.000000Z",
                "updated_at": "2025-12-05T06:00:43.000000Z",
                "sender": {
                    "id": 1,
                    "name": "Admin",
                    "email": "admin@gmail.com",
                    "status": "active"
                }
            }
        ],
        "first_page_url": "https://api.example.com/api/rides/1/messages?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "https://api.example.com/api/rides/1/messages?page=1",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "page": null,
                "active": false
            },
            {
                "url": "https://api.example.com/api/rides/1/messages?page=1",
                "label": "1",
                "page": 1,
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "page": null,
                "active": false
            }
        ],
        "next_page_url": null,
        "path": "https://api.example.com/api/rides/1/messages",
        "per_page": 50,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }
}
```
