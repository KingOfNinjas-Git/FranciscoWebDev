@extends('admin.layout')

@section('title', 'Visits')

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="section-title">Visits</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-400">Anonymous page views and button clicks. No cookies, IP addresses or personal data are stored, and robots, your own admin visits and visitors who send "Do Not Track" are left out, so real numbers are a little higher.</p>
    </div>
    <div class="flex flex-col items-start gap-3 sm:items-end">
    <nav class="flex gap-2" aria-label="Time range">
        @foreach ([7, 30, 90] as $range)
            <a href="{{ route('admin.stats', ['days' => $range]) }}" @if ($days === $range) aria-current="page" @endif class="chip {{ $days === $range ? '!border-sky-400 !text-white' : 'hover:border-sky-400' }}">{{ $range }} days</a>
        @endforeach
    </nav>
    <form action="{{ route('admin.stats.report') }}" method="POST">
        @csrf
        <button class="btn btn-ghost px-4 py-2 text-xs font-medium">Email me the weekly report now</button>
    </form>
    </div>
</div>

@if ($missing)
    <div class="glass rounded-3xl p-10 text-center text-slate-400">The statistics table has not been created yet. It appears after the next deploy.</div>
@else
    @php
        $max = max(1, $report['series']->max('views'));
        $events = \App\Services\Stats::EVENTS;
        $rate = $report['views'] > 0 ? round($report['leads'] / $report['views'] * 100, 1) : 0;
        $label = fn (string $path) => $path === '/' ? 'Home (English)' : (in_array($path, ['/pt', '/es'], true) ? 'Home (' . strtoupper(ltrim($path, '/')) . ')' : $path);
    @endphp

    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        <div class="glass rounded-3xl p-6"><p class="font-mono text-xs uppercase tracking-[0.2rem] text-slate-400">Page views</p><p class="mt-2 text-4xl font-bold text-white">{{ number_format($report['views']) }}</p></div>
        <div class="glass rounded-3xl p-6"><p class="font-mono text-xs uppercase tracking-[0.2rem] text-slate-400">Briefs sent</p><p class="mt-2 text-4xl font-bold text-white">{{ $report['leads'] }}</p></div>
        <div class="glass rounded-3xl p-6"><p class="font-mono text-xs uppercase tracking-[0.2rem] text-slate-400">Views per brief</p><p class="mt-2 text-4xl font-bold text-white">{{ $report['leads'] > 0 ? number_format($report['views'] / $report['leads'], 0) : '-' }}</p><p class="mt-1 text-xs text-slate-400">{{ $rate }}% of views end in a brief</p></div>
    </div>

    <section class="glass mb-8 rounded-3xl p-6" aria-labelledby="chart-title">
        <h2 id="chart-title" class="mb-4 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Views per day</h2>
        <div class="flex h-40 items-end gap-[2px]" role="img" aria-label="Bar chart of daily page views over the last {{ $days }} days. Busiest day: {{ $report['series']->max('views') }} views.">
            @foreach ($report['series'] as $point)
                <div class="group relative flex-1" style="height: 100%;">
                    <div class="absolute inset-x-0 bottom-0 rounded-t bg-sky-400/70 transition group-hover:bg-sky-300" style="height: {{ $point['views'] > 0 ? max(2, round($point['views'] / $max * 100)) : 0 }}%;" title="{{ $point['day'] }}: {{ $point['views'] }}"></div>
                </div>
            @endforeach
        </div>
        <div class="mt-2 flex justify-between text-xs text-slate-400"><span>{{ $report['series']->first()['day'] }}</span><span>{{ $report['series']->last()['day'] }}</span></div>
    </section>

    <div class="grid gap-8 lg:grid-cols-2">
        @foreach ([['Top pages', $report['pages']], ['Projects', $report['projects']], ['Blog posts', $report['posts']], ['Where visitors come from', $report['referrers']]] as [$title, $rows])
            <section class="glass rounded-3xl p-6">
                <h2 class="mb-4 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">{{ $title }}</h2>
                @if ($rows->isEmpty())
                    <p class="text-sm text-slate-400">Nothing yet.</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach ($rows as $name => $total)
                            <li class="flex items-center justify-between gap-4"><span class="truncate text-slate-200">{{ $title === 'Top pages' ? $label($name) : $name }}</span><span class="font-mono text-slate-400">{{ number_format($total) }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach

        <section class="glass rounded-3xl p-6">
            <h2 class="mb-4 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Language of the page</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($report['languages'] as $code => $total)
                    <li class="flex items-center justify-between gap-4"><span class="text-slate-200">{{ strtoupper($code) }}</span><span class="font-mono text-slate-400">{{ number_format($total) }}</span></li>
                @endforeach
            </ul>
        </section>

        <section class="glass rounded-3xl p-6">
            <h2 class="mb-4 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Clicks and actions</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($events as $key => $text)
                    <li class="flex items-center justify-between gap-4"><span class="text-slate-200">{{ $text }}</span><span class="font-mono text-slate-400">{{ number_format($report['events'][$key] ?? 0) }}</span></li>
                @endforeach
            </ul>
        </section>
    </div>
@endif
@endsection
