# **To-Let System API Documentation**

Base URL: `http://127.0.0.1:8000/api`

Authentication: Required for all endpoints except listing/searching rents (`Bearer Token`)

---

## **1. List All Rents**

**Endpoint:** `GET /api/rents`
**Authentication:** Optional

**Query Parameters:**

| Parameter   | Type    | Required | Description                                                |
| ----------- | ------- | -------- | ---------------------------------------------------------- |
| `category`  | string  | No       | Filter by category: `room`, `flat`, `single_bed`, `sublet` |
| `location`  | string  | No       | Filter by location (partial match)                         |
| `available` | boolean | No       | Filter by availability (`true` or `false`)                 |
| `min_rent`  | integer | No       | Minimum rent                                               |
| `max_rent`  | integer | No       | Maximum rent                                               |

**Example Request:**

```
GET /api/rents?category=single_bed&location=Mirpur&available=true
```

**Success Response:** `200 OK`

```json
{
    "current_page": 1,
    "data": [
        {
            "id": 2,
            "user_id": 1,
            "title": "1 bed for Rent at Mirpur-2",
            "description": "A quiet room suitable for a student. Wifi and water included.",
            "category": "single_bed",
            "rent": 2500,
            "location": "Mirpur-2, Dhaka",
            "address_detail": "House 12, Road 3, Block B",
            "contact_number": "017XXXXXXXX",
            "bedrooms": 1,
            "washrooms": 1,
            "available_from": "2025-01-15",
            "is_available": 1,
            "created_at": "2025-12-08T08:36:20.000000Z",
            "updated_at": "2025-12-08T08:36:20.000000Z",
            "user": {
                "id": 1,
                "name": "Admin",
                "email": "admin@gmail.com"
            }
        }
    ],
    "total": 1,
    "per_page": 20
}
```

---

## **2. View Single Rent Post**

**Endpoint:** `GET /api/rents/{id}`
**Authentication:** Optional

**Path Parameters:**

| Parameter | Type    | Required | Description  |
| --------- | ------- | -------- | ------------ |
| `id`      | integer | ✓        | Rent post ID |

**Example Request:**

```
GET /api/rents/1
```

**Success Response:** `200 OK`

```json
{
    "id": 1,
    "user_id": 1,
    "title": "Single Room for Rent at Mirpur-10",
    "description": "A quiet room suitable for a student. Wifi and water included.",
    "category": "room",
    "contact_number": "017XXXXXXXX",
    "rent": 5500,
    "location": "Mirpur-10, Dhaka",
    "address_detail": "House 12, Road 3, Block B",
    "bedrooms": 1,
    "washrooms": 1,
    "available_from": "2025-01-15",
    "is_available": 1,
    "created_at": "2025-12-08T08:34:58.000000Z",
    "updated_at": "2025-12-08T08:34:58.000000Z",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@gmail.com"
    }
}
```

---

## **3. Search Rents**

**Endpoint:** `GET /api/rents/search`
**Authentication:** Optional

**Query Parameters:**

| Parameter   | Type    | Required | Description                        |
| ----------- | ------- | -------- | ---------------------------------- |
| `q`         | string  | No       | Keyword search (title/description) |
| `category`  | string  | No       | Filter by category                 |
| `location`  | string  | No       | Filter by location                 |
| `available` | boolean | No       | Filter by availability             |

**Example Request:**

```
GET /api/rents/search?q=quiet&category=single_bed&location=Mirpur&available=true
```

**Success Response:** `200 OK`

```json
{
    "current_page": 1,
    "data": [
        {
            "id": 2,
            "user_id": 1,
            "title": "1 bed for Rent at Mirpur-2",
            "description": "A quiet room suitable for a student. Wifi and water included.",
            "category": "single_bed",
            "contact_number": "017XXXXXXXX",
            "rent": 2500,
            "location": "Mirpur-2, Dhaka",
            "address_detail": "House 12, Road 3, Block B",
            "bedrooms": 1,
            "washrooms": 1,
            "available_from": "2025-01-15",
            "is_available": 1,
            "created_at": "2025-12-08T08:36:20.000000Z",
            "updated_at": "2025-12-08T08:36:20.000000Z",
            "user": {
                "id": 1,
                "name": "Admin",
                "email": "admin@gmail.com"
            }
        }
    ],
    "total": 1,
    "per_page": 20
}
```

---

## **4. Create Rent Post**

**Endpoint:** `POST /api/rents`
**Authentication:** Required (Bearer Token)

**Request Body:**

```json
{
    "title": "2 Bedroom Flat for Rent at Gulshan",
    "description": "Spacious flat with balcony. Near bus stop.",
    "category": "flat",
    "contact_number": "017XXXXXXXX",
    "rent": 20000,
    "location": "Gulshan, Dhaka",
    "address_detail": "House 45, Road 2",
    "bedrooms": 2,
    "washrooms": 2,
    "available_from": "2026-01-01"
}
```

**Success Response:** `201 Created`

```json
{
    "success": true,
    "message": "Rent post created successfully",
    "data": {
        "id": 3,
        "title": "2 Bedroom Flat for Rent at Gulshan",
        "category": "flat",
        "contact_number": "017XXXXXXXX",
        "rent": 20000,
        "location": "Gulshan, Dhaka",
        "address_detail": "House 45, Road 2",
        "bedrooms": 2,
        "washrooms": 2,
        "available_from": "2026-01-01",
        "is_available": 1,
        "user_id": 1
    }
}
```

---

## **5. Update Rent Post**

**Endpoint:** `PUT /api/rents/{id}`
**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description  |
| --------- | ------- | -------- | ------------ |
| `id`      | integer | ✓        | Rent post ID |

**Request Body (any fields to update):**

```json
{
    "title": "Updated Title",
    "rent": 25000,
    "is_available": false
}
```

**Success Response:** `200 OK`

```json
{
    "message": "Rent post updated",
    "data": {
        "id": 3,
        "title": "Updated Title",
        "category": "flat",
        "contact_number": "017XXXXXXXX",
        "rent": 25000,
        "location": "Gulshan, Dhaka",
        "address_detail": "House 45, Road 2",
        "bedrooms": 2,
        "washrooms": 2,
        "available_from": "2026-01-01",
        "is_available": 0,
        "user_id": 1
    }
}
```

---

## **6. Delete Rent Post**

**Endpoint:** `DELETE /api/rents/{id}`
**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description  |
| --------- | ------- | -------- | ------------ |
| `id`      | integer | ✓        | Rent post ID |

**Success Response:** `200 OK`

```json
{
    "message": "Rent post deleted"
}
```

---

## **7. Set Availability**

**Endpoint:** `PUT /api/rents/{id}/availability`
**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description  |
| --------- | ------- | -------- | ------------ |
| `id`      | integer | ✓        | Rent post ID |

**Request Body:**

```json
{
    "is_available": true
}
```

**Success Response:** `200 OK`

```json
{
    "message": "Availability updated",
    "data": {
        "id": 3,
        "title": "2 Bedroom Flat for Rent at Gulshan",
        "category": "flat",
        "contact_number": "017XXXXXXXX",
        "rent": 20000,
        "location": "Gulshan, Dhaka",
        "address_detail": "House 45, Road 2",
        "bedrooms": 2,
        "washrooms": 2,
        "available_from": "2026-01-01",
        "is_available": 1,
        "user_id": 1
    }
}
```

---

## **8. Get My Rent Posts**

**Endpoint:** `GET /api/my-rents`
**Authentication:** Required (Bearer Token)

**Success Response:** `200 OK`

```json
[
    {
        "id": 2,
        "user_id": 1,
        "title": "1 bed for Rent at Mirpur-2",
        "description": "A quiet room suitable for a student. Wifi and water included.",
        "category": "single_bed",
        "contact_number": "017XXXXXXXX",
        "rent": 2500,
        "location": "Mirpur-2, Dhaka",
        "address_detail": "House 12, Road 3, Block B",
        "bedrooms": 1,
        "washrooms": 1,
        "available_from": "2025-01-15",
        "is_available": 1,
        "created_at": "2025-12-08T08:36:20.000000Z",
        "updated_at": "2025-12-08T08:36:20.000000Z"
    },
    {
        "id": 1,
        "user_id": 1,
        "title": "Single Room for Rent at Mirpur-10",
        "description": "A quiet room suitable for a student. Wifi and water included.",
        "category": "room",
        "contact_number": "017XXXXXXXX",
        "rent": 5500,
        "location": "Mirpur-10, Dhaka",
        "address_detail": "House 12, Road 3, Block B",
        "bedrooms": 1,
        "washrooms": 1,
        "available_from": "2025-01-15",
        "is_available": 1,
        "created_at": "2025-12-08T08:34:58.000000Z",
        "updated_at": "2025-12-08T08:34:58.000000Z"
    }
]
```