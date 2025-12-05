# AI Coding Agent Instructions for BUBT Nexus

## Project Overview

BUBT Nexus is a **Laravel 12 + Vue 3 + Inertia.js** web application for BUBT IT Club management with a **ride-sharing module**. The primary focus is the ride-sharing feature (branch: `ride-sharing`) which includes real-time location tracking, messaging, and WebSocket broadcasts.

**Key Stack:**
- Backend: Laravel 12, Sanctum (auth), Spatie Permissions, Laravel Reverb (WebSockets)
- Frontend: Vue 3, TypeScript, Inertia.js, Tailwind CSS 4
- Database: SQLite (testing), PostgreSQL/MySQL (production)
- Real-time: Laravel Reverb + Laravel Echo (broadcasts to drivers/passengers)

---

## Architecture Overview

### Core Data Model: Ride-Sharing System

The ride-sharing feature centers on four interconnected models:

1. **Ride** (`app/Models/Ride.php`): Represents a driver's trip offering
   - Relations: `belongsTo(User, 'driver_id')`, `hasMany(RideRequest)`, `hasMany(RideLocation)`, `hasMany(Message)`
   - Key fields: `from_location`, `to_location`, `from_lat`/`to_lat` (decimals), `available_seats`, `fare_per_seat`, `status` (pending/active/completed/cancelled)
   - Scopes: `available()`, `nearby($lat, $lng, $radius)` for geo-queries

2. **RideRequest** (`app/Models/RideRequest.php`): Passenger request to join a ride
   - Relations: `belongsTo(Ride)`, `belongsTo(User, 'passenger_id')`
   - Unique constraint: `[ride_id, passenger_id]` prevents duplicate requests
   - Status flow: pending → accepted/rejected

3. **RideLocation** (`app/Models/RideLocation.php`): Real-time location tracking during active rides
   - Records driver position and vehicle check-in user IDs
   - Used for live map updates via WebSocket

4. **Message** (`app/Models/Message.php`): In-ride communication
   - Ties to rides and users, broadcasted via `RideMessageSent` event

### API Layer Pattern

API controllers (`app/Http/Controllers/Api/`) use a consistent response pattern:

```php
// Success response (always includes 'status' and 'data')
return response()->json([
    'status' => 'success',
    'data' => $resource
], 200);

// Error response (includes 'status', 'message', optional 'errors' for validation)
return response()->json([
    'status' => 'error',
    'message' => 'User-friendly message',
    'errors' => $validator->errors() // Validation failures only
], 422); // or 404/500 as appropriate
```

**Key endpoint groups** (`routes/api.php`):
- `/api/rides/create` - Driver creates new ride
- `/api/rides/nearby?latitude=X&longitude=Y&radius=20` - Geo-filtered search
- `/api/rides/{id}/request` - Passenger requests to join
- `/api/rides/{rideId}/request/{requestId}` - Driver accepts/rejects requests
- `/api/rides/{id}/location` - Driver updates position (broadcasts to passengers)
- `/api/rides/{id}/message` - Send in-ride messages

### Real-time Broadcasting (WebSockets)

The project uses **Laravel Reverb** for WebSocket events. Events are defined in `app/Events/`:

| Event | Broadcast Channel | Use Case |
|-------|-------------------|----------|
| `RideRequestEvent` | `user.{driver_id}.requests` | Driver receives passenger requests |
| `RideRequestStatusUpdated` | `user.{passenger_id}.requests` | Passenger learns if request accepted |
| `RideLocationUpdated` | `ride.{ride_id}.location` | Passengers see live driver position |
| `RideMessageSent` | `ride.{ride_id}.messages` | In-ride chat updates |

**Frontend consumption** (`resources/js/app.ts`):
```typescript
configureEcho({
    broadcaster: 'reverb', // or 'pusher'
});
// Component usage: window.Echo.private('user.123.requests').listen(...)
```

---

## Development Workflows

### Setup & Local Development

```bash
# Install PHP + Node dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database (in-memory SQLite for tests, regular for local)
php artisan migrate
php artisan db:seed

# Run development server (Vite + Laravel)
composer run dev  # Runs vite in one terminal, Laravel in another via concurrently

# Alternative: Start separately
npm run dev       # Vite at localhost:5173
php artisan serve # Laravel at localhost:8000
```

### Testing

```bash
# Run all tests (Unit + Feature)
php artisan test

# Run specific suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# With coverage report
php artisan test --coverage

# PHPUnit config: tests use SQLite in-memory DB, see phpunit.xml
```

### Code Quality

```bash
# Format code (Prettier for frontend)
npm run format

# Lint & auto-fix
npm run lint

# Format check only
npm run format:check
```

### Building for Production

```bash
composer install --optimize-autoloader --no-dev
npm run build      # Builds assets to public/build

# For SSR (if enabled in vite.config.ts):
npm run build:ssr  # Generates SSR bundle alongside
```

---

## Code Conventions & Patterns

### Backend (Laravel/PHP)

**Form Requests (Validation):** Always use `FormRequest` classes instead of inline validation
- Location: `app/Http/Requests/` (organized by subdomain: `Api/`, `Settings/`, etc.)
- Example: `Api/LoginRequest` extends `FormRequest`, defines rules in `rules()` method
- Validation failures automatically return 422 with structured error responses

**Resource Classes:** Use for consistent API response shapes
- Location: `app/Http/Resources/`
- Example: `AuthResource` transforms User model + token for login response
- **NOT used in RideController yet** (direct model serialization) — consistency opportunity for new endpoints

**Model Scopes:** For reusable query logic
- Example: `Ride::available()->nearby($lat, $lng, $radius)` chains scopes
- Define in model: `public function scopeAvailable($query) { ... }`

**Events & Broadcasting:** For real-time updates
- Extend `ShouldBroadcast` interface
- Define channel in `broadcastOn()` (private for user channels)
- Use `broadcastWith()` to shape data sent to clients

**Error Handling:** Use descriptive error messages + logging
```php
try {
    // operation
} catch (\Exception $e) {
    Log::error('Operation failed', ['error' => $e->getMessage()]);
    return response()->json(['status' => 'error', 'message' => 'User-friendly message'], 500);
}
```

### Frontend (Vue 3 / TypeScript)

**Composables:** For reusable logic
- Location: `resources/js/composables/`
- Example: `useAppearance.ts` (theme management), `useTwoFactorAuth.ts`
- Pattern: Return reactive state + methods

**Components:** Reusable UI units
- Location: `resources/js/components/`
- Use Reka UI (component library) for base components
- Style with Tailwind CSS 4 (via `@tailwindcss/vite` plugin)

**Pages:** Inertia.js page components (mapped to routes)
- Location: `resources/js/pages/`
- Receive props from Laravel controllers
- Example: `Dashboard.vue` receives user data from `HomeController`

**Types:** Define in `resources/js/types/` for TypeScript safety
- Import and use in components/composables for IDE autocompletion

**Echo/WebSocket Listeners:**
```typescript
// In Vue component
window.Echo.private(`user.${userId}.requests`)
    .listen('RideRequestEvent', (data) => {
        // Handle real-time update
    });
```

### Authentication & Authorization

**Sanctum (Token-based API Auth):**
- Tokens generated at login: `$user->createToken('auth_token')->plainTextToken`
- Middleware: `auth:sanctum` protects API routes
- Tested with Bearer tokens in request headers

**Spatie Permissions:**
- Roles & permissions defined in database (migrations seeded)
- Guards: `'web'` (session), `'api'` (tokens)
- Usage: `$user->hasRole('admin')`, `$user->can('edit_rides')`

---

## Project-Specific Patterns & Quirks

### Validation Patterns

1. **Inline Validator** (in controllers, for simple cases):
   ```php
   $validator = Validator::make($request->all(), [
       'latitude' => 'required|numeric|between:-90,90',
       'radius' => 'integer|min:1|max:100'
   ]);
   if ($validator->fails()) {
       return response()->json([...], 422);
   }
   ```

2. **Form Requests** (preferred for complex validation):
   ```php
   // In RegisterRequest
   public function rules(): array {
       return [
           'email' => ['required', 'email', 'unique:users'],
       ];
   }
   // In controller: public function register(RegisterRequest $request) { ... }
   ```

### Geolocation Queries

Rides use **haversine formula approximation** for distance filtering:
```php
// In RideController::getNearbyRides
$radius = $request->radius ?? 20; // km
$rides = Ride::available()
    ->nearby($lat, $lng, $radius) // Uses model scope
    ->get();
```

Ensure lat/lng are cast as decimals in migrations (`:8` precision for 1m accuracy).

### Real-time Data Flow

**Driver Updates Location:**
1. Driver calls `PATCH /api/rides/{id}/location` with new `lat`/`lng`
2. Controller creates `RideLocation` record, broadcasts `RideLocationUpdated` event
3. Event includes channel `ride.{ride_id}.location` + data
4. Connected passengers receive update via `window.Echo.channel('ride.123.location')`

**Passenger Requests Ride:**
1. Passenger calls `POST /api/rides/{id}/request` with seats + message
2. Controller creates `RideRequest` record, broadcasts `RideRequestEvent`
3. Event channels to `user.{driver_id}.requests`
4. Driver's device receives notification + updates UI

### Notification System (FCM)

**Device Registration** (for push notifications):
- Model: `UserDevice` stores FCM tokens
- Endpoints: `POST /api/device/register`, `DELETE /api/device/unregister/{token}`
- FCM sending: Use `laravel-notification-channels/fcm` package for broadcasts

---

## File Organization Reference

| Path | Purpose |
|------|---------|
| `app/Models/Ride.php` | Core ride entity, scopes for queries |
| `app/Http/Controllers/Api/RideController.php` | All ride API endpoints (594 lines) |
| `app/Events/Ride*.php` | WebSocket events (RequestEvent, LocationUpdated, etc.) |
| `routes/api.php` | REST endpoint definitions, auth middleware |
| `resources/js/app.ts` | Vue app entry, Echo config |
| `resources/js/composables/` | Reusable logic (theme, settings, auth) |
| `config/broadcasting.php` | Reverb/Pusher config, default to `env('BROADCAST_CONNECTION')` |
| `database/migrations/` | Schema for rides, requests, locations, messages |
| `tests/Feature/` | Integration tests (API endpoints) |
| `tests/Unit/` | Unit tests (models, business logic) |

---

## Common Tasks

### Add a New API Endpoint

1. Create **Form Request** in `app/Http/Requests/Api/` (validation rules)
2. Add **method** to `app/Http/Controllers/Api/*Controller` (business logic + JSON response)
3. Register **route** in `routes/api.php` with middleware (`auth:sanctum` for protected)
4. Create **event** in `app/Events/` if real-time broadcast needed
5. Write **feature test** in `tests/Feature/` to verify endpoint + auth

### Add Real-time Feature

1. Create **Event** in `app/Events/` extending `ShouldBroadcast`
2. Define channel in `broadcastOn()` (e.g., `new Channel('ride.{id}')`)
3. Dispatch event from controller: `RideLocationUpdated::dispatch($rideLocation)`
4. In **Vue component**, listen: `window.Echo.channel('ride.123').listen('LocationUpdated', ...)`

### Modify Database Schema

1. Create migration: `php artisan make:migration table_name`
2. Define schema in `up()`, rollback in `down()`
3. Run: `php artisan migrate` (local), `php artisan migrate:fresh --seed` (reset all)

---

## Debugging Tips

- **Laravel logs:** `storage/logs/laravel.log` (check for event broadcasting issues)
- **Vite dev mode:** Hot reloads Vue components, check browser console for JS errors
- **SQLite in-memory (tests):** Database state doesn't persist between test runs by design
- **Echo not connecting?** Verify `BROADCAST_CONNECTION=reverb` in `.env`, check port 8080 (Reverb default)
- **API auth failing?** Ensure Bearer token in header: `Authorization: Bearer {token}`

---

## Questions to Ask Before Starting Work

- Is this a **new API endpoint** (add route + controller method) or **modifying existing logic**?
- Does it need **real-time updates**? If yes, add WebSocket event broadcast.
- Is **geolocation involved**? Use `nearby()` scope on Ride model.
- Should **frontend/backend be updated together**, or just backend? Plan component updates if so.

