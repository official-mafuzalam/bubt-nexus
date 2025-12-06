<?php
namespace App\Http\Class;

class Data
{
    public static function getSemesterOptions()
    {
        return [
            'Fall 2025' => 611,
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
}