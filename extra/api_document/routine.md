Routine

**Endpoint:** `POST /api/routine`

**Authentication:** Required (Bearer Token)

**Path Parameters:**

| Parameter | Type    | Required | Description                              |
| --------- | ------- | -------- | ---------------------------------------- |
| `program` | integer | ✓        | Program code or name to retrieve routine |
| `intake`  | integer | ✓        | Intake information to retrieve routine   |
| `section` | integer | ✓        | Section information to retrieve routine  |

**Example Request:**

```
POST /api/routine
```

**Success Response:** `200 OK`

```json
{
    "status": true,
    "message": "Routine retrieved successfully",
    "program": "019",
    "program_name": "B.Sc. Engg in CSE (Evening)",
    "intake": "45",
    "section": 1,
    "semester": "Fall 2025",
    "routine": [
        {
            "day": "FRI",
            "08:20 AM to 09:30 AM": "CSE 220: MANQ R: 706"
        }
    ],
    "detailed_routines": [
        {
            "id": 2546,
            "day": "FRI",
            "time_slot": "08:20 AM to 09:30 AM",
            "formatted_time": "08:20 AM to 09:30 AM",
            "course_code": "CSE 220",
            "course_name": null,
            "teacher_code": "MANQ",
            "teacher_name": "Nur Quraishe",
            "room_number": "706",
            "room_type": "Classroom",
            "class_details": "CSE 220: MANQ R: 706",
            "status": "active"
        }
    ],
    "meta": {
        "total_classes": 1,
        "days_count": 1,
        "unique_courses": 1,
        "unique_teachers": 1
    }
}
```
