<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View | \Illuminate\Http\JsonResponse
    {
        $perPage = 10;

        if ($request->is('api/*') || $request->wantsJson()) {
            $page = $request->input('page', 1);

            $posts = Post::orderBy('created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            return response()->json(['posts' => $posts]);
        }

        $posts = Post::latest()->paginate($perPage);

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post): \Illuminate\Contracts\View\View
    {
        return view('posts.show', compact('post'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'text' => 'required|string',
        ]);

        if (!auth()->user()->hasVerifiedEmail()) {
            return redirect()->back()->with('error', 'You must verify your email to create a post.');
        }

        Post::create([
            'title' => $request->title,
            'text' => $request->text,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    public function destroy(Post $post): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $post);

        if ($post->comments()->count() > 0) {
            return redirect()->back()->with('error', 'You cannot delete a post with comments.');
        }

        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

}
