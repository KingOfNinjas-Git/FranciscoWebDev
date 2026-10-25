@extends('admin.layout')

@section('title', 'Leads')

@section('content')
<div class="mb-8">
    <p class="eyebrow">Admin</p>
    <h1 class="section-title">Leads</h1>
    <p class="mt-2 text-sm text-slate-400">Every brief sent through the contact form is saved here, even if the email never arrives.</p>
</div>

<nav class="mb-6 flex flex-wrap gap-2" aria-label="Filter leads by status">
    <a href="{{ route('admin.leads.index') }}" @if (! $status) aria-current="page" @endif class="chip {{ $status ? 'hover:border-sky-400' : '!border-sky-400 !text-white' }}">All &middot; {{ $counts->sum() }}</a>
    @foreach (\App\Models\Lead::STATUSES as $key => $label)
        <a href="{{ route('admin.leads.index', ['status' => $key]) }}" @if ($status === $key) aria-current="page" @endif class="chip {{ $status === $key ? '!border-sky-400 !text-white' : 'hover:border-sky-400' }}">{{ $label }} &middot; {{ $counts[$key] ?? 0 }}</a>
    @endforeach
</nav>

@if ($leads->isEmpty())
    <div class="glass rounded-3xl p-10 text-center text-slate-400">No leads here yet.</div>
@else
    <div class="glass overflow-hidden rounded-3xl">
        <ul class="divide-y divide-white/10">
            @foreach ($leads as $lead)
                @php
                    $pill = ['new' => 'bg-sky-400/15 text-sky-300', 'replied' => 'bg-violet-400/15 text-violet-300', 'won' => 'bg-emerald-400/15 text-emerald-300', 'lost' => 'bg-white/10 text-slate-400'][$lead->status] ?? 'bg-white/10 text-slate-300';
                @endphp
                <li>
                    <a href="{{ route('admin.leads.show', $lead->id) }}" class="flex flex-col gap-2 p-5 transition hover:bg-white/5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 font-mono text-[10px] uppercase tracking-widest {{ $pill }}">{{ \App\Models\Lead::STATUSES[$lead->status] ?? $lead->status }}</span>
                                @unless ($lead->mail_sent)<span class="rounded-full bg-rose-400/15 px-2.5 py-0.5 font-mono text-[10px] uppercase tracking-widest text-rose-300">Email not sent</span>@endunless
                            </div>
                            <p class="mt-2 truncate text-lg font-semibold text-white">{{ $lead->name }} <span class="text-sm font-normal text-slate-400">&middot; {{ $lead->email }}</span></p>
                            <p class="mt-1 truncate text-sm text-slate-400">{{ \Illuminate\Support\Str::limit($lead->description, 110) }}</p>
                        </div>
                        <div class="shrink-0 text-xs text-slate-400 sm:text-right">
                            <p>{{ $lead->created_at->format('d M Y H:i') }}</p>
                            <p class="mt-1">{{ __('site.contact.types', [], 'en')[$lead->project_type] ?? $lead->project_type }} &middot; {{ strtoupper($lead->locale) }}</p>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
