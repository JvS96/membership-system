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
        // Create some sample members for testing with valid SA ID numbers and cellphone numbers
        Member::updateOrCreate([
            'member_number' => 'MBR0001',
            'id_number' => '9001015009087',
            'first_name' => 'Sophia',
            'last_name' => 'Clark',
            'email' => 'sophia.clark@email.com',
            'cellphone' => '0821234567',
            'date_of_birth' => '1990-01-01',
            'status' => 'active'
        ]);

        Member::updateOrCreate([
            'member_number' => 'MBR0002',
            'id_number' => '8506115009087',
            'first_name' => 'Liam',
            'last_name' => 'Walker',
            'email' => 'liam.walker@email.com',
            'cellphone' => '0827654321',
            'date_of_birth' => '1985-06-11',
            'status' => 'active'
        ]);

        Member::updateOrCreate([
            'member_number' => 'MBR0003',
            'id_number' => '9512314567082',
            'first_name' => 'Olivia',
            'last_name' => 'Green',
            'email' => 'olivia.green@email.com',
            'cellphone' => '0829876543',
            'date_of_birth' => '1995-12-31',
            'status' => 'inactive'
        ]);

        Member::updateOrCreate([
            'member_number' => 'MBR0004',
            'id_number' => '8002284567089',
            'first_name' => 'Noah',
            'last_name' => 'Hill',
            'email' => 'noah.hill@email.com',
            'cellphone' => '0831112233',
            'date_of_birth' => '1980-02-28',
            'status' => 'active'
        ]);

        Member::updateOrCreate([
            'member_number' => 'MBR0005',
            'id_number' => '7503154567081',
            'first_name' => 'Ava',
            'last_name' => 'Baker',
            'email' => 'ava.baker@email.com',
            'cellphone' => '0844455566',
            'date_of_birth' => '1975-03-15',
            'status' => 'suspended'
        ]);

        Member::updateOrCreate([
            'member_number' => 'MBR0006',
            'id_number' => '9203105026094',
            'first_name' => 'Ethan',
            'last_name' => 'Cook',
            'email' => 'ethan.cook@email.com',
            'cellphone' => '0765556677',
            'date_of_birth' => '1992-03-10',
            'status' => 'active'
        ]);

        Member::updateOrCreate([
            'member_number' => 'MBR0007',
            'id_number' => '8808154567086',
            'first_name' => 'Isabella',
            'last_name' => 'Murphy',
            'email' => 'isabella.murphy@email.com',
            'cellphone' => '0781234567',
            'date_of_birth' => '1988-08-15',
            'status' => 'active'
        ]);

        Member::updateOrCreate([
            'member_number' => 'MBR0008',
            'id_number' => '9105225009083',
            'first_name' => 'Mason',
            'last_name' => 'Rivera',
            'email' => 'mason.rivera@email.com',
            'cellphone' => '0712345678',
            'date_of_birth' => '1991-05-22',
            'status' => 'inactive'
        ]);

        Member::updateOrCreate([
            'member_number' => 'MBR0009',
            'id_number' => '9607084567088',
            'first_name' => 'Mia',
            'last_name' => 'Cooper',
            'email' => 'mia.cooper@email.com',
            'cellphone' => '0734567890',
            'date_of_birth' => '1996-07-08',
            'status' => 'active'
        ]);

        Member::updateOrCreate([
            'member_number' => 'MBR0010',
            'id_number' => '8712095009081',
            'first_name' => 'Alexander',
            'last_name' => 'Reed',
            'email' => 'alexander.reed@email.com',
            'cellphone' => '0821987654',
            'date_of_birth' => '1987-12-09',
            'status' => 'active'
        ]);

        // Create additional random members using the factory
        Member::factory()->count(40)->create();

        $this->command->info('Created 50 members successfully!');
    }
}
