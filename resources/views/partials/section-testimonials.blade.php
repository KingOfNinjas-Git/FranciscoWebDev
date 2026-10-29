{{-- Client quotes. The whole section is left out until at least one quote is published. --}}
@php $testimonials = $testimonials ?? collect(); @endphp
@if ($testimonials->isNotEmpty())
<section id="testimonials" class="section-anchor px-4 py-24" aria-labelledby="testimonials-title">
    <div class="mx-auto max-w-6xl">
        <header class="reveal mb-12">
            <p class="eyebrow">{{ __('site.testimonials.eyebrow') }}</p>
            <h2 id="testimonials-title" class="section-title">{{ __('site.testimonials.title') }}</h2>
        </header>

        @php
            $columns = match (true) {
                $testimonials->count() === 1 => 'mx-auto max-w-2xl',
                $testimonials->count() === 2 => 'md:grid-cols-2',
                default => 'md:grid-cols-2 lg:grid-cols-3',
            };
        @endphp
        <div class="grid gap-6 {{ $columns }}">
            @foreach ($testimonials as $t)
                <figure class="reveal glass flex flex-col rounded-3xl p-6 md:p-8" style="--d:{{ $loop->index * 90 }}ms">
                    <span class="font-serif text-5xl leading-none text-sky-300/70" aria-hidden="true">&ldquo;</span>
                    <blockquote class="mt-4 flex-1 text-lg leading-8 text-slate-200">{{ $t->localizedQuote() }}</blockquote>
                    <figcaption class="mt-6 border-t border-white/10 pt-4">
                        <p class="font-semibold text-white">{{ $t->name }}</p>
                        @if ($t->role)<p class="text-sm text-slate-400">{{ $t->role }}</p>@endif
                        @if ($t->source_url)
                            <a href="{{ $t->source_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-xs text-sky-300 underline underline-offset-2 hover:text-white">{{ __('site.testimonials.source') }} &nearr;</a>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif
