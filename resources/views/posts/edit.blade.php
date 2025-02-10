<x-app-layout>
    <div class="container mx-auto my-10">
        <h1 class="text-white text-4xl font-bold mb-4">Edit Post</h1>
        <form method="POST" action="{{ route('posts.update', $post) }}">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $post->title) }}"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-white rounded"
                    required
                />
                @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content</label>
                <textarea
                    name="content"
                    id="content"
                    rows="5"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-white rounded"
                    required
                >{{ old('content', $post->content) }}</textarea>
                @error('content')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white dark:bg-blue-700 rounded hover:bg-blue-800">
                Save Changes
            </button>
        </form>
    </div>
</x-app-layout>