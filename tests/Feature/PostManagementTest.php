<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * 1) Users can create new posts.
 */
test('users can create new posts', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    actingAs($user)
        ->post(route('posts.store'), [
            'title' => 'New Post Title',
            'content' => 'This is the content for the new post.',
        ])
        ->assertRedirect();

    expect(Post::where('title', 'New Post Title')->exists())->toBeTrue();
});

/**
 * 2) A post has a title, text, and creation date.
 */
test('a post has a title, content, and creation date', function () {
    $post = Post::factory()->create();

    expect($post)->toHaveKey('title')
        ->toHaveKey('content')
        ->toHaveKey('created_at');
});

/**
 * 3) Users can add comments to posts.
 */
test('users can add comments to posts', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    actingAs($user)
        ->post(route('posts.comments.store', $post), [
            'content' => 'This is a comment.',
        ])
        ->assertRedirect();

    expect(Comment::where('content', 'This is a comment')->exists())->toBeTrue();
});

/**
 * 4) A comment has a text, creation date, and editing date.
 */
test('a comment has content, creation date, and editing date', function () {
    $comment = Comment::factory()->create(['content' => 'Sample Comment']);

    expect($comment)
        ->toHaveKey('content')
        ->toHaveKey('created_at')
        ->toHaveKey('updated_at');
});

/**
 * 5) Users can edit and delete their comments.
 */
test('users can edit and delete their comments', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

    actingAs($user);

    $this->patch(route('posts.comments.update', [$post, $comment]), [
        'content' => 'Updated Comment',
    ]);

    $comment->refresh();
    expect($comment->content)->toBe('Updated Comment');

    $this->delete(route('posts.comments.destroy', [$post, $comment]))->assertRedirect();
    expect(Comment::find($comment->id))->toBeNull();
});

/**
 * 6) Users can edit and delete their posts if there are no comments.
 */
test('users can edit and delete their posts only if there are no comments', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    actingAs($user);

    $this->patch(route('posts.update', $post), [
        'title' => 'Updated Title',
        'content' => 'Updated content.',
    ])->assertRedirect();

    $post->refresh();
    expect($post->title)->toBe('Updated Title');

    Comment::factory()->create(['post_id' => $post->id]);
    $this->delete(route('posts.destroy', $post))->assertRedirect();

    expect(Post::find($post->id))->not->toBeNull();
});

/**
 * 7) Users are notified by email when a new comment is added to their post.
 */
test('users are notified by email when a new comment is added', function () {
    Mail::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);
    $commenter = User::factory()->create(['email_verified_at' => now()]);

    actingAs($commenter)
        ->post(route('posts.comments.store', $post), ['content' => 'New comment']);

    Mail::assertQueued(function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

/**
 * 8) Posts are ordered by their creation date (descending).
 */
test('posts are ordered by their creation date', function () {
    Post::factory()->create(['created_at' => now()->subDays(2)]);
    Post::factory()->create(['created_at' => now()->subDay()]);
    Post::factory()->create(['created_at' => now()]);

    $response = get(route('posts.index'))->assertStatus(200);
    $posts = $response->viewData('posts');

    expect($posts->pluck('created_at'))
        ->toBeSorted(['descending' => true]);
});

/**
 * 9) Users can search for posts with specific content (title, text, or comments).
 */
test('users can search for posts by title, content, or comments', function () {
    $user = User::factory()->create();

    // Create posts matching the search query
    $postWithTitle = Post::factory()->create(['title' => 'Searchable Title']);
    $postWithContent = Post::factory()->create(['content' => 'Searchable Content']);
    $postWithComment = Post::factory()->create();
    Comment::factory()->create(['post_id' => $postWithComment->id, 'content' => 'Searchable Comment']);

    actingAs($user);

    // Perform the search using the term "Searchable"
    $response = $this->get(route('posts.index', ['search' => 'Searchable']));

    // Assert that the posts in the response include those matching the search query
    $response->assertSee($postWithTitle->title);
    $response->assertSee($postWithContent->content);
    $response->assertSee($postWithComment->title);

    // Optionally, ensure no irrelevant data appears
    $response->assertDontSee('No matches found');
});

/**
 * 10) Users can only create posts or comments if they have verified their email address.
 */
test('users cannot create posts or comments without verifying their email', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    $post = Post::factory()->create();

    actingAs($user);

    $this->post(route('posts.store'), [
        'title' => 'Unverified Post',
        'content' => 'Content',
    ]);

    $this->post(route('posts.comments.store', $post), [
        'content' => 'Comment from unverified user',
    ]);

    expect(Post::where('title', 'Comment from unverified user')->exists())->toBeFalse();
});

/**
 * 11) Posts inactive for 1 year are soft deleted if they have no comments.
 */
test('posts not commented on for 1 year are soft deleted', function () {
    $oldPost = Post::factory()->create(['created_at' => now()->subYears(2)]);
    $recentPost = Post::factory()->create(['created_at' => now()->subMonths(6)]);
    $commentedPost = Post::factory()->create(['created_at' => now()->subYears(2)]);
    Comment::factory()->create(['post_id' => $commentedPost->id, 'created_at' => now()->subMonth()]);

    $this->artisan('posts:cleanup')->assertSuccessful();

    $oldPost->refresh();
    $recentPost->refresh();
    $commentedPost->refresh();

    expect($oldPost->trashed())->toBeTrue();
    expect($recentPost->trashed())->toBeFalse();
    expect($commentedPost->trashed())->toBeFalse();
});
