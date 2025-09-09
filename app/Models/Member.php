<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_number',
        'id_number',
        'first_name',
        'last_name',
        'email',
        'cellphone',
        'date_of_birth',
        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Extract date of birth from South African ID number
     */
    public static function extractDateOfBirthFromId($idNumber)
    {
        // Clean the ID number first
        $idNumber = (string) preg_replace('/[^0-9]/', '', $idNumber);

        if (!self::isValidSouthAfricanId($idNumber)) {
            return null;
        }

        $year = (int) substr($idNumber, 0, 2);
        $month = (int) substr($idNumber, 2, 2);
        $day = (int) substr($idNumber, 4, 2);

        // Determine century (assuming current year is 2024)
        $currentYear = (int) date('Y');
        $currentYearLastTwo = $currentYear % 100;
        $currentCentury = floor($currentYear / 100) * 100;
        $previousCentury = $currentCentury - 100;

        // If year is greater than current year's last two digits, it's from previous century
        // For example: if current year is 2024 (24), and ID year is 25-99, it's 1925-1999
        // If ID year is 00-24, it's 2000-2024
        if ($year > $currentYearLastTwo) {
            $fullYear = $previousCentury + $year;
        } else {
            $fullYear = $currentCentury + $year;
        }

        // Additional validation: ensure the date is not in the future
        // and not too far in the past (e.g., not before 1900)
        if ($fullYear < 1900 || $fullYear > $currentYear) {
            return null;
        }

        try {
            return Carbon::createFromDate($fullYear, $month, $day);
        } catch (\Exception $e) {
            \Log::error('Failed to create date from ID: ' . $idNumber, ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validate South African ID number
     */
    public static function isValidSouthAfricanId($idNumber)
    {
        // Remove any spaces or special characters and convert to string
        $idNumber = (string) preg_replace('/[^0-9]/', '', $idNumber);

        // Check if it's exactly 13 digits
        if (strlen($idNumber) !== 13) {
            return false;
        }

        // Extract date components
        $year = (int) substr($idNumber, 0, 2);
        $month = (int) substr($idNumber, 2, 2);
        $day = (int) substr($idNumber, 4, 2);

        // Validate date components
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return false;
        }

        // Additional date validation - check if it's a reasonable date
        $currentYear = (int) date('Y');
        $currentYearLastTwo = $currentYear % 100;
        $currentCentury = floor($currentYear / 100) * 100;
        $previousCentury = $currentCentury - 100;

        // Determine full year
        $fullYear = ($year > $currentYearLastTwo) ? $previousCentury + $year : $currentCentury + $year;

        // Check if the date is valid using checkdate
        if (!checkdate($month, $day, $fullYear)) {
            return false;
        }

        // Validate using Luhn algorithm (modified for SA ID)
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $idNumber[$i];
            if ($i % 2 === 0) {
                $sum += $digit;
            } else {
                $doubled = $digit * 2;
                $sum += ($doubled > 9) ? $doubled - 9 : $doubled;
            }
        }

        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit == $idNumber[12];
    }

    /**
     * Generate unique member number
     */
    public static function generateMemberNumber()
    {
        do {
            $memberNumber = 'MBR' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('member_number', $memberNumber)->exists());

        return $memberNumber;
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope for searching by ID number or member number
     */
    public function scopeSearchByIdOrMember($query, $search)
    {
        return $query->where('id_number', 'like', '%' . $search . '%')
            ->orWhere('member_number', 'like', '%' . $search . '%');
    }

    /**
     * Scope for filtering by cellphone
     */
    public function scopeFilterByCellphone($query, $cellphone)
    {
        return $query->where('cellphone', 'like', '%' . $cellphone . '%');
    }
}
