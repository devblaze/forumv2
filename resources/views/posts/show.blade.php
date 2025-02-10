@vite(['resources/js/infiniteScroll.js'])

<x-app-layout>
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
        </div>

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
