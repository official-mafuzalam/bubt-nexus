## Device Management Endpoints

### 1. Register Device for Push Notifications

Register a device to receive Firebase Cloud Messaging (FCM) push notifications.

**Endpoint:** `POST /api/device/register`

**Authentication:** Required (Bearer Token)

**Request Body:**

```json
{
    "device_token": "fcm_token_example_12345",
    "device_type": "android",
    "device_id": "device_12345"
}
```

**Request Parameters:**

| Field          | Type   | Required | Description                    |
| -------------- | ------ | -------- | ------------------------------ |
| `device_token` | string | ✓        | FCM device token from Firebase |
| `device_type`  | string | ✓        | Device type (android/ios/web)  |
| `device_id`    | string | ✓        | Unique device identifier       |

**Success Response:** `201 Created`

```json
{
    "status": "success",
    "message": "Device registered successfully",
    "data": {
        "id": 1,
        "user_id": 2,
        "device_token": "fcm_token_example_12345",
        "device_type": "android",
        "device_id": "device_12345",
        "created_at": "2025-12-05T06:04:00.000000Z",
        "updated_at": "2025-12-05T06:04:00.000000Z"
    }
}
```

---

### 2. Unregister Device

Remove a device from receiving push notifications.

**Endpoint:** `DELETE /api/device/unregister/{token}`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type   | Required | Description                    |
| --------- | ------ | -------- | ------------------------------ |
| `token`   | string | ✓        | FCM device token to unregister |

**Example Request:**

```
DELETE /api/device/unregister/fcm_token_example_12345
```

**Success Response:** `200 OK`

```json
{
    "status": "success",
    "message": "Device unregistered successfully"
}
```
