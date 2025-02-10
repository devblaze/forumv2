<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NewComment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Post $post, Request $request)
    {
        $comments = $post->comments()->latest()->paginate(10);

        return response()->json([
            'comments' => $comments->items(),
            'total' => $comments->total(),
            'hasMore' => $comments->hasMorePages(),
        ]);
    }

    public function show(Comment $comment)
    {
        return response()->json($comment);
//        return view('comments.show', compact('comment'));
    }

    public function store(Request $request, Post $post): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'content' => 'required|string|min:3|max:500'
        ]);

        $comment = $post->comments()->create([
            'content' => $request->input('content'),
            'user_id' => auth()->id(),
        ]);
        
        if ($post->user && $post->user->id !== auth()->id()) {
            $post->user->notify(new NewComment($post));
        }

        return redirect()->route('posts.show', $post)->with('success', 'Comment added successfully.');
    }

    public function edit($post_id, Comment $comment)
    {
        $this->authorize('update', $comment);
        return view('comments.edit', compact('comment'));
    }

    public function update(Request $request, $post_id, Comment $comment)
    {
        if (auth()->id() !== $comment->user_id) {
            return redirect()->route('posts.show', $post_id)->with('error', 'You are not authorized to update this comment.');
        }

        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment->forceFill([
            'content' => $request->input('content'),
            'edited_at' => now(),
        ])->save();

        return redirect()->route('posts.show', $post_id)->with('success', 'Comment updated successfully.');
    }

    public function destroy($post_id, Comment $comment)
    {
        if (auth()->id() !== $comment->user_id) {
            return redirect()->route('posts.show', $post_id)->with('error', 'You are not authorized to update this comment.');
        }

        $comment->delete();

        return redirect()->route('posts.show', $post_id)->with('success', 'Comment deleted successfully.');
    }
}
