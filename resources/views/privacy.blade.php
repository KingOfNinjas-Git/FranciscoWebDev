@extends('layouts.public', [
    'canonical' => \App\Http\Middleware\SetLocale::privacyUrl(),
    'alternates' => collect(\App\Http\Middleware\SetLocale::LOCALES)->mapWithKeys(fn ($l) => [$l => \App\Http\Middleware\SetLocale::privacyUrl($l)])->all(),
])

@section('title', __('site.privacy.meta_title'))
@section('description', __('site.privacy.meta_description'))

@section('content')
@php $email = config('portfolio.contact_email'); @endphp
<article class="mx-auto max-w-3xl">
    <header class="reveal mb-10">
        <p class="eyebrow">{{ __('site.privacy.eyebrow') }}</p>
        <h1 class="section-title">{{ __('site.privacy.title') }}</h1>
        <p class="mt-3 text-sm text-slate-400">{{ __('site.privacy.updated') }}</p>
    </header>

    <div class="space-y-8">
        @foreach (__('site.privacy.sections') as $section)
            <section class="reveal glass rounded-3xl p-6 md:p-8" aria-labelledby="priv-{{ $loop->index }}">
                <h2 id="priv-{{ $loop->index }}" class="text-xl font-semibold text-white">{{ $section['title'] }}</h2>
                @foreach ($section['body'] as $paragraph)
                    <p class="mt-3 leading-7 text-slate-300">
                        {!! str_replace(':email', '<a href="mailto:' . e($email) . '" class="text-sky-300 underline underline-offset-2 hover:text-white">' . e($email) . '</a>', e($paragraph)) !!}
                    </p>
                @endforeach
                @if (!empty($section['items']))
                    <ul class="mt-3 list-disc space-y-2 pl-6 leading-7 text-slate-300 marker:text-sky-400">
                        @foreach ($section['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach
    </div>
</article>
@endsection
