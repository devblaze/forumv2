<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use Carbon\Carbon;

class CleanupPosts extends Command
{
    protected $signature = 'posts:cleanup';
    protected $description = 'Soft-delete posts not commented on for 1 year';

    public function handle()
    {
        $oneYearAgo = Carbon::now()->subYear();

        $posts = Post::whereDoesntHave('comments', function($q) use ($oneYearAgo){
            $q->where('created_at', '>', $oneYearAgo);
        })
            ->where('created_at', '<=', $oneYearAgo)
            ->get();

        foreach ($posts as $post) {
            $post->delete();
        }

        $this->info($posts->count().' post(s) soft-deleted.');
        return 0;
    }
}
