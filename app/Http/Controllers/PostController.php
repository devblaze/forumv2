<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View | \Illuminate\Http\JsonResponse
    {
        $perPage = 10;
        $search = $request->input('search');
        $query = Post::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%")
                    ->orWhereHas('comments', function ($commentQuery) use ($search) {
                        $commentQuery->where('content', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->is('api/*') || $request->wantsJson()) {
            $page = $request->input('page', 1);

            $posts = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            return response()->json(['posts' => $posts]);
        }

        $posts = $query->latest()->paginate($perPage);

        return view('posts.index', compact('posts', 'search'));
    }

    public function show(Post $post): \Illuminate\Contracts\View\View
    {
        return view('posts.show', compact('post'));
    }

    public function create(): \Illuminate\Contracts\View\View | \Illuminate\Http\RedirectResponse
    {
        return view('posts.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if (!auth()->user()->hasVerifiedEmail()) {
            return redirect()->back()->with('error', 'You cannot create a post without verifying your email.');
        }

        Post::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post): \Illuminate\Contracts\View\View | \Illuminate\Http\RedirectResponse
    {
        if ($post->comments()->count() > 0) {
            return redirect()->back()->with('error', 'You cannot edit a post that has comments.');
        }

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($post->comments()->count() > 0) {
            return redirect()->back()->with('error', 'You cannot update a post that has comments.');
        }

        $post->update($request->only('title', 'content'));

        return redirect()->route('posts.show', $post)->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): \Illuminate\Http\RedirectResponse
    {
        if ($post->comments()->count() > 0) {
            return redirect()->back()->with('error', 'You cannot delete a post that has comments.');
        }

        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }
}
