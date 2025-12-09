# API Documentation---

## Response Format

### Success Response Structure

All successful API responses follow this format:

```json
{
    "status": "success",
    "message": "Optional message describing the action",
    "data": {
        // Response data object or array
    }
}
```

**HTTP Status Codes:**

- `200 OK` - Successful GET/PUT request
- `201 Created` - Successful POST request (resource created)

---

### Error Response Structure

All error responses follow this format:

```json
{
    "status": "error",
    "message": "Human-readable error message",
    "errors": {
        // Optional: validation errors by field
    }
}
```

---

## Error Handling

### Common HTTP Status Codes

| Status Code | Meaning               | Scenario                                    |
| ----------- | --------------------- | ------------------------------------------- |
| `200`       | OK                    | Successful request                          |
| `201`       | Created               | Resource successfully created               |
| `400`       | Bad Request           | Invalid request format or parameters        |
| `401`       | Unauthorized          | Missing or invalid authentication token     |
| `403`       | Forbidden             | Authenticated user lacks permission         |
| `404`       | Not Found             | Resource doesn't exist                      |
| `409`       | Conflict              | Resource conflict (e.g., duplicate request) |
| `422`       | Unprocessable Entity  | Validation failed                           |
| `500`       | Internal Server Error | Server error                                |

### Validation Error Example

When a request fails validation, the response includes field-specific errors:

```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "total_seats": ["The total seats field is required."],
        "departure_time": ["The departure time must be after now."],
        "fare_per_seat": ["The fare per seat must be at least 0."]
    }
}
```

---

## Real-Time Events (WebSocket)

The API uses Laravel Reverb for WebSocket broadcasts. Connect to the Echo listener for real-time updates.

### Broadcast Channels and Events

| Channel                        | Event                      | Trigger                         | Payload                             |
| ------------------------------ | -------------------------- | ------------------------------- | ----------------------------------- |
| `user.{driver_id}.requests`    | `RideRequestEvent`         | Passenger requests to join ride | Request ID, passenger info, seats   |
| `user.{passenger_id}.requests` | `RideRequestStatusUpdated` | Driver accepts/rejects request  | Request ID, new status              |
| `ride.{rideId}.location`       | `RideLocationUpdated`      | Driver updates position         | Latitude, longitude, speed, bearing |
| `ride.{rideId}.messages`       | `RideMessageSent`          | New message sent in ride        | Message ID, sender, content         |

### Example WebSocket Listener (Vue.js)

```typescript
// Listen for new ride requests
window.Echo.private(`user.${driverId}.requests`).listen(
    'RideRequestEvent',
    (data) => {
        console.log('New request:', data);
        // Update UI with new request
    },
);

// Listen for request status updates
window.Echo.private(`user.${passengerId}.requests`).listen(
    'RideRequestStatusUpdated',
    (data) => {
        console.log('Request status:', data.status);
        // Update UI with new status
    },
);

// Listen for location updates
window.Echo.channel(`ride.${rideId}.location`).listen(
    'RideLocationUpdated',
    (data) => {
        console.log('New location:', data);
        // Update map with driver location
    },
);

// Listen for messages
window.Echo.channel(`ride.${rideId}.messages`).listen(
    'RideMessageSent',
    (data) => {
        console.log('New message:', data.message);
        // Add message to chat
    },
);
```

---

## Rate Limiting

Currently, rate limiting is not enforced but may be implemented. Configuration is available in `routes/api.php`:

```php
// Route::middleware('throttle:60,1')->group(function () {
//     // Limited routes
// });
```

---

## Testing API Endpoints

Use the provided `ride-apis.json` in your REST client (Postman, Thunder Client, REST Client, etc.):

1. Set `{{apiBase}}` to your API base URL (e.g., `http://localhost:8000/api`)
2. Set `{{token}}` to your Bearer token from login
3. Execute requests

**Example Postman Environment:**

```json
{
    "apiBase": "http://localhost:8000/api",
    "token": "your_bearer_token_here"
}
```

---

## Best Practices

1. **Always include Authorization header** for protected endpoints
2. **Validate coordinates** before sending location updates (lat: -90 to 90, lng: -180 to 180)
3. **Handle WebSocket disconnections** gracefully with reconnection logic
4. **Implement pagination** when fetching large message histories
5. **Cache ride searches** client-side to reduce API calls
6. **Use timestamps** from server responses for consistency
7. **Validate input** on client before sending to API
8. **Handle 422 validation errors** by displaying field-specific messages to users

---

## Troubleshooting

**Issue: 401 Unauthorized**

- Ensure token is included in `Authorization: Bearer {token}` header
- Verify token hasn't expired
- Check token format (should be a long alphanumeric string)

**Issue: WebSocket not connecting**

- Verify `BROADCAST_CONNECTION=reverb` in `.env`
- Check Reverb server is running (port 8080 by default)
- Ensure frontend Echo is configured correctly

**Issue: 422 Validation Failed**

- Check all required fields are provided
- Verify data types match specification
- Review error messages for specific field issues

**Issue: Duplicate ride request**

- User has already requested this ride
- Try with a different ride or wait for current request to be resolved

---

## Contact & Support

For API issues or questions, refer to `.github/copilot-instructions.md` for architectural details.
