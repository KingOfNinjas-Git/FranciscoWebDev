<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Services\ExistingImagesOptimizer;
use App\Services\ImageVariants;
use App\Services\ImageOptimizer;
use App\Models\ProjectImage;
use Illuminate\Support\Str;

class ProjectAdminController extends Controller
{
    public function index()
    {
        $projects = Project::with('images')->orderByDesc('created_at')->get();
        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function store(Request $request)
    {
        $this->validateProject($request);

        $project = new Project();
        $project->title = $request->input('title');
        $project->slug = $this->uniqueSlug($project->title);
        $this->fill($project, $request);
        $project->save();

        return redirect()->route('admin.projects.edit', $project->id)->with('success', 'Project created. You can now add images.');
    }

    public function edit($id)
    {
        $project = Project::with('images')->findOrFail($id);
        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $this->validateProject($request);

        // The slug is kept stable so public URLs and image folders don't change on rename
        $project->title = $request->input('title');
        $this->fill($project, $request);
        $project->save();

        $note = '';
        if (\App\Services\IndexNow::enabled()) {
            \App\Services\IndexNow::pingLater(\App\Services\IndexNow::projectUrls($project->slug));
            $note = ' Bing and other IndexNow search engines were told about it.';
        }

        return redirect()->back()->with('success', 'Project saved.' . $note);
    }

    public function destroy($id)
    {
        $project = Project::with('images')->findOrFail($id);

        foreach ($project->images as $img) {
            $this->deleteImageFile($img);
        }
        $project->images()->delete();
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted.');
    }

    public function storeImage(Request $request, $id, ImageOptimizer $optimizer)
    {
        $project = Project::findOrFail($id);

        $request->validate(array_merge([
            'image' => 'required|image|max:5120',
        ], $this->imageTextRules()));

        // Save directly into public folder so asset() works reliably on all platforms
        $slug = $project->slug ?? $project->id;
        $pubDir = public_path('imgs/projects/' . $slug);
        if (!is_dir($pubDir)) {
            mkdir($pubDir, 0755, true);
        }

        // Resized + converted to WebP when the server supports it
        $filename = $optimizer->store($request->file('image'), $pubDir);
        ImageVariants::make('imgs/projects/' . $slug . '/' . $filename); // 480 / 960 px copies for phones and thumbnails

        $position = $project->images()->max('position');
        $position = is_null($position) ? 0 : $position + 1;

        ProjectImage::create([
            'project_id' => $project->id,
            'path' => 'imgs/projects/' . $slug . '/' . $filename,
            'alt' => $request->input('alt'),
            'caption' => $request->input('caption'),
            'translations' => $this->parseImageTranslations($request),
            'position' => $position,
        ]);

        return redirect()->back()->with('success', 'Image uploaded.');
    }

    public function updateImage(Request $request, $id)
    {
        $img = ProjectImage::findOrFail($id);

        $request->validate($this->imageTextRules());

        $img->alt = $request->input('alt');
        $img->caption = $request->input('caption');
        $img->translations = $this->parseImageTranslations($request);
        $img->save();

        return redirect()->back()->with('success', 'Image text saved.');
    }

    // Receives the new left-to-right order as a list of image ids
    public function reorderImages(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $ids = collect($request->input('order', []))->map(fn ($v) => (int) $v)->values();
        $owned = $project->images()->pluck('id');

        if ($ids->diff($owned)->isNotEmpty()) {
            return response()->json(['message' => 'Invalid image list'], 422);
        }

        foreach ($ids as $position => $imageId) {
            ProjectImage::where('id', $imageId)->update(['position' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    // Shrinks images uploaded before optimisation existed (for servers without command-line access)
    public function optimizeImages(ExistingImagesOptimizer $optimizer, ImageOptimizer $images)
    {
        if (!$images->supported()) {
            return redirect()->route('admin.projects.index')
                ->withErrors(['optimize' => 'The server\'s PHP has no WebP support, so existing images cannot be converted.']);
        }

        $result = $optimizer->run();

        $parts = [];
        if ($result['converted'] > 0) {
            $parts[] = "compressed {$result['converted']} " . \Illuminate\Support\Str::plural('image', $result['converted'])
                . ', saving ' . round($result['saved_bytes'] / 1048576, 1) . ' MB';
        }
        if ($result['variants'] > 0) {
            $parts[] = "made smaller phone-size versions of {$result['variants']} " . \Illuminate\Support\Str::plural('image', $result['variants']);
        }

        $message = $parts ? ucfirst(implode(' and ', $parts)) . '.' : 'Nothing to optimize: your images are already small.';

        return redirect()->route('admin.projects.index')->with('success', $message);
    }

    public function coverImage($id)
    {
        $img = ProjectImage::findOrFail($id);

        // The first image (lowest position) is the project's cover
        $min = ProjectImage::where('project_id', $img->project_id)->min('position');
        $img->position = ($min ?? 0) - 1;
        $img->save();

        return redirect()->back()->with('success', 'Cover image updated.');
    }

    public function destroyImage($id)
    {
        $img = ProjectImage::findOrFail($id);
        $this->deleteImageFile($img);
        $img->delete();
        return redirect()->back()->with('success', 'Image deleted.');
    }

    private function imageTextRules(): array
    {
        return [
            'alt' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
            'translations.*.alt' => 'nullable|string|max:255',
            'translations.*.caption' => 'nullable|string|max:500',
        ];
    }

    private function parseImageTranslations(Request $request): ?array
    {
        $out = [];
        foreach (Project::TRANSLATION_LOCALES as $locale) {
            foreach (['alt', 'caption'] as $field) {
                $value = trim((string) $request->input("translations.$locale.$field"));
                if ($value !== '') $out[$locale][$field] = $value;
            }
        }
        return $out ?: null;
    }

    private function validateProject(Request $request): void
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'technologies' => 'nullable|string',
            'live_url' => 'nullable|url',
            'repo_url' => 'nullable|url',
            'notes' => 'nullable|string',
            'created_at' => 'nullable|date',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.notes' => 'nullable|string',
        ]);
    }

    // Applies the shared (non-title) fields from the request
    private function fill(Project $project, Request $request): void
    {
        $project->description = $request->input('description');
        $project->notes = $request->input('notes');
        $project->live_url = $request->input('live_url');
        $project->repo_url = $request->input('repo_url');
        $project->technologies = $this->parseTechnologies($request->input('technologies'));
        $project->translations = $this->parseTranslations($request);

        if ($request->filled('created_at')) {
            $project->created_at = $request->input('created_at');
        }
    }

    // Keeps only non-empty Portuguese/Spanish values; empty ones fall back to English on the site
    private function parseTranslations(Request $request): ?array
    {
        $out = [];
        foreach (Project::TRANSLATION_LOCALES as $locale) {
            foreach (Project::TRANSLATABLE as $field) {
                $value = trim((string) $request->input("translations.$locale.$field"));
                if ($value !== '') $out[$locale][$field] = $value;
            }
        }
        return $out ?: null;
    }

    // Accepts a comma-separated string or a JSON array
    private function parseTechnologies(?string $input): array
    {
        if (empty($input)) return [];

        if (Str::startsWith($input, '[')) {
            $decoded = json_decode($input, true);
            return is_array($decoded) ? $decoded : [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $input))));
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: (string) time();
        $slug = $base;
        for ($i = 2; Project::where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }
        return $slug;
    }

    private function deleteImageFile(ProjectImage $img): void
    {
        $path = public_path($img->public_path);
        if (is_file($path)) {
            @unlink($path);
        }
        ImageVariants::delete($img->public_path);
    }
}
