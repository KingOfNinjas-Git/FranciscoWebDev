{{-- The big card for the newest post, on the first page of the blog. Expects $post. --}}
@php $locale = app()->getLocale(); @endphp
<a href="{{ \App\Http\Middleware\SetLocale::postUrl($post->slug) }}" class="reveal glass glass-hover blog-featured group mb-8 grid overflow-hidden rounded-3xl lg:grid-cols-[1.55fr_1fr]">
    <div class="relative aspect-[16/10] overflow-hidden bg-slate-950/60 lg:aspect-auto lg:min-h-[20rem]">
        @if ($post->cover_path)
            @php $coverSrcset = $post->coverSrcset(); $cs = $post->coverSize(); @endphp
            <img src="{{ $post->coverUrl() }}" @if ($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(min-width: 1152px) 600px, (min-width: 1024px) 52vw, 92vw" @endif @if ($cs) width="{{ $cs[0] }}" height="{{ $cs[1] }}" @endif alt="{{ $post->cover_alt }}" fetchpriority="high" decoding="async" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-sky-500/20 to-violet-500/20 font-mono text-slate-400" aria-hidden="true">&lt;/&gt;</div>
        @endif
        <span class="blog-featured-badge">{{ __('site.blog.featured') }}</span>
    </div>
    <div class="flex flex-col justify-center p-6 sm:p-8 lg:p-10">
        <p class="text-sm text-slate-400">
            {{ $post->published_at->locale($locale)->translatedFormat('d M Y') }} &middot; {{ __('site.blog.min_read', ['min' => $post->readingMinutes()]) }}
        </p>
        <h2 class="mt-3 text-2xl font-bold leading-tight tracking-tight text-white sm:text-3xl lg:text-4xl">{{ $post->localized('title') }}</h2>
        @if ($post->localized('excerpt'))
            <p class="mt-4 line-clamp-4 leading-7 text-slate-300">{{ $post->localized('excerpt') }}</p>
        @endif
        @if ($post->tags)
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach (array_slice($post->tags, 0, 3) as $tag)
                    <span class="chip">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
        <p class="mt-7"><span class="btn btn-primary px-6 py-3 text-sm font-semibold">{{ __('site.blog.read') }}</span></p>
    </div>
</a>
