<?php
namespace App\Http\Class;

class Data
{
    public static function getSemesterOptions()
    {
        return [
            'Winter 2025',
            'Fall 2025',
            'Summer 2025',
            'Spring 2025',
            'Winter 2026',
            'Fall 2026',
            'Summer 2026',
            'Spring 2026',
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