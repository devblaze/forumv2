<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::factory()
            ->count(100)
            ->hasComments(random_int(1, 100))
            ->create();
    }
}
