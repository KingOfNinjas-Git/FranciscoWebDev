<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

/** The admin pages for client quotes: add, edit, publish and remove. */
class TestimonialAdminController extends Controller
{
    public function index()
    {
        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::orderBy('position')->orderBy('id')->get(),
            'contactEmail' => config('portfolio.contact_email'),
        ]);
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $testimonial = Testimonial::create($this->data($request));

        return redirect()->route('admin.testimonials.index')->with('success', $testimonial->published ? 'Quote saved and shown on the site.' : 'Quote saved. It is not shown on the site until you publish it.');
    }

    public function edit(int $id)
    {
        return view('admin.testimonials.edit', ['testimonial' => Testimonial::findOrFail($id)]);
    }

    public function update(Request $request, int $id)
    {
        Testimonial::findOrFail($id)->update($this->data($request));

        return redirect()->route('admin.testimonials.index')->with('success', 'Quote updated.');
    }

    public function destroy(int $id)
    {
        Testimonial::findOrFail($id)->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Quote deleted.');
    }

    /** @return array<string, mixed> */
    private function data(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'role' => 'nullable|string|max:160',
            'quote' => 'required|string|max:1200',
            'source_url' => 'nullable|url|max:255',
            'position' => 'nullable|integer|min:0|max:9999',
            'translations.*.quote' => 'nullable|string|max:1200',
        ]);

        $translations = [];
        foreach (Testimonial::TRANSLATION_LOCALES as $locale) {
            $text = trim((string) $request->input("translations.$locale.quote"));
            if ($text !== '') {
                $translations[$locale]['quote'] = $text;
            }
        }

        return [
            'name' => $validated['name'],
            'role' => $validated['role'] ?? null,
            'quote' => $validated['quote'],
            'source_url' => $validated['source_url'] ?? null,
            'position' => (int) ($validated['position'] ?? 0),
            'published' => $request->boolean('published'),
            'translations' => $translations ?: null,
        ];
    }
}
