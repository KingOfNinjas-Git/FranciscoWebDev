{{-- One blog post card. Used on the blog index and on the home page. Expects $post and optionally $delay (ms). --}}
@php $locale = app()->getLocale(); @endphp
<a href="{{ \App\Http\Middleware\SetLocale::postUrl($post->slug) }}" class="reveal glass glass-hover group flex flex-col overflow-hidden rounded-3xl" style="--d:{{ $delay ?? 0 }}ms">
    <div class="project-media">
        @if ($post->cover_path)
            @php $coverSrcset = $post->coverSrcset(); @endphp
            <img src="{{ $post->coverUrl() }}" @if ($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(min-width: 1024px) 380px, (min-width: 768px) 45vw, 92vw" @endif alt="{{ $post->cover_alt }}" @if (!empty($priority)) fetchpriority="high" @else loading="lazy" @endif decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center bg-gradient-to-br from-sky-500/20 to-violet-500/20 font-mono text-sm text-slate-400" aria-hidden="true">&lt;/&gt;</div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <p class="font-mono text-[11px] uppercase tracking-[0.2rem] text-slate-400">
            {{ $post->published_at->locale($locale)->translatedFormat('d M Y') }} &middot; {{ __('site.blog.min_read', ['min' => $post->readingMinutes()]) }}
        </p>
        <h2 class="mt-2 text-xl font-semibold text-white">{{ $post->localized('title') }}</h2>
        @if ($post->localized('excerpt'))
            <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-300">{{ $post->localized('excerpt') }}</p>
        @endif
        <div class="mt-auto flex flex-wrap gap-2 pt-4">
            @foreach (array_slice($post->tags ?? [], 0, 3) as $tag)
                <span class="chip">{{ $tag }}</span>
            @endforeach
        </div>
        <p class="mt-4 font-mono text-xs uppercase tracking-[0.2rem] text-sky-300 transition group-hover:text-white">{{ __('site.blog.read') }} &rarr;</p>
    </div>
</a>
