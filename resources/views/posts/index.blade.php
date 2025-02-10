@vite(['resources/js/infiniteScroll.js'])

<x-app-layout>
    <div
        x-data="infiniteScroll({ apiUrl: '/api/posts', dataField: 'posts' })"
        x-init="init()"
        class="container mx-auto px-4 sm:px-6 lg:px-8 mt-4"
        style="max-width: 70em;"
    >
        <div class="flex flex-col gap-4">
            <!-- Render Post Cards Dynamically -->
            <template x-for="(post, index) in items" :key="`post-${post.id}`">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <!-- Post Title -->
                    <h2 class="text-2xl font-bold mb-2">
                        <a :href="'/posts/' + post.id"
                           class="text-blue-600 dark:text-blue-400 hover:underline"
                           x-text="post.title">
                        </a>
                    </h2>

                    <!-- Post Metadata -->
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Posted <span x-text="post.created_at_diff"></span> by User #<span x-text="post.author_id"></span>
                    </p>

                    <!-- Post Excerpt -->
                    <p class="mt-4 text-gray-800 dark:text-gray-200" x-text="post.excerpt"></p>
                </div>
            </template>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="text-center mt-6">
                <p class="text-gray-500 dark:text-gray-300">Loading more posts...</p>
            </div>

            <!-- End of Posts -->
            <div x-show="!hasMore && items.length > 0" class="text-center mt-6">
                <p class="text-gray-500 dark:text-gray-400">No more posts to load.</p>
            </div>

            <!-- No Posts -->
            <div x-show="items.length === 0 && !isLoading" class="text-center mt-6">
                <p class="text-gray-500 dark:text-gray-400">No posts available.</p>
            </div>
        </div>
    </div>
</x-app-layout>
