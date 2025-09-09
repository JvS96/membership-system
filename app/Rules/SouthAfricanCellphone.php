<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SouthAfricanCellphone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove any spaces or special characters
        $cleanedValue = preg_replace('/[^0-9+]/', '', $value);

        // Check for valid South African cellphone format
        $pattern = '/^(\+27|0)[6-8][0-9]{8}$/';

        if (!preg_match($pattern, $cleanedValue)) {
            $fail('The :attribute must be a valid South African cellphone number.');
        }
    }
}
