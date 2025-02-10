<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
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
        $request->validate([
            'text' => 'required|string',
        ]);

        if (!auth()->user()->hasVerifiedEmail()) {
            return redirect()->back()->with('error', 'You must verify your email to add a comment.');
        }

        $post->comments()->create([
            'text' => $request->text,
            'user_id' => auth()->id(),
        ]);

        $post->user->notify(new \App\Notifications\NewComment($post));

        return redirect()->route('posts.show', $post->id)->with('success', 'Comment added successfully.');
    }

    public function destroy(Comment $comment): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }

}
