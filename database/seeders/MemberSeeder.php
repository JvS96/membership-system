<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample members for testing
        Member::factory()->create([
            'member_number' => 'MBR0001',
            'first_name' => 'Sophia',
            'last_name' => 'Clark',
            'email' => 'sophia.clark@email.com',
            'cellphone' => '0821234567',
            'status' => 'active'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0002',
            'first_name' => 'Liam',
            'last_name' => 'Walker',
            'email' => 'liam.walker@email.com',
            'cellphone' => '0827654321',
            'status' => 'active'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0003',
            'first_name' => 'Olivia',
            'last_name' => 'Green',
            'email' => 'olivia.green@email.com',
            'cellphone' => '0829876543',
            'status' => 'inactive'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0004',
            'first_name' => 'Noah',
            'last_name' => 'Hill',
            'email' => 'noah.hill@email.com',
            'cellphone' => '0831112233',
            'status' => 'active'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0005',
            'first_name' => 'Ava',
            'last_name' => 'Baker',
            'email' => 'ava.baker@email.com',
            'cellphone' => '0844455566',
            'status' => 'suspended'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0006',
            'first_name' => 'Ethan',
            'last_name' => 'Cook',
            'email' => 'ethan.cook@email.com',
            'cellphone' => '0765556677',
            'status' => 'active'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0007',
            'first_name' => 'Isabella',
            'last_name' => 'Murphy',
            'email' => 'isabella.murphy@email.com',
            'cellphone' => '0788990011',
            'status' => 'active'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0008',
            'first_name' => 'Mason',
            'last_name' => 'Rivera',
            'email' => 'mason.rivera@email.com',
            'cellphone' => '0712345678',
            'status' => 'inactive'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0009',
            'first_name' => 'Mia',
            'last_name' => 'Cooper',
            'email' => 'mia.cooper@email.com',
            'cellphone' => '0734567890',
            'status' => 'active'
        ]);

        Member::factory()->create([
            'member_number' => 'MBR0010',
            'first_name' => 'Alexander',
            'last_name' => 'Reed',
            'email' => 'alexander.reed@email.com',
            'cellphone' => '0756789012',
            'status' => 'active'
        ]);

        // Create additional random members
        Member::factory()->count(40)->create();
    }
}
