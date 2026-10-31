@if (isset($latestPosts) && $latestPosts->isNotEmpty())
<section id="blog" class="section-anchor px-4 py-24">
    <div class="mx-auto max-w-6xl">
        <header class="reveal mb-12 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">{{ __('site.blog.eyebrow') }}</p>
                <h2 class="section-title">{{ __('site.blog.latest_title') }}</h2>
            </div>
            <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}" class="btn btn-ghost px-5 py-2.5 text-sm font-medium">{{ __('site.blog.view_all') }}</a>
        </header>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestPosts as $post)
                @include('blog._card', ['post' => $post, 'delay' => $loop->index * 90])
            @endforeach
        </div>
    </div>
</section>
@endif
