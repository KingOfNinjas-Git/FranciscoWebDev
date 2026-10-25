@extends('admin.layout')

@php
    $editing = (bool) $post;
    $tr = $editing ? ($post->translations ?? []) : [];
    $langs = ['en' => 'English', 'pt' => 'Português', 'es' => 'Español'];
    $publishedAt = old('published_at', $editing && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '');
@endphp

@section('title', $editing ? $post->title : 'New post')

@section('content')
<div class="mb-8 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.posts.index') }}" class="btn btn-ghost px-4 py-2 text-sm font-medium">&larr; Back to posts</a>
    @if ($editing)
        <a href="{{ \App\Http\Middleware\SetLocale::postUrl($post->slug, 'en') }}" target="_blank" class="btn btn-ghost px-4 py-2 text-sm font-medium">{{ $post->isPublished() ? 'View live post' : 'Preview as visitor' }} &nearr;</a>
    @endif
</div>

{{-- ============ AI drafting panel ============ --}}
<section id="ai-panel" class="glass mb-8 rounded-3xl p-6 md:p-8"
         data-context-url="{{ route('admin.ai.context') }}"
         data-draft-url="{{ route('admin.ai.draft') }}"
         data-translate-url="{{ route('admin.ai.translate') }}"
         data-status-url="{{ url('/admin/ai') }}">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-white">&#10024; Draft with AI</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-400">Before writing, the AI reads your live website, your projects and your existing posts, so it knows what's already published and focuses on what's new. Nothing is saved or published automatically: it fills the form below for you to review.</p>
        </div>
        <span class="rounded-full border border-white/15 px-3 py-1 font-mono text-[11px] uppercase tracking-widest {{ $aiReady ? 'text-emerald-300' : 'text-amber-300' }}">{{ $aiReady ? 'AI ready: ' . config('ai.provider') : 'API key missing' }}</span>
    </div>

    @unless ($aiReady)
        <p class="mt-4 rounded-2xl border border-amber-400/40 bg-amber-400/10 px-4 py-3 text-sm text-amber-200">Add <code>GEMINI_API_KEY=...</code> (or <code>ANTHROPIC_API_KEY=...</code>) to the server's <code>.env</code> file to turn this on. You can still write posts by hand.</p>
    @endunless

    <div class="mt-6 grid gap-5 md:grid-cols-2">
        <label class="field md:col-span-2">
            <span>What should the post be about?</span>
            <textarea id="ai-topic" rows="3" placeholder="e.g. How I made the project gallery on my portfolio accessible with keyboard support and screen reader announcements"></textarea>
        </label>
        <label class="field">
            <span>Post type</span>
            <select id="ai-type">
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ \Illuminate\Support\Str::before($label, ':') }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">
            <span>Related project (optional)</span>
            <select id="ai-project">
                <option value="">None / general</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->slug }}">{{ $p->title }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">
            <span>Length</span>
            <select id="ai-length">
                @foreach ($lengths as $value => $l)
                    <option value="{{ $value }}" @selected($value === 'medium')>{{ $l['label'] }} (~{{ $l['words'] }} words)</option>
                @endforeach
            </select>
        </label>
        <label class="field">
            <span>Tone</span>
            <select id="ai-tone">
                @foreach ($tones as $value => $label)
                    <option value="{{ $value }}">{{ \Illuminate\Support\Str::before($label, ',') }}</option>
                @endforeach
            </select>
        </label>
        <label class="check-chip md:col-span-2">
            <input type="checkbox" id="ai-fresh" checked>
            <span>Prioritise what's new since my last published post</span>
        </label>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <button type="button" id="ai-generate" class="btn btn-primary px-6 py-3 text-sm font-semibold" @disabled(!$aiReady)>Generate draft</button>
        <button type="button" id="ai-context-btn" class="btn btn-ghost px-5 py-3 text-sm font-medium">See what the AI will read</button>
        <p id="ai-status" class="text-sm text-slate-400" role="status" aria-live="polite"></p>
    </div>

    <div id="ai-notes" class="mt-5 hidden rounded-2xl border border-amber-400/40 bg-amber-400/10 px-4 py-3 text-sm text-amber-100"></div>

    <details id="ai-context-box" class="mt-5 hidden rounded-2xl border border-white/10 bg-slate-950/40 p-4">
        <summary class="cursor-pointer text-sm font-medium text-sky-300">Context the AI reads before writing</summary>
        <p id="ai-context-stats" class="mt-3 font-mono text-xs text-slate-400"></p>
        <pre id="ai-context-text" class="mt-3 max-h-80 overflow-auto whitespace-pre-wrap text-xs leading-5 text-slate-300"></pre>
        <button type="button" id="ai-context-refresh" class="btn btn-ghost mt-3 px-4 py-2 text-xs font-medium">Re-read the live site</button>
    </details>
</section>

{{-- ============ The post form ============ --}}
<form id="post-form" action="{{ $editing ? route('admin.posts.update', $post->id) : route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-8 lg:grid-cols-[1.4fr_0.6fr]"
      data-preview-url="{{ route('admin.posts.preview') }}">
    @csrf
    @if ($editing) @method('PUT') @endif

    <section class="glass space-y-6 rounded-3xl p-6 md:p-8">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Content</h2>
            <div class="lang-switch flex items-center rounded-full p-1" role="tablist" aria-label="Language of the text">
                @foreach ($langs as $code => $label)
                    <button type="button" role="tab" data-tab="{{ $code }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" class="lang-btn rounded-full px-3 py-1 text-xs font-semibold {{ $loop->first ? 'is-active' : 'text-slate-400 hover:text-white' }}">{{ strtoupper($code) }}</button>
                @endforeach
            </div>
        </div>
        <p class="-mt-3 text-xs text-slate-500">English is the default and is shown whenever a translation is empty. The body is written in Markdown.</p>

        @foreach ($langs as $code => $label)
            @php
                $n = fn ($f) => $code === 'en' ? $f : "translations[$code][$f]";
                $o = fn ($f) => $code === 'en'
                    ? old($f, $editing ? $post->{$f} : '')
                    : old("translations.$code.$f", $tr[$code][$f] ?? '');
            @endphp
            <div data-panel="{{ $code }}" role="tabpanel" class="space-y-5 {{ $loop->first ? '' : 'hidden' }}">
                <label class="field">
                    <span>Title ({{ $label }}){{ $code === 'en' ? ' *' : '' }}</span>
                    <input type="text" id="f-{{ $code }}-title" name="{{ $n('title') }}" value="{{ $o('title') }}" maxlength="255" @if ($code === 'en') required @endif placeholder="{{ $code === 'en' ? '' : 'Leave empty to use the English title' }}">
                </label>
                <label class="field">
                    <span>Excerpt ({{ $label }})</span>
                    <textarea id="f-{{ $code }}-excerpt" name="{{ $n('excerpt') }}" rows="2" maxlength="600" placeholder="One or two sentences for the blog list and search results">{{ $o('excerpt') }}</textarea>
                </label>
                <div class="field">
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="f-{{ $code }}-body" class="field-label !mb-0">Body ({{ $label }}, Markdown){{ $code === 'en' ? ' *' : '' }}</label>
                        <button type="button" data-preview="{{ $code }}" class="rounded-full px-3 py-1 text-xs font-medium text-sky-300 hover:bg-white/10">Preview</button>
                    </div>
                    <textarea id="f-{{ $code }}-body" name="{{ $n('body') }}" rows="18" @if ($code === 'en') required @endif class="font-mono text-sm" placeholder="## A heading&#10;&#10;Write in Markdown...">{{ $o('body') }}</textarea>
                    <div id="preview-{{ $code }}" class="prose-blog mt-3 hidden rounded-2xl border border-white/10 bg-slate-950/40 p-5"></div>
                </div>
            </div>
        @endforeach

        <div class="border-t border-white/10 pt-5">
            <button type="button" id="ai-translate" class="btn btn-ghost px-5 py-2.5 text-sm font-medium" @disabled(!$aiReady)>&#10024; Translate English to Portuguese &amp; Spanish with AI</button>
            <p class="mt-2 text-xs text-slate-500">Overwrites the PT and ES fields with a fresh translation of the English text. Review it before saving.</p>
        </div>
    </section>

    <aside class="space-y-6 lg:sticky lg:top-6 lg:self-start">
        <section class="glass space-y-5 rounded-3xl p-6">
            <h2 class="font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Publishing</h2>
            <label class="field">
                <span>Status</span>
                <select name="status">
                    <option value="draft" @selected(old('status', $editing ? $post->status : 'draft') === 'draft')>Draft (only you can see it)</option>
                    <option value="published" @selected(old('status', $editing ? $post->status : 'draft') === 'published')>Published</option>
                </select>
            </label>
            <label class="field">
                <span>Publish date &amp; time</span>
                <input type="datetime-local" name="published_at" value="{{ $publishedAt }}">
                <small class="mt-1 block text-xs text-slate-500">Empty = now. A future date schedules the post.</small>
            </label>
            <label class="field">
                <span>Tags (comma-separated)</span>
                <input type="text" id="f-tags" name="tags" value="{{ old('tags', $editing && $post->tags ? implode(', ', $post->tags) : '') }}" placeholder="accessibility, laravel">
            </label>
            <label class="check-chip">
                <input type="checkbox" id="f-ai" name="ai_assisted" value="1" @checked(old('ai_assisted', $editing ? $post->ai_assisted : false))>
                <span>Show "drafted with AI assistance" note</span>
            </label>
        </section>

        <section class="glass space-y-5 rounded-3xl p-6">
            <h2 class="font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Cover image</h2>
            @if ($editing && $post->cover_path)
                <img src="{{ $post->coverUrl() }}" alt="" class="w-full rounded-2xl border border-white/10 object-cover">
            @endif
            <label class="field">
                <span>{{ $editing && $post->cover_path ? 'Replace image' : 'Upload image' }} (max 5 MB)</span>
                <input type="file" name="cover" accept="image/*">
            </label>
            <label class="field">
                <span>Image description (alt text)</span>
                <input type="text" name="cover_alt" value="{{ old('cover_alt', $editing ? $post->cover_alt : '') }}" maxlength="255">
            </label>
        </section>

        <button type="submit" class="btn btn-primary w-full px-4 py-3 text-sm font-semibold">{{ $editing ? 'Save post' : 'Create post' }}</button>

        @if ($editing)
            <button type="submit" form="delete-post" class="btn btn-ghost w-full px-4 py-3 text-sm font-medium text-rose-300 hover:!border-rose-400/60">Delete post</button>
        @endif
    </aside>
</form>

@if ($editing)
    <form id="delete-post" action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" data-confirm="Delete &quot;{{ $post->title }}&quot;? This cannot be undone.">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection
