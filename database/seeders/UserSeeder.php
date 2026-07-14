<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create 1 Super Admin for testing login
        User::factory()->admin()->create();

        // 2. Create 1 Manager Assistant
        User::factory()->manager()->create();

        // 3. Create 50 regular users without any subscription
        User::factory()->count(50)->create();

        // 4. Create 30 users with an active premium subscription
        User::factory()->count(30)->subscribed()->create();

        // 5. Create 20 users with an expired subscription (to test limits)
        User::factory()->count(20)->expiredSubscription()->create();
    }
}
