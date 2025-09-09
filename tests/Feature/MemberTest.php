<?php

use App\Models\Member;
use Carbon\Carbon;

beforeEach(function () {
    $this->member = Member::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'cellphone' => '0821234567',
        'status' => 'active'
    ]);
});

describe('Member CRUD Operations', function () {

    it('can create a member', function () {
        $memberData = Member::factory()->make()->toArray();

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
        $newEmail = 'updated.email@example.com';
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
        // Valid ID numbers
        expect(Member::isValidSouthAfricanId('9001015009087'))->toBeTrue();
        expect(Member::isValidSouthAfricanId('8506115009087'))->toBeTrue();

        // Invalid ID numbers
        expect(Member::isValidSouthAfricanId('1234567890123'))->toBeFalse(); // Invalid checksum
        expect(Member::isValidSouthAfricanId('123456789012'))->toBeFalse();  // Too short
        expect(Member::isValidSouthAfricanId('12345678901234'))->toBeFalse(); // Too long
        expect(Member::isValidSouthAfricanId('9013315009087'))->toBeFalse(); // Invalid month
        expect(Member::isValidSouthAfricanId('9001335009087'))->toBeFalse(); // Invalid day
    });

    it('extracts date of birth from ID number correctly', function () {
        $idNumber = '9001015009087'; // January 1, 1990
        $dateOfBirth = Member::extractDateOfBirthFromId($idNumber);

        expect($dateOfBirth)->toBeInstanceOf(Carbon::class);
        expect($dateOfBirth->year)->toBe(1990);
        expect($dateOfBirth->month)->toBe(1);
        expect($dateOfBirth->day)->toBe(1);

        // Test with different century
        $idNumber = '0501015009087'; // January 1, 2005
        $dateOfBirth = Member::extractDateOfBirthFromId($idNumber);

        expect($dateOfBirth->year)->toBe(2005);
    });

    it('generates unique member numbers', function () {
        $memberNumber1 = Member::generateMemberNumber();
        $memberNumber2 = Member::generateMemberNumber();

        expect($memberNumber1)->not->toBe($memberNumber2);
        expect($memberNumber1)->toStartWith('MBR');
        expect($memberNumber2)->toStartWith('MBR');
        expect(strlen($memberNumber1))->toBe(7); // MBR + 4 digits
    });

    it('requires unique email addresses', function () {
        $memberData = Member::factory()->make([
            'email' => $this->member->email
        ])->toArray();

        expect(function () use ($memberData) {
            Member::create($memberData);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('requires unique cellphone numbers', function () {
        $memberData = Member::factory()->make([
            'cellphone' => $this->member->cellphone
        ])->toArray();

        expect(function () use ($memberData) {
            Member::create($memberData);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('requires unique ID numbers', function () {
        $memberData = Member::factory()->make([
            'id_number' => $this->member->id_number
        ])->toArray();

        expect(function () use ($memberData) {
            Member::create($memberData);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });
});

describe('Member Search and Filter Functionality', function () {

    beforeEach(function () {
        Member::factory()->create([
            'member_number' => 'MBR0001',
            'id_number' => '9001015009087',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'cellphone' => '0821111111'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0002',
            'id_number' => '8506115009087',
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'cellphone' => '0822222222'
        ]);
    });

    it('can search by ID number', function () {
        $results = Member::searchByIdOrMember('9001015009087')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->first_name)->toBe('Alice');
    });

    it('can search by member number', function () {
        $results = Member::searchByIdOrMember('MBR0002')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->first_name)->toBe('Bob');
    });

    it('can search by partial ID number', function () {
        $results = Member::searchByIdOrMember('90010')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->id_number)->toBe('9001015009087');
    });

    it('can search by partial member number', function () {
        $results = Member::searchByIdOrMember('MBR00')->get();

        expect($results)->toHaveCount(2);
    });

    it('can filter by cellphone containing digits', function () {
        $results = Member::filterByCellphone('111')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->cellphone)->toBe('0821111111');
    });

    it('can filter by cellphone starting with prefix', function () {
        $results = Member::filterByCellphone('082')->get();

        expect($results)->toHaveCount(2);
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
});

describe('Member Factory Tests', function () {

    it('creates members with valid South African ID numbers', function () {
        $members = Member::factory()->count(10)->create();

        $members->each(function ($member) {
            expect(Member::isValidSouthAfricanId($member->id_number))->toBeTrue();
        });
    });

    it('creates members with valid cellphone numbers', function () {
        $members = Member::factory()->count(10)->create();

        $members->each(function ($member) {
            expect($member->cellphone)->toMatch('/^0[6-8][0-9]{8}$/');
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

describe('Edge Cases and Error Handling', function () {

    it('handles invalid ID numbers gracefully', function () {
        expect(Member::extractDateOfBirthFromId('invalid'))->toBeNull();
        expect(Member::extractDateOfBirthFromId('1234567890123'))->toBeNull();
        expect(Member::extractDateOfBirthFromId(''))->toBeNull();
    });

    it('handles empty search queries', function () {
        $results = Member::searchByIdOrMember('')->get();
        expect($results)->toHaveCount(0);

        $results = Member::filterByCellphone('')->get();
        expect($results)->toHaveCount(0);
    });

    it('validates member number format', function () {
        $memberNumber = Member::generateMemberNumber();
        expect($memberNumber)->toMatch('/^MBR\d{4}$/');
    });

    it('prevents duplicate member numbers', function () {
        // Create a member with a specific member number
        $member1 = Member::factory()->create(['member_number' => 'MBR9999']);

        // Ensure generated numbers don't conflict
        $memberNumber = Member::generateMemberNumber();
        expect($memberNumber)->not->toBe('MBR9999');
    });
});

describe('Data Integrity Tests', function () {

    it('maintains referential integrity on updates', function () {
        $originalId = $this->member->id;

        $this->member->update(['first_name' => 'Updated Name']);

        expect($this->member->id)->toBe($originalId);
        expect($this->member->fresh()->first_name)->toBe('Updated Name');
    });

    it('validates email format strictly', function () {
        expect(function () {
            Member::factory()->create(['email' => 'invalid-email']);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('enforces status enum values', function () {
        // Valid status values should work
        $this->member->update(['status' => 'inactive']);
        expect($this->member->fresh()->status)->toBe('inactive');

        $this->member->update(['status' => 'suspended']);
        expect($this->member->fresh()->status)->toBe('suspended');

        $this->member->update(['status' => 'active']);
        expect($this->member->fresh()->status)->toBe('active');
    });
});

describe('Performance and Scaling Tests', function () {

    it('can handle bulk member creation efficiently', function () {
        $startTime = microtime(true);

        Member::factory()->count(100)->create();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time (adjust threshold as needed)
        expect($executionTime)->toBeLessThan(10); // 10 seconds max

        // Verify all members were created
        expect(Member::count())->toBeGreaterThanOrEqual(100);
    });

    it('search functionality scales with larger datasets', function () {
        // Create a larger dataset
        Member::factory()->count(50)->create();

        $startTime = microtime(true);
        $results = Member::searchByIdOrMember('MBR')->get();
        $endTime = microtime(true);

        $searchTime = $endTime - $startTime;
        expect($searchTime)->toBeLessThan(1); // Should be fast even with more data
    });
});
