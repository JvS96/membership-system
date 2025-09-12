<?php

// tests/Feature/MemberTest.php

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->member = Member::factory()->create([
        'member_number' => 'MBR9999',
        'id_number' => '9001015009086', // Updated to valid SA ID
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@test.com',
        'cellphone' => '0821234567',
        'status' => 'active'
    ]);
});

describe('Member CRUD Operations', function () {

    it('can create a member', function () {
        $memberData = [
            'member_number' => 'MBR1001',
            'id_number' => '8506115009084', // Updated to valid SA ID
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@test.com',
            'cellphone' => '0827654321',
            'date_of_birth' => '1985-06-11',
            'status' => 'active'
        ];

        $member = Member::create($memberData);

        expect($member)->toBeInstanceOf(Member::class);
        expect($member->exists)->toBeTrue();
        expect($member->first_name)->toBe($memberData['first_name']);
        expect($member->email)->toBe($memberData['email']);

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'email' => $memberData['email']
        ]);
    });

    it('can read a member', function () {
        $foundMember = Member::find($this->member->id);

        expect($foundMember)->not->toBeNull();
        expect($foundMember->id)->toBe($this->member->id);
        expect($foundMember->email)->toBe($this->member->email);
    });

    it('can update a member', function () {
        $newEmail = 'updated.email@test.com';
        $newStatus = 'inactive';

        $this->member->update([
            'email' => $newEmail,
            'status' => $newStatus
        ]);

        expect($this->member->fresh()->email)->toBe($newEmail);
        expect($this->member->fresh()->status)->toBe($newStatus);

        $this->assertDatabaseHas('members', [
            'id' => $this->member->id,
            'email' => $newEmail,
            'status' => $newStatus
        ]);
    });

    it('can delete a member', function () {
        $memberId = $this->member->id;

        $this->member->delete();

        $this->assertDatabaseMissing('members', [
            'id' => $memberId
        ]);

        expect(Member::find($memberId))->toBeNull();
    });
});

describe('Member Model Validation', function () {

    it('validates South African ID numbers correctly', function () {
        // Valid ID numbers (with correct checksums)
        expect(Member::isValidSouthAfricanId('9001015009086'))->toBeTrue(); // Jan 1, 1990
        expect(Member::isValidSouthAfricanId('8506115009084'))->toBeTrue(); // Jun 11, 1985
        expect(Member::isValidSouthAfricanId('9512314567087'))->toBeTrue(); // Dec 31, 1995

        // Invalid ID numbers
        expect(Member::isValidSouthAfricanId('1234567890123'))->toBeFalse(); // Invalid checksum
        expect(Member::isValidSouthAfricanId('123456789012'))->toBeFalse();  // Too short
        expect(Member::isValidSouthAfricanId('12345678901234'))->toBeFalse(); // Too long
        expect(Member::isValidSouthAfricanId('9013315009087'))->toBeFalse(); // Invalid month (33)
        expect(Member::isValidSouthAfricanId('9001335009087'))->toBeFalse(); // Invalid day (33)
        expect(Member::isValidSouthAfricanId(''))->toBeFalse(); // Empty string
        expect(Member::isValidSouthAfricanId('abcdefghijklm'))->toBeFalse(); // Non-numeric
    });

    it('extracts date of birth from ID number correctly', function () {
        $idNumber = '9001015009086'; // January 1, 1990
        $dateOfBirth = Member::extractDateOfBirthFromId($idNumber);

        expect($dateOfBirth)->toBeInstanceOf(Carbon::class);
        expect($dateOfBirth->year)->toBe(1990);
        expect($dateOfBirth->month)->toBe(1);
        expect($dateOfBirth->day)->toBe(1);

        // Test with different century
        $idNumber = '0501015009084'; // January 1, 2005 (correct checksum)
        $dateOfBirth = Member::extractDateOfBirthFromId($idNumber);

        expect($dateOfBirth->year)->toBe(2005);

        // Test edge case - current year boundary
        $idNumber = '2501015009082'; // Should be 2025 if current year allows (correct checksum)
        $dateOfBirth = Member::extractDateOfBirthFromId($idNumber);
        expect($dateOfBirth)->not->toBeNull();
    });

    it('returns null for invalid ID numbers when extracting date', function () {
        expect(Member::extractDateOfBirthFromId('invalid'))->toBeNull();
        expect(Member::extractDateOfBirthFromId('1234567890123'))->toBeNull();
        expect(Member::extractDateOfBirthFromId(''))->toBeNull();
        expect(Member::extractDateOfBirthFromId('9013315009087'))->toBeNull(); // Invalid month
    });

    it('generates unique member numbers', function () {
        $memberNumbers = [];

        // Generate multiple member numbers to test uniqueness
        for ($i = 0; $i < 10; $i++) {
            $memberNumber = Member::generateMemberNumber();
            expect($memberNumber)->toStartWith('MBR');
            expect(strlen($memberNumber))->toBe(7); // MBR + 4 digits
            expect($memberNumbers)->not->toContain($memberNumber);
            $memberNumbers[] = $memberNumber;
        }
    });

    it('enforces unique email addresses', function () {
        $memberData = [
            'member_number' => 'MBR2001',
            'id_number' => '8506115009084',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $this->member->email, // Same email as existing member
            'cellphone' => '0827777777',
            'date_of_birth' => '1985-06-11',
            'status' => 'active'
        ];

        expect(function () use ($memberData) {
            Member::create($memberData);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('enforces unique cellphone numbers', function () {
        $memberData = [
            'member_number' => 'MBR2002',
            'id_number' => '8506115009084',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test.unique@test.com',
            'cellphone' => $this->member->cellphone, // Same cellphone as existing member
            'date_of_birth' => '1985-06-11',
            'status' => 'active'
        ];

        expect(function () use ($memberData) {
            Member::create($memberData);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('enforces unique ID numbers', function () {
        $memberData = [
            'member_number' => 'MBR2003',
            'id_number' => $this->member->id_number, // Same ID as existing member
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test.unique2@test.com',
            'cellphone' => '0827777778',
            'date_of_birth' => '1990-01-01',
            'status' => 'active'
        ];

        expect(function () use ($memberData) {
            Member::create($memberData);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });
});

describe('Member Search and Filter Functionality', function () {

    beforeEach(function () {
        // Clean up any existing test data and create fresh test members
        Member::where('member_number', 'like', 'MBRT%')->delete();
        Member::where('id_number', '9512314567087')->delete();
        Member::where('id_number', '7503154567089')->delete();

        Member::create([
            'member_number' => 'MBRT001',
            'id_number' => '9512314567087', // Valid checksum
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice.smith@test.com',
            'cellphone' => '0821111111',
            'date_of_birth' => '1995-12-31',
            'status' => 'active'
        ]);

        Member::create([
            'member_number' => 'MBRT002',
            'id_number' => '7503154567089', // Valid checksum for 1975-03-15
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'email' => 'bob.johnson@test.com',
            'cellphone' => '0822222222',
            'date_of_birth' => '1975-03-15',
            'status' => 'active'
        ]);
    });

    it('can search by ID number', function () {
        $results = Member::searchByIdOrMember('9512314567087')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->first_name)->toBe('Alice');
    });

    it('can search by member number', function () {
        $results = Member::searchByIdOrMember('MBRT002')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->first_name)->toBe('Bob');
    });

    it('can search by partial ID number', function () {
        $results = Member::searchByIdOrMember('95123')->get();

        expect($results->count())->toBeGreaterThanOrEqual(1);
        expect($results->pluck('id_number'))->toContain('9512314567087');
    });

    it('can search by partial member number', function () {
        $results = Member::searchByIdOrMember('MBRT')->get();

        expect($results->count())->toBeGreaterThanOrEqual(2);
    });

    it('can filter by cellphone containing digits', function () {
        $results = Member::filterByCellphone('1111')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->cellphone)->toBe('0821111111');
    });

    it('can filter by cellphone starting with prefix', function () {
        $results = Member::filterByCellphone('082')->get();

        expect($results->count())->toBeGreaterThanOrEqual(2);
    });

    it('returns empty results for non-matching searches', function () {
        $results = Member::searchByIdOrMember('NONEXISTENT')->get();
        expect($results)->toHaveCount(0);

        $results = Member::filterByCellphone('9999')->get();
        expect($results)->toHaveCount(0);
    });
});

describe('Member Model Attributes and Relationships', function () {

    it('returns full name attribute', function () {
        expect($this->member->full_name)->toBe($this->member->first_name . ' ' . $this->member->last_name);
    });

    it('casts date_of_birth to Carbon instance', function () {
        expect($this->member->date_of_birth)->toBeInstanceOf(Carbon::class);
    });

    it('has correct fillable attributes', function () {
        $fillable = [
            'member_number',
            'id_number',
            'first_name',
            'last_name',
            'email',
            'cellphone',
            'date_of_birth',
            'status'
        ];

        expect($this->member->getFillable())->toBe($fillable);
    });

    it('has correct status options', function () {
        $validStatuses = ['active', 'inactive', 'suspended'];

        foreach ($validStatuses as $status) {
            $member = Member::factory()->create(['status' => $status]);
            expect($member->status)->toBe($status);
        }
    });
});

describe('Member Factory Tests', function () {

    it('creates members with valid South African ID numbers', function () {
        $members = Member::factory()->count(5)->create();

        $members->each(function ($member) {
            expect(Member::isValidSouthAfricanId($member->id_number))->toBeTrue();
            expect(strlen($member->id_number))->toBe(13);
        });
    });

    it('creates members with valid cellphone numbers', function () {
        $members = Member::factory()->count(5)->create();

        $members->each(function ($member) {
            expect($member->cellphone)->toMatch('/^0[6-8][0-9]{8}$/');
            expect(strlen($member->cellphone))->toBe(10);
        });
    });

    it('creates members with date of birth matching ID number', function () {
        $member = Member::factory()->create();
        $extractedDob = Member::extractDateOfBirthFromId($member->id_number);

        expect($member->date_of_birth->format('Y-m-d'))->toBe($extractedDob->format('Y-m-d'));
    });

    it('can create members with specific status', function () {
        $activeMember = Member::factory()->active()->create();
        $inactiveMember = Member::factory()->inactive()->create();
        $suspendedMember = Member::factory()->suspended()->create();

        expect($activeMember->status)->toBe('active');
        expect($inactiveMember->status)->toBe('inactive');
        expect($suspendedMember->status)->toBe('suspended');
    });
});

describe('Edge Cases and Data Integrity', function () {

    it('handles empty or null values gracefully', function () {
        expect(Member::isValidSouthAfricanId(null))->toBeFalse();
        expect(Member::isValidSouthAfricanId(''))->toBeFalse();
        expect(Member::extractDateOfBirthFromId(null))->toBeNull();
        expect(Member::extractDateOfBirthFromId(''))->toBeNull();
    });

    it('handles special characters in ID numbers', function () {
        expect(Member::isValidSouthAfricanId('9001-015-009-087'))->toBeFalse();
        expect(Member::isValidSouthAfricanId('9001 015 009 087'))->toBeFalse();
        expect(Member::isValidSouthAfricanId('90010150090ab'))->toBeFalse();
    });

    it('validates member number format', function () {
        $memberNumber = Member::generateMemberNumber();
        expect($memberNumber)->toMatch('/^MBR\d{4}$/');
    });

    it('maintains data consistency', function () {
        $originalId = $this->member->id;
        $originalMemberNumber = $this->member->member_number;

        $this->member->update(['first_name' => 'Updated Name']);

        expect($this->member->id)->toBe($originalId);
        expect($this->member->member_number)->toBe($originalMemberNumber);
        expect($this->member->fresh()->first_name)->toBe('Updated Name');
    });
});
