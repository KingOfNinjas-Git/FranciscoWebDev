@extends('admin.layout')

@section('title', 'Lead from ' . $lead->name)

@section('content')
@php
    $types = __('site.contact.types', [], 'en');
    $budgets = __('site.contact.budgets', [], 'en');
    $feats = __('site.contact.feat', [], 'en');
    $subject = rawurlencode('Re: your website brief');
@endphp
<div class="mb-8 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.leads.index') }}" class="btn btn-ghost px-4 py-2 text-sm font-medium">&larr; Back to leads</a>
    <form action="{{ route('admin.leads.destroy', $lead->id) }}" method="POST" data-confirm="Delete the brief from {{ $lead->name }}? This cannot be undone.">
        @csrf
        @method('DELETE')
        <button class="btn btn-ghost px-4 py-2 text-sm font-medium text-rose-300 hover:!border-rose-400/60">Delete lead</button>
    </form>
</div>

<div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
    <section class="glass rounded-3xl p-6 md:p-8">
        <p class="font-mono text-xs uppercase tracking-[0.2rem] text-slate-400">{{ $lead->created_at->format('d M Y H:i') }} &middot; {{ strtoupper($lead->locale) }}</p>
        <h1 class="mt-2 text-3xl font-bold text-white">{{ $lead->name }}</h1>
        <p class="mt-1"><a href="mailto:{{ $lead->email }}?subject={{ $subject }}" class="text-sky-300 hover:text-white">{{ $lead->email }}</a></p>

        @unless ($lead->mail_sent)
            <p role="alert" class="mt-5 rounded-2xl border border-rose-400/40 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">The notification email to you did not go out{{ $lead->mail_error ? ' (' . $lead->mail_error . ')' : '' }}. This page is the only copy, so reply from here.</p>
        @endunless

        <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-400">Project type</dt><dd class="mt-1 text-slate-100">{{ $types[$lead->project_type] ?? $lead->project_type }}</dd></div>
            <div><dt class="text-slate-400">Budget</dt><dd class="mt-1 text-slate-100">{{ $lead->budget ? ($budgets[$lead->budget] ?? $lead->budget) : '-' }}</dd></div>
            <div><dt class="text-slate-400">Deadline</dt><dd class="mt-1 text-slate-100">{{ $lead->deadline ?: '-' }}</dd></div>
            <div><dt class="text-slate-400">Features</dt><dd class="mt-1 text-slate-100">{{ $lead->features ? implode(', ', array_map(fn ($f) => $feats[$f] ?? $f, $lead->features)) : '-' }}</dd></div>
        </dl>

        <h2 class="mt-8 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Description</h2>
        <p class="mt-2 whitespace-pre-wrap leading-7 text-slate-200">{{ $lead->description }}</p>

        @if ($lead->style)
            <h2 class="mt-8 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Look and feel</h2>
            <p class="mt-2 whitespace-pre-wrap leading-7 text-slate-200">{{ $lead->style }}</p>
        @endif

        <a href="mailto:{{ $lead->email }}?subject={{ $subject }}" class="btn btn-primary mt-8 px-6 py-3 text-sm font-semibold">Reply by email</a>
    </section>

    <section class="glass h-fit rounded-3xl p-6 md:p-8">
        <h2 class="text-xl font-semibold text-white">Follow-up</h2>
        <form action="{{ route('admin.leads.update', $lead->id) }}" method="POST" class="mt-5 space-y-5">
            @csrf
            @method('PUT')
            <label class="field">
                <span>Status</span>
                <select name="status">
                    @foreach (\App\Models\Lead::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($lead->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span>Private notes</span>
                <textarea name="notes" data-safe rows="6" placeholder="Quote sent, call on Friday...">{{ old('notes', $lead->notes) }}</textarea>
            </label>
            <button class="btn btn-primary w-full px-6 py-3 text-sm font-semibold">Save</button>
        </form>
    </section>
</div>
@endsection
