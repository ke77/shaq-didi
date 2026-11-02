<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // create 5 restaurants, each with 4 products
        Restaurant::factory()->count(5)->hasProducts(4)->create();
            
        // testing users
        User::factory()->create([
            'name' => 'Permanent Staff Account',
            'email' => 'ps@example.com',
            'role' => 'permanent_staff',
            'phone_number' => '0241111111',
        ]);

        User::factory()->create([
            'name' => 'National Service Personnel',
            'email' => 'nsp@example.com',
            'role' => 'national_service',
            'phone_number' => '0242222222',
        ]);
    }
}
