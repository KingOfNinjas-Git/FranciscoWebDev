@extends('admin.layout')

@section('title', 'Blog posts')

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="section-title">Blog posts</h1>
        <p class="mt-2 text-sm text-slate-400">{{ $posts->count() }} {{ \Illuminate\Support\Str::plural('post', $posts->count()) }}. Drafts are only visible to you.</p>
    </div>
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary px-6 py-3 text-sm font-semibold">+ New post</a>
</div>

@if ($posts->isEmpty())
    <div class="glass rounded-3xl p-10 text-center text-slate-400">
        No posts yet. Start one with <strong class="text-slate-200">New post</strong> and let the AI draft the first version.
    </div>
@else
    <div class="glass overflow-hidden rounded-3xl">
        <ul class="divide-y divide-white/10">
            @foreach ($posts as $post)
                @php
                    $live = $post->isPublished();
                    $scheduled = $post->status === 'published' && !$live;
                @endphp
                <li class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 font-mono text-[10px] uppercase tracking-widest {{ $live ? 'bg-emerald-400/15 text-emerald-300' : ($scheduled ? 'bg-amber-400/15 text-amber-300' : 'bg-white/10 text-slate-300') }}">{{ $live ? 'Published' : ($scheduled ? 'Scheduled' : 'Draft') }}</span>
                            @if ($post->ai_assisted)<span class="rounded-full bg-violet-400/15 px-2.5 py-0.5 font-mono text-[10px] uppercase tracking-widest text-violet-300">AI-assisted</span>@endif
                        </div>
                        <h2 class="mt-2 truncate text-lg font-semibold text-white">{{ $post->title }}</h2>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $post->published_at ? $post->published_at->format('d M Y H:i') : 'Updated ' . $post->updated_at->format('d M Y') }}
                            &middot;
                            @foreach (\App\Models\Post::TRANSLATION_LOCALES as $loc)
                                <span class="{{ $post->hasTranslation($loc) ? 'text-emerald-300' : 'text-slate-400' }}">{{ strtoupper($loc) }} {!! $post->hasTranslation($loc) ? '&#10003;' : '&ndash;' !!}</span>
                            @endforeach
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <a href="{{ \App\Http\Middleware\SetLocale::postUrl($post->slug, 'en') }}" target="_blank" class="btn btn-ghost px-4 py-2 text-sm font-medium">{{ $live ? 'View' : 'Preview' }}</a>
                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-primary px-4 py-2 text-sm font-semibold">Edit</a>
                        <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" data-confirm="Delete &quot;{{ $post->title }}&quot;? This cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-ghost px-4 py-2 text-sm font-medium text-rose-300 hover:!border-rose-400/60" aria-label="Delete {{ $post->title }}">Delete</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
