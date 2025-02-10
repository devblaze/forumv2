@vite(['resources/js/infiniteScroll.js'])

<x-app-layout>
    <!-- Notification Section -->
    @if (session('success') || session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3000)"
            x-show="show"
            class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg"
            :class="{ 'bg-green-500': '{{ session('success') }}', 'bg-red-500': '{{ session('error') }}' }"
            style="display: none;"
        >
        {{ session('success') ?? session('error') }}
        </div>
    @endif

    <div class="container mx-auto my-10">
        <!-- Post Section -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-8">
            <h1 class="text-white text-4xl font-bold mb-4">{{ $post->title }}</h1>
            <p class="text-white-600 dark:text-gray-400 text-sm mb-4">
                Posted {{ $post->created_at->diffForHumans() }} by User #{{ $post->author_id }}
            </p>
            <div class="text-gray-800 dark:text-gray-200">
                {{ $post->content }}
            </div>

            <!-- Edit and Delete Controls -->
            @if ($post->comments()->count() === 0)
            <div class="mt-4 flex gap-4">
                <!-- Edit Button -->
                <a href="{{ route('posts.edit', $post) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    Edit Post
                </a>

                <!-- Delete Button -->
                <form method="POST" action="{{ route('posts.destroy', $post) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Delete Post
                    </button>
                </form>
            </div>
            @else
                <p class="mt-4 text-red-500">This post cannot be edited or deleted because it has comments.</p>
            @endif
        </div>

        <!-- Comment Form -->
        @if (auth()->check() && auth()->user()->hasVerifiedEmail())
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Add a Comment</h3>

            <!-- Form -->
            <form method="POST" action="{{ route('posts.comments.store', $post) }}">
                @csrf
                <div class="mb-4">
            <textarea
                name="content"
                id="content"
                rows="3"
                class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 dark:text-gray-200 p-2"
                placeholder="Write your comment here..."
                required
            ></textarea>
                </div>

                <button
                    type="submit"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
                >
                    Add Comment
                </button>
            </form>
        </div>
        @else
            <div class="mt-6 text-red-500">
                <p>You must be logged in and have a verified email to add comments.</p>
            </div>
        @endif

        <!-- Comments Section -->
        <div
            class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6"
            x-data="infiniteScroll({ apiUrl: '/api/posts/{{ $post->id }}/comments', dataField: 'comments' })"
            x-init="init()"
        >
            <h2 class="text-green-800 text-2xl font-bold mb-4">
                Comments (<span x-text="items.length"></span>)
            </h2>

            <!-- Comments List -->
            <ul>
                <template x-for="comment in items" :key="comment.id">
                    <li class="border-b last:border-none border-gray-200 dark:border-gray-700 py-4">
                        <p class="text-gray-800 dark:text-gray-200 mb-2" x-text="comment.content"></p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Posted <span x-text="comment.created_at_diff"></span> by User #<span
                                x-text="comment.user_id"></span>
                        </p>
                    </li>
                </template>
            </ul>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="text-center mt-6">
                <p class="text-gray-500 dark:text-gray-300">Loading more comments...</p>
            </div>

            <!-- End of Comments -->
            <div x-show="!hasMore && items.length > 0" class="text-center mt-6">
                <p class="text-gray-500 dark:text-gray-400">No more comments to load.</p>
            </div>

            <!-- Empty State -->
            <div x-show="items.length === 0 && !isLoading" class="text-center mt-6">
                <p class="text-gray-500 dark:text-gray-400">No comments yet.</p>
            </div>
        </div>
    </div>
</x-app-layout>
