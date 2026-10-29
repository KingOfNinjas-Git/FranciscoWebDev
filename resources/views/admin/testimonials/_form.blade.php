@php
    $editing = isset($testimonial);
    $tr = $editing ? ($testimonial->translations ?? []) : [];
@endphp
<form action="{{ $editing ? route('admin.testimonials.update', $testimonial->id) : route('admin.testimonials.store') }}" method="POST" class="space-y-5">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="grid gap-5 md:grid-cols-2">
        <label class="field">
            <span>Name *</span>
            <input type="text" name="name" value="{{ old('name', $editing ? $testimonial->name : '') }}" maxlength="120" required>
        </label>
        <label class="field">
            <span>Role and business</span>
            <input type="text" name="role" value="{{ old('role', $editing ? $testimonial->role : '') }}" maxlength="160" placeholder="Owner, Padaria Central">
        </label>
    </div>

    <label class="field">
        <span>Quote *</span>
        <textarea name="quote" data-safe rows="5" maxlength="1200" required placeholder="Their own words, exactly as they sent them.">{{ old('quote', $editing ? $testimonial->quote : '') }}</textarea>
    </label>

    <div class="grid gap-5 md:grid-cols-2">
        <label class="field">
            <span>Quote in Portuguese (optional)</span>
            <textarea name="translations[pt][quote]" data-safe rows="4" maxlength="1200" placeholder="Leave empty to show the original">{{ old('translations.pt.quote', $tr['pt']['quote'] ?? '') }}</textarea>
        </label>
        <label class="field">
            <span>Quote in Spanish (optional)</span>
            <textarea name="translations[es][quote]" data-safe rows="4" maxlength="1200" placeholder="Leave empty to show the original">{{ old('translations.es.quote', $tr['es']['quote'] ?? '') }}</textarea>
        </label>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <label class="field">
            <span>Where it can be checked (optional link)</span>
            <input type="url" name="source_url" value="{{ old('source_url', $editing ? $testimonial->source_url : '') }}" maxlength="255" placeholder="https://www.linkedin.com/...">
        </label>
        <label class="field">
            <span>Order (smallest first)</span>
            <input type="number" name="position" min="0" max="9999" value="{{ old('position', $editing ? $testimonial->position : 0) }}">
        </label>
    </div>

    <label class="flex items-center gap-3 text-sm text-slate-200">
        <input type="checkbox" name="published" value="1" @checked(old('published', $editing ? $testimonial->published : false)) class="h-5 w-5 rounded">
        Show this quote on the site (only tick this when the person agreed to it)
    </label>

    <button type="submit" class="btn btn-primary w-full px-4 py-3 text-sm font-semibold">{{ $editing ? 'Save quote' : 'Add quote' }}</button>
</form>
