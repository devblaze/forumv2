<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\artisan;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewComment;

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
        ->post(route('posts.comments.store', $post), ['content' => 'This is a comment.',])
        ->assertRedirect()
        ->assertSessionDoesntHaveErrors();
});

test('users can update their own comments', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);

    actingAs($user);

    $this->patch(route('posts.comments.update', [$post, $comment]), [
        'content' => 'Updated Comment',
    ])->assertRedirect();
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
    $comment = Comment::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'content' => 'Original Content',
    ]);

    actingAs($user);

    $this->patch(route('posts.comments.update', [$post, $comment]), [
        'content' => 'Updated Comment',
    ])->assertRedirect(route('posts.show', $post));

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
    Notification::fake();

    $postOwner = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $postOwner->id]);
    $commenter = User::factory()->create(['email_verified_at' => now()]);

    actingAs($commenter)
        ->post(route('posts.comments.store', $post), ['content' => 'This is a new comment.']);

    Notification::assertSentTo($postOwner, NewComment::class, function ($notification) use ($post) {
        return $notification->post->id === $post->id;
    });
});

/**
 * 8) Posts are ordered by their creation date (descending).
 */
test('posts are ordered by their creation date', function () {
    $user = User::factory()->create();
    actingAs($user);

    $post1 = Post::factory()->create(['created_at' => now()->subDays(2)]);
    $post2 = Post::factory()->create(['created_at' => now()->subDay()]);
    $post3 = Post::factory()->create(['created_at' => now()]);

    $response = get(route('posts.index'))->assertStatus(200);

    $posts = $response->viewData('posts');

    $actualOrder = $posts->pluck('created_at')->toArray();
    $expectedOrder = collect([$post1, $post2, $post3])
        ->sortByDesc('created_at')
        ->pluck('created_at')
        ->toArray();

    expect($actualOrder)->toEqual($expectedOrder);
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
    Carbon::setTestNow(Carbon::now());

    $post1 = Post::factory()->create(['created_at' => Carbon::now()->subYears(2)]);

    $post2 = Post::factory()->create(['created_at' => Carbon::now()->subYears(2)]);
    Comment::factory()->create([
        'post_id' => $post2->id,
        'created_at' => Carbon::now()->subMonths(6),
    ]);

    $post3 = Post::factory()->create([
        'created_at' => Carbon::now()->subMonths(6),
    ]);

    artisan('posts:cleanup')->assertSuccessful();

    expect(Post::withTrashed()->find($post1->id)?->deleted_at)->not->toBeNull();

    expect(Post::find($post2->id))->not->toBeNull();

    expect(Post::find($post3->id))->not->toBeNull();
});
