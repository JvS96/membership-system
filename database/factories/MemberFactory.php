<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Member::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $idNumber = $this->generateValidSouthAfricanId();
        $dateOfBirth = Member::extractDateOfBirthFromId($idNumber);

        return [
            'member_number' => Member::generateMemberNumber(),
            'id_number' => $idNumber,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'cellphone' => $this->generateSouthAfricanCellphone(),
            'date_of_birth' => $dateOfBirth,
            'status' => fake()->randomElement(['active', 'inactive', 'suspended']),
        ];
    }

    /**
     * Generate a valid South African ID number
     */
    private function generateValidSouthAfricanId(): string
    {
        // Generate random date of birth (between 1950 and 2005)
        $birthDate = fake()->dateTimeBetween('1950-01-01', '2005-12-31');

        $year = $birthDate->format('y');
        $month = $birthDate->format('m');
        $day = $birthDate->format('d');

        // Gender digit (0-4 for female, 5-9 for male)
        $genderDigit = fake()->numberBetween(0, 9);

        // Sequence number (usually 0-2 for SA citizens born before 1995, 3-9 for after)
        $sequenceDigit = fake()->numberBetween(0, 9);

        // Citizenship digit (0 for SA citizen, 1 for permanent resident)
        $citizenshipDigit = fake()->randomElement([0, 1]);

        // Race digit (usually 8 or 9, but can be 0-9)
        $raceDigit = fake()->numberBetween(0, 9);

        // First 12 digits
        $idWithoutCheck = $year . $month . $day . $genderDigit . $sequenceDigit . $citizenshipDigit . $raceDigit;

        // Calculate check digit using Luhn algorithm
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $idWithoutCheck[$i];
            if ($i % 2 === 0) {
                $sum += $digit;
            } else {
                $doubled = $digit * 2;
                $sum += ($doubled > 9) ? $doubled - 9 : $doubled;
            }
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $idWithoutCheck . $checkDigit;
    }

    /**
     * Generate a South African cellphone number
     */
    private function generateSouthAfricanCellphone(): string
    {
        // Valid South African mobile prefixes
        $prefixes = [
            '060', '061', '062', '063', '064', '065', '066', '067', '068', '069',
            '070', '071', '072', '073', '074', '076', '078', '079',
            '081', '082', '083', '084'
        ];

        $prefix = fake()->randomElement($prefixes);
        $number = fake()->numberBetween(1000000, 9999999);

        return $prefix . str_pad($number, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Indicate that the member is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the member is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the member is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
