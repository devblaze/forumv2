<x-app-layout>
    <div class="max-w-xl mx-auto mt-8 bg-white dark:bg-gray-800 p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">
            Create a New Post
        </h1>

        @if (session('error'))
            <div class="bg-red-200 dark:bg-red-900 text-red-700 dark:text-red-100 p-2 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-200 dark:bg-green-900 text-green-700 dark:text-green-100 p-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('posts.store') }}">
            @csrf

            <!-- Title Field -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Title
                </label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title') }}"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                    required
                />
                @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Text Field -->
            <div class="mb-4">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Content
                </label>
                <textarea
                    name="content"
                    id="content"
                    rows="5"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                    required
                >{{ old('content') }}</textarea>
                @error('content')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white dark:bg-blue-700 rounded hover:bg-blue-700 dark:hover:bg-blue-800"
            >
                Create Post
            </button>
        </form>
    </div>
</x-app-layout>
