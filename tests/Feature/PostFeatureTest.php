<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the main page for displaying posts.
     */
    public function test_posts_are_displayed_on_the_main_page(): void
    {
        // Create 3 sample posts
        Post::factory()->count(3)->create();

        // Visit the home route
        $response = $this->get(route('home'));

        // Assert the response status is 200
        $response->assertStatus(200);

        // Check if the posts are visible in the response
        $posts = Post::all();
        foreach ($posts as $post) {
            $response->assertSee(e($post->title)); // Ensure post titles are displayed
        }
    }

    /**
     * Test displaying a single post with its comments.
     */
    public function test_single_post_and_comments_are_displayed(): void
    {
        // Create a sample post and corresponding comments
        $post = Post::factory()->create();
        $comments = $post->comments()->createMany([
            ['content' => 'First comment', 'user_id' => 1],
            ['content' => 'Second comment', 'user_id' => 1],
        ]);

        // Visit the single post route
        $response = $this->get(route('posts.show', $post->id));

        // Assert the response status is 200
        $response->assertStatus(200);

        // Check if the post content is visible
        $response->assertSee(e($post->title));
        $response->assertSee(e($post->content));

        // Check if the comments are visible
        foreach ($comments as $comment) {
            $response->assertSee(e($comment->content));
        }
    }

    /**
     * Test the search functionality.
     */
    public function test_search_functionality(): void
    {
        // Create sample posts
        $post1 = Post::factory()->create(['title' => 'Laravel Testing']);
        $post2 = Post::factory()->create(['title' => 'Introduction to PHP']);
        Post::factory()->create(['title' => 'Another unrelated post']);

        // Perform a search query
        $response = $this->get(route('posts.search', ['query' => 'Laravel']));

        // Assert the response status is 200
        $response->assertStatus(200);

        // Ensure the relevant post is shown
        $response->assertSee(e($post1->title));
        $response->assertDontSee(e($post2->title));
    }
}
