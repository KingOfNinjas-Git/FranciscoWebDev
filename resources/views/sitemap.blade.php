{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($locales as $locale)
    <url>
        <loc>{{ App\Http\Middleware\SetLocale::homeUrl($locale) }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
@foreach ($locales as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt }}" href="{{ App\Http\Middleware\SetLocale::homeUrl($alt) }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ App\Http\Middleware\SetLocale::homeUrl('en') }}"/>
    </url>
@endforeach
</urlset>
