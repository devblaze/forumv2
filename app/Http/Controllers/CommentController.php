<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
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

    public function edit(Comment $comment): \Illuminate\Contracts\View\View
    {
        $this->authorize('update', $comment);

        return view('comments.edit', compact('comment'));
    }

    public function update(Request $request, Comment $comment): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $comment);

        $request->validate([
            'text' => 'required|string',
        ]);

        $comment->update([
            'text' => $request->text,
            'edited_at' => now(),
        ]);

        return redirect()->route('posts.show', $comment->post_id)->with('success', 'Comment updated successfully.');
    }

    public function store(Request $request, Post $post): \Illuminate\Http\RedirectResponse
    {
        if (!auth()->user() || !auth()->user()->hasVerifiedEmail()) {
            throw new AuthorizationException('You must verify your email to add a comment.');
        }

        $request->validate([
            'content' => 'required|string|min:3|max:500'
        ]);

        $post->comments()->create([
            'content' => $request->input('content'),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('posts.show', $post)->with('success', 'Comment added successfully.');
    }

    public function destroy(Comment $comment): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }

}
