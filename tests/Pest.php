<?php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');

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
