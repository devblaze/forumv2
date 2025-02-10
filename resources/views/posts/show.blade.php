@vite(['resources/js/infiniteScroll.js'])

<x-app-layout>
    <!-- Notification Section -->
    @if (session('success') || session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3000)"
            x-show="show"
            class="fixed bottom-4 right-4 px-6 py-4 flex items-center gap-2 rounded-md shadow-lg text-white"
            :class="{
                'bg-green-500': '{{ session('success') }}',
                'bg-red-500': '{{ session('error') }}'
            }"
            style="display: none;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m-6 8a8.003 8.003 0 0013-6.6 8.003 8.003 0 00-13-6.6M21 21l-2-2" />
            </svg>
            <span>{{ session('success') ?? session('error') }}</span>
        </div>
    @endif

    <div class="container mx-auto my-10">
        <!-- Post Section -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-8">
            <h1 class="text-white text-4xl font-bold mb-4">{{ $post->title }}</h1>
            <p class="text-white-600 dark:text-gray-400 text-sm mb-4">
                Posted {{ $post->created_at->diffForHumans() }} by User #{{ $post->user_id }}
            </p>
            <div class="text-gray-800 dark:text-gray-200">
                {{ $post->content }}
            </div>
        </div>

        <!-- Comment Form -->
        @if (auth()->check() && auth()->user()->hasVerifiedEmail())
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Add a Comment</h3>

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
                        <div x-data="{ isEditing: false, temporaryContent: comment.content }">
                            <!-- Display Mode -->
                            <div x-show="!isEditing">
                                <p class="text-gray-800 dark:text-gray-200 mb-2" x-text="comment.content"></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Posted <span x-text="comment.created_at_diff"></span> by User #<span x-text="comment.user_id"></span>
                                </p>

                                <!-- Buttons -->
                                @auth
                                    <div x-show="comment.user_id === {{ auth()->id() }}">
                                        <!-- Edit -->
                                        <button
                                            class="text-blue-500 hover:underline"
                                            @click="isEditing = true; temporaryContent = comment.content"
                                        >
                                            Edit
                                        </button>

                                        <!-- Delete -->
                                        <form method="POST" :action="`{{ url('posts/' . $post->id . '/comments') }}/${comment.id}`" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                @endauth
                            </div>

                            <!-- Edit Mode -->
                            <div x-show="isEditing">
                                <form method="POST" :action="`{{ url('posts/' . $post->id . '/comments') }}/${comment.id}`">
                                    @csrf
                                    @method('PUT')
                                    <textarea
                                        name="content"
                                        x-model="temporaryContent"
                                        class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 dark:text-gray-200 p-2"
                                        required
                                    ></textarea>

                                    <div class="flex gap-2 mt-2">
                                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
                                        <button
                                            type="button"
                                            @click="isEditing = false"
                                            class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>

            <div x-show="isLoading" class="text-center mt-6">
                <p class="text-gray-500 dark:text-gray-300">Loading more comments...</p>
            </div>
            <div x-show="!hasMore && items.length > 0" class="text-center mt-6">
                <p class="text-gray-500 dark:text-gray-400">No more comments to load.</p>
            </div>
            <div x-show="items.length === 0 && !isLoading" class="text-center mt-6">
                <p class="text-gray-500 dark:text-gray-400">No comments yet.</p>
            </div>
        </div>
    </div>
</x-app-layout>
