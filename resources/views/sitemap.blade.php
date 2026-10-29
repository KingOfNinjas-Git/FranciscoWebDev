{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($locales as $locale)
    <url>
        <loc>{{ App\Http\Middleware\SetLocale::homeUrl($locale) }}</loc>
        <lastmod>{{ $lastmods['home'] }}</lastmod>
@foreach ($locales as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt }}" href="{{ App\Http\Middleware\SetLocale::homeUrl($alt) }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ App\Http\Middleware\SetLocale::homeUrl('en') }}"/>
    </url>
    <url>
        <loc>{{ App\Http\Middleware\SetLocale::blogUrl($locale) }}</loc>
        <lastmod>{{ $lastmods['blog'] }}</lastmod>
@foreach ($locales as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt }}" href="{{ App\Http\Middleware\SetLocale::blogUrl($alt) }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ App\Http\Middleware\SetLocale::blogUrl('en') }}"/>
    </url>
    <url>
        <loc>{{ App\Http\Middleware\SetLocale::privacyUrl($locale) }}</loc>
        <lastmod>{{ $lastmods['privacy'] }}</lastmod>
@foreach ($locales as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt }}" href="{{ App\Http\Middleware\SetLocale::privacyUrl($alt) }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ App\Http\Middleware\SetLocale::privacyUrl('en') }}"/>
    </url>
    <url>
        <loc>{{ App\Http\Middleware\SetLocale::accessibilityUrl($locale) }}</loc>
        <lastmod>{{ $lastmods['privacy'] }}</lastmod>
@foreach ($locales as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt }}" href="{{ App\Http\Middleware\SetLocale::accessibilityUrl($alt) }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ App\Http\Middleware\SetLocale::accessibilityUrl('en') }}"/>
    </url>
    <url>
        <loc>{{ App\Http\Middleware\SetLocale::labUrl($locale) }}</loc>
        <lastmod>{{ $lastmods['lab'] }}</lastmod>
@foreach ($locales as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt }}" href="{{ App\Http\Middleware\SetLocale::labUrl($alt) }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ App\Http\Middleware\SetLocale::labUrl('en') }}"/>
    </url>
@endforeach
@foreach ($projects as $project)
@foreach ($locales as $locale)
    <url>
        <loc>{{ App\Http\Middleware\SetLocale::projectUrl($project->slug, $locale) }}</loc>
        <lastmod>{{ $project->updated ?? $lastmods['home'] }}</lastmod>
@foreach ($locales as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt }}" href="{{ App\Http\Middleware\SetLocale::projectUrl($project->slug, $alt) }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ App\Http\Middleware\SetLocale::projectUrl($project->slug, 'en') }}"/>
    </url>
@endforeach
@endforeach
@foreach ($posts as $post)
@foreach ($locales as $locale)
    <url>
        <loc>{{ App\Http\Middleware\SetLocale::postUrl($post->slug, $locale) }}</loc>
        <lastmod>{{ $post->updated_at->toDateString() }}</lastmod>
@foreach ($locales as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt }}" href="{{ App\Http\Middleware\SetLocale::postUrl($post->slug, $alt) }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ App\Http\Middleware\SetLocale::postUrl($post->slug, 'en') }}"/>
    </url>
@endforeach
@endforeach
</urlset>
