<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(Tests\TestCase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');

beforeEach(function () {
    // Create admin user for all tests
    User::create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);
});

// Add any custom expectations here
expect()->extend('toBeValidSouthAfricanId', function () {
    return $this->toBeTrue();
});

// Helper functions for tests
function createValidMember($attributes = [])
{
    return \App\Models\Member::factory()->create($attributes);
}

function validSouthAfricanId()
{
    return '9001015009087'; // Known valid ID
}

function invalidSouthAfricanId()
{
    return '1234567890123'; // Known invalid ID
}
