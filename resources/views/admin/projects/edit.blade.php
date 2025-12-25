<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Images — {{ $project->title }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-indigo-400 via-blue-200 to-purple-100 min-h-screen font-sans">
    @include('navbar')

    <main class="container mx-auto p-6 py-12">
        <!-- Back Link -->
        <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold mb-6 transition">
            <span class="mr-2">←</span> Back to Projects
        </a>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-extrabold text-indigo-900 mb-2">{{ $project->title }}</h1>
            <p class="text-gray-700">Edit project details, upload images, and manage your portfolio</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 rounded-lg shadow-md font-semibold">
                ✓ {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left: Project Details -->
            <div class="bg-gradient-to-b from-indigo-50/80 via-white/90 to-cyan-50/70 rounded-2xl shadow-lg border border-white/80 p-8 backdrop-blur-sm">
                <h2 class="text-2xl font-extrabold text-indigo-900 mb-6">Project Details</h2>
                <form action="{{ route('admin.projects.update', $project->id) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-bold text-indigo-900 mb-2">Project Title</label>
                        <input type="text" name="title" value="{{ old('title', $project->title) }}" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 focus:border-indigo-500 focus:outline-none shadow-sm bg-white text-gray-900 font-medium transition" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-indigo-900 mb-2">Short Description (Intro)</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 focus:border-indigo-500 focus:outline-none shadow-sm bg-white text-gray-900 transition resize-none" placeholder="Brief summary for the project list...">{{ old('description', $project->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-indigo-900 mb-2">Technologies (comma-separated)</label>
                        <input type="text" name="technologies" value="{{ old('technologies', is_array($project->technologies) ? implode(', ', $project->technologies) : $project->technologies) }}" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 focus:border-indigo-500 focus:outline-none shadow-sm bg-white text-gray-900 transition" placeholder="Laravel, Tailwind, JavaScript">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-indigo-900 mb-2">Main Content / Notes</label>
                        <textarea name="notes" rows="10" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 focus:border-indigo-500 focus:outline-none shadow-sm bg-white text-gray-900 transition resize-y" placeholder="Full project details...">{{ old('notes', $project->notes) }}</textarea>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 via-cyan-500 to-indigo-500 hover:from-emerald-600 hover:via-cyan-600 hover:to-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-emerald-400/40 hover:shadow-emerald-400/60 hover:-translate-y-0.5 transition duration-200">
                        Save Details
                    </button>
                </form>
            </div>
            <div class="space-y-8">
                <!-- Upload Section -->
                <div class="bg-gradient-to-b from-cyan-50/80 via-white/90 to-indigo-50/70 rounded-2xl shadow-lg border border-white/80 p-8 backdrop-blur-sm mb-8">
                    <h2 class="text-2xl font-extrabold text-indigo-900 mb-6">Upload Image</h2>
                    <form action="{{ route('admin.projects.images.store', $project->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-sm font-bold text-indigo-900 mb-2">Select Image</label>
                            <input type="file" name="image" accept="image/*" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 focus:border-indigo-500 focus:outline-none shadow-sm bg-white text-gray-900 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:bg-indigo-500 file:text-white file:font-semibold file:border-0 file:cursor-pointer hover:file:bg-indigo-600 transition" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-indigo-900 mb-2">Alt Text</label>
                            <input type="text" name="alt" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 focus:border-indigo-500 focus:outline-none shadow-sm bg-white text-gray-900 transition" placeholder="Describe the image for accessibility">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-indigo-900 mb-2">Caption (Optional)</label>
                            <input type="text" name="caption" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 focus:border-indigo-500 focus:outline-none shadow-sm bg-white text-gray-900 transition" placeholder="Display caption for this image">
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 hover:from-indigo-600 hover:via-cyan-600 hover:to-emerald-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-indigo-400/40 hover:shadow-indigo-400/60 hover:-translate-y-0.5 transition duration-200">
                            Upload Image
                        </button>
                    </form>
                </div>

                <!-- Images Gallery -->
                <div class="bg-gradient-to-b from-indigo-50/80 via-white/90 to-cyan-50/70 rounded-2xl shadow-lg border border-white/80 p-8 backdrop-blur-sm">
                    <h2 class="text-2xl font-extrabold text-indigo-900 mb-6">Images ({{ count($project->images) }})</h2>

                    @if(count($project->images) > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($project->images as $img)
                        <div class="group relative rounded-xl overflow-hidden bg-gradient-to-br from-gray-100 to-gray-50 shadow-md hover:shadow-lg transition border border-gray-200">
                            <img src="{{ asset($img->public_path) }}" alt="{{ $img->alt }}" class="w-full h-40 object-cover group-hover:scale-105 transition duration-300">

                            <!-- Info Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col justify-end p-3">
                                <p class="text-white text-xs font-bold line-clamp-1">{{ $img->caption ?? $img->alt ?? 'Untitled' }}</p>
                                <p class="text-white/70 text-xs">Position: {{ $img->position }}</p>
                            </div>

                            <!-- Delete Button -->
                            <form action="{{ route('admin.images.destroy', $img->id) }}" method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition" onsubmit="return confirm('Delete this image?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white p-1.5 rounded-lg shadow-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-12">
                        <p class="text-gray-600 text-sm">No images uploaded yet. Upload your first image above!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</body>
</html>
