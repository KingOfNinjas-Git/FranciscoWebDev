<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectAdminController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        return view('admin.projects.index', compact('projects'));
    }

    public function edit($id)
    {
        $project = Project::with('images')->findOrFail($id);
        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'technologies' => 'nullable|string',
            'live_url' => 'nullable|url',
            'repo_url' => 'nullable|url',
            'notes' => 'nullable|string'
        ]);

        $techInput = $request->input('technologies');
        $techs = [];
        if (!empty($techInput)) {
            // Accept comma-separated or JSON array
            if (Str::startsWith($techInput, '[')) {
                $decoded = json_decode($techInput, true);
                if (is_array($decoded)) $techs = $decoded;
            } else {
                $techs = array_values(array_filter(array_map('trim', explode(',', $techInput))));
            }
        }

        $project->title = $request->input('title');
        $project->slug = Str::slug($project->title);
        $project->description = $request->input('description');
        $project->technologies = $techs;
        // Only update optional fields if present in the request (avoid clearing them when inputs are removed)
        if ($request->has('live_url')) {
            $project->live_url = $request->input('live_url');
        }
        if ($request->has('repo_url')) {
            $project->repo_url = $request->input('repo_url');
        }
        if ($request->has('notes')) {
            $project->notes = $request->input('notes');
        }
        $project->save();

        return redirect()->back()->with('success', 'Project updated.');
    }

    public function storeImage(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'image' => 'required|image|max:5120',
            'alt' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
        ]);

        $file = $request->file('image');

        // Save directly into public folder so asset() works reliably on all platforms
        $slug = $project->slug ?? $project->id;
        $pubDir = public_path('imgs/projects/' . $slug);
        if (!is_dir($pubDir)) {
            mkdir($pubDir, 0755, true);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::slug($originalName) . '.' . $extension;
        $file->move($pubDir, $filename);

        $publicPath = 'imgs/projects/' . $slug . '/' . $filename;

        $position = $project->images()->max('position');
        $position = is_null($position) ? 0 : $position + 1;

        ProjectImage::create([
            'project_id' => $project->id,
            'path' => $publicPath,
            'alt' => $request->input('alt'),
            'caption' => $request->input('caption'),
            'position' => $position,
        ]);

        return redirect()->back()->with('success', 'Image uploaded.');
    }

    public function destroyImage($id)
    {
        $img = ProjectImage::findOrFail($id);
        // delete file from public folder if exists
        $pubPath = public_path($img->path);
        if (file_exists($pubPath)) {
            @unlink($pubPath);
        }
        $img->delete();
        return redirect()->back()->with('success', 'Image deleted.');
    }
}
