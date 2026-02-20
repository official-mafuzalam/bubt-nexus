<?php
namespace App\Http\Class;

class Data
{
    public static function getSemesterOptions()
    {
        return [
            'Fall 2025' => 611,
            'Spring 2026' => 612,
        ];
    }

    public static function getDepartmentOptions()
    {
        return [
            'CSE',
            'EEE',
            'BBA',
            'LAW',
            'ARCH',
            'ENG',
            'TEXTILE',
        ];
    }

    public static function getDesignationOptions()
    {
        return [
            'Professor',
            'Associate Professor',
            'Assistant Professor',
            'Lecturer',
            'Senior Lecturer',
            'Adjunct Faculty',
            'Industry Professional',
        ];
    }

    public static function getTimeSlotOptions()
    {
        return [
            [
                'id' => 1,
                'slot_name' => '08:00 AM to 09:15 AM',
                'start_time' => '08:00:00',
                'end_time' => '09:15:00'
            ],
            [
                'id' => 2,
                'slot_name' => '08:15 AM to 09:45 AM',
                'start_time' => '08:15:00',
                'end_time' => '09:45:00'
            ],
            [
                'id' => 3,
                'slot_name' => '09:15 AM to 10:30 AM',
                'start_time' => '09:15:00',
                'end_time' => '10:30:00'
            ],
            [
                'id' => 4,
                'slot_name' => '09:45 AM to 11:15 AM',
                'start_time' => '09:45:00',
                'end_time' => '11:15:00'
            ],
            [
                'id' => 5,
                'slot_name' => '10:30 AM to 11:45 AM',
                'start_time' => '10:30:00',
                'end_time' => '11:45:00'
            ],
            [
                'id' => 6,
                'slot_name' => '11:15 AM to 12:45 PM',
                'start_time' => '11:15:00',
                'end_time' => '12:45:00'
            ],
            [
                'id' => 7,
                'slot_name' => '11:45 AM to 01:00 PM',
                'start_time' => '11:45:00',
                'end_time' => '13:00:00'
            ],
            [
                'id' => 8,
                'slot_name' => '01:15 PM to 02:45 PM',
                'start_time' => '13:15:00',
                'end_time' => '14:45:00'
            ],
            [
                'id' => 9,
                'slot_name' => '02:45 PM to 04:15 PM',
                'start_time' => '14:45:00',
                'end_time' => '16:15:00'
            ],
            [
                'id' => 10,
                'slot_name' => '03:15 PM to 04:30 PM',
                'start_time' => '15:15:00',
                'end_time' => '16:30:00'
            ],
            [
                'id' => 11,
                'slot_name' => '04:15 PM to 05:45 PM',
                'start_time' => '16:15:00',
                'end_time' => '17:45:00'
            ],
            [
                'id' => 12,
                'slot_name' => '04:30 PM to 05:45 PM',
                'start_time' => '16:30:00',
                'end_time' => '17:45:00'
            ],
            [
                'id' => 13,
                'slot_name' => '05:45 PM to 07:00 PM',
                'start_time' => '17:45:00',
                'end_time' => '19:00:00'
            ],
            [
                'id' => 14,
                'slot_name' => '07:00 PM to 08:15 PM',
                'start_time' => '19:00:00',
                'end_time' => '20:15:00'
            ],
            [
                'id' => 15,
                'slot_name' => '08:15 PM to 09:30 PM',
                'start_time' => '20:15:00',
                'end_time' => '21:30:00'
            ],
        ];
    }
}