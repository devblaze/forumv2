<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PostSeeder::class);
        // User::factory(10)->create();

        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin',
            'password' => bcrypt('admin'), // Hash password
        ]);
    }
}
